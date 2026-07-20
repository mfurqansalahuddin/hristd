<?php

use App\Models\Department;
use App\Models\User;
use App\Services\LocationVisibilityService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->service = new LocationVisibilityService;
});

test('staf lihat diri sendiri & rekan 1 seksi yang sama, tidak lihat pejabat', function () {
    $org = makeOrgTree();
    $bagian = Department::create(['name' => 'Bagian Umum', 'type' => 'BAGIAN', 'parent_department_id' => $org['dirKeuangan']->id]);
    $seksiA = Department::create(['name' => 'Seksi A', 'type' => 'SEKSI', 'parent_department_id' => $bagian->id]);
    $seksiB = Department::create(['name' => 'Seksi B', 'type' => 'SEKSI', 'parent_department_id' => $bagian->id]);

    $staf = User::factory()->create(['job_level' => 4, 'department_id' => $seksiA->id]);
    $rekanSeksi = User::factory()->create(['job_level' => 4, 'department_id' => $seksiA->id]);
    $stafSeksiLain = User::factory()->create(['job_level' => 4, 'department_id' => $seksiB->id]);
    $kasi = User::factory()->create(['job_level' => 3, 'department_id' => $seksiA->id]);

    $visible = $this->service->visibleUserIdsFor($staf);

    expect($visible)->toContain($rekanSeksi->id)
        ->toContain($staf->id) // staf cuma punya 1 map — lokasinya sendiri ikut tampil
        ->not->toContain($stafSeksiLain->id)
        ->not->toContain($kasi->id);
});

test('kasi default lihat diri sendiri & seluruh pejabat se-perumdam lintas bagian & level', function () {
    $org = makeOrgTree();
    $bagianA = Department::create(['name' => 'Bagian A', 'type' => 'BAGIAN', 'parent_department_id' => $org['dirKeuangan']->id]);
    $bagianB = Department::create(['name' => 'Bagian B', 'type' => 'BAGIAN', 'parent_department_id' => $org['dirTeknik']->id]);
    $seksiA = Department::create(['name' => 'Seksi A', 'type' => 'SEKSI', 'parent_department_id' => $bagianA->id]);

    $kasiA = User::factory()->create(['job_level' => 3, 'department_id' => $seksiA->id]);
    $kabagB = User::factory()->create(['job_level' => 2, 'department_id' => $bagianB->id]);

    $visible = $this->service->visibleUserIdsFor($kasiA, scope: 'peers');

    expect($visible)->toContain($kabagB->id)
        ->toContain($org['userDirut']->id)
        ->toContain($kasiA->id); // pejabat lihat dirinya sendiri di tab "Pejabat"
});

test('kasi switch bawahan diperluas ke 1 bagian penuh, bukan cuma seksinya sendiri', function () {
    $org = makeOrgTree();
    $bagian = Department::create(['name' => 'Bagian Umum', 'type' => 'BAGIAN', 'parent_department_id' => $org['dirKeuangan']->id]);
    $seksiSekretariat = Department::create(['name' => 'Seksi Sekretariat', 'type' => 'SEKSI', 'parent_department_id' => $bagian->id]);
    $seksiPerlengkapan = Department::create(['name' => 'Seksi Perlengkapan', 'type' => 'SEKSI', 'parent_department_id' => $bagian->id]);

    $kasiSekretariat = User::factory()->create(['job_level' => 3, 'department_id' => $seksiSekretariat->id]);
    $stafSekretariat = User::factory()->create(['job_level' => 4, 'department_id' => $seksiSekretariat->id]);
    $stafPerlengkapan = User::factory()->create(['job_level' => 4, 'department_id' => $seksiPerlengkapan->id]);

    $visible = $this->service->visibleUserIdsFor($kasiSekretariat, scope: 'subordinates');

    expect($visible)->toContain($stafSekretariat->id)
        ->toContain($stafPerlengkapan->id); // beda seksi, satu bagian -> tetap terlihat (2026-07-16)
});

test('kabag switch bawahan otomatis mencakup semua seksi di bagiannya', function () {
    $org = makeOrgTree();
    $bagian = Department::create(['name' => 'Bagian Umum', 'type' => 'BAGIAN', 'parent_department_id' => $org['dirKeuangan']->id]);
    $seksiA = Department::create(['name' => 'Seksi A', 'type' => 'SEKSI', 'parent_department_id' => $bagian->id]);
    $seksiB = Department::create(['name' => 'Seksi B', 'type' => 'SEKSI', 'parent_department_id' => $bagian->id]);

    $kabag = User::factory()->create(['job_level' => 2, 'department_id' => $bagian->id]);
    $stafA = User::factory()->create(['job_level' => 4, 'department_id' => $seksiA->id]);
    $stafB = User::factory()->create(['job_level' => 4, 'department_id' => $seksiB->id]);

    $visible = $this->service->visibleUserIdsFor($kabag, scope: 'subordinates');

    expect($visible)->toContain($stafA->id)->toContain($stafB->id);
});

test('unit ti solo: kanit lihat staf yang melekat langsung ke node unit', function () {
    $org = makeOrgTree();
    $unit = Department::create(['name' => 'Unit Teknologi Informasi', 'type' => 'UNIT', 'parent_department_id' => $org['dirKeuangan']->id]);
    $kanit = User::factory()->create(['job_level' => 2, 'department_id' => $unit->id]);
    $stafSolo = User::factory()->create(['job_level' => 4, 'department_id' => $unit->id]);

    $visible = $this->service->visibleUserIdsFor($kanit, scope: 'subordinates');

    expect($visible)->toContain($stafSolo->id);
});

test('direksi default lihat pejabat (kabag-setara & kasi), exclude sesama direksi', function () {
    $org = makeOrgTree();
    $bagian = Department::create(['name' => 'Bagian Umum', 'type' => 'BAGIAN', 'parent_department_id' => $org['dirKeuangan']->id]);
    $seksi = Department::create(['name' => 'Seksi A', 'type' => 'SEKSI', 'parent_department_id' => $bagian->id]);

    $kabag = User::factory()->create(['job_level' => 2, 'department_id' => $bagian->id]);
    $kasi = User::factory()->create(['job_level' => 3, 'department_id' => $seksi->id]);
    $staf = User::factory()->create(['job_level' => 4, 'department_id' => $seksi->id]);

    $visible = $this->service->visibleUserIdsFor($org['userDirut'], levelFilter: null);

    expect($visible)->toContain($kabag->id)->toContain($kasi->id)
        ->not->toContain($staf->id)
        ->not->toContain($org['userDirKeuangan']->id);
});

test('direksi filter seluruh perumdam + sub-filter per bagian termasuk staf', function () {
    $org = makeOrgTree();
    $bagianTarget = Department::create(['name' => 'Bagian Target', 'type' => 'BAGIAN', 'parent_department_id' => $org['dirKeuangan']->id]);
    $seksiTarget = Department::create(['name' => 'Seksi Target', 'type' => 'SEKSI', 'parent_department_id' => $bagianTarget->id]);
    $bagianLain = Department::create(['name' => 'Bagian Lain', 'type' => 'BAGIAN', 'parent_department_id' => $org['dirTeknik']->id]);

    $stafTarget = User::factory()->create(['job_level' => 4, 'department_id' => $seksiTarget->id]);
    $stafLain = User::factory()->create(['job_level' => 4, 'department_id' => $bagianLain->id]);

    $visible = $this->service->visibleUserIdsFor($org['userDirut'], levelFilter: 'everyone', departmentFilter: $bagianTarget->id);

    expect($visible)->toContain($stafTarget->id)
        ->not->toContain($stafLain->id)
        ->not->toContain($org['userDirut']->id); // Direksi tidak pernah lihat lokasinya sendiri
});
