<?php

namespace Database\Seeders;

use App\Models\KpiEvaluatorWeight;
use Illuminate\Database\Seeder;

class KpiEvaluatorWeightSeeder extends Seeder
{
    /**
     * Seragam 50/50/0 di semua level (Staf, Kasi, Kabag-setara) — Penilai 3
     * (rekan sejawat) tidak lagi menilai Kinerja, dialihkan ke Integritas
     * (lihat kpi_integrity_source_weights). Baris PENILAI_3 dipertahankan
     * (bukan dihapus) supaya kinerjaScore()/extraCriterionScore() tidak
     * perlu berubah — weight 0 sudah menutup kontribusinya lewat fallback
     * `?? 0` yang sudah ada.
     */
    public function run(): void
    {
        foreach ([2, 3, 4] as $jobLevel) {
            foreach (['PENILAI_1' => 50, 'PENILAI_2' => 50, 'PENILAI_3' => 0] as $slot => $weight) {
                KpiEvaluatorWeight::updateOrCreate(
                    ['job_level' => $jobLevel, 'slot' => $slot],
                    ['weight' => $weight]
                );
            }
        }
    }
}
