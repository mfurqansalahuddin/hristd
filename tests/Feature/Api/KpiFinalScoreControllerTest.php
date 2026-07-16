<?php

use App\Models\KpiEvaluation;
use App\Models\KpiFinalScore;
use App\Models\KpiPeriod;
use App\Models\KpiPlan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('masa evaluation cuma nampilin progress, bukan skor', function () {
    $period = KpiPeriod::create(['month' => 7, 'year' => 2026, 'status' => 'EVALUATION']);
    $staf = User::factory()->create(['job_level' => 4]);
    $plan = KpiPlan::create(['user_id' => $staf->id, 'period_id' => $period->id, 'target_description' => 'A', 'weight' => 20, 'status' => 'APPROVED']);
    $evaluator = User::factory()->create();
    KpiEvaluation::create(['kpi_plan_id' => $plan->id, 'evaluator_id' => $evaluator->id, 'evaluator_role' => 'PENILAI_1', 'score' => 90]);

    $response = $this->actingAs($staf, 'sanctum')->getJson("/api/kpi/final-score?period_id={$period->id}")->assertOk();

    expect($response->json('progress.0.evaluators_done'))->toBe(1)
        ->and($response->json('final_score'))->toBeNull()
        ->and($response->json('evaluations'))->toBeNull();
});

test('masa dispute membuka skor + alasan serentak, penilai 3 tetap anonim', function () {
    $period = KpiPeriod::create(['month' => 7, 'year' => 2026, 'status' => 'DISPUTE']);
    $staf = User::factory()->create(['job_level' => 4]);
    $plan = KpiPlan::create(['user_id' => $staf->id, 'period_id' => $period->id, 'target_description' => 'A', 'weight' => 20, 'status' => 'APPROVED']);

    $atasan = User::factory()->create(['name' => 'Kasi Budi']);
    $rekan = User::factory()->create(['name' => 'Rekan Rahasia']);
    KpiEvaluation::create(['kpi_plan_id' => $plan->id, 'evaluator_id' => $atasan->id, 'evaluator_role' => 'PENILAI_1', 'score' => 85, 'note' => 'Baik']);
    KpiEvaluation::create(['kpi_plan_id' => $plan->id, 'evaluator_id' => $rekan->id, 'evaluator_role' => 'PENILAI_3', 'score' => 80, 'note' => 'Cukup baik']);

    KpiFinalScore::create([
        'user_id' => $staf->id, 'period_id' => $period->id,
        'score_kinerja' => 40, 'score_kehadiran' => 18, 'score_apel' => 5, 'score_pakaian' => 5, 'score_integritas' => 20,
        'grand_total_score' => 88,
    ]);

    $response = $this->actingAs($staf, 'sanctum')->getJson("/api/kpi/final-score?period_id={$period->id}")->assertOk();

    expect((float) $response->json('final_score.grand_total_score'))->toBe(88.0)
        ->and($response->json('progress'))->toBeNull();

    $penilai1 = collect($response->json('evaluations'))->firstWhere('evaluator_role', 'PENILAI_1');
    $penilai3 = collect($response->json('evaluations'))->firstWhere('evaluator_role', 'PENILAI_3');
    expect($penilai1['evaluator_name'])->toBe('Kasi Budi')
        ->and($penilai3['evaluator_name'])->toBeNull();
});
