<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DailyActivity;
use App\Models\KpiEvaluation;
use App\Models\KpiExtraCriterion;
use App\Models\KpiExtraCriterionScore;
use App\Models\KpiPeriod;
use App\Models\KpiPlan;
use App\Models\User;
use App\Services\EvaluationProgressService;
use App\Services\EvaluatorResolutionService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/**
 * §14.5/§15.4 mobile-app.md sub-tab 5.4 — Beri Penilaian (Kinerja + Integritas
 * digabung 1 layar, 2026-07-20: PENILAI_3/rekan cuma kebagian Integritas).
 */
class KpiEvaluationController extends Controller
{
    public function pending(Request $request, EvaluatorResolutionService $service, EvaluationProgressService $progress)
    {
        $period = KpiPeriod::current();

        if (! $period) {
            return response()->json(['data' => []]);
        }

        $data = $service->evaluateesFor($request->user(), $period->id)
            ->map(function (array $entry) use ($period, $request, $progress) {
                $plans = KpiPlan::where('user_id', $entry['evaluee']->id)->where('period_id', $period->id)->where('status', 'APPROVED')->get();

                return [
                    'user_id' => $entry['evaluee']->id,
                    'name' => $entry['evaluee']->name,
                    'evaluator_role' => $entry['slot'],
                    'is_peer' => $entry['is_peer'],
                    'self_assessment_done' => $plans->isNotEmpty() && $plans->every(fn (KpiPlan $plan) => $plan->self_assessment_score !== null),
                    'already_evaluated' => $progress->isFullyEvaluated($request->user(), $entry['evaluee'], $entry['slot'], $period),
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

    /**
     * Logbook evaluee dibatasi bulan periode — bukti kerja untuk penilai (fase
     * Working/Evaluation). `kpi_plan_id` opsional: diisi = logbook 1 target
     * tertentu (tombol "Logbook terkait pekerjaan ini" per item), kosong =
     * seluruh logbook evaluee di periode ini (kolom di bawah header Beri
     * Penilaian, §5.4/§5.4b mobile-app.md). Semua 3 slot boleh akses (termasuk
     * PENILAI_3/rekan yang cuma menilai Integritas — tetap butuh konteks logbook).
     */
    public function logbook(Request $request, User $user, EvaluatorResolutionService $service)
    {
        $period = KpiPeriod::current();
        $this->authorizeAnySlot($request->user(), $user, $period, $service);

        $data = $request->validate(['kpi_plan_id' => ['nullable', 'integer']]);

        $activities = DailyActivity::where('user_id', $user->id)
            ->whereYear('activity_date', $period->year)
            ->whereMonth('activity_date', $period->month)
            ->when(! empty($data['kpi_plan_id']), function ($query) use ($data, $user, $period) {
                $plan = KpiPlan::where('id', $data['kpi_plan_id'])->where('user_id', $user->id)->where('period_id', $period->id)->firstOrFail();
                $query->where('kpi_plan_id', $plan->id);
            })
            ->orderByDesc('activity_date')
            ->get();

        return response()->json(['data' => $activities]);
    }

    public function store(Request $request, User $user, EvaluatorResolutionService $service)
    {
        $period = KpiPeriod::current();
        $slot = $this->authorizeEvaluator($request->user(), $user, $period, $service);
        abort_unless($period->isScoringOpen(), 422, 'Periode KPI sedang tidak dalam masa penilaian.');

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
        abort_unless($period->isScoringOpen(), 422, 'Periode KPI sedang tidak dalam masa penilaian.');

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

    /** @return string slot penilai (PENILAI_1/2 saja) milik $evaluator terhadap $subject — Kinerja, PENILAI_3 ditolak. */
    private function authorizeEvaluator(User $evaluator, User $subject, ?KpiPeriod $period, EvaluatorResolutionService $service): string
    {
        $slot = $this->authorizeAnySlot($evaluator, $subject, $period, $service);

        abort_if($slot === 'PENILAI_3', 403, 'Rekan sejawat tidak lagi menilai Kinerja.');

        return $slot;
    }

    /** @return string slot penilai (PENILAI_1..3) milik $evaluator terhadap $subject */
    private function authorizeAnySlot(User $evaluator, User $subject, ?KpiPeriod $period, EvaluatorResolutionService $service): string
    {
        if (! $period) {
            throw ValidationException::withMessages(['period_id' => ['Tidak ada periode KPI berjalan.']]);
        }

        $slot = collect($service->resolveFor($subject, $period->id))->first(fn (array $s) => $s['evaluator']?->id === $evaluator->id);

        abort_unless($slot, 403, 'Anda bukan penilai pegawai ini.');

        return $slot['slot'];
    }
}
