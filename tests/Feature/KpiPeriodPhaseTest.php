<?php

use App\Models\KpiPeriod;
use App\Models\KpiPeriodPhase;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('currentFor mengambil fase yang tanggalnya mencakup hari ini', function () {
    $period = KpiPeriod::create(['month' => now()->month, 'year' => now()->year]);

    $period->phases()->create([
        'phase' => 'PENILAIAN',
        'start_date' => now()->subDays(2),
        'end_date' => now()->addDays(2),
    ]);
    $period->phases()->create([
        'phase' => 'FINALISASI',
        'start_date' => now()->addDays(3),
        'end_date' => now()->addDays(5),
    ]);

    expect(KpiPeriodPhase::currentFor($period)->phase)->toBe('PENILAIAN');
});

test('currentFor null kalau tidak ada fase yang mencakup hari ini atau periode kosong', function () {
    $period = KpiPeriod::create(['month' => now()->month, 'year' => now()->year]);
    $period->phases()->create([
        'phase' => 'FINALISASI',
        'start_date' => now()->addDays(3),
        'end_date' => now()->addDays(5),
    ]);

    expect(KpiPeriodPhase::currentFor($period))->toBeNull()
        ->and(KpiPeriodPhase::currentFor(null))->toBeNull();
});

test('showBanner true dari H-3 sampai hari terakhir, false di luar itu', function (int $endOffset, bool $expected) {
    $period = KpiPeriod::create(['month' => now()->month, 'year' => now()->year]);
    $phase = $period->phases()->create([
        'phase' => 'PENILAIAN',
        'start_date' => now()->subDays(10),
        'end_date' => now()->addDays($endOffset),
    ]);

    expect($phase->showBanner())->toBe($expected);
})->with([
    'H-4 (belum tampil)' => [4, false],
    'H-3 (mulai tampil)' => [3, true],
    'H-0 (hari terakhir)' => [0, true],
    'sudah lewat (H+1)' => [-1, false],
]);
