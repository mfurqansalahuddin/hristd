<?php

use App\Models\Department;
use App\Models\KpiEvaluation;
use App\Models\KpiEvaluatorWeight;
use App\Models\KpiExtraCriterion;
use App\Models\KpiExtraCriterionScore;
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

test('kriteria penilaian tambahan bisa diisi skor+alasan oleh penilai', function () {
    $period = KpiPeriod::create(['month' => 7, 'year' => 2026, 'status' => 'EVALUATION']);
    $bagian = Department::create(['name' => 'Bagian Umum', 'type' => 'BAGIAN']);
    $seksi = Department::create(['name' => 'Seksi A', 'type' => 'SEKSI', 'parent_department_id' => $bagian->id]);
    $kasi = User::factory()->create(['job_level' => 3, 'department_id' => $seksi->id]);
    $staf = User::factory()->create(['job_level' => 4, 'department_id' => $seksi->id]);
    $criterion = KpiExtraCriterion::create(['name' => 'Kedisiplinan', 'description' => 'Tepat waktu rapat', 'weight' => 10, 'is_active' => true]);

    $this->actingAs($kasi, 'sanctum')->postJson("/api/kpi/evaluations/{$staf->id}/criteria", [
        'kpi_extra_criterion_id' => $criterion->id, 'score' => 80, 'reason' => 'Cukup baik',
    ])->assertOk();

    $score = KpiExtraCriterionScore::where('kpi_extra_criterion_id', $criterion->id)->where('evaluator_id', $kasi->id)->first();
    expect($score->evaluator_role)->toBe('PENILAI_1')->and($score->score)->toBe(80);

    $response = $this->actingAs($kasi, 'sanctum')->getJson("/api/kpi/evaluations/{$staf->id}")->assertOk();
    $entry = collect($response->json('extra_criteria'))->firstWhere('id', $criterion->id);
    expect($entry['score'])->toBe(80)->and($entry['reason'])->toBe('Cukup baik');
});

test('kriteria tambahan yang tidak aktif tidak muncul di form penilaian', function () {
    $period = KpiPeriod::create(['month' => 7, 'year' => 2026, 'status' => 'EVALUATION']);
    $bagian = Department::create(['name' => 'Bagian Umum', 'type' => 'BAGIAN']);
    $seksi = Department::create(['name' => 'Seksi A', 'type' => 'SEKSI', 'parent_department_id' => $bagian->id]);
    $kasi = User::factory()->create(['job_level' => 3, 'department_id' => $seksi->id]);
    $staf = User::factory()->create(['job_level' => 4, 'department_id' => $seksi->id]);
    $criterion = KpiExtraCriterion::create(['name' => 'Nonaktif', 'weight' => 10, 'is_active' => false]);

    $response = $this->actingAs($kasi, 'sanctum')->getJson("/api/kpi/evaluations/{$staf->id}")->assertOk();

    expect(collect($response->json('extra_criteria'))->pluck('id'))->not->toContain($criterion->id);
});

test('fase WORKING: penilai bisa lihat detail tapi submit skor ditolak', function () {
    $period = KpiPeriod::create(['month' => 7, 'year' => 2026, 'status' => 'WORKING']);
    $bagian = Department::create(['name' => 'Bagian Umum', 'type' => 'BAGIAN']);
    $seksi = Department::create(['name' => 'Seksi A', 'type' => 'SEKSI', 'parent_department_id' => $bagian->id]);
    $kasi = User::factory()->create(['job_level' => 3, 'department_id' => $seksi->id]);
    $staf = User::factory()->create(['job_level' => 4, 'department_id' => $seksi->id]);
    $plan = KpiPlan::create(['user_id' => $staf->id, 'period_id' => $period->id, 'target_description' => 'A', 'weight' => 20, 'status' => 'APPROVED']);

    $this->actingAs($kasi, 'sanctum')->getJson('/api/kpi/evaluations/pending')->assertOk()
        ->assertJsonPath('data.0.user_id', $staf->id);
    $this->actingAs($kasi, 'sanctum')->getJson("/api/kpi/evaluations/{$staf->id}")->assertOk()
        ->assertJsonPath('data.0.id', $plan->id);

    $this->actingAs($kasi, 'sanctum')->postJson("/api/kpi/evaluations/{$staf->id}", [
        'kpi_plan_id' => $plan->id, 'score' => 85,
    ])->assertStatus(422);
});

test('rekan sejawat (PENILAI_3) muncul di pending list dan bisa lihat logbook, tapi ditolak menilai Kinerja', function () {
    $period = KpiPeriod::create(['month' => 7, 'year' => 2026, 'status' => 'EVALUATION']);
    $bagian = Department::create(['name' => 'Bagian Umum', 'type' => 'BAGIAN']);
    $seksi = Department::create(['name' => 'Seksi A', 'type' => 'SEKSI', 'parent_department_id' => $bagian->id]);
    $staf = User::factory()->create(['job_level' => 4, 'department_id' => $seksi->id]);
    $rekan = User::factory()->create(['job_level' => 4, 'department_id' => $seksi->id]); // 2 staf di 1 seksi -> saling menilai (mutual)
    $plan = KpiPlan::create(['user_id' => $staf->id, 'period_id' => $period->id, 'target_description' => 'A', 'weight' => 20, 'status' => 'APPROVED']);

    $pending = $this->actingAs($rekan, 'sanctum')->getJson('/api/kpi/evaluations/pending')->assertOk();
    $entry = collect($pending->json('data'))->firstWhere('user_id', $staf->id);
    expect($entry['evaluator_role'])->toBe('PENILAI_3')->and($entry['is_peer'])->toBeTrue();

    $this->actingAs($rekan, 'sanctum')->getJson("/api/kpi/evaluations/{$staf->id}")->assertStatus(403);
    $this->actingAs($rekan, 'sanctum')->postJson("/api/kpi/evaluations/{$staf->id}", [
        'kpi_plan_id' => $plan->id, 'score' => 85,
    ])->assertStatus(403);

    $this->actingAs($rekan, 'sanctum')->getJson("/api/kpi/evaluations/{$staf->id}/logbook")->assertOk();
});

test('penilai bisa lihat logbook evaluee yang tertaut satu rencana kerja, dibatasi bulan periode', function () {
    $period = KpiPeriod::create(['month' => 7, 'year' => 2026, 'status' => 'WORKING']);
    $bagian = Department::create(['name' => 'Bagian Umum', 'type' => 'BAGIAN']);
    $seksi = Department::create(['name' => 'Seksi A', 'type' => 'SEKSI', 'parent_department_id' => $bagian->id]);
    $kasi = User::factory()->create(['job_level' => 3, 'department_id' => $seksi->id]);
    $staf = User::factory()->create(['job_level' => 4, 'department_id' => $seksi->id]);
    $plan = KpiPlan::create(['user_id' => $staf->id, 'period_id' => $period->id, 'target_description' => 'A', 'weight' => 20, 'status' => 'APPROVED']);
    $planLain = KpiPlan::create(['user_id' => $staf->id, 'period_id' => $period->id, 'target_description' => 'B', 'weight' => 10, 'status' => 'APPROVED']);

    $terkait = \App\Models\DailyActivity::create(['user_id' => $staf->id, 'kpi_plan_id' => $plan->id, 'activity_date' => '2026-07-10', 'description' => 'Kerja terkait']);
    \App\Models\DailyActivity::create(['user_id' => $staf->id, 'kpi_plan_id' => $plan->id, 'activity_date' => '2026-06-10', 'description' => 'Di luar periode']);
    \App\Models\DailyActivity::create(['user_id' => $staf->id, 'kpi_plan_id' => $planLain->id, 'activity_date' => '2026-07-11', 'description' => 'Plan lain']);
    \App\Models\DailyActivity::create(['user_id' => $staf->id, 'kpi_plan_id' => null, 'activity_date' => '2026-07-12', 'description' => 'Tidak tertaut']);

    $response = $this->actingAs($kasi, 'sanctum')
        ->getJson("/api/kpi/evaluations/{$staf->id}/logbook?kpi_plan_id={$plan->id}")->assertOk();

    expect(collect($response->json('data'))->pluck('id')->all())->toBe([$terkait->id]);

    $bukanPenilai = User::factory()->create(['job_level' => 3]);
    $this->actingAs($bukanPenilai, 'sanctum')
        ->getJson("/api/kpi/evaluations/{$staf->id}/logbook?kpi_plan_id={$plan->id}")->assertStatus(403);
});

test('submit skor ditolak kalau periode bukan EVALUATION', function () {
    $period = KpiPeriod::create(['month' => 7, 'year' => 2026, 'status' => 'DRAFT']);
    $bagian = Department::create(['name' => 'Bagian Umum', 'type' => 'BAGIAN']);
    $seksi = Department::create(['name' => 'Seksi A', 'type' => 'SEKSI', 'parent_department_id' => $bagian->id]);
    $kasi = User::factory()->create(['job_level' => 3, 'department_id' => $seksi->id]);
    $staf = User::factory()->create(['job_level' => 4, 'department_id' => $seksi->id]);
    $plan = KpiPlan::create(['user_id' => $staf->id, 'period_id' => $period->id, 'target_description' => 'A', 'weight' => 20, 'status' => 'APPROVED']);

    $this->actingAs($kasi, 'sanctum')->postJson("/api/kpi/evaluations/{$staf->id}", [
        'kpi_plan_id' => $plan->id, 'score' => 85,
    ])->assertStatus(422);
});

test('skor kriteria tambahan ditolak kalau periode bukan EVALUATION', function () {
    KpiPeriod::create(['month' => 7, 'year' => 2026, 'status' => 'DRAFT']);
    $bagian = Department::create(['name' => 'Bagian Umum', 'type' => 'BAGIAN']);
    $seksi = Department::create(['name' => 'Seksi A', 'type' => 'SEKSI', 'parent_department_id' => $bagian->id]);
    $kasi = User::factory()->create(['job_level' => 3, 'department_id' => $seksi->id]);
    $staf = User::factory()->create(['job_level' => 4, 'department_id' => $seksi->id]);
    $criterion = KpiExtraCriterion::create(['name' => 'Kedisiplinan', 'description' => 'Tepat waktu rapat', 'weight' => 10, 'is_active' => true]);

    $this->actingAs($kasi, 'sanctum')->postJson("/api/kpi/evaluations/{$staf->id}/criteria", [
        'kpi_extra_criterion_id' => $criterion->id, 'score' => 80, 'reason' => 'Cukup baik',
    ])->assertStatus(422);
});
