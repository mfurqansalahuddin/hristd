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
     * Membangun struktur organisasi yang mencakup 3 edge case atasan-bawahan
     * di plan.md: Kasi kosong, Staf Ahli, Unit TI sendirian.
     */
    public function run(): void
    {
        $direksi = Department::create(['name' => 'Direksi', 'type' => 'PUSAT']);
        $bagianUmum = Department::create(['name' => 'Bagian Umum', 'type' => 'CABANG']);
        $bagianKasiKosong = Department::create(['name' => 'Bagian Tanpa Kasi', 'type' => 'CABANG']);
        $unitTi = Department::create(['name' => 'Unit TI', 'type' => 'UNIT']);
        $spi = Department::create(['name' => 'SPI', 'type' => 'SPI']);

        $direktur = User::factory()->create([
            'name' => 'Direktur Utama', 'email' => 'direktur@example.com',
            'department_id' => $direksi->id, 'job_level' => 1, 'is_admin' => true,
        ]);

        // Kasus normal: Kabag -> Kasi -> Staf (>1 rekan sejawat)
        $kabagUmum = User::factory()->create([
            'name' => 'Kabag Umum', 'email' => 'kabag.umum@example.com',
            'department_id' => $bagianUmum->id, 'job_level' => 2,
            'direct_supervisor_id' => $direktur->id, 'final_supervisor_id' => $direktur->id,
        ]);
        $kasiUmum = User::factory()->create([
            'name' => 'Kasi Umum', 'email' => 'kasi.umum@example.com',
            'department_id' => $bagianUmum->id, 'job_level' => 3,
            'direct_supervisor_id' => $kabagUmum->id, 'final_supervisor_id' => $kabagUmum->id,
        ]);
        User::factory(3)->create([
            'department_id' => $bagianUmum->id, 'job_level' => 4,
            'direct_supervisor_id' => $kasiUmum->id, 'final_supervisor_id' => $kabagUmum->id,
        ]);

        // Edge case: Kasi Kosong -> bobot 33% ditarik Kabag, Kabag pegang 66%
        $kabagKosong = User::factory()->create([
            'name' => 'Kabag Tanpa Kasi', 'email' => 'kabag.kosong@example.com',
            'department_id' => $bagianKasiKosong->id, 'job_level' => 2,
            'direct_supervisor_id' => $direktur->id, 'final_supervisor_id' => $direktur->id,
        ]);
        User::factory(2)->create([
            'department_id' => $bagianKasiKosong->id, 'job_level' => 4,
            'direct_supervisor_id' => $kabagKosong->id, 'final_supervisor_id' => $kabagKosong->id,
        ]);

        // Edge case: Unit TI staf sendirian -> Kanit dapat 100% hak evaluasi kinerja
        User::factory()->create([
            'name' => 'Kanit TI', 'email' => 'kanit.ti@example.com',
            'department_id' => $unitTi->id, 'job_level' => 2,
            'direct_supervisor_id' => $direktur->id, 'final_supervisor_id' => $direktur->id,
        ]);

        // Edge case: Staf Ahli (tanpa bawahan) -> dinilai langsung Direktur Bidang
        User::factory()->create([
            'name' => 'Staf Ahli', 'email' => 'staf.ahli@example.com',
            'department_id' => $spi->id, 'job_level' => 2,
            'direct_supervisor_id' => $direktur->id, 'final_supervisor_id' => $direktur->id,
        ]);
    }
}
