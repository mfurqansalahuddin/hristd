<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\KpiEvaluation;
use App\Models\KpiFinalScore;
use App\Models\KpiPeriod;
use App\Models\KpiPlan;
use App\Models\User;
use App\Services\EvaluationProgressService;
use App\Services\EvaluatorResolutionService;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

/**
 * §5.4 mobile-app.md — juga dipakai sbg tampilan masa sanggah (§9 plan.md).
 * Server yang menentukan level detail berdasar kpi_periods.status, bukan
 * client, supaya skor tidak bocor sebelum window sanggah dibuka (2026-07-16).
 */
class KpiFinalScoreController extends Controller
{
    private const HIDDEN_STATUSES = ['DRAFT', 'WORKING', 'EVALUATION'];

    public function show(Request $request, EvaluatorResolutionService $resolver, EvaluationProgressService $progressService)
    {
        $data = $request->validate(['period_id' => ['required', 'integer', 'exists:kpi_periods,id']]);

        $period = KpiPeriod::findOrFail($data['period_id']);
        $user = $request->user();

        $plans = KpiPlan::where('user_id', $user->id)->where('period_id', $period->id)->where('status', 'APPROVED')->get();

        if (in_array($period->status, self::HIDDEN_STATUSES, true)) {
            return response()->json([
                'progress' => $this->buildProgress($user, $period, $plans, $resolver, $progressService),
            ]);
        }

        $finalScore = KpiFinalScore::with('extras.criterion')->where('user_id', $user->id)->where('period_id', $period->id)->first();

        $extraCriteria = $finalScore?->extras->map(fn ($extra) => [
            'id' => $extra->kpi_extra_criterion_id,
            'name' => $extra->criterion->name,
            'score' => $extra->score,
        ]) ?? [];

        $evaluations = KpiEvaluation::with('evaluator')
            ->whereIn('kpi_plan_id', $plans->pluck('id'))
            ->get()
            ->map(fn (KpiEvaluation $evaluation) => [
                'kpi_plan_id' => $evaluation->kpi_plan_id,
                'evaluator_role' => $evaluation->evaluator_role,
                // Penilai 3 (rekan) tetap anonim, §5.4 mobile-app.md.
                'evaluator_name' => $evaluation->evaluator_role === 'PENILAI_3' ? null : $evaluation->evaluator?->name,
                'score' => $evaluation->score,
                'note' => $evaluation->note,
            ]);

        return response()->json([
            'final_score' => $finalScore,
            'evaluations' => $evaluations,
            'extra_criteria' => $extraCriteria,
        ]);
    }

    /**
     * Progres agregat (bukan per rencana kerja) — Kinerja wajib PENILAI_1+2 dan
     * Integritas wajib PENILAI_1-3 dihitung lewat EvaluationProgressService
     * supaya aturan "siapa wajib menilai" tidak diduplikasi di sini (§7.1
     * kpi-calculation.md, PENILAI_3 dialihkan penuh ke Integritas 2026-07-20).
     */
    private function buildProgress(User $user, KpiPeriod $period, Collection $plans, EvaluatorResolutionService $resolver, EvaluationProgressService $progressService): ?array
    {
        if ($plans->isEmpty()) {
            return null;
        }

        $slots = collect($resolver->resolveFor($user, $period->id))->filter(fn (array $s) => $s['evaluator'] !== null);
        $kinerjaSlots = $slots->reject(fn (array $s) => $s['slot'] === 'PENILAI_3');

        return [
            'kinerja_done' => $kinerjaSlots->filter(fn (array $s) => $progressService->kinerjaDone($s['evaluator'], $user, $period))->count(),
            'kinerja_total' => $kinerjaSlots->count(),
            'integritas_done' => $slots->filter(fn (array $s) => $progressService->integritasDone($s['evaluator'], $user, $period))->count(),
            'integritas_total' => $slots->count(),
        ];
    }
}
