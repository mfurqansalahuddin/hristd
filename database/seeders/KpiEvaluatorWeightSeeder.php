<?php

namespace Database\Seeders;

use App\Models\KpiEvaluatorWeight;
use Illuminate\Database\Seeder;

class KpiEvaluatorWeightSeeder extends Seeder
{
    /**
     * Seragam 33/33/34 di semua level (Staf, Kasi, Kabag-setara) — lihat
     * docs/kpi-calculation.md §1 dan plan.md §5.4 (revert 2026-07-15, Kasi
     * kembali ke 3 penilai setelah sempat jadi 4/25-25-25-25).
     */
    public function run(): void
    {
        foreach ([2, 3, 4] as $jobLevel) {
            foreach (['PENILAI_1' => 33, 'PENILAI_2' => 33, 'PENILAI_3' => 34] as $slot => $weight) {
                KpiEvaluatorWeight::updateOrCreate(
                    ['job_level' => $jobLevel, 'slot' => $slot],
                    ['weight' => $weight]
                );
            }
        }
    }
}
