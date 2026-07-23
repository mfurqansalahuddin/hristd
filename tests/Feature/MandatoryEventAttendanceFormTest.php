<?php

use App\Livewire\Admin\MandatoryEventAttendanceForm;
use App\Models\Department;
use App\Models\KpiMandatoryEvent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('peserta dikelompokkan: pejabat terpisah dari staf per bagian/cabang, dan search/filter berfungsi', function () {
    $bagian = Department::create(['name' => 'Bagian Umum & SDM', 'type' => 'BAGIAN']);
    $seksi = Department::create(['name' => 'Seksi SDM', 'type' => 'SEKSI', 'parent_department_id' => $bagian->id]);

    $kabag = User::factory()->create(['job_level' => 2, 'department_id' => $bagian->id, 'name' => 'Budi Kabag']);
    $kasi = User::factory()->create(['job_level' => 3, 'department_id' => $seksi->id, 'name' => 'Sari Kasi']);
    $staff = User::factory()->create(['job_level' => 4, 'department_id' => $seksi->id, 'name' => 'Andi Staff']);

    $event = KpiMandatoryEvent::create(['name' => 'Apel Pagi', 'date' => now()->toDateString()]);
    $event->participants()->createMany([
        ['user_id' => $kabag->id],
        ['user_id' => $kasi->id],
        ['user_id' => $staff->id],
    ]);

    $component = Livewire::test(MandatoryEventAttendanceForm::class, ['event' => $event]);

    // Kabag & Kasi (job_level 2-3) tampil sebagai "Pejabat", terpisah dari staf.
    expect($component->viewData('leaders')->map(fn ($p) => $p->user->name)->all())
        ->toBe(['Budi Kabag', 'Sari Kasi']);

    // Staf (job_level 4) dikelompokkan per Bagian/Cabang - seksinya naik ke induk (Bagian Umum & SDM), bukan "Seksi SDM".
    $staffGroups = $component->viewData('staffGroups');
    expect($staffGroups)->toHaveCount(1);
    expect($staffGroups->first()['label'])->toBe('Bagian Umum & SDM');
    expect($staffGroups->first()['people']->map(fn ($p) => $p->user->name)->all())->toBe(['Andi Staff']);

    // Search menyaring ke satu orang saja lintas kedua kelompok.
    $component->set('search', 'andi');
    expect($component->viewData('leaders'))->toHaveCount(0);
    expect($component->viewData('staffGroups')->first()['people'])->toHaveCount(1);
    $component->set('search', '');

    // Filter status: belum ada presensi terisi, jadi filter "hadir" tidak menyisakan siapa pun.
    expect($component->viewData('noResults'))->toBeFalse();
    $component->set('filterStatus', 'hadir');
    expect($component->viewData('noResults'))->toBeTrue();
});

test('grouping_mode gabung menampilkan satu kelompok gabungan, tidak dipisah pejabat/bagian', function () {
    $bagian = Department::create(['name' => 'Bagian Umum & SDM', 'type' => 'BAGIAN']);

    $kabag = User::factory()->create(['job_level' => 2, 'department_id' => $bagian->id, 'name' => 'Budi Kabag']);
    $staff = User::factory()->create(['job_level' => 4, 'department_id' => $bagian->id, 'name' => 'Andi Staff']);

    $event = KpiMandatoryEvent::create([
        'name' => 'Kegiatan Acak Atasan', 'date' => now()->toDateString(), 'grouping_mode' => 'gabung',
    ]);
    $event->participants()->createMany([
        ['user_id' => $kabag->id],
        ['user_id' => $staff->id],
    ]);

    $component = Livewire::test(MandatoryEventAttendanceForm::class, ['event' => $event]);

    expect($component->viewData('leaders'))->toHaveCount(0);

    $staffGroups = $component->viewData('staffGroups');
    expect($staffGroups)->toHaveCount(1);
    expect($staffGroups->first()['label'])->toBe('Semua Peserta');
    expect($staffGroups->first()['people']->map(fn ($p) => $p->user->name)->all())
        ->toBe(['Andi Staff', 'Budi Kabag']);
});

test('alasan yang sudah tersimpan terkunci (ringkas) sampai diubah manual atau status berganti, dan ringkasan per kelompok akurat', function () {
    $bagian = Department::create(['name' => 'Bagian Keuangan', 'type' => 'BAGIAN']);
    $staff = User::factory()->create(['job_level' => 4, 'department_id' => $bagian->id, 'name' => 'Rani Staff']);

    $event = KpiMandatoryEvent::create(['name' => 'Apel Sore', 'date' => now()->toDateString()]);
    $participant = $event->participants()->create(['user_id' => $staff->id]);

    $component = Livewire::test(MandatoryEventAttendanceForm::class, ['event' => $event])
        ->set("edits.{$participant->id}.status", 'TIDAK_HADIR')
        ->set("edits.{$participant->id}.reason", 'Sakit, ada surat dokter')
        ->call('save', $participant->id);

    // Ringkasan kelompok staf (Bagian Keuangan) mencerminkan status yang baru disimpan.
    expect($component->viewData('staffGroups')->first()['summary'])
        ->toBe(['hadir' => 0, 'telat' => 0, 'tidak_hadir' => 1]);

    // Sudah tersimpan & bersih -> kotak alasan terkunci: tombol "Ubah" tampil, bukan "Kunci".
    $component->assertSee('Ubah')->assertDontSee('Kunci');

    // Klik "Ubah" membuka lagi kotak alasan untuk diedit.
    $component->call('editReason', $participant->id)->assertSee('Kunci');

    // Tanpa editReason(): mengganti status membuat baris dirty lagi, otomatis membuka kotak alasan.
    Livewire::test(MandatoryEventAttendanceForm::class, ['event' => $event])
        ->set("edits.{$participant->id}.status", 'TELAT')
        ->assertSee('Kunci');
});
