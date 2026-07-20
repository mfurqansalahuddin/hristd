<?php

namespace App\Services;

use App\Models\KpiEvaluation;
use App\Models\KpiExtraCriterion;
use App\Models\KpiExtraCriterionScore;
use App\Models\KpiIntegrityCategory;
use App\Models\KpiIntegrityEvaluation;
use App\Models\KpiPeriod;
use App\Models\KpiPlan;
use App\Models\User;

/**
 * "Sudah selesai dinilai?" untuk 1 pasangan (evaluator, evaluatee, slot) di 1 periode —
 * dipakai KpiEvaluationController::pending() (flag per baris, list gabungan Kinerja+
 * Integritas, §7.1 kpi-calculation.md) dan HomeController (badge Home). PENILAI_3 cuma
 * menilai Integritas (tidak lagi Kinerja), jadi kelengkapannya beda per slot.
 */
class EvaluationProgressService
{
    public function isFullyEvaluated(User $evaluator, User $evaluatee, string $slot, KpiPeriod $period): bool
    {
        if (! $this->integritasDone($evaluator, $evaluatee, $period)) {
            return false;
        }

        return $slot === 'PENILAI_3' || $this->kinerjaDone($evaluator, $evaluatee, $period);
    }

    public function kinerjaDone(User $evaluator, User $evaluatee, KpiPeriod $period): bool
    {
        $plans = KpiPlan::where('user_id', $evaluatee->id)->where('period_id', $period->id)->where('status', 'APPROVED')->get();

        if ($plans->isEmpty()) {
            return false;
        }

        $evaluatedCount = KpiEvaluation::whereIn('kpi_plan_id', $plans->pluck('id'))->where('evaluator_id', $evaluator->id)->count();

        $activeCriteria = KpiExtraCriterion::where('is_active', true)->pluck('id');
        $criteriaScoredCount = KpiExtraCriterionScore::whereIn('kpi_extra_criterion_id', $activeCriteria)
            ->where('user_id', $evaluatee->id)->where('period_id', $period->id)->where('evaluator_id', $evaluator->id)->count();

        return $evaluatedCount >= $plans->count() && $criteriaScoredCount >= $activeCriteria->count();
    }

    public function integritasDone(User $evaluator, User $evaluatee, KpiPeriod $period): bool
    {
        $totalCategories = KpiIntegrityCategory::count();

        if ($totalCategories === 0) {
            return false;
        }

        $answeredCount = KpiIntegrityEvaluation::where('reported_user_id', $evaluatee->id)
            ->where('period_id', $period->id)->where('evaluator_id', $evaluator->id)->count();

        return $answeredCount >= $totalCategories;
    }
}
