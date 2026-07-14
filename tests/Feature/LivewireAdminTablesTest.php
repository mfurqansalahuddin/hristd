<?php

use App\Livewire\Admin\AttendancesTable;
use App\Livewire\Admin\CutiTable;
use App\Livewire\Admin\DinasLuarTable;
use App\Livewire\Admin\EmployeesTable;
use App\Livewire\Admin\JabatanTable;
use App\Livewire\Admin\KpiCategoriesPanel;
use App\Livewire\Admin\KpiPeriodsTable;
use App\Livewire\Admin\LocationsTable;
use App\Livewire\Admin\SakitTable;
use App\Models\Attendance;
use App\Models\Department;
use App\Models\KpiComponentWeight;
use App\Models\KpiPeriod;
use App\Models\LeaveRequest;
use App\Models\Location;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('employees table filters by search without a full page reload', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    User::factory()->create(['name' => 'Budi Santoso']);
    User::factory()->create(['name' => 'Siti Aminah']);

    Livewire::actingAs($admin)->test(EmployeesTable::class)
        ->assertSee('Budi Santoso')
        ->assertSee('Siti Aminah')
        ->set('search', 'Budi')
        ->assertSee('Budi Santoso')
        ->assertDontSee('Siti Aminah')
        ->assertNoRedirect();
});

test('employees table deletes a row via a livewire action', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $employee = User::factory()->create(['name' => 'Dihapus Nanti']);

    Livewire::actingAs($admin)->test(EmployeesTable::class)
        ->call('delete', $employee->id)
        ->assertDontSee('Dihapus Nanti')
        ->assertNoRedirect();

    expect(User::find($employee->id))->toBeNull();
});

test('locations table paginates and deletes without a redirect', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $location = Location::create([
        'name' => 'Kantor Cabang', 'type' => 'RADIUS', 'lat' => 5.5, 'long' => 95.3, 'radius_meters' => 50,
    ]);

    Livewire::actingAs($admin)->test(LocationsTable::class)
        ->assertSee('Kantor Cabang')
        ->call('delete', $location->id)
        ->assertDontSee('Kantor Cabang')
        ->assertNoRedirect();

    expect(Location::find($location->id))->toBeNull();
});

test('attendances table filters by status realtime', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $onTime = User::factory()->create(['name' => 'Budi Ontime']);
    Attendance::factory()->create(['user_id' => $onTime->id, 'date' => '2026-07-14', 'clock_in' => '2026-07-14 07:45:00']);
    $late = User::factory()->create(['name' => 'Siti Telat']);
    Attendance::factory()->create(['user_id' => $late->id, 'date' => '2026-07-14', 'clock_in' => '2026-07-14 08:30:00']);

    Livewire::actingAs($admin)->test(AttendancesTable::class, ['date' => '2026-07-14'])
        ->set('date', '2026-07-14')
        ->assertSee('Budi Ontime')
        ->assertSee('Siti Telat')
        ->set('status', 'TERLAMBAT')
        ->assertSee('Siti Telat')
        ->assertDontSee('Budi Ontime')
        ->assertNoRedirect();
});

test('kpi categories panel updates weights and manages integrity categories live', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $kinerja = KpiComponentWeight::where('component', 'KINERJA')->firstOrFail();
    $originalWeight = $kinerja->weight;

    $component = Livewire::actingAs($admin)->test(KpiCategoriesPanel::class)
        ->set("weights.{$kinerja->id}", $originalWeight + 5)
        ->call('updateWeights')
        ->assertHasErrors('weights');

    expect(KpiComponentWeight::find($kinerja->id)->weight)->toBe($originalWeight);

    $component->set("weights.{$kinerja->id}", $originalWeight)
        ->call('storeIntegrityCategory')
        ->assertHasErrors(['newName', 'newDeductionValue'])
        ->set('newName', 'Terlambat Apel')
        ->set('newDeductionValue', 5)
        ->call('storeIntegrityCategory')
        ->assertHasNoErrors()
        ->assertSee('Terlambat Apel')
        ->assertNoRedirect();
});

test('kpi periods table opens a new period and updates status live', function () {
    $admin = User::factory()->create(['is_admin' => true]);

    $component = Livewire::actingAs($admin)->test(KpiPeriodsTable::class)
        ->set('month', 3)
        ->set('year', 2026)
        ->call('store')
        ->assertHasNoErrors()
        ->assertSee('3/2026')
        ->assertNoRedirect();

    $period = KpiPeriod::where('month', 3)->where('year', 2026)->firstOrFail();

    $component->set("statusEdits.{$period->id}", 'EVALUATION')
        ->call('updateStatus', $period->id)
        ->assertNoRedirect();

    expect($period->fresh()->status)->toBe('EVALUATION');
});

test('jabatan table assigns and vacates a position without locking it', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $root = Department::create(['name' => 'Direktur Utama', 'type' => 'DIREKSI']);
    $bagian = Department::create(['name' => 'Bagian Umum', 'type' => 'BAGIAN', 'parent_department_id' => $root->id]);
    $kabag = User::factory()->create(['name' => 'Budi Kabag', 'department_id' => $bagian->id, 'job_level' => 2]);
    $calon = User::factory()->create(['name' => 'Calon Kabag', 'department_id' => $bagian->id, 'job_level' => 4]);

    $component = Livewire::actingAs($admin)->test(JabatanTable::class)
        ->assertSee('Budi Kabag')
        ->call('vacate', $bagian->id)
        ->assertNoRedirect();

    expect($kabag->fresh()->job_level)->toBe(4);

    $component->set("selected.{$bagian->id}", $calon->id)
        ->call('assign', $bagian->id)
        ->assertSee('Calon Kabag')
        ->assertNoRedirect();

    expect($calon->fresh()->job_level)->toBe(2)
        ->and($calon->fresh()->department_id)->toBe($bagian->id);
});

test('jabatan table creates a new position and assigns an existing employee', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $root = Department::create(['name' => 'Direktur Utama', 'type' => 'DIREKSI']);
    $direkturBidang = Department::create(['name' => 'Direktur Teknik', 'type' => 'DIREKSI', 'parent_department_id' => $root->id, 'directorate' => 'TEKNIK']);
    $calon = User::factory()->create(['name' => 'Calon Kabag Baru', 'job_level' => 4]);

    Livewire::actingAs($admin)->test(JabatanTable::class)
        ->call('openCreateForm')
        ->set('newName', 'Bagian Pemasaran')
        ->set('newType', 'BAGIAN')
        ->set('newParentId', $direkturBidang->id)
        ->set('newUserId', $calon->id)
        ->call('createJabatan')
        ->assertHasNoErrors()
        ->assertSee('Bagian Pemasaran')
        ->assertSee('Calon Kabag Baru')
        ->assertNoRedirect();

    $department = Department::where('name', 'Bagian Pemasaran')->firstOrFail();
    expect($department->parent_department_id)->toBe($direkturBidang->id)
        ->and($calon->fresh()->department_id)->toBe($department->id)
        ->and($calon->fresh()->job_level)->toBe(2);
});

test('cuti table creates a manual entry and approves an app-submitted request', function () {
    Storage::fake('public');
    $admin = User::factory()->create(['is_admin' => true]);
    $employee = User::factory()->create(['name' => 'Cuti Pegawai']);

    Livewire::actingAs($admin)->test(CutiTable::class)
        ->call('openCreateForm')
        ->set('newUserId', $employee->id)
        ->set('newStartDate', '2026-07-20')
        ->set('newEndDate', '2026-07-21')
        ->set('newReason', 'Acara keluarga')
        ->set('newStatus', 'APPROVED')
        ->call('createCuti')
        ->assertHasNoErrors()
        ->assertSee('Cuti Pegawai')
        ->assertNoRedirect();

    expect(LeaveRequest::where('user_id', $employee->id)->firstOrFail())
        ->status->toBe('APPROVED')
        ->source->toBe('HR_MANUAL');

    $pending = LeaveRequest::create([
        'user_id' => $employee->id,
        'type' => LeaveRequest::TYPE_CUTI,
        'start_date' => '2026-08-01',
        'end_date' => '2026-08-02',
        'reason' => 'Sakit keluarga',
        'status' => 'PENDING',
        'source' => 'APP',
    ]);

    Livewire::actingAs($admin)->test(CutiTable::class)
        ->call('openApprove', $pending->id)
        ->set('approveAttachment', UploadedFile::fake()->create('acc-direktur.pdf', 100))
        ->call('confirmApprove')
        ->assertNoRedirect();

    expect($pending->fresh())
        ->status->toBe('APPROVED')
        ->attachment_path->not->toBeNull();
});

test('cuti table rejects a request with a required reason', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $employee = User::factory()->create();
    $pending = LeaveRequest::create([
        'user_id' => $employee->id,
        'type' => LeaveRequest::TYPE_CUTI,
        'start_date' => '2026-08-01',
        'end_date' => '2026-08-02',
        'reason' => 'Acara pribadi',
        'status' => 'PENDING',
        'source' => 'APP',
    ]);

    Livewire::actingAs($admin)->test(CutiTable::class)
        ->call('openReject', $pending->id)
        ->call('confirmReject')
        ->assertHasErrors('rejectReason');

    Livewire::actingAs($admin)->test(CutiTable::class)
        ->call('openReject', $pending->id)
        ->set('rejectReason', 'Kuota cuti habis')
        ->call('confirmReject')
        ->assertHasNoErrors()
        ->assertNoRedirect();

    expect($pending->fresh())
        ->status->toBe('REJECTED')
        ->rejection_reason->toBe('Kuota cuti habis');
});

test('sakit table lists requests read-only', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $employee = User::factory()->create(['name' => 'Sakit Pegawai']);
    LeaveRequest::create([
        'user_id' => $employee->id,
        'type' => LeaveRequest::TYPE_SAKIT,
        'start_date' => '2026-07-20',
        'end_date' => '2026-07-20',
        'status' => 'PENDING',
        'source' => 'APP',
    ]);

    Livewire::actingAs($admin)->test(SakitTable::class)
        ->assertSee('Sakit Pegawai')
        ->assertSee('PENDING')
        ->assertNoRedirect();
});

test('dinas luar table creates entries for multiple employees sharing one batch', function () {
    Storage::fake('public');
    $admin = User::factory()->create(['is_admin' => true]);
    $employeeA = User::factory()->create(['name' => 'DL Pegawai A']);
    $employeeB = User::factory()->create(['name' => 'DL Pegawai B']);

    Livewire::actingAs($admin)->test(DinasLuarTable::class)
        ->call('openCreateForm')
        ->set('newUserIds', [$employeeA->id, $employeeB->id])
        ->set('newStartDate', '2026-07-20')
        ->set('newEndDate', '2026-07-22')
        ->set('newReason', 'Pelatihan')
        ->set('newAttachment', UploadedFile::fake()->create('sppd.pdf', 100))
        ->call('createDinasLuar')
        ->assertHasNoErrors()
        ->assertSee('DL Pegawai A')
        ->assertSee('DL Pegawai B')
        ->assertNoRedirect();

    $rows = LeaveRequest::where('type', LeaveRequest::TYPE_DINAS_LUAR)->get();

    expect($rows)->toHaveCount(2)
        ->and($rows->pluck('dl_batch_uuid')->unique())->toHaveCount(1)
        ->and($rows->pluck('status')->unique()->first())->toBe('APPROVED');
});
