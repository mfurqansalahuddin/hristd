<?php

use App\Livewire\Admin\ViolationsTable;
use App\Models\KpiIntegrityCategory;
use App\Models\KpiPeriod;
use App\Models\User;
use App\Models\ViolationReport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('aduan sama orang sama hari sama kategori dikelompokkan jadi 1 baris dengan jumlah yang benar', function () {
    $period = KpiPeriod::create(['month' => 7, 'year' => 2026, 'status' => 'DRAFT']);
    $target = User::factory()->create();
    $reporter = User::factory()->create();

    ViolationReport::create([
        'reported_user_id' => $target->id, 'reporter_id' => $reporter->id, 'period_id' => $period->id,
        'category' => 'PAKAIAN_DINAS', 'photo_path' => 'violations/a.jpg', 'incident_date' => '2026-07-10', 'status' => 'PENDING',
    ]);
    ViolationReport::create([
        'reported_user_id' => $target->id, 'reporter_id' => $reporter->id, 'period_id' => $period->id,
        'category' => 'PAKAIAN_DINAS', 'photo_path' => 'violations/b.jpg', 'incident_date' => '2026-07-10', 'status' => 'PENDING',
    ]);
    // Hari berbeda -> baris terpisah.
    ViolationReport::create([
        'reported_user_id' => $target->id, 'reporter_id' => $reporter->id, 'period_id' => $period->id,
        'category' => 'PAKAIAN_DINAS', 'photo_path' => 'violations/c.jpg', 'incident_date' => '2026-07-11', 'status' => 'PENDING',
    ]);

    $component = Livewire::test(ViolationsTable::class);
    $groups = $component->viewData('groups');

    expect($groups)->toHaveCount(2);
    $day10 = collect($groups->items())->firstWhere('incident_date.day', 10);
    expect($day10['count'])->toBe(2);
});

test('validasi menandai semua aduan dalam grup jadi VALIDATED, grup lain tidak ikut', function () {
    $period = KpiPeriod::create(['month' => 7, 'year' => 2026, 'status' => 'DRAFT']);
    $category = KpiIntegrityCategory::first();
    $targetA = User::factory()->create();
    $targetB = User::factory()->create();
    $reporter = User::factory()->create();

    $a1 = ViolationReport::create([
        'reported_user_id' => $targetA->id, 'reporter_id' => $reporter->id, 'period_id' => $period->id,
        'category' => 'INTEGRITAS', 'integrity_category_id' => $category->id, 'description' => 'x',
        'incident_date' => '2026-07-10', 'status' => 'PENDING',
    ]);
    $a2 = ViolationReport::create([
        'reported_user_id' => $targetA->id, 'reporter_id' => $reporter->id, 'period_id' => $period->id,
        'category' => 'INTEGRITAS', 'integrity_category_id' => $category->id, 'description' => 'y',
        'incident_date' => '2026-07-10', 'status' => 'PENDING',
    ]);
    $b = ViolationReport::create([
        'reported_user_id' => $targetB->id, 'reporter_id' => $reporter->id, 'period_id' => $period->id,
        'category' => 'INTEGRITAS', 'integrity_category_id' => $category->id, 'description' => 'z',
        'incident_date' => '2026-07-10', 'status' => 'PENDING',
    ]);

    Livewire::test(ViolationsTable::class)
        ->call('confirmValidate', [$a1->id, $a2->id]);

    expect($a1->fresh()->status)->toBe('VALIDATED')
        ->and($a2->fresh()->status)->toBe('VALIDATED')
        ->and($b->fresh()->status)->toBe('PENDING');
});

test('filter tanggal dan pencarian nama/NIK menyaring hasil', function () {
    $period = KpiPeriod::create(['month' => 7, 'year' => 2026, 'status' => 'DRAFT']);
    $target = User::factory()->create(['name' => 'Budi Santoso', 'nik' => '12345']);
    $other = User::factory()->create(['name' => 'Siti Aminah', 'nik' => '99999']);
    $reporter = User::factory()->create();

    foreach ([$target, $other] as $user) {
        ViolationReport::create([
            'reported_user_id' => $user->id, 'reporter_id' => $reporter->id, 'period_id' => $period->id,
            'category' => 'PAKAIAN_DINAS', 'photo_path' => 'violations/a.jpg', 'incident_date' => '2026-07-10', 'status' => 'PENDING',
        ]);
    }

    $component = Livewire::test(ViolationsTable::class)->set('search', 'Budi');
    expect($component->viewData('groups'))->toHaveCount(1);

    $component = Livewire::test(ViolationsTable::class)->set('dateFrom', '2026-07-11');
    expect($component->viewData('groups'))->toHaveCount(0);
});
