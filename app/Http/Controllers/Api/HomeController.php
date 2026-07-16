<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\KpiEvaluation;
use App\Models\KpiFinalScore;
use App\Models\KpiPeriod;
use App\Models\KpiPlan;
use App\Models\LeaveRequest;
use App\Models\User;
use App\Services\EvaluatorResolutionService;
use Illuminate\Http\Request;

/**
 * Payload gabungan Halaman Utama mobile (§14.2/§15.1 mobile-app.md, §2 §2.1-2.3
 * mobile-app.md) — 1 call, hindari N call terpisah dari client.
 */
class HomeController extends Controller
{
    public function index(Request $request, EvaluatorResolutionService $evaluatorResolutionService)
    {
        $user = $request->user();
        $period = KpiPeriod::current();

        return response()->json([
            'greeting_name' => $user->name,
            'kpi_phase' => $this->kpiPhase($user, $period),
            'last_month_score' => $this->lastMonthScore($user, $period),
            'attendance_today' => $this->attendanceToday($user),
            'pending_evaluations' => $this->pendingEvaluations($user, $period, $evaluatorResolutionService),
            'pending_sick_approvals' => $this->pendingSickApprovals($user, $evaluatorResolutionService),
        ]);
    }

    /** @return array{period: ?array, status: ?string, my_step: ?string} */
    private function kpiPhase(User $user, ?KpiPeriod $period): array
    {
        // Direksi di luar cakupan KPI bulanan (§5.2 plan.md) — tidak punya rencana kerja.
        if (! $period || (int) $user->job_level === 1) {
            return ['period' => null, 'status' => null, 'my_step' => null];
        }

        $periodPayload = ['id' => $period->id, 'month' => $period->month, 'year' => $period->year];

        return [
            'period' => $periodPayload,
            'status' => $period->status,
            'my_step' => $this->myStep($user, $period),
        ];
    }

    private function myStep(User $user, KpiPeriod $period): string
    {
        $plans = KpiPlan::where('user_id', $user->id)->where('period_id', $period->id)->get();

        if ($period->status === 'DRAFT') {
            if ($plans->isEmpty() || $plans->every(fn (KpiPlan $plan) => $plan->status === 'DRAFT')) {
                $wasRevised = \App\Models\KpiPlanReview::where('period_id', $period->id)
                    ->where('user_id', $user->id)
                    ->where('action', 'REVISION_REQUESTED')
                    ->exists();

                return $wasRevised ? 'REVISION_REQUESTED' : 'NOT_STARTED';
            }

            if ($plans->contains(fn (KpiPlan $plan) => $plan->status === 'SUBMITTED')) {
                return 'SUBMITTED';
            }

            return 'APPROVED';
        }

        // Working / Evaluation / Dispute: perlu rencana APPROVED dulu.
        $approvedPlans = $plans->where('status', 'APPROVED');

        if ($approvedPlans->isEmpty()) {
            return 'NOT_STARTED';
        }

        if ($approvedPlans->contains(fn (KpiPlan $plan) => $plan->self_assessment_score === null)) {
            return 'NEEDS_SELF_ASSESSMENT';
        }

        if ($period->status === 'DISPUTE') {
            return 'EVALUATED';
        }

        $expected = $approvedPlans->count() * 3;
        $actual = KpiEvaluation::whereIn('kpi_plan_id', $approvedPlans->pluck('id'))->count();

        return $actual >= $expected ? 'EVALUATED' : 'WAITING_EVALUATION';
    }

    private function lastMonthScore(User $user, ?KpiPeriod $period): ?float
    {
        $previous = $period?->previous();

        if (! $previous) {
            return null;
        }

        $score = KpiFinalScore::where('user_id', $user->id)->where('period_id', $previous->id)->first();

        return $score ? (float) $score->grand_total_score : null;
    }

    /** @return array{clocked_in: bool, clocked_out: bool} */
    private function attendanceToday(User $user): array
    {
        $today = Attendance::where('user_id', $user->id)->whereDate('date', now()->toDateString())->first();

        return [
            'clocked_in' => (bool) $today?->clock_in,
            'clocked_out' => (bool) $today?->clock_out,
        ];
    }

    /** @return array{peers: int, subordinates: int} */
    private function pendingEvaluations(User $user, ?KpiPeriod $period, EvaluatorResolutionService $service): array
    {
        if (! $period) {
            return ['peers' => 0, 'subordinates' => 0];
        }

        $pending = $service->evaluateesFor($user, $period->id)->filter(function (array $entry) use ($user, $period) {
            $plans = KpiPlan::where('user_id', $entry['evaluee']->id)->where('period_id', $period->id)->where('status', 'APPROVED')->get();

            if ($plans->isEmpty()) {
                return false;
            }

            $evaluatedCount = KpiEvaluation::whereIn('kpi_plan_id', $plans->pluck('id'))->where('evaluator_id', $user->id)->count();

            return $evaluatedCount < $plans->count();
        });

        return [
            'peers' => $pending->where('is_peer', true)->count(),
            'subordinates' => $pending->where('is_peer', false)->count(),
        ];
    }

    private function pendingSickApprovals(User $user, EvaluatorResolutionService $service): int
    {
        return LeaveRequest::where('type', 'SAKIT')
            ->where('status', 'PENDING')
            ->get()
            ->filter(function (LeaveRequest $leaveRequest) use ($user, $service) {
                $atasanPertama = $service->resolveFor($leaveRequest->user, periodId: 0)[0]['evaluator'] ?? null;

                return $atasanPertama?->id === $user->id;
            })
            ->count();
    }
}
