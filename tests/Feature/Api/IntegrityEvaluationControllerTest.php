<?php

use App\Models\Department;
use App\Models\KpiIntegrityCategory;
use App\Models\KpiIntegrityEvaluation;
use App\Models\KpiPeriod;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

function makeStafDanRekanUntukIntegritas(): array
{
    $bagian = Department::create(['name' => 'Bagian Umum', 'type' => 'BAGIAN']);
    $seksi = Department::create(['name' => 'Seksi A', 'type' => 'SEKSI', 'parent_department_id' => $bagian->id]);
    $kasi = User::factory()->create(['job_level' => 3, 'department_id' => $seksi->id]);
    $staf = User::factory()->create(['job_level' => 4, 'department_id' => $seksi->id]);
    $rekan = User::factory()->create(['job_level' => 4, 'department_id' => $seksi->id]);

    return compact('kasi', 'staf', 'rekan');
}

test('show mengembalikan 8 kategori dengan deduction_value, ketiga slot boleh akses', function () {
    KpiPeriod::create(['month' => 7, 'year' => 2026, 'status' => 'EVALUATION']);
    ['kasi' => $kasi, 'staf' => $staf, 'rekan' => $rekan] = makeStafDanRekanUntukIntegritas();

    $response = $this->actingAs($kasi, 'sanctum')->getJson("/api/kpi/integrity-evaluations/{$staf->id}")->assertOk();
    expect($response->json('data'))->toHaveCount(KpiIntegrityCategory::count())
        ->and($response->json('data.0'))->toHaveKeys(['id', 'name', 'deduction_value', 'decision', 'description', 'photo_path']);

    $this->actingAs($rekan, 'sanctum')->getJson("/api/kpi/integrity-evaluations/{$staf->id}")->assertOk();

    $bukanPenilai = User::factory()->create(['job_level' => 3]);
    $this->actingAs($bukanPenilai, 'sanctum')->getJson("/api/kpi/integrity-evaluations/{$staf->id}")->assertStatus(403);
});

test('KURANGIN wajib alasan, foto opsional, BIARIN tidak perlu keduanya', function () {
    Storage::fake('public');
    KpiPeriod::create(['month' => 7, 'year' => 2026, 'status' => 'EVALUATION']);
    ['kasi' => $kasi, 'staf' => $staf] = makeStafDanRekanUntukIntegritas();
    $kategori = KpiIntegrityCategory::first();

    $this->actingAs($kasi, 'sanctum')->postJson("/api/kpi/integrity-evaluations/{$staf->id}", [
        'kpi_integrity_category_id' => $kategori->id, 'decision' => 'KURANGIN',
    ])->assertStatus(422)->assertJsonValidationErrors(['description']);

    $this->actingAs($kasi, 'sanctum')->postJson("/api/kpi/integrity-evaluations/{$staf->id}", [
        'kpi_integrity_category_id' => $kategori->id, 'decision' => 'KURANGIN',
        'description' => 'Terlambat berulang',
    ])->assertOk();

    $kategori2 = KpiIntegrityCategory::skip(1)->first();
    $this->actingAs($kasi, 'sanctum')->postJson("/api/kpi/integrity-evaluations/{$staf->id}", [
        'kpi_integrity_category_id' => $kategori2->id, 'decision' => 'BIARIN',
    ])->assertOk();

    expect(KpiIntegrityEvaluation::where('evaluator_id', $kasi->id)->where('reported_user_id', $staf->id)->count())->toBe(2);
});

test('edit tanpa unggah foto baru mempertahankan foto lama', function () {
    Storage::fake('public');
    KpiPeriod::create(['month' => 7, 'year' => 2026, 'status' => 'EVALUATION']);
    ['kasi' => $kasi, 'staf' => $staf] = makeStafDanRekanUntukIntegritas();
    $kategori = KpiIntegrityCategory::first();

    $this->actingAs($kasi, 'sanctum')->postJson("/api/kpi/integrity-evaluations/{$staf->id}", [
        'kpi_integrity_category_id' => $kategori->id, 'decision' => 'KURANGIN',
        'description' => 'Terlambat berulang', 'photo' => UploadedFile::fake()->image('bukti.jpg'),
    ])->assertOk();

    $photoPath = KpiIntegrityEvaluation::where('evaluator_id', $kasi->id)->where('reported_user_id', $staf->id)->first()->photo_path;
    expect($photoPath)->not->toBeNull();

    $this->actingAs($kasi, 'sanctum')->postJson("/api/kpi/integrity-evaluations/{$staf->id}", [
        'kpi_integrity_category_id' => $kategori->id, 'decision' => 'KURANGIN',
        'description' => 'Terlambat berulang, direvisi alasannya',
    ])->assertOk();

    $evaluation = KpiIntegrityEvaluation::where('evaluator_id', $kasi->id)->where('reported_user_id', $staf->id)->first();
    expect($evaluation->photo_path)->toBe($photoPath)
        ->and($evaluation->description)->toBe('Terlambat berulang, direvisi alasannya');
});

test('submit ulang kategori yang sama meng-update, bukan bikin baris baru', function () {
    Storage::fake('public');
    KpiPeriod::create(['month' => 7, 'year' => 2026, 'status' => 'EVALUATION']);
    ['kasi' => $kasi, 'staf' => $staf] = makeStafDanRekanUntukIntegritas();
    $kategori = KpiIntegrityCategory::first();

    $this->actingAs($kasi, 'sanctum')->postJson("/api/kpi/integrity-evaluations/{$staf->id}", [
        'kpi_integrity_category_id' => $kategori->id, 'decision' => 'BIARIN',
    ])->assertOk();

    $this->actingAs($kasi, 'sanctum')->postJson("/api/kpi/integrity-evaluations/{$staf->id}", [
        'kpi_integrity_category_id' => $kategori->id, 'decision' => 'KURANGIN',
        'description' => 'Ternyata ada masalah', 'photo' => UploadedFile::fake()->image('bukti.jpg'),
    ])->assertOk();

    $rows = KpiIntegrityEvaluation::where('evaluator_id', $kasi->id)->where('reported_user_id', $staf->id)->get();
    expect($rows)->toHaveCount(1)->and($rows->first()->decision)->toBe('KURANGIN');
});

test('rekan sejawat (PENILAI_3) bisa menilai integritas', function () {
    Storage::fake('public');
    KpiPeriod::create(['month' => 7, 'year' => 2026, 'status' => 'EVALUATION']);
    ['staf' => $staf, 'rekan' => $rekan] = makeStafDanRekanUntukIntegritas();
    $kategori = KpiIntegrityCategory::first();

    $this->actingAs($rekan, 'sanctum')->postJson("/api/kpi/integrity-evaluations/{$staf->id}", [
        'kpi_integrity_category_id' => $kategori->id, 'decision' => 'BIARIN',
    ])->assertOk();

    $evaluation = KpiIntegrityEvaluation::where('evaluator_id', $rekan->id)->where('reported_user_id', $staf->id)->first();
    expect($evaluation->evaluator_role)->toBe('PENILAI_3');
});
