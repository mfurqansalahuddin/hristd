<?php

use App\Models\KpiPeriod;
use App\Models\KpiPlan;
use App\Models\KpiPlanReview;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('bisa tambah rencana kerja satu per satu selama total bobot <= 50', function () {
    $period = KpiPeriod::create(['month' => 7, 'year' => 2026, 'status' => 'DRAFT']);
    $staf = User::factory()->create(['job_level' => 4]);

    $this->actingAs($staf, 'sanctum')->postJson('/api/kpi/plans', [
        'period_id' => $period->id, 'name' => 'Nama', 'target_description' => 'Target A', 'weight' => 30,
    ])->assertCreated();

    $this->actingAs($staf, 'sanctum')->postJson('/api/kpi/plans', [
        'period_id' => $period->id, 'name' => 'Nama', 'target_description' => 'Target B', 'weight' => 25,
    ])->assertStatus(422)->assertJsonValidationErrors('weight');

    expect(KpiPlan::where('user_id', $staf->id)->count())->toBe(1);
});

test('hanya bisa edit rencana kerja berstatus DRAFT', function () {
    $period = KpiPeriod::create(['month' => 7, 'year' => 2026, 'status' => 'DRAFT']);
    $staf = User::factory()->create(['job_level' => 4]);
    $plan = KpiPlan::create(['user_id' => $staf->id, 'period_id' => $period->id, 'name' => 'Nama', 'target_description' => 'A', 'weight' => 20, 'status' => 'SUBMITTED']);

    $this->actingAs($staf, 'sanctum')->putJson("/api/kpi/plans/{$plan->id}", [
        'name' => 'Nama', 'target_description' => 'A revisi', 'weight' => 25,
    ])->assertStatus(422);
});

test('rencana kerja berstatus REJECTED bisa diedit lagi (alur ajukan ulang)', function () {
    $period = KpiPeriod::create(['month' => 7, 'year' => 2026, 'status' => 'DRAFT']);
    $staf = User::factory()->create(['job_level' => 4]);
    $plan = KpiPlan::create(['user_id' => $staf->id, 'period_id' => $period->id, 'name' => 'Nama', 'target_description' => 'A', 'weight' => 20, 'status' => 'REJECTED']);

    $this->actingAs($staf, 'sanctum')->putJson("/api/kpi/plans/{$plan->id}", [
        'name' => 'Nama', 'target_description' => 'A revisi', 'weight' => 25,
    ])->assertOk();

    expect($plan->fresh()->target_description)->toBe('A revisi');
});

test('user lain tidak bisa edit rencana kerja orang lain', function () {
    $period = KpiPeriod::create(['month' => 7, 'year' => 2026, 'status' => 'DRAFT']);
    $owner = User::factory()->create(['job_level' => 4]);
    $intruder = User::factory()->create(['job_level' => 4]);
    $plan = KpiPlan::create(['user_id' => $owner->id, 'period_id' => $period->id, 'name' => 'Nama', 'target_description' => 'A', 'weight' => 20, 'status' => 'DRAFT']);

    $this->actingAs($intruder, 'sanctum')->putJson("/api/kpi/plans/{$plan->id}", [
        'name' => 'Nama', 'target_description' => 'diubah', 'weight' => 10,
    ])->assertStatus(403);
});

test('hapus rencana kerja berstatus DRAFT', function () {
    $period = KpiPeriod::create(['month' => 7, 'year' => 2026, 'status' => 'DRAFT']);
    $staf = User::factory()->create(['job_level' => 4]);
    $plan = KpiPlan::create(['user_id' => $staf->id, 'period_id' => $period->id, 'name' => 'Nama', 'target_description' => 'A', 'weight' => 20, 'status' => 'DRAFT']);

    $this->actingAs($staf, 'sanctum')->deleteJson("/api/kpi/plans/{$plan->id}")->assertNoContent();

    expect(KpiPlan::find($plan->id))->toBeNull();
});

test('hanya bisa hapus rencana kerja berstatus DRAFT', function () {
    $period = KpiPeriod::create(['month' => 7, 'year' => 2026, 'status' => 'DRAFT']);
    $staf = User::factory()->create(['job_level' => 4]);
    $plan = KpiPlan::create(['user_id' => $staf->id, 'period_id' => $period->id, 'name' => 'Nama', 'target_description' => 'A', 'weight' => 20, 'status' => 'SUBMITTED']);

    $this->actingAs($staf, 'sanctum')->deleteJson("/api/kpi/plans/{$plan->id}")->assertStatus(422);

    expect(KpiPlan::find($plan->id))->not->toBeNull();
});

test('rencana kerja berstatus REJECTED bisa dihapus', function () {
    $period = KpiPeriod::create(['month' => 7, 'year' => 2026, 'status' => 'DRAFT']);
    $staf = User::factory()->create(['job_level' => 4]);
    $plan = KpiPlan::create(['user_id' => $staf->id, 'period_id' => $period->id, 'name' => 'Nama', 'target_description' => 'A', 'weight' => 20, 'status' => 'REJECTED']);

    $this->actingAs($staf, 'sanctum')->deleteJson("/api/kpi/plans/{$plan->id}")->assertNoContent();

    expect(KpiPlan::find($plan->id))->toBeNull();
});

test('user lain tidak bisa hapus rencana kerja orang lain', function () {
    $period = KpiPeriod::create(['month' => 7, 'year' => 2026, 'status' => 'DRAFT']);
    $owner = User::factory()->create(['job_level' => 4]);
    $intruder = User::factory()->create(['job_level' => 4]);
    $plan = KpiPlan::create(['user_id' => $owner->id, 'period_id' => $period->id, 'name' => 'Nama', 'target_description' => 'A', 'weight' => 20, 'status' => 'DRAFT']);

    $this->actingAs($intruder, 'sanctum')->deleteJson("/api/kpi/plans/{$plan->id}")->assertStatus(403);

    expect(KpiPlan::find($plan->id))->not->toBeNull();
});

test('submit mengubah semua rencana DRAFT jadi SUBMITTED', function () {
    $period = KpiPeriod::create(['month' => 7, 'year' => 2026, 'status' => 'DRAFT']);
    $staf = User::factory()->create(['job_level' => 4]);
    KpiPlan::create(['user_id' => $staf->id, 'period_id' => $period->id, 'name' => 'Nama', 'target_description' => 'A', 'weight' => 20, 'status' => 'DRAFT']);
    KpiPlan::create(['user_id' => $staf->id, 'period_id' => $period->id, 'name' => 'Nama', 'target_description' => 'B', 'weight' => 20, 'status' => 'DRAFT']);

    $this->actingAs($staf, 'sanctum')->postJson('/api/kpi/plans/submit', ['period_id' => $period->id])->assertOk();

    expect(KpiPlan::where('user_id', $staf->id)->where('status', 'SUBMITTED')->count())->toBe(2);
});

test('submit memproses rencana REJECTED juga, bukan cuma DRAFT (alur ajukan ulang)', function () {
    $period = KpiPeriod::create(['month' => 7, 'year' => 2026, 'status' => 'DRAFT']);
    $staf = User::factory()->create(['job_level' => 4]);
    KpiPlan::create(['user_id' => $staf->id, 'period_id' => $period->id, 'name' => 'Nama', 'target_description' => 'A', 'weight' => 20, 'status' => 'DRAFT']);
    $rejected = KpiPlan::create(['user_id' => $staf->id, 'period_id' => $period->id, 'name' => 'Nama', 'target_description' => 'B', 'weight' => 20, 'status' => 'REJECTED']);

    $this->actingAs($staf, 'sanctum')->postJson('/api/kpi/plans/submit', ['period_id' => $period->id])->assertOk();

    expect($rejected->fresh()->status)->toBe('SUBMITTED')
        ->and(KpiPlan::where('user_id', $staf->id)->where('status', 'SUBMITTED')->count())->toBe(2);
});

test('self-assessment hanya bisa diisi kalau rencana sudah APPROVED', function () {
    $period = KpiPeriod::create(['month' => 7, 'year' => 2026, 'status' => 'EVALUATION']);
    $staf = User::factory()->create(['job_level' => 4]);
    $plan = KpiPlan::create(['user_id' => $staf->id, 'period_id' => $period->id, 'name' => 'Nama', 'target_description' => 'A', 'weight' => 20, 'status' => 'SUBMITTED']);

    $this->actingAs($staf, 'sanctum')->putJson("/api/kpi/plans/{$plan->id}/self-assessment", [
        'self_assessment_score' => 90, 'self_assessment_note' => 'Tercapai',
    ])->assertStatus(422);

    $plan->update(['status' => 'APPROVED']);

    $this->actingAs($staf, 'sanctum')->putJson("/api/kpi/plans/{$plan->id}/self-assessment", [
        'self_assessment_score' => 90, 'self_assessment_note' => 'Tercapai',
    ])->assertOk();

    expect($plan->fresh()->self_assessment_score)->toBe(90);
});

test('salin rencana kerja dari bulan lalu menduplikasi target_description dan weight, status fresh DRAFT', function () {
    $periodLalu = KpiPeriod::create(['month' => 6, 'year' => 2026, 'status' => 'CLOSED']);
    $periodIni = KpiPeriod::create(['month' => 7, 'year' => 2026, 'status' => 'DRAFT']);
    $staf = User::factory()->create(['job_level' => 4]);
    KpiPlan::create(['user_id' => $staf->id, 'period_id' => $periodLalu->id, 'name' => 'Nama', 'target_description' => 'Catat meter', 'weight' => 30, 'status' => 'APPROVED', 'self_assessment_score' => 90]);

    $this->actingAs($staf, 'sanctum')->postJson('/api/kpi/plans/copy-previous', ['period_id' => $periodIni->id])->assertCreated();

    $copied = KpiPlan::where('user_id', $staf->id)->where('period_id', $periodIni->id)->first();
    expect($copied->target_description)->toBe('Catat meter')
        ->and((float) $copied->weight)->toBe(30.0)
        ->and($copied->status)->toBe('DRAFT')
        ->and($copied->self_assessment_score)->toBeNull();
});

test('salin rencana kerja ditolak kalau periode ini sudah punya rencana', function () {
    $periodLalu = KpiPeriod::create(['month' => 6, 'year' => 2026, 'status' => 'CLOSED']);
    $periodIni = KpiPeriod::create(['month' => 7, 'year' => 2026, 'status' => 'DRAFT']);
    $staf = User::factory()->create(['job_level' => 4]);
    KpiPlan::create(['user_id' => $staf->id, 'period_id' => $periodLalu->id, 'name' => 'Nama', 'target_description' => 'Catat meter', 'weight' => 30, 'status' => 'APPROVED']);
    KpiPlan::create(['user_id' => $staf->id, 'period_id' => $periodIni->id, 'name' => 'Nama', 'target_description' => 'Sudah ada', 'weight' => 10, 'status' => 'DRAFT']);

    $this->actingAs($staf, 'sanctum')->postJson('/api/kpi/plans/copy-previous', ['period_id' => $periodIni->id])
        ->assertStatus(422)->assertJsonValidationErrors('period_id');
});

test('salin rencana kerja ditolak kalau tidak ada rencana bulan lalu', function () {
    $periodIni = KpiPeriod::create(['month' => 7, 'year' => 2026, 'status' => 'DRAFT']);
    $staf = User::factory()->create(['job_level' => 4]);

    $this->actingAs($staf, 'sanctum')->postJson('/api/kpi/plans/copy-previous', ['period_id' => $periodIni->id])
        ->assertStatus(422)->assertJsonValidationErrors('period_id');
});

test('rencana kerja tidak bisa dibuat kalau periode sudah bukan DRAFT', function () {
    $period = KpiPeriod::create(['month' => 7, 'year' => 2026, 'status' => 'EVALUATION']);
    $staf = User::factory()->create(['job_level' => 4]);

    $this->actingAs($staf, 'sanctum')->postJson('/api/kpi/plans', [
        'period_id' => $period->id, 'name' => 'Nama', 'target_description' => 'Target A', 'weight' => 30,
    ])->assertStatus(422);
});

test('rencana kerja tidak bisa diedit kalau periode sudah bukan DRAFT', function () {
    $period = KpiPeriod::create(['month' => 7, 'year' => 2026, 'status' => 'EVALUATION']);
    $staf = User::factory()->create(['job_level' => 4]);
    $plan = KpiPlan::create(['user_id' => $staf->id, 'period_id' => $period->id, 'name' => 'Nama', 'target_description' => 'A', 'weight' => 20, 'status' => 'DRAFT']);

    $this->actingAs($staf, 'sanctum')->putJson("/api/kpi/plans/{$plan->id}", [
        'name' => 'Nama', 'target_description' => 'A revisi', 'weight' => 25,
    ])->assertStatus(422);
});

test('submit ditolak kalau periode sudah bukan DRAFT', function () {
    $period = KpiPeriod::create(['month' => 7, 'year' => 2026, 'status' => 'EVALUATION']);
    $staf = User::factory()->create(['job_level' => 4]);
    KpiPlan::create(['user_id' => $staf->id, 'period_id' => $period->id, 'name' => 'Nama', 'target_description' => 'A', 'weight' => 20, 'status' => 'DRAFT']);

    $this->actingAs($staf, 'sanctum')->postJson('/api/kpi/plans/submit', ['period_id' => $period->id])->assertStatus(422);
});

test('fase WORKING: self-assessment bisa diisi tapi rencana kerja terkunci', function () {
    $period = KpiPeriod::create(['month' => 7, 'year' => 2026, 'status' => 'WORKING']);
    $staf = User::factory()->create(['job_level' => 4]);
    $plan = KpiPlan::create(['user_id' => $staf->id, 'period_id' => $period->id, 'name' => 'Nama', 'target_description' => 'A', 'weight' => 20, 'status' => 'APPROVED']);

    $this->actingAs($staf, 'sanctum')->putJson("/api/kpi/plans/{$plan->id}/self-assessment", [
        'self_assessment_score' => 90, 'self_assessment_note' => 'Tercapai',
    ])->assertOk();

    expect($plan->fresh()->self_assessment_score)->toBe(90);

    $this->actingAs($staf, 'sanctum')->postJson('/api/kpi/plans', [
        'period_id' => $period->id, 'name' => 'Baru', 'target_description' => 'B', 'weight' => 10,
    ])->assertStatus(422);
});

test('self-assessment ditolak kalau periode bukan WORKING/EVALUATION', function () {
    $period = KpiPeriod::create(['month' => 7, 'year' => 2026, 'status' => 'DRAFT']);
    $staf = User::factory()->create(['job_level' => 4]);
    $plan = KpiPlan::create(['user_id' => $staf->id, 'period_id' => $period->id, 'name' => 'Nama', 'target_description' => 'A', 'weight' => 20, 'status' => 'APPROVED']);

    $this->actingAs($staf, 'sanctum')->putJson("/api/kpi/plans/{$plan->id}/self-assessment", [
        'self_assessment_score' => 90, 'self_assessment_note' => 'Tercapai',
    ])->assertStatus(422);
});

test('salin rencana kerja ditolak kalau periode ini sudah bukan DRAFT', function () {
    $periodLalu = KpiPeriod::create(['month' => 6, 'year' => 2026, 'status' => 'CLOSED']);
    $periodIni = KpiPeriod::create(['month' => 7, 'year' => 2026, 'status' => 'EVALUATION']);
    $staf = User::factory()->create(['job_level' => 4]);
    KpiPlan::create(['user_id' => $staf->id, 'period_id' => $periodLalu->id, 'name' => 'Nama', 'target_description' => 'Catat meter', 'weight' => 30, 'status' => 'APPROVED']);

    $this->actingAs($staf, 'sanctum')->postJson('/api/kpi/plans/copy-previous', ['period_id' => $periodIni->id])
        ->assertStatus(422);
});

test('GET kpi/plans menyertakan task_requests dari atasan', function () {
    $period = KpiPeriod::create(['month' => 7, 'year' => 2026, 'status' => 'DRAFT']);
    $staf = User::factory()->create(['job_level' => 4]);
    $atasan = User::factory()->create(['job_level' => 3]);
    KpiPlanReview::create([
        'period_id' => $period->id, 'user_id' => $staf->id, 'reviewer_id' => $atasan->id,
        'action' => 'TASK_REQUESTED', 'comment' => 'Tambahkan target baru',
    ]);

    $response = $this->actingAs($staf, 'sanctum')->getJson("/api/kpi/plans?period_id={$period->id}")->assertOk();

    expect($response->json('task_requests.0.comment'))->toBe('Tambahkan target baru');
});
