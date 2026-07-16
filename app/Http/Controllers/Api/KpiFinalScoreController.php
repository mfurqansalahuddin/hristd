<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\KpiEvaluation;
use App\Models\KpiFinalScore;
use App\Models\KpiPeriod;
use App\Models\KpiPlan;
use Illuminate\Http\Request;

/**
 * §5.4 mobile-app.md — juga dipakai sbg tampilan masa sanggah (§9 plan.md).
 * Server yang menentukan level detail berdasar kpi_periods.status, bukan
 * client, supaya skor tidak bocor sebelum window sanggah dibuka (2026-07-16).
 */
class KpiFinalScoreController extends Controller
{
    private const HIDDEN_STATUSES = ['DRAFT', 'EVALUATION'];

    public function show(Request $request)
    {
        $data = $request->validate(['period_id' => ['required', 'integer', 'exists:kpi_periods,id']]);

        $period = KpiPeriod::findOrFail($data['period_id']);
        $user = $request->user();

        $plans = KpiPlan::where('user_id', $user->id)->where('period_id', $period->id)->where('status', 'APPROVED')->get();

        if (in_array($period->status, self::HIDDEN_STATUSES, true)) {
            return response()->json([
                'progress' => $plans->map(fn (KpiPlan $plan) => [
                    'kpi_plan_id' => $plan->id,
                    'evaluators_done' => KpiEvaluation::where('kpi_plan_id', $plan->id)->count(),
                    'evaluators_total' => 3,
                ]),
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
}
