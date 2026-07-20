<?php

use App\Models\Department;
use App\Models\KpiEvaluatorWeight;
use App\Models\KpiPeriod;
use App\Models\KpiPlan;
use App\Models\KpiPlanReview;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    foreach ([2, 3, 4] as $jobLevel) {
        foreach (['PENILAI_1' => 33, 'PENILAI_2' => 33, 'PENILAI_3' => 34] as $slot => $weight) {
            KpiEvaluatorWeight::create(['job_level' => $jobLevel, 'slot' => $slot, 'weight' => $weight]);
        }
    }
});

function makeStafDenganAtasanUntukApproval(): array
{
    $bagian = Department::create(['name' => 'Bagian Umum', 'type' => 'BAGIAN']);
    $seksi = Department::create(['name' => 'Seksi A', 'type' => 'SEKSI', 'parent_department_id' => $bagian->id]);
    $kasi = User::factory()->create(['job_level' => 3, 'department_id' => $seksi->id]);
    $staf = User::factory()->create(['job_level' => 4, 'department_id' => $seksi->id]);

    return compact('kasi', 'staf');
}

test('atasan pertama melihat bawahan dengan plan SUBMITTED di daftar approval', function () {
    $period = KpiPeriod::create(['month' => 7, 'year' => 2026, 'status' => 'DRAFT']);
    ['kasi' => $kasi, 'staf' => $staf] = makeStafDenganAtasanUntukApproval();
    KpiPlan::create(['user_id' => $staf->id, 'period_id' => $period->id, 'target_description' => 'A', 'weight' => 20, 'status' => 'SUBMITTED']);

    $response = $this->actingAs($kasi, 'sanctum')->getJson('/api/kpi/approvals')->assertOk();

    expect($response->json('data.*.id'))->toContain($staf->id);
});

test('bukan atasan pertama ditolak saat approve', function () {
    KpiPeriod::create(['month' => 7, 'year' => 2026, 'status' => 'DRAFT']);
    ['staf' => $staf] = makeStafDenganAtasanUntukApproval();
    $orangLain = User::factory()->create(['job_level' => 3]);

    $this->actingAs($orangLain, 'sanctum')->postJson("/api/kpi/approvals/{$staf->id}", ['action' => 'APPROVED'])
        ->assertStatus(403);
});

test('approve mengunci plan jadi APPROVED, revision_requested balik ke DRAFT', function () {
    $period = KpiPeriod::create(['month' => 7, 'year' => 2026, 'status' => 'DRAFT']);
    ['kasi' => $kasi, 'staf' => $staf] = makeStafDenganAtasanUntukApproval();
    $plan = KpiPlan::create(['user_id' => $staf->id, 'period_id' => $period->id, 'target_description' => 'A', 'weight' => 20, 'status' => 'SUBMITTED']);

    $this->actingAs($kasi, 'sanctum')->postJson("/api/kpi/approvals/{$staf->id}", ['action' => 'APPROVED'])->assertOk();
    expect($plan->fresh()->status)->toBe('APPROVED');

    KpiPlan::whereKey($plan->id)->update(['status' => 'SUBMITTED']);
    $this->actingAs($kasi, 'sanctum')->postJson("/api/kpi/approvals/{$staf->id}", [
        'action' => 'REVISION_REQUESTED', 'comment' => 'Perjelas target',
    ])->assertOk();
    expect($plan->fresh()->status)->toBe('DRAFT');

    expect(KpiPlanReview::where('user_id', $staf->id)->count())->toBe(2);
});

test('atasan pertama bisa minta bawahan tambah item rencana kerja baru, tanpa mengubah status plan', function () {
    $period = KpiPeriod::create(['month' => 7, 'year' => 2026, 'status' => 'DRAFT']);
    ['kasi' => $kasi, 'staf' => $staf] = makeStafDenganAtasanUntukApproval();
    $plan = KpiPlan::create(['user_id' => $staf->id, 'period_id' => $period->id, 'target_description' => 'A', 'weight' => 20, 'status' => 'APPROVED']);

    $this->actingAs($kasi, 'sanctum')->postJson("/api/kpi/approvals/{$staf->id}/request-task", [
        'comment' => 'Tolong tambahkan target rekonsiliasi bulanan',
    ])->assertCreated();

    $review = KpiPlanReview::where('user_id', $staf->id)->where('action', 'TASK_REQUESTED')->first();
    expect($review)->not->toBeNull()
        ->and($review->kpi_plan_id)->toBeNull()
        ->and($review->comment)->toBe('Tolong tambahkan target rekonsiliasi bulanan')
        ->and($plan->fresh()->status)->toBe('APPROVED');
});

test('bukan atasan pertama ditolak saat minta tambah tugas', function () {
    KpiPeriod::create(['month' => 7, 'year' => 2026, 'status' => 'DRAFT']);
    ['staf' => $staf] = makeStafDenganAtasanUntukApproval();
    $orangLain = User::factory()->create(['job_level' => 3]);

    $this->actingAs($orangLain, 'sanctum')->postJson("/api/kpi/approvals/{$staf->id}/request-task", [
        'comment' => 'Coba minta',
    ])->assertStatus(403);
});

test('approve ditolak kalau periode sudah bukan DRAFT', function () {
    $period = KpiPeriod::create(['month' => 7, 'year' => 2026, 'status' => 'EVALUATION']);
    ['kasi' => $kasi, 'staf' => $staf] = makeStafDenganAtasanUntukApproval();
    KpiPlan::create(['user_id' => $staf->id, 'period_id' => $period->id, 'target_description' => 'A', 'weight' => 20, 'status' => 'SUBMITTED']);

    $this->actingAs($kasi, 'sanctum')->postJson("/api/kpi/approvals/{$staf->id}", ['action' => 'APPROVED'])
        ->assertStatus(422);
});

test('request-task ditolak kalau periode sudah bukan DRAFT', function () {
    KpiPeriod::create(['month' => 7, 'year' => 2026, 'status' => 'EVALUATION']);
    ['kasi' => $kasi, 'staf' => $staf] = makeStafDenganAtasanUntukApproval();

    $this->actingAs($kasi, 'sanctum')->postJson("/api/kpi/approvals/{$staf->id}/request-task", [
        'comment' => 'Coba minta',
    ])->assertStatus(422);
});
