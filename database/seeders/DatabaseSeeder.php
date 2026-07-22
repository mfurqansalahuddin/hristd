<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    private RealEmployeeSeeder $realEmployees;

    private RealStaffSeeder $realStaff;

    public function __construct()
    {
        $this->realEmployees = new RealEmployeeSeeder;
        $this->realStaff = new RealStaffSeeder;
    }

    /**
     * Seed the application's database.
     *
     * Membangun pohon organisasi riil Perumdam Tirta Daroy sesuai plan.md §3.1:
     * 3 Direksi, seluruh Bagian/Cabang/Unit/SPI/PAL/Staf Ahli + Seksi terisi penuh
     * (tidak ada Kabag/Kasi kosong di seeder ini), diisi nama pejabat riil lewat
     * RealEmployeeSeeder dan sisanya (staf level 4, seksi yang belum ada datanya)
     * tetap factory random supaya jumlah staf per Seksi bervariasi (1, 2, 3, 4)
     * untuk menutupi kasus solo, mutual (2 orang), dan siklik (≥3 orang) di aturan
     * rekan sejawat §5.3.
     */
    public function run(): void
    {
        $direkturUtama = Department::create(['name' => 'Direktur Utama', 'type' => 'DIREKSI']);

        $direkturAdm = Department::create([
            'name' => 'Direktur ADM & Keuangan', 'type' => 'DIREKSI',
            'parent_department_id' => $direkturUtama->id, 'directorate' => 'KEUANGAN',
        ]);
        $direkturTeknik = Department::create([
            'name' => 'Direktur Teknik', 'type' => 'DIREKSI',
            'parent_department_id' => $direkturUtama->id, 'directorate' => 'TEKNIK',
        ]);

        $this->realEmployees->makeUser($direkturUtama, 1, 'SUPER_ADMIN');
        $this->realEmployees->makeUser($direkturAdm, 1);
        $this->realEmployees->makeUser($direkturTeknik, 1);

        $bagianDefs = [
            ['parent' => $direkturAdm, 'name' => 'Bagian Keuangan', 'type' => 'BAGIAN', 'seksi' => ['Seksi Anggaran', 'Seksi Kas/Gaji', 'Seksi Akuntansi']],
            ['parent' => $direkturAdm, 'name' => 'Bagian Umum', 'type' => 'BAGIAN', 'seksi' => ['Seksi Kepegawaian dan Hukum', 'Seksi Sekretariat dan ADM', 'Seksi Gudang', 'Seksi Perlengkapan']],
            ['parent' => $direkturAdm, 'name' => 'Bagian Hubungan Pelanggan', 'type' => 'BAGIAN', 'seksi' => ['Seksi Pelayanan Pelanggan', 'Seksi Pembaca Meter', 'Seksi Rekening']],
            ['parent' => $direkturAdm, 'name' => 'Satuan Pengawas Internal (SPI)', 'type' => 'SPI', 'seksi' => ['Seksi Pengawasan Bidang Umum & Keuangan', 'Seksi Pengawasan Bidang Teknik']],
            ['parent' => $direkturAdm, 'name' => 'Unit Teknologi Informasi', 'type' => 'UNIT', 'seksi' => []],
            ['parent' => $direkturAdm, 'name' => 'Staf Ahli Bidang Administrasi', 'type' => 'STAF_AHLI', 'seksi' => []],

            ['parent' => $direkturTeknik, 'name' => 'Bagian Perencanaan Teknik dan Pengawasan Teknik', 'type' => 'BAGIAN', 'seksi' => ['Seksi Perencanaan Teknik', 'Seksi Pengawasan Teknik']],
            ['parent' => $direkturTeknik, 'name' => 'Bagian Produksi', 'type' => 'BAGIAN', 'seksi' => ['Seksi Operasi', 'Seksi Laboratorium', 'Seksi Pemeliharaan']],
            ['parent' => $direkturTeknik, 'name' => 'Bagian Transmisi dan Distribusi', 'type' => 'BAGIAN', 'seksi' => ['Seksi Sistem Pendistribusian Air', 'Seksi Penanganan Kebocoran & Pengendalian Kehilangan Air']],
            ['parent' => $direkturTeknik, 'name' => 'Bagian PAL (Pengolahan Air Limbah)', 'type' => 'PAL', 'seksi' => ['Seksi ADM Bagian Pengolahan Air Limbah', 'Seksi Teknik Bagian Pengolahan Air Limbah']],
            ['parent' => $direkturTeknik, 'name' => 'Cabang Sultan Iskandar Muda', 'type' => 'CABANG', 'seksi' => ['Seksi Adm Cabang Sultan Iskandar Muda', 'Seksi Teknik Cabang Sultan Iskandar Muda']],
            ['parent' => $direkturTeknik, 'name' => 'Cabang Teuku Nyak Arief', 'type' => 'CABANG', 'seksi' => ['Seksi Adm Cabang Teuku Nyak Arief', 'Seksi Teknik Cabang Teuku Nyak Arief']],
            ['parent' => $direkturTeknik, 'name' => 'Cabang Syiah Kuala', 'type' => 'CABANG', 'seksi' => ['Seksi Adm Cabang Syiah Kuala', 'Seksi Teknik Cabang Syiah Kuala']],
            ['parent' => $direkturTeknik, 'name' => 'Cabang Teuku Umar', 'type' => 'CABANG', 'seksi' => ['Seksi Adm Cabang Teuku Umar', 'Seksi Teknik Cabang Teuku Umar']],
            ['parent' => $direkturTeknik, 'name' => 'Staf Ahli Bidang Teknik', 'type' => 'STAF_AHLI', 'seksi' => []],
        ];

        // ponytail: variasi jumlah staf per seksi cukup di-cycle 1..4, tak perlu didaftar manual satu-satu.
        $staffCountCycle = [1, 2, 3, 4];
        $cycleIndex = 0;

        foreach ($bagianDefs as $def) {
            $bagian = Department::create([
                'name' => $def['name'],
                'type' => $def['type'],
                'parent_department_id' => $def['parent']->id,
            ]);

            $this->realEmployees->makeUser($bagian, 2);

            foreach ($def['seksi'] as $seksiName) {
                $seksi = Department::create([
                    'name' => $seksiName,
                    'type' => 'SEKSI',
                    'parent_department_id' => $bagian->id,
                ]);

                $this->realEmployees->makeUser($seksi, 3);

                if ($this->realStaff->makeUsersFor($seksi) === 0) {
                    $staffCount = $staffCountCycle[$cycleIndex % count($staffCountCycle)];
                    $cycleIndex++;
                    User::factory($staffCount)->create(['department_id' => $seksi->id, 'job_level' => 4]);
                }
            }

            if ($def['type'] === 'UNIT' && $this->realStaff->makeUsersFor($bagian) === 0) {
                // Kasus solo: staf melekat langsung ke Unit, tanpa Kasi (§3.3).
                User::factory()->create(['department_id' => $bagian->id, 'job_level' => 4]);
            }
        }

        $this->call(LocationSeeder::class);
        $this->call(AttendanceSeeder::class);
        $this->call(KpiEvaluatorWeightSeeder::class);
    }
}
