<?php

use App\Livewire\Admin\KpiPeriodsTable;
use App\Models\KpiEvaluation;
use App\Models\KpiFinalScore;
use App\Models\KpiPeriod;
use App\Models\KpiPlan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

function makePlan(User $user, KpiPeriod $period, string $status): KpiPlan
{
    return KpiPlan::create([
        'user_id' => $user->id, 'period_id' => $period->id,
        'target_description' => 'Target', 'weight' => 10, 'status' => $status,
    ]);
}

test('kolom progres periode DRAFT menampilkan bucket belum mengisi, menunggu approval, dan sudah di-approve', function () {
    $period = KpiPeriod::create(['month' => 7, 'year' => 2026, 'status' => 'DRAFT']);

    User::factory()->create(['job_level' => 4]); // belum mengisi rencana sama sekali

    $submitted = User::factory()->create(['job_level' => 4]);
    makePlan($submitted, $period, 'SUBMITTED');

    $approved = User::factory()->create(['job_level' => 4]);
    makePlan($approved, $period, 'APPROVED');

    $direksi = User::factory()->create(['job_level' => 1]);
    makePlan($direksi, $period, 'APPROVED');

    Livewire::test(KpiPeriodsTable::class)
        ->assertSee('Belum mengisi: 1')
        ->assertSee('Menunggu approval: 1')
        ->assertSee('Sudah di-approve: 1');
});

function scorePlan(KpiPlan $plan, array $roles): void
{
    foreach ($roles as $role) {
        // job_level 1 (Direksi) supaya evaluator throwaway ini tidak ikut terhitung di populasi eligible (job_level 2-4).
        $evaluator = User::factory()->create(['job_level' => 1]);
        KpiEvaluation::create(['kpi_plan_id' => $plan->id, 'evaluator_id' => $evaluator->id, 'evaluator_role' => $role, 'score' => 80]);
    }
}

test('bucket EVALUATION menghitung user yang sudah dinilai lengkap oleh 3 evaluator vs yang belum', function () {
    $period = KpiPeriod::create(['month' => 7, 'year' => 2026, 'status' => 'EVALUATION']);

    $lengkap = User::factory()->create(['job_level' => 4]);
    scorePlan(makePlan($lengkap, $period, 'APPROVED'), ['PENILAI_1', 'PENILAI_2', 'PENILAI_3']);

    $kurang = User::factory()->create(['job_level' => 4]);
    scorePlan(makePlan($kurang, $period, 'APPROVED'), ['PENILAI_1', 'PENILAI_2']);

    $sebagian = User::factory()->create(['job_level' => 4]);
    scorePlan(makePlan($sebagian, $period, 'APPROVED'), ['PENILAI_1', 'PENILAI_2', 'PENILAI_3']);
    scorePlan(makePlan($sebagian, $period, 'APPROVED'), ['PENILAI_1']);

    User::factory()->create(['job_level' => 4]); // tidak punya rencana APPROVED sama sekali

    Livewire::test(KpiPeriodsTable::class)
        ->assertSee('Sudah dinilai lengkap: 1')
        ->assertSee('Belum: 3');
});

test('bucket yang sama dipakai untuk periode berstatus DISPUTE', function () {
    $period = KpiPeriod::create(['month' => 7, 'year' => 2026, 'status' => 'DISPUTE']);

    $lengkap = User::factory()->create(['job_level' => 4]);
    scorePlan(makePlan($lengkap, $period, 'APPROVED'), ['PENILAI_1', 'PENILAI_2', 'PENILAI_3']);

    User::factory()->create(['job_level' => 4]); // belum dinilai

    Livewire::test(KpiPeriodsTable::class)
        ->assertSee('Sudah dinilai lengkap: 1')
        ->assertSee('Belum: 1');
});

test('kolom progres periode CLOSED menampilkan jumlah per kategori predikat nilai akhir', function () {
    $period = KpiPeriod::create(['month' => 7, 'year' => 2026, 'status' => 'CLOSED']);

    foreach ([40, 60, 70, 85, 95] as $score) {
        KpiFinalScore::create([
            'user_id' => User::factory()->create(['job_level' => 4])->id,
            'period_id' => $period->id,
            'score_kinerja' => 0, 'score_kehadiran' => 0, 'score_apel' => 0,
            'score_pakaian' => 0, 'score_integritas' => 0,
            'grand_total_score' => $score,
        ]);
    }

    Livewire::test(KpiPeriodsTable::class)
        ->assertSee('Tidak Memuaskan: 1')
        ->assertSee('Kurang Memuaskan: 1')
        ->assertSee('Rata-rata: 1')
        ->assertSee('Memuaskan: 1')
        ->assertSee('Sangat Memuaskan: 1');
});

test('predikat() mengklasifikasikan grand_total_score sesuai batas kategori', function () {
    $predikatFor = fn (float $score) => (new KpiFinalScore(['grand_total_score' => $score]))->predikat();

    expect($predikatFor(50))->toBe('Tidak Memuaskan')
        ->and($predikatFor(51))->toBe('Kurang Memuaskan')
        ->and($predikatFor(65))->toBe('Kurang Memuaskan')
        ->and($predikatFor(66))->toBe('Rata-rata')
        ->and($predikatFor(75))->toBe('Rata-rata')
        ->and($predikatFor(76))->toBe('Memuaskan')
        ->and($predikatFor(90))->toBe('Memuaskan')
        ->and($predikatFor(90.5))->toBe('Sangat Memuaskan');
});

test('periode DRAFT tanpa pegawai eligible tidak error', function () {
    KpiPeriod::create(['month' => 7, 'year' => 2026, 'status' => 'DRAFT']);

    Livewire::test(KpiPeriodsTable::class)
        ->assertSee('Belum mengisi: 0');
});
