<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\KpiFinalScore;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class KpiFinalScoreController extends Controller
{
    public function index()
    {
        return view('pages.admin.kpi-final-scores.index', [
            'title' => 'Nilai Akhir & Persentase Gaji',
        ]);
    }

    /** Rekap CSV per periode untuk diserahkan ke Keuangan (buffer 2 hari sebelum payroll). */
    public function export(Request $request): StreamedResponse
    {
        $data = $request->validate(['period_id' => ['required', 'integer', 'exists:kpi_periods,id']]);

        $scores = KpiFinalScore::with(['user.department'])
            ->where('period_id', $data['period_id'])
            ->orderByDesc('grand_total_score')
            ->get();

        return response()->streamDownload(function () use ($scores) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'NIK', 'Nama', 'Jabatan', 'Departemen', 'Kinerja', 'Kehadiran',
                'Apel', 'Pakaian Dinas', 'Integritas', 'Grand Total', 'Persentase Gaji',
            ]);

            foreach ($scores as $score) {
                fputcsv($handle, [
                    $score->user->nik,
                    $score->user->name,
                    $score->user->jabatanLabel(),
                    $score->user->department?->name,
                    $score->score_kinerja,
                    $score->score_kehadiran,
                    $score->score_apel,
                    $score->score_pakaian,
                    $score->score_integritas,
                    $score->grand_total_score,
                    $score->salary_percentage,
                ]);
            }

            fclose($handle);
        }, "nilai-akhir-kpi-periode-{$data['period_id']}.csv", ['Content-Type' => 'text/csv']);
    }
}
