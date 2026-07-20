<?php

use App\Models\Attendance;
use App\Models\KpiEvaluation;
use App\Models\KpiEvaluatorWeight;
use App\Models\KpiExtraCriterion;
use App\Models\KpiExtraCriterionScore;
use App\Models\KpiIntegrityCategory;
use App\Models\KpiPeriod;
use App\Models\KpiPlan;
use App\Models\User;
use App\Models\ViolationReport;
use App\Services\KpiEvaluationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

uses(RefreshDatabase::class);

beforeEach(function () {
    foreach (['PENILAI_1' => 33, 'PENILAI_2' => 33, 'PENILAI_3' => 34] as $slot => $weight) {
        KpiEvaluatorWeight::create(['job_level' => 4, 'slot' => $slot, 'weight' => $weight]);
    }

    $this->service = new KpiEvaluationService;
    $this->period = KpiPeriod::create(['month' => 6, 'year' => 2026, 'status' => 'EVALUATION']);
});

test('kinerja: skor plan = rata-rata tertimbang 3 penilai, dikonversi ke bobot item', function () {
    $staf = User::factory()->create(['job_level' => 4]);

    $plan = KpiPlan::create([
        'user_id' => $staf->id, 'period_id' => $this->period->id,
        'target_description' => 'Target A', 'weight' => 40, 'status' => 'APPROVED',
    ]);

    foreach (['PENILAI_1' => 100, 'PENILAI_2' => 100, 'PENILAI_3' => 100] as $role => $score) {
        KpiEvaluation::create(['kpi_plan_id' => $plan->id, 'evaluator_id' => User::factory()->create()->id, 'evaluator_role' => $role, 'score' => $score]);
    }

    $final = $this->service->calculateForUser($staf, $this->period);

    expect((float) $final->score_kinerja)->toBe(40.0);
});

test('kinerja: plafon tidak pernah melebihi bobot komponen (50%) meski total item lebih besar', function () {
    $staf = User::factory()->create(['job_level' => 4]);

    $plan = KpiPlan::create([
        'user_id' => $staf->id, 'period_id' => $this->period->id,
        'target_description' => 'Target Besar', 'weight' => 50, 'status' => 'APPROVED',
    ]);

    foreach (['PENILAI_1' => 100, 'PENILAI_2' => 100, 'PENILAI_3' => 100] as $role => $score) {
        KpiEvaluation::create(['kpi_plan_id' => $plan->id, 'evaluator_id' => User::factory()->create()->id, 'evaluator_role' => $role, 'score' => $score]);
    }

    $final = $this->service->calculateForUser($staf, $this->period);

    expect((float) $final->score_kinerja)->toBe(50.0);
});

test('kehadiran & apel: hadir penuh + apel penuh menghasilkan skor maksimum bucket masing-masing', function () {
    $staf = User::factory()->create(['job_level' => 4]);

    $start = Carbon::create(2026, 6, 1);
    $end = $start->copy()->endOfMonth();

    for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
        if ($date->isSunday()) {
            continue;
        }

        Attendance::factory()->create([
            'user_id' => $staf->id,
            'date' => $date->toDateString(),
            'status' => 'HADIR',
            'is_apel' => $date->isMonday(),
        ]);
    }

    $final = $this->service->calculateForUser($staf, $this->period);

    expect((float) $final->score_kehadiran)->toBe(20.0)
        ->and((float) $final->score_apel)->toBe(5.0);
});

test('kehadiran: hari Alpa mengurangi rasio hadir', function () {
    $staf = User::factory()->create(['job_level' => 4]);

    $start = Carbon::create(2026, 6, 1);
    $end = $start->copy()->endOfMonth();
    $workingDays = 0;
    $alpaMarked = false;

    for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
        if ($date->isSunday()) {
            continue;
        }
        $workingDays++;

        Attendance::factory()->create([
            'user_id' => $staf->id,
            'date' => $date->toDateString(),
            'status' => ! $alpaMarked ? 'ALPA' : 'HADIR',
        ]);
        $alpaMarked = true;
    }

    $final = $this->service->calculateForUser($staf, $this->period);

    $expected = round((($workingDays - 1) / $workingDays) * 20, 2);
    expect((float) $final->score_kehadiran)->toBe($expected);
});

test('pakaian dinas: dedup harian, deduction_point 0 fallback ke default 5', function () {
    $staf = User::factory()->create(['job_level' => 4]);
    $reporter = User::factory()->create();

    // 2 aduan hari sama -> dihitung 1x.
    ViolationReport::create([
        'reported_user_id' => $staf->id, 'reporter_id' => $reporter->id, 'period_id' => $this->period->id,
        'category' => 'PAKAIAN_DINAS', 'incident_date' => '2026-06-05', 'status' => 'VALIDATED', 'deduction_point' => 0,
    ]);
    ViolationReport::create([
        'reported_user_id' => $staf->id, 'reporter_id' => $reporter->id, 'period_id' => $this->period->id,
        'category' => 'PAKAIAN_DINAS', 'incident_date' => '2026-06-05', 'status' => 'VALIDATED', 'deduction_point' => 0,
    ]);

    $final = $this->service->calculateForUser($staf, $this->period);

    // (100 - 5) / 100 * 5 = 4.75
    expect((float) $final->score_pakaian)->toBe(4.75);
});

test('integritas: pengurangan flat per kategori dari sumber Aduan Perusahaan, floor 0 tidak minus, digabung 4 sumber', function () {
    $staf = User::factory()->create(['job_level' => 4]);
    $reporter = User::factory()->create();
    $etika = KpiIntegrityCategory::where('name', 'Etika')->first(); // deduction_value 10

    // 2 kejadian tervalidasi di kategori sama, hari BEDA -> tidak dedup, tapi floor 0 (bukan -10).
    ViolationReport::create([
        'reported_user_id' => $staf->id, 'reporter_id' => $reporter->id, 'period_id' => $this->period->id,
        'category' => 'INTEGRITAS', 'integrity_category_id' => $etika->id,
        'incident_date' => '2026-06-01', 'status' => 'VALIDATED',
    ]);
    ViolationReport::create([
        'reported_user_id' => $staf->id, 'reporter_id' => $reporter->id, 'period_id' => $this->period->id,
        'category' => 'INTEGRITAS', 'integrity_category_id' => $etika->id,
        'incident_date' => '2026-06-02', 'status' => 'VALIDATED',
    ]);

    $final = $this->service->calculateForUser($staf, $this->period);

    // Hanya sumber ADUAN_PERUSAHAAN yang punya temuan (Etika zeroed): (100-10)/100*100 = 90.
    // Penilai 1/2/3 tidak punya keputusan -> skor penuh 100 tiap sumber (belum dinilai, bukan "aman").
    // Rata-rata tertimbang 25/25/25/25: (90+100+100+100)/4 = 97.5, diskalakan ke bobot 20 -> 19.5.
    expect((float) $final->score_integritas)->toBe(19.5);
});

test('kriteria tambahan aktif ikut dijumlah ke grand_total_score sebagai bucket ekstra', function () {
    $staf = User::factory()->create(['job_level' => 4]);
    $criterion = KpiExtraCriterion::create(['name' => 'Kedisiplinan', 'weight' => 10, 'is_active' => true]);
    KpiExtraCriterion::create(['name' => 'Nonaktif', 'weight' => 5, 'is_active' => false]);

    foreach (['PENILAI_1' => 100, 'PENILAI_2' => 100, 'PENILAI_3' => 100] as $role => $score) {
        KpiExtraCriterionScore::create([
            'kpi_extra_criterion_id' => $criterion->id, 'user_id' => $staf->id, 'period_id' => $this->period->id,
            'evaluator_id' => User::factory()->create()->id, 'evaluator_role' => $role, 'score' => $score,
        ]);
    }

    $final = $this->service->calculateForUser($staf, $this->period);

    $expectedGrandTotal = $final->score_kinerja + $final->score_kehadiran + $final->score_apel
        + $final->score_pakaian + $final->score_integritas + 10.0;

    expect((float) $final->extras()->where('kpi_extra_criterion_id', $criterion->id)->first()->score)->toBe(10.0)
        ->and((float) $final->grand_total_score)->toBe(round($expectedGrandTotal, 2))
        ->and($final->extras()->count())->toBe(1); // kriteria nonaktif tidak ikut disimpan
});

test('calculateForPeriod hanya menghitung job_level 2-4, Direksi di luar cakupan', function () {
    $direksi = User::factory()->create(['job_level' => 1]);
    User::factory()->create(['job_level' => 4]);

    $this->service->calculateForPeriod($this->period);

    expect(\App\Models\KpiFinalScore::where('user_id', $direksi->id)->exists())->toBeFalse()
        ->and(\App\Models\KpiFinalScore::where('period_id', $this->period->id)->count())->toBe(1);
});
