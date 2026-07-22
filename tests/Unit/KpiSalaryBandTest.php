<?php

use App\Models\KpiSalaryBand;

/** Band disepakati user: >95->100%, >85->95%, >80->85%, >60->75%, <=60->60% (celah 51-59 masuk 60%). */
$bands = [
    ['min_score' => 95, 'percentage' => 100],
    ['min_score' => 85, 'percentage' => 95],
    ['min_score' => 80, 'percentage' => 85],
    ['min_score' => 60, 'percentage' => 75],
    ['min_score' => null, 'percentage' => 60],
];

test('percentageFor: tepat di ambang jatuh ke band di bawahnya, di atas ambang naik satu band', function () use ($bands) {
    expect(KpiSalaryBand::percentageFor(95.01, $bands))->toBe(100)
        ->and(KpiSalaryBand::percentageFor(95.00, $bands))->toBe(95)
        ->and(KpiSalaryBand::percentageFor(85.01, $bands))->toBe(95)
        ->and(KpiSalaryBand::percentageFor(85.00, $bands))->toBe(85)
        ->and(KpiSalaryBand::percentageFor(80.01, $bands))->toBe(85)
        ->and(KpiSalaryBand::percentageFor(80.00, $bands))->toBe(75)
        ->and(KpiSalaryBand::percentageFor(60.01, $bands))->toBe(75)
        ->and(KpiSalaryBand::percentageFor(60.00, $bands))->toBe(60);
});

test('percentageFor: celah 51-59 masuk ke band 60% (catch-all)', function () use ($bands) {
    expect(KpiSalaryBand::percentageFor(55.0, $bands))->toBe(60)
        ->and(KpiSalaryBand::percentageFor(0.0, $bands))->toBe(60);
});

test('percentageFor: urutan band di input tidak mempengaruhi hasil (diurutkan ulang internal)', function () use ($bands) {
    $shuffled = collect($bands)->shuffle()->all();

    expect(KpiSalaryBand::percentageFor(97.0, $shuffled))->toBe(100)
        ->and(KpiSalaryBand::percentageFor(70.0, $shuffled))->toBe(75);
});
