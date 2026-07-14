<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * Membangun pohon organisasi riil Perumdam Tirta Daroy sesuai plan.md §3.1:
     * 3 Direksi, seluruh Bagian/Cabang/Unit/SPI/PAL/Staf Ahli + Seksi terisi penuh
     * (tidak ada Kabag/Kasi kosong di seeder ini), dan jumlah Staf per Seksi
     * dibuat bervariasi (1, 2, 3, 4) untuk menutupi kasus solo, mutual (2 orang),
     * dan siklik (≥3 orang) di aturan rekan sejawat §5.3.
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

        User::factory()->create([
            'name' => 'Direktur Utama', 'username' => 'direktur.utama', 'email' => 'direktur.utama@tirtadaroy.id',
            'department_id' => $direkturUtama->id, 'job_level' => 1, 'is_admin' => true,
        ]);
        User::factory()->create(['department_id' => $direkturAdm->id, 'job_level' => 1]);
        User::factory()->create(['department_id' => $direkturTeknik->id, 'job_level' => 1]);

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

            User::factory()->create(['department_id' => $bagian->id, 'job_level' => 2]);

            foreach ($def['seksi'] as $seksiName) {
                $seksi = Department::create([
                    'name' => $seksiName,
                    'type' => 'SEKSI',
                    'parent_department_id' => $bagian->id,
                ]);

                User::factory()->create(['department_id' => $seksi->id, 'job_level' => 3]);

                $staffCount = $staffCountCycle[$cycleIndex % count($staffCountCycle)];
                $cycleIndex++;
                User::factory($staffCount)->create(['department_id' => $seksi->id, 'job_level' => 4]);
            }

            if ($def['type'] === 'UNIT') {
                // Kasus solo: staf melekat langsung ke Unit, tanpa Kasi (§3.3).
                User::factory()->create(['department_id' => $bagian->id, 'job_level' => 4]);
            }
        }

        $this->call(AttendanceSeeder::class);
    }
}
