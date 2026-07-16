<?php

use App\Models\Department;
use App\Models\KpiIntegrityCategory;
use App\Models\KpiPeriod;
use App\Models\User;
use App\Models\ViolationReport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(fn () => KpiPeriod::create(['month' => 7, 'year' => 2026, 'status' => 'DRAFT']));

test('siapa saja bisa lapor pakaian dinas, wajib foto', function () {
    Storage::fake('public');
    $reporter = User::factory()->create();
    $target = User::factory()->create();

    $this->actingAs($reporter, 'sanctum')->postJson('/api/violations', [
        'reported_user_id' => $target->id, 'category' => 'PAKAIAN_DINAS', 'incident_date' => now()->toDateString(),
    ])->assertStatus(422)->assertJsonValidationErrors('photo');

    $this->actingAs($reporter, 'sanctum')->postJson('/api/violations', [
        'reported_user_id' => $target->id, 'category' => 'PAKAIAN_DINAS', 'incident_date' => now()->toDateString(),
        'photo' => UploadedFile::fake()->image('foto.jpg'),
    ])->assertCreated();
});

test('aduan integritas hanya boleh dari atasan langsung', function () {
    $bagian = Department::create(['name' => 'Bagian Umum', 'type' => 'BAGIAN']);
    $seksi = Department::create(['name' => 'Seksi A', 'type' => 'SEKSI', 'parent_department_id' => $bagian->id]);
    $kasi = User::factory()->create(['job_level' => 3, 'department_id' => $seksi->id]);
    $staf = User::factory()->create(['job_level' => 4, 'department_id' => $seksi->id]);
    $orangLain = User::factory()->create(['job_level' => 3]);
    $kategori = KpiIntegrityCategory::first();

    $this->actingAs($orangLain, 'sanctum')->postJson('/api/violations', [
        'reported_user_id' => $staf->id, 'category' => 'INTEGRITAS', 'integrity_category_id' => $kategori->id,
        'description' => 'Datang terlambat terus', 'incident_date' => now()->toDateString(),
    ])->assertStatus(403);

    $this->actingAs($kasi, 'sanctum')->postJson('/api/violations', [
        'reported_user_id' => $staf->id, 'category' => 'INTEGRITAS', 'integrity_category_id' => $kategori->id,
        'description' => 'Datang terlambat terus', 'incident_date' => now()->toDateString(),
    ])->assertCreated();

    expect(ViolationReport::where('reported_user_id', $staf->id)->count())->toBe(1);
});
