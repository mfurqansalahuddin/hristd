<?php

use App\Models\Department;
use App\Models\KpiEvaluatorWeight;
use App\Models\User;
use App\Services\EvaluatorResolutionService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    foreach ([2, 3, 4] as $jobLevel) {
        foreach (['PENILAI_1' => 33, 'PENILAI_2' => 33, 'PENILAI_3' => 34] as $slot => $weight) {
            KpiEvaluatorWeight::create(['job_level' => $jobLevel, 'slot' => $slot, 'weight' => $weight]);
        }
    }

    $this->service = new EvaluatorResolutionService;
});

test('staf biasa dinilai kasi, kabag, dan 1 rekan seksi (siklus rekan)', function () {
    $org = makeOrgTree();
    $bagian = Department::create(['name' => 'Bagian Umum', 'type' => 'BAGIAN', 'parent_department_id' => $org['dirKeuangan']->id]);
    $seksi = Department::create(['name' => 'Seksi Gudang', 'type' => 'SEKSI', 'parent_department_id' => $bagian->id]);

    $kabag = User::factory()->create(['job_level' => 2, 'department_id' => $bagian->id]);
    $kasi = User::factory()->create(['job_level' => 3, 'department_id' => $seksi->id]);
    $staf1 = User::factory()->create(['job_level' => 4, 'department_id' => $seksi->id]);
    $staf2 = User::factory()->create(['job_level' => 4, 'department_id' => $seksi->id]);
    $staf3 = User::factory()->create(['job_level' => 4, 'department_id' => $seksi->id]);

    $result = $this->service->resolveFor($staf1, periodId: 1);

    expect($result[0]['slot'])->toBe('PENILAI_1')->and($result[0]['evaluator']->id)->toBe($kasi->id)
        ->and($result[1]['evaluator']->id)->toBe($kabag->id)
        ->and($result[2]['is_peer'])->toBeTrue()
        ->and($result[2]['evaluator']->id)->toBeIn([$staf2->id, $staf3->id])
        ->and($result[2]['evaluator']->id)->not->toBe($staf1->id);

    expect(collect($result)->sum('weight'))->toBe(100);
});

test('kasi kosong: staf penilai 1 turun ke kabag, penilai 2 tetap kabag (rangkap slot)', function () {
    $org = makeOrgTree();
    $bagian = Department::create(['name' => 'Bagian Umum', 'type' => 'BAGIAN', 'parent_department_id' => $org['dirKeuangan']->id]);
    $seksi = Department::create(['name' => 'Seksi Gudang', 'type' => 'SEKSI', 'parent_department_id' => $bagian->id]);

    $kabag = User::factory()->create(['job_level' => 2, 'department_id' => $bagian->id]);
    $staf1 = User::factory()->create(['job_level' => 4, 'department_id' => $seksi->id]);
    User::factory()->create(['job_level' => 4, 'department_id' => $seksi->id]);

    $result = $this->service->resolveFor($staf1, periodId: 1);

    expect($result[0]['evaluator']->id)->toBe($kabag->id)
        ->and($result[1]['evaluator']->id)->toBe($kabag->id);
});

test('kasi kosong dan staf sendirian: kabag mengisi ketiga slot (100%)', function () {
    $org = makeOrgTree();
    $bagian = Department::create(['name' => 'Bagian Umum', 'type' => 'BAGIAN', 'parent_department_id' => $org['dirKeuangan']->id]);
    $seksi = Department::create(['name' => 'Seksi Gudang', 'type' => 'SEKSI', 'parent_department_id' => $bagian->id]);

    $kabag = User::factory()->create(['job_level' => 2, 'department_id' => $bagian->id]);
    $stafSolo = User::factory()->create(['job_level' => 4, 'department_id' => $seksi->id]);

    $result = $this->service->resolveFor($stafSolo, periodId: 1);

    expect($result[0]['evaluator']->id)->toBe($kabag->id)
        ->and($result[1]['evaluator']->id)->toBe($kabag->id)
        ->and($result[2]['evaluator']->id)->toBe($kabag->id);
});

test('staf sendirian di seksi tapi kasi ada: penilai 3 diambil alih kasi', function () {
    $org = makeOrgTree();
    $bagian = Department::create(['name' => 'Bagian Umum', 'type' => 'BAGIAN', 'parent_department_id' => $org['dirKeuangan']->id]);
    $seksi = Department::create(['name' => 'Seksi Gudang', 'type' => 'SEKSI', 'parent_department_id' => $bagian->id]);

    User::factory()->create(['job_level' => 2, 'department_id' => $bagian->id]);
    $kasi = User::factory()->create(['job_level' => 3, 'department_id' => $seksi->id]);
    $stafSolo = User::factory()->create(['job_level' => 4, 'department_id' => $seksi->id]);

    $result = $this->service->resolveFor($stafSolo, periodId: 1);

    expect($result[2]['evaluator']->id)->toBe($kasi->id);
});

test('unit ti solo: staf melekat langsung ke unit, tanpa kasi', function () {
    $org = makeOrgTree();
    $unit = Department::create(['name' => 'Unit Teknologi Informasi', 'type' => 'UNIT', 'parent_department_id' => $org['dirKeuangan']->id]);
    $kanit = User::factory()->create(['job_level' => 2, 'department_id' => $unit->id]);
    $stafSolo = User::factory()->create(['job_level' => 4, 'department_id' => $unit->id]);

    $result = $this->service->resolveFor($stafSolo, periodId: 1);

    expect($result[0]['evaluator']->id)->toBe($kanit->id)
        ->and($result[1]['evaluator']->id)->toBe($kanit->id)
        ->and($result[2]['evaluator']->id)->toBe($kanit->id);
});

test('kasi dinilai kabag, direktur bidang, dan rekan sesama kasi (3 penilai, bukan 4)', function () {
    $org = makeOrgTree();
    $bagian = Department::create(['name' => 'Bagian Umum', 'type' => 'BAGIAN', 'parent_department_id' => $org['dirKeuangan']->id]);
    $seksiA = Department::create(['name' => 'Seksi A', 'type' => 'SEKSI', 'parent_department_id' => $bagian->id]);
    $seksiB = Department::create(['name' => 'Seksi B', 'type' => 'SEKSI', 'parent_department_id' => $bagian->id]);

    $kabag = User::factory()->create(['job_level' => 2, 'department_id' => $bagian->id]);
    $kasiA = User::factory()->create(['job_level' => 3, 'department_id' => $seksiA->id]);
    $kasiB = User::factory()->create(['job_level' => 3, 'department_id' => $seksiB->id]);

    $result = $this->service->resolveFor($kasiA, periodId: 1);

    expect($result)->toHaveCount(3)
        ->and($result[0]['evaluator']->id)->toBe($kabag->id)
        ->and($result[1]['evaluator']->id)->toBe($org['userDirKeuangan']->id)
        ->and($result[2]['evaluator']->id)->toBe($kasiB->id);

    // Grup 2 orang -> mutual (Cabang selalu 2 Kasi, §5.3).
    $reverse = $this->service->resolveFor($kasiB, periodId: 1);
    expect($reverse[2]['evaluator']->id)->toBe($kasiA->id);
});

test('kabag-setara dinilai direktur bidang, direktur utama, dan rekan sesama kabag', function () {
    $org = makeOrgTree();
    $bagianA = Department::create(['name' => 'Bagian A', 'type' => 'BAGIAN', 'parent_department_id' => $org['dirTeknik']->id]);
    $bagianB = Department::create(['name' => 'Bagian B', 'type' => 'BAGIAN', 'parent_department_id' => $org['dirTeknik']->id]);

    $kabagA = User::factory()->create(['job_level' => 2, 'department_id' => $bagianA->id]);
    $kabagB = User::factory()->create(['job_level' => 2, 'department_id' => $bagianB->id]);

    $result = $this->service->resolveFor($kabagA, periodId: 1);

    expect($result[0]['evaluator']->id)->toBe($org['userDirTeknik']->id)
        ->and($result[1]['evaluator']->id)->toBe($org['userDirut']->id)
        ->and($result[2]['evaluator']->id)->toBe($kabagB->id);
});

test('staf ahli: rekan diacak dari seluruh job_level 2, bukan dibatasi 1 direktorat', function () {
    $org = makeOrgTree();
    $stafAhli = Department::create(['name' => 'Staf Ahli Bidang Teknik', 'type' => 'STAF_AHLI', 'parent_department_id' => $org['dirTeknik']->id]);
    $bagianLainDirektorat = Department::create(['name' => 'Bagian Keuangan', 'type' => 'BAGIAN', 'parent_department_id' => $org['dirKeuangan']->id]);

    $userStafAhli = User::factory()->create(['job_level' => 2, 'department_id' => $stafAhli->id]);
    $kabagLain = User::factory()->create(['job_level' => 2, 'department_id' => $bagianLainDirektorat->id]);

    $result = $this->service->resolveFor($userStafAhli, periodId: 1);

    // Tidak ada Kabag lain di direktorat Teknik -> fallback ke pool seluruh job_level 2.
    expect($result[2]['evaluator']->id)->toBe($kabagLain->id);
});

test('grup rekan >= 3 orang: siklus, tidak ada yang menilai diri sendiri', function () {
    $org = makeOrgTree();
    $bagian = Department::create(['name' => 'Bagian Umum', 'type' => 'BAGIAN', 'parent_department_id' => $org['dirKeuangan']->id]);
    $seksi = Department::create(['name' => 'Seksi Gudang', 'type' => 'SEKSI', 'parent_department_id' => $bagian->id]);

    User::factory()->create(['job_level' => 2, 'department_id' => $bagian->id]);
    User::factory()->create(['job_level' => 3, 'department_id' => $seksi->id]);
    $stafs = User::factory(4)->create(['job_level' => 4, 'department_id' => $seksi->id]);

    $evaluatorIds = $stafs->map(fn (User $staf) => $this->service->resolveFor($staf, periodId: 1)[2]['evaluator']->id);

    foreach ($stafs as $index => $staf) {
        expect($evaluatorIds[$index])->not->toBe($staf->id);
    }

    // Setiap orang di grup jadi evaluator tepat satu rekan lain (siklus penuh).
    expect($evaluatorIds->unique()->sort()->values()->all())->toBe($stafs->pluck('id')->sort()->values()->all());
});

test('pramagang tetap ikut cakupan KPI penuh (dikonfirmasi user: pembiasaan sebelum berlaku ke gaji)', function () {
    $org = makeOrgTree();
    $bagian = Department::create(['name' => 'Bagian Umum', 'type' => 'BAGIAN', 'parent_department_id' => $org['dirKeuangan']->id]);
    $seksi = Department::create(['name' => 'Seksi Gudang', 'type' => 'SEKSI', 'parent_department_id' => $bagian->id]);

    $kabag = User::factory()->create(['job_level' => 2, 'department_id' => $bagian->id]);
    $kasi = User::factory()->create(['job_level' => 3, 'department_id' => $seksi->id]);
    $pramagang = User::factory()->create(['job_level' => 4, 'department_id' => $seksi->id, 'employment_status' => 'PRAMAGANG']);

    $result = $this->service->resolveFor($pramagang, periodId: 1);

    expect($result[0]['evaluator']->id)->toBe($kasi->id)
        ->and($result[1]['evaluator']->id)->toBe($kabag->id);
});

test('pramagang bisa terpilih jadi rekan sejawat (Penilai 3) untuk staf lain', function () {
    $org = makeOrgTree();
    $bagian = Department::create(['name' => 'Bagian Umum', 'type' => 'BAGIAN', 'parent_department_id' => $org['dirKeuangan']->id]);
    $seksi = Department::create(['name' => 'Seksi Gudang', 'type' => 'SEKSI', 'parent_department_id' => $bagian->id]);

    User::factory()->create(['job_level' => 2, 'department_id' => $bagian->id]);
    User::factory()->create(['job_level' => 3, 'department_id' => $seksi->id]);
    $staf = User::factory()->create(['job_level' => 4, 'department_id' => $seksi->id]);
    $pramagang = User::factory()->create(['job_level' => 4, 'department_id' => $seksi->id, 'employment_status' => 'PRAMAGANG']);

    // Satu-satunya "rekan" yang ada berstatus PRAMAGANG -> tetap sah jadi Penilai 3 (bukan dikecualikan lagi).
    $result = $this->service->resolveFor($staf, periodId: 1);

    expect($result[2]['evaluator']->id)->toBe($pramagang->id);
});

test('evaluateesFor mengembalikan kebalikan dari resolveFor', function () {
    $org = makeOrgTree();
    $bagian = Department::create(['name' => 'Bagian Umum', 'type' => 'BAGIAN', 'parent_department_id' => $org['dirKeuangan']->id]);
    $seksi = Department::create(['name' => 'Seksi Gudang', 'type' => 'SEKSI', 'parent_department_id' => $bagian->id]);

    $kabag = User::factory()->create(['job_level' => 2, 'department_id' => $bagian->id]);
    $kasi = User::factory()->create(['job_level' => 3, 'department_id' => $seksi->id]);
    $staf = User::factory()->create(['job_level' => 4, 'department_id' => $seksi->id]);

    $evaluatees = $this->service->evaluateesFor($kasi, periodId: 1);

    expect($evaluatees->pluck('evaluee.id'))->toContain($staf->id);

    $entry = $evaluatees->firstWhere('evaluee.id', $staf->id);
    expect($entry['slot'])->toBe('PENILAI_1')->and($entry['is_peer'])->toBeFalse();
});
