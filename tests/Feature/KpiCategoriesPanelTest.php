<?php

use App\Livewire\Admin\KpiCategoriesPanel;
use App\Models\KpiComponentWeight;
use App\Models\KpiExtraCriterion;
use App\Models\KpiIntegrityCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('total bobot digabung dari 5 komponen tetap + kriteria tambahan aktif', function () {
    KpiExtraCriterion::create(['name' => 'Kedisiplinan', 'weight' => 10, 'is_active' => true]);
    KpiExtraCriterion::create(['name' => 'Nonaktif', 'weight' => 15, 'is_active' => false]);

    Livewire::test(KpiCategoriesPanel::class)
        ->assertSee('110%') // 100 (5 komponen tetap) + 10 (kriteria aktif), kriteria nonaktif tidak ikut
        ->assertDontSee('125%');
});

test('simpan bobot komponen tetap tetap tersimpan meski total jadi tidak 100, total live ikut berubah', function () {
    KpiExtraCriterion::create(['name' => 'Kedisiplinan', 'weight' => 10, 'is_active' => true]);
    $kinerja = KpiComponentWeight::where('component', 'KINERJA')->first();

    Livewire::test(KpiCategoriesPanel::class)
        ->set("componentEdits.{$kinerja->id}.weight", 60)
        ->call('updateComponentWeight', $kinerja->id)
        ->assertHasNoErrors()
        ->assertSee('120%'); // 60+20+5+5+20 (komponen) + 10 (kriteria aktif)

    expect($kinerja->fresh()->weight)->toBe(60); // rebalance antar baris satu-per-satu tetap mungkin
});

test('hapus kriteria tambahan lewat konfirmasi dua-langkah menghapus baris dan mengeluarkannya dari total', function () {
    $criterion = KpiExtraCriterion::create(['name' => 'Kedisiplinan', 'weight' => 10, 'is_active' => true]);

    Livewire::test(KpiCategoriesPanel::class)
        ->assertSee('110%')
        ->set('confirmingCriterionId', $criterion->id)
        ->assertSee('Yakin hapus?')
        ->call('deleteCriterion', $criterion->id)
        ->assertSet('confirmingCriterionId', null)
        ->assertSee('100%')
        ->assertDontSee('Kedisiplinan');

    expect(KpiExtraCriterion::find($criterion->id))->toBeNull();
});

test('simpan kategori integritas samar sampai ada perubahan', function () {
    $category = KpiIntegrityCategory::create(['name' => 'Etika Kerja', 'deduction_value' => 10]);

    $component = Livewire::test(KpiCategoriesPanel::class);
    expect($component->instance()->isIntegrityDirty($category->id))->toBeFalse();

    $component->set("edits.{$category->id}.deduction_value", 15);
    expect($component->instance()->isIntegrityDirty($category->id))->toBeTrue();

    $component->call('updateIntegrityCategory', $category->id);
    expect($component->instance()->isIntegrityDirty($category->id))->toBeFalse()
        ->and($category->fresh()->deduction_value)->toBe(15);
});

test('hapus kategori integritas lewat konfirmasi dua-langkah', function () {
    $category = KpiIntegrityCategory::create(['name' => 'Kategori Uji Hapus', 'deduction_value' => 10]);

    Livewire::test(KpiCategoriesPanel::class)
        ->set('confirmingIntegrityId', $category->id)
        ->assertSee('Yakin hapus?')
        ->call('deleteIntegrityCategory', $category->id)
        ->assertSet('confirmingIntegrityId', null)
        ->assertDontSee('Kategori Uji Hapus');

    expect(KpiIntegrityCategory::find($category->id))->toBeNull();
});

test('toggle satu baris lalu baris lain tidak mereset dirty-state baris pertama', function () {
    $a = KpiExtraCriterion::create(['name' => 'A', 'description' => 'x', 'weight' => 5, 'is_active' => true]);
    $b = KpiExtraCriterion::create(['name' => 'B', 'description' => 'y', 'weight' => 5, 'is_active' => true]);

    $component = Livewire::test(KpiCategoriesPanel::class);

    $component->set("criteriaEdits.{$a->id}.is_active", false);
    expect($component->instance()->isCriterionDirty($a->id))->toBeTrue();

    // Menyentuh baris B (request/render terpisah) tidak boleh membuat baris A jadi "bersih" lagi.
    $component->set("criteriaEdits.{$b->id}.is_active", false);
    expect($component->instance()->isCriterionDirty($a->id))->toBeTrue()
        ->and($component->instance()->isCriterionDirty($b->id))->toBeTrue();

    $component->call('updateCriterion', $a->id);
    expect($component->instance()->isCriterionDirty($a->id))->toBeFalse()
        ->and($component->instance()->isCriterionDirty($b->id))->toBeTrue(); // B belum disimpan, tetap dirty

    expect($a->fresh()->is_active)->toBeFalse()
        ->and($b->fresh()->is_active)->toBeTrue(); // belum disimpan
});

test('komponen tetap: toggle bobot lalu bobot komponen lain tidak mereset dirty-state pertama', function () {
    $kinerja = KpiComponentWeight::where('component', 'KINERJA')->first();
    $kehadiran = KpiComponentWeight::where('component', 'KEHADIRAN')->first();

    $component = Livewire::test(KpiCategoriesPanel::class);

    $component->set("componentEdits.{$kinerja->id}.weight", 40);
    expect($component->instance()->isComponentDirty($kinerja->id))->toBeTrue();

    $component->set("componentEdits.{$kehadiran->id}.weight", 30);
    expect($component->instance()->isComponentDirty($kinerja->id))->toBeTrue()
        ->and($component->instance()->isComponentDirty($kehadiran->id))->toBeTrue();
});

test('deskripsi komponen tetap bisa diisi manual dan baru tersimpan setelah tekan simpan', function () {
    $kinerja = KpiComponentWeight::where('component', 'KINERJA')->first();

    $component = Livewire::test(KpiCategoriesPanel::class)
        ->set("componentEdits.{$kinerja->id}.description", 'Catatan manual dari admin');

    expect($component->instance()->isComponentDirty($kinerja->id))->toBeTrue()
        ->and($kinerja->fresh()->description)->not->toBe('Catatan manual dari admin'); // belum disimpan

    $component->call('updateComponentWeight', $kinerja->id);

    expect($kinerja->fresh()->description)->toBe('Catatan manual dari admin')
        ->and($component->instance()->isComponentDirty($kinerja->id))->toBeFalse();
});

test('kriteria tambahan baru langsung ikut ke total gabungan, deskripsi wajib diisi', function () {
    Livewire::test(KpiCategoriesPanel::class)
        ->set('newCriterionName', 'Kerja Sama Tim')
        ->set('newCriterionWeight', 10)
        ->call('storeCriterion')
        ->assertHasErrors(['newCriterionDescription' => 'required']);

    expect(KpiExtraCriterion::where('name', 'Kerja Sama Tim')->exists())->toBeFalse();

    Livewire::test(KpiCategoriesPanel::class)
        ->set('newCriterionName', 'Kerja Sama Tim')
        ->set('newCriterionDescription', 'Kontribusi dalam kerja tim lintas seksi')
        ->set('newCriterionWeight', 10)
        ->call('storeCriterion')
        ->assertSee('110%');

    expect(KpiExtraCriterion::where('name', 'Kerja Sama Tim')->exists())->toBeTrue();
});
