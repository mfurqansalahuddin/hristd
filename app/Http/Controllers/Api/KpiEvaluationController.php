<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\KpiEvaluation;
use App\Models\KpiExtraCriterion;
use App\Models\KpiExtraCriterionScore;
use App\Models\KpiPeriod;
use App\Models\KpiPlan;
use App\Models\User;
use App\Services\EvaluatorResolutionService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/**
 * §14.5/§15.4 mobile-app.md sub-tab 5.4 — Beri Penilaian.
 */
class KpiEvaluationController extends Controller
{
    public function pending(Request $request, EvaluatorResolutionService $service)
    {
        $period = KpiPeriod::current();

        if (! $period) {
            return response()->json(['data' => []]);
        }

        $data = $service->evaluateesFor($request->user(), $period->id)
            ->map(function (array $entry) use ($period, $request) {
                $plans = KpiPlan::where('user_id', $entry['evaluee']->id)->where('period_id', $period->id)->where('status', 'APPROVED')->get();
                $evaluatedCount = KpiEvaluation::whereIn('kpi_plan_id', $plans->pluck('id'))->where('evaluator_id', $request->user()->id)->count();

                $activeCriteria = KpiExtraCriterion::where('is_active', true)->pluck('id');
                $criteriaScoredCount = KpiExtraCriterionScore::whereIn('kpi_extra_criterion_id', $activeCriteria)
                    ->where('user_id', $entry['evaluee']->id)
                    ->where('period_id', $period->id)
                    ->where('evaluator_id', $request->user()->id)
                    ->count();

                return [
                    'user_id' => $entry['evaluee']->id,
                    'name' => $entry['evaluee']->name,
                    'evaluator_role' => $entry['slot'],
                    'is_peer' => $entry['is_peer'],
                    'self_assessment_done' => $plans->isNotEmpty() && $plans->every(fn (KpiPlan $plan) => $plan->self_assessment_score !== null),
                    'already_evaluated' => $plans->isNotEmpty() && $evaluatedCount >= $plans->count()
                        && $criteriaScoredCount >= $activeCriteria->count(),
                ];
            })
            ->values();

        return response()->json(['data' => $data]);
    }

    public function show(Request $request, User $user, EvaluatorResolutionService $service)
    {
        $period = KpiPeriod::current();
        $this->authorizeEvaluator($request->user(), $user, $period, $service);

        $plans = KpiPlan::where('user_id', $user->id)->where('period_id', $period->id)->where('status', 'APPROVED')->get();

        $myScores = KpiExtraCriterionScore::where('user_id', $user->id)
            ->where('period_id', $period->id)
            ->where('evaluator_id', $request->user()->id)
            ->get()
            ->keyBy('kpi_extra_criterion_id');

        $extraCriteria = KpiExtraCriterion::where('is_active', true)->get()
            ->map(fn (KpiExtraCriterion $criterion) => [
                'id' => $criterion->id,
                'name' => $criterion->name,
                'description' => $criterion->description,
                'score' => $myScores[$criterion->id]->score ?? null,
                'reason' => $myScores[$criterion->id]->reason ?? null,
            ]);

        return response()->json(['data' => $plans, 'extra_criteria' => $extraCriteria]);
    }

    public function store(Request $request, User $user, EvaluatorResolutionService $service)
    {
        $period = KpiPeriod::current();
        $slot = $this->authorizeEvaluator($request->user(), $user, $period, $service);

        $data = $request->validate([
            'kpi_plan_id' => ['required', 'integer', 'exists:kpi_plans,id'],
            'score' => ['required', 'integer', 'min:0', 'max:100'],
            'note' => ['nullable', 'string'],
        ]);

        $plan = KpiPlan::where('id', $data['kpi_plan_id'])->where('user_id', $user->id)->where('period_id', $period->id)->firstOrFail();

        $evaluation = KpiEvaluation::updateOrCreate(
            ['kpi_plan_id' => $plan->id, 'evaluator_id' => $request->user()->id],
            ['evaluator_role' => $slot, 'score' => $data['score'], 'note' => $data['note'] ?? null]
        );

        return response()->json($evaluation);
    }

    public function storeCriteriaScore(Request $request, User $user, EvaluatorResolutionService $service)
    {
        $period = KpiPeriod::current();
        $slot = $this->authorizeEvaluator($request->user(), $user, $period, $service);

        $data = $request->validate([
            'kpi_extra_criterion_id' => ['required', 'integer', 'exists:kpi_extra_criteria,id'],
            'score' => ['required', 'integer', 'min:0', 'max:100'],
            'reason' => ['nullable', 'string'],
        ]);

        $score = KpiExtraCriterionScore::updateOrCreate(
            [
                'kpi_extra_criterion_id' => $data['kpi_extra_criterion_id'],
                'user_id' => $user->id,
                'period_id' => $period->id,
                'evaluator_id' => $request->user()->id,
            ],
            ['evaluator_role' => $slot, 'score' => $data['score'], 'reason' => $data['reason'] ?? null]
        );

        return response()->json($score);
    }

    /** @return string slot penilai (PENILAI_1..3) milik $evaluator terhadap $subject */
    private function authorizeEvaluator(User $evaluator, User $subject, ?KpiPeriod $period, EvaluatorResolutionService $service): string
    {
        if (! $period) {
            throw ValidationException::withMessages(['period_id' => ['Tidak ada periode KPI berjalan.']]);
        }

        $slot = collect($service->resolveFor($subject, $period->id))->first(fn (array $s) => $s['evaluator']?->id === $evaluator->id);

        abort_unless($slot, 403, 'Anda bukan penilai pegawai ini.');

        return $slot['slot'];
    }
}
