<?php

use App\Models\KpiPeriod;
use App\Models\KpiPlan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('bisa tambah rencana kerja satu per satu selama total bobot <= 50', function () {
    $period = KpiPeriod::create(['month' => 7, 'year' => 2026, 'status' => 'DRAFT']);
    $staf = User::factory()->create(['job_level' => 4]);

    $this->actingAs($staf, 'sanctum')->postJson('/api/kpi/plans', [
        'period_id' => $period->id, 'target_description' => 'Target A', 'weight' => 30,
    ])->assertCreated();

    $this->actingAs($staf, 'sanctum')->postJson('/api/kpi/plans', [
        'period_id' => $period->id, 'target_description' => 'Target B', 'weight' => 25,
    ])->assertStatus(422)->assertJsonValidationErrors('weight');

    expect(KpiPlan::where('user_id', $staf->id)->count())->toBe(1);
});

test('hanya bisa edit rencana kerja berstatus DRAFT', function () {
    $period = KpiPeriod::create(['month' => 7, 'year' => 2026, 'status' => 'DRAFT']);
    $staf = User::factory()->create(['job_level' => 4]);
    $plan = KpiPlan::create(['user_id' => $staf->id, 'period_id' => $period->id, 'target_description' => 'A', 'weight' => 20, 'status' => 'SUBMITTED']);

    $this->actingAs($staf, 'sanctum')->putJson("/api/kpi/plans/{$plan->id}", [
        'target_description' => 'A revisi', 'weight' => 25,
    ])->assertStatus(422);
});

test('user lain tidak bisa edit rencana kerja orang lain', function () {
    $period = KpiPeriod::create(['month' => 7, 'year' => 2026, 'status' => 'DRAFT']);
    $owner = User::factory()->create(['job_level' => 4]);
    $intruder = User::factory()->create(['job_level' => 4]);
    $plan = KpiPlan::create(['user_id' => $owner->id, 'period_id' => $period->id, 'target_description' => 'A', 'weight' => 20, 'status' => 'DRAFT']);

    $this->actingAs($intruder, 'sanctum')->putJson("/api/kpi/plans/{$plan->id}", [
        'target_description' => 'diubah', 'weight' => 10,
    ])->assertStatus(403);
});

test('submit mengubah semua rencana DRAFT jadi SUBMITTED', function () {
    $period = KpiPeriod::create(['month' => 7, 'year' => 2026, 'status' => 'DRAFT']);
    $staf = User::factory()->create(['job_level' => 4]);
    KpiPlan::create(['user_id' => $staf->id, 'period_id' => $period->id, 'target_description' => 'A', 'weight' => 20, 'status' => 'DRAFT']);
    KpiPlan::create(['user_id' => $staf->id, 'period_id' => $period->id, 'target_description' => 'B', 'weight' => 20, 'status' => 'DRAFT']);

    $this->actingAs($staf, 'sanctum')->postJson('/api/kpi/plans/submit', ['period_id' => $period->id])->assertOk();

    expect(KpiPlan::where('user_id', $staf->id)->where('status', 'SUBMITTED')->count())->toBe(2);
});

test('self-assessment hanya bisa diisi kalau rencana sudah APPROVED', function () {
    $period = KpiPeriod::create(['month' => 7, 'year' => 2026, 'status' => 'EVALUATION']);
    $staf = User::factory()->create(['job_level' => 4]);
    $plan = KpiPlan::create(['user_id' => $staf->id, 'period_id' => $period->id, 'target_description' => 'A', 'weight' => 20, 'status' => 'SUBMITTED']);

    $this->actingAs($staf, 'sanctum')->putJson("/api/kpi/plans/{$plan->id}/self-assessment", [
        'self_assessment_score' => 90, 'self_assessment_note' => 'Tercapai',
    ])->assertStatus(422);

    $plan->update(['status' => 'APPROVED']);

    $this->actingAs($staf, 'sanctum')->putJson("/api/kpi/plans/{$plan->id}/self-assessment", [
        'self_assessment_score' => 90, 'self_assessment_note' => 'Tercapai',
    ])->assertOk();

    expect($plan->fresh()->self_assessment_score)->toBe(90);
});
