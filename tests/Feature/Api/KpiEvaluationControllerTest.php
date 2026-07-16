<?php

use App\Models\Department;
use App\Models\KpiEvaluation;
use App\Models\KpiEvaluatorWeight;
use App\Models\KpiPeriod;
use App\Models\KpiPlan;
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

test('kasi bisa nilai staf bawahannya, tersimpan dengan evaluator_role yang benar', function () {
    $period = KpiPeriod::create(['month' => 7, 'year' => 2026, 'status' => 'EVALUATION']);
    $bagian = Department::create(['name' => 'Bagian Umum', 'type' => 'BAGIAN']);
    $seksi = Department::create(['name' => 'Seksi A', 'type' => 'SEKSI', 'parent_department_id' => $bagian->id]);
    $kasi = User::factory()->create(['job_level' => 3, 'department_id' => $seksi->id]);
    $staf = User::factory()->create(['job_level' => 4, 'department_id' => $seksi->id]);
    $plan = KpiPlan::create(['user_id' => $staf->id, 'period_id' => $period->id, 'target_description' => 'A', 'weight' => 20, 'status' => 'APPROVED']);

    $this->actingAs($kasi, 'sanctum')->postJson("/api/kpi/evaluations/{$staf->id}", [
        'kpi_plan_id' => $plan->id, 'score' => 85, 'note' => 'Bagus',
    ])->assertOk();

    $evaluation = KpiEvaluation::where('kpi_plan_id', $plan->id)->where('evaluator_id', $kasi->id)->first();
    expect($evaluation->evaluator_role)->toBe('PENILAI_1')->and($evaluation->score)->toBe(85);
});

test('orang yang bukan penilai ditolak saat submit skor', function () {
    $period = KpiPeriod::create(['month' => 7, 'year' => 2026, 'status' => 'EVALUATION']);
    $staf = User::factory()->create(['job_level' => 4]);
    $bukanPenilai = User::factory()->create(['job_level' => 3]);
    $plan = KpiPlan::create(['user_id' => $staf->id, 'period_id' => $period->id, 'target_description' => 'A', 'weight' => 20, 'status' => 'APPROVED']);

    $this->actingAs($bukanPenilai, 'sanctum')->postJson("/api/kpi/evaluations/{$staf->id}", [
        'kpi_plan_id' => $plan->id, 'score' => 85,
    ])->assertStatus(403);
});

test('submit ulang skor dari penilai yang sama meng-update, bukan bikin baris baru', function () {
    $period = KpiPeriod::create(['month' => 7, 'year' => 2026, 'status' => 'EVALUATION']);
    $bagian = Department::create(['name' => 'Bagian Umum', 'type' => 'BAGIAN']);
    $seksi = Department::create(['name' => 'Seksi A', 'type' => 'SEKSI', 'parent_department_id' => $bagian->id]);
    $kasi = User::factory()->create(['job_level' => 3, 'department_id' => $seksi->id]);
    $staf = User::factory()->create(['job_level' => 4, 'department_id' => $seksi->id]);
    $plan = KpiPlan::create(['user_id' => $staf->id, 'period_id' => $period->id, 'target_description' => 'A', 'weight' => 20, 'status' => 'APPROVED']);

    $this->actingAs($kasi, 'sanctum')->postJson("/api/kpi/evaluations/{$staf->id}", ['kpi_plan_id' => $plan->id, 'score' => 70])->assertOk();
    $this->actingAs($kasi, 'sanctum')->postJson("/api/kpi/evaluations/{$staf->id}", ['kpi_plan_id' => $plan->id, 'score' => 90])->assertOk();

    expect(KpiEvaluation::where('kpi_plan_id', $plan->id)->count())->toBe(1)
        ->and(KpiEvaluation::where('kpi_plan_id', $plan->id)->first()->score)->toBe(90);
});

test('pending menampilkan is_peer sesuai slot dan status sudah/belum dinilai', function () {
    $period = KpiPeriod::create(['month' => 7, 'year' => 2026, 'status' => 'EVALUATION']);
    $bagian = Department::create(['name' => 'Bagian Umum', 'type' => 'BAGIAN']);
    $seksi = Department::create(['name' => 'Seksi A', 'type' => 'SEKSI', 'parent_department_id' => $bagian->id]);
    $kasi = User::factory()->create(['job_level' => 3, 'department_id' => $seksi->id]);
    $staf = User::factory()->create(['job_level' => 4, 'department_id' => $seksi->id]);
    KpiPlan::create(['user_id' => $staf->id, 'period_id' => $period->id, 'target_description' => 'A', 'weight' => 20, 'status' => 'APPROVED']);

    $response = $this->actingAs($kasi, 'sanctum')->getJson('/api/kpi/evaluations/pending')->assertOk();

    $entry = collect($response->json('data'))->firstWhere('user_id', $staf->id);
    expect($entry['evaluator_role'])->toBe('PENILAI_1')
        ->and($entry['is_peer'])->toBeFalse()
        ->and($entry['already_evaluated'])->toBeFalse();
});
