<?php

use App\Models\Department;
use App\Models\KpiEvaluation;
use App\Models\KpiIntegrityCategory;
use App\Models\KpiIntegrityEvaluation;
use App\Models\KpiPeriod;
use App\Models\KpiPlan;
use App\Models\User;
use App\Services\EvaluationProgressService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->service = new EvaluationProgressService;
    $this->period = KpiPeriod::create(['month' => 7, 'year' => 2026, 'status' => 'EVALUATION']);

    $bagian = Department::create(['name' => 'Bagian Umum', 'type' => 'BAGIAN']);
    $seksi = Department::create(['name' => 'Seksi A', 'type' => 'SEKSI', 'parent_department_id' => $bagian->id]);
    $this->kasi = User::factory()->create(['job_level' => 3, 'department_id' => $seksi->id]);
    $this->staf = User::factory()->create(['job_level' => 4, 'department_id' => $seksi->id]);
    $this->plan = KpiPlan::create(['user_id' => $this->staf->id, 'period_id' => $this->period->id, 'target_description' => 'A', 'weight' => 20, 'status' => 'APPROVED']);
});

function isiSemuaKategoriIntegritas(User $evaluator, User $evaluatee, KpiPeriod $period): void
{
    foreach (KpiIntegrityCategory::all() as $category) {
        KpiIntegrityEvaluation::create([
            'evaluator_id' => $evaluator->id, 'reported_user_id' => $evaluatee->id, 'period_id' => $period->id,
            'kpi_integrity_category_id' => $category->id, 'evaluator_role' => 'PENILAI_1', 'decision' => 'BIARIN',
        ]);
    }
}

test('slot PENILAI_1/2: belum selesai kalau kinerja sudah tapi integritas belum, atau sebaliknya', function () {
    KpiEvaluation::create(['kpi_plan_id' => $this->plan->id, 'evaluator_id' => $this->kasi->id, 'evaluator_role' => 'PENILAI_1', 'score' => 80]);

    expect($this->service->isFullyEvaluated($this->kasi, $this->staf, 'PENILAI_1', $this->period))->toBeFalse();

    isiSemuaKategoriIntegritas($this->kasi, $this->staf, $this->period);

    expect($this->service->isFullyEvaluated($this->kasi, $this->staf, 'PENILAI_1', $this->period))->toBeTrue();
});

test('slot PENILAI_3: cukup integritas selesai, kinerja tidak dihitung sama sekali', function () {
    $rekan = User::factory()->create(['job_level' => 4]);

    expect($this->service->isFullyEvaluated($rekan, $this->staf, 'PENILAI_3', $this->period))->toBeFalse();

    isiSemuaKategoriIntegritas($rekan, $this->staf, $this->period);

    // Tidak ada KpiEvaluation sama sekali dari $rekan (rekan tidak menilai Kinerja) -> tetap true.
    expect($this->service->isFullyEvaluated($rekan, $this->staf, 'PENILAI_3', $this->period))->toBeTrue();
});
