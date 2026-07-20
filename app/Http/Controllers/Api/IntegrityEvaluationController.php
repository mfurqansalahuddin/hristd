<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\KpiIntegrityCategory;
use App\Models\KpiIntegrityEvaluation;
use App\Models\KpiPeriod;
use App\Models\User;
use App\Services\EvaluatorResolutionService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/**
 * Penilaian Integritas oleh Penilai 1/2/3 — per kategori (8 kategori),
 * pilih BIARIN atau KURANGIN, wajib alasan (foto opsional) kalau KURANGIN.
 * Foto tidak diupload ulang = foto lama dipertahankan (updateOrCreate tidak
 * boleh menimpa photo_path jadi null cuma karena request ini tanpa file baru).
 * Berbeda dari
 * KpiEvaluationController: ketiga slot (termasuk PENILAI_3/rekan) ikut menilai
 * di sini — rekan dibebaskan dari Kinerja, dialihkan penuh ke Integritas.
 * Digabung 1 layar dengan Beri Penilaian Kinerja di mobile (2026-07-20) — tidak
 * ada list "pending" terpisah, pakai KpiEvaluationController::pending() (unified).
 */
class IntegrityEvaluationController extends Controller
{
    public function show(Request $request, User $user, EvaluatorResolutionService $service)
    {
        $period = KpiPeriod::current();
        $this->authorizeEvaluator($request->user(), $user, $period, $service);

        $myDecisions = KpiIntegrityEvaluation::where('reported_user_id', $user->id)
            ->where('period_id', $period->id)
            ->where('evaluator_id', $request->user()->id)
            ->get()
            ->keyBy('kpi_integrity_category_id');

        $categories = KpiIntegrityCategory::all()
            ->map(fn (KpiIntegrityCategory $category) => [
                'id' => $category->id,
                'name' => $category->name,
                'deduction_value' => $category->deduction_value,
                'decision' => $myDecisions[$category->id]->decision ?? null,
                'description' => $myDecisions[$category->id]->description ?? null,
                'photo_path' => $myDecisions[$category->id]->photo_path ?? null,
            ]);

        return response()->json(['data' => $categories]);
    }

    public function store(Request $request, User $user, EvaluatorResolutionService $service)
    {
        $period = KpiPeriod::current();
        $slot = $this->authorizeEvaluator($request->user(), $user, $period, $service);
        abort_unless($period->isScoringOpen(), 422, 'Periode KPI sedang tidak dalam masa penilaian.');

        $data = $request->validate([
            'kpi_integrity_category_id' => ['required', 'integer', 'exists:kpi_integrity_categories,id'],
            'decision' => ['required', 'in:BIARIN,KURANGIN'],
            'description' => ['required_if:decision,KURANGIN', 'nullable', 'string'],
            'photo' => ['nullable', 'image', 'max:5120'],
        ]);

        $keys = [
            'evaluator_id' => $request->user()->id,
            'reported_user_id' => $user->id,
            'period_id' => $period->id,
            'kpi_integrity_category_id' => $data['kpi_integrity_category_id'],
        ];

        $photoPath = $request->hasFile('photo')
            ? $request->file('photo')->store('integrity-evaluations', 'public')
            : KpiIntegrityEvaluation::where($keys)->value('photo_path');

        $evaluation = KpiIntegrityEvaluation::updateOrCreate($keys, [
            'evaluator_role' => $slot,
            'decision' => $data['decision'],
            'description' => $data['description'] ?? null,
            'photo_path' => $photoPath,
        ]);

        return response()->json($evaluation);
    }

    /** @return string slot penilai (PENILAI_1..3) milik $evaluator terhadap $subject */
    private function authorizeEvaluator(User $evaluator, User $subject, ?KpiPeriod $period, EvaluatorResolutionService $service): string
    {
        if (! $period) {
            throw ValidationException::withMessages(['period_id' => ['Tidak ada periode KPI berjalan.']]);
        }

        $slot = collect($service->resolveFor($subject, $period->id))->first(fn (array $s) => $s['evaluator']?->id === $evaluator->id);

        abort_unless($slot, 403, 'Anda bukan penilai integritas pegawai ini.');

        return $slot['slot'];
    }
}
