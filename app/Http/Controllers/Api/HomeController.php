<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\KpiEvaluation;
use App\Models\KpiFinalScore;
use App\Models\KpiPeriod;
use App\Models\KpiPlan;
use App\Models\LeaveRequest;
use App\Models\Location;
use App\Models\User;
use App\Services\EvaluationProgressService;
use App\Services\EvaluatorResolutionService;
use Illuminate\Http\Request;

/**
 * Payload gabungan Halaman Utama mobile (§14.2/§15.1 mobile-app.md, §2 §2.1-2.3
 * mobile-app.md) — 1 call, hindari N call terpisah dari client.
 */
class HomeController extends Controller
{
    public function index(Request $request, EvaluatorResolutionService $evaluatorResolutionService, EvaluationProgressService $progress)
    {
        $user = $request->user();
        $period = KpiPeriod::current();

        return response()->json([
            'greeting_name' => $user->name,
            'kpi_phase' => $this->kpiPhase($user, $period),
            'last_month_score' => $this->lastMonthScore($user, $period),
            'attendance_today' => $this->attendanceToday($user),
            'pending_evaluations' => $this->pendingEvaluations($user, $period, $evaluatorResolutionService, $progress),
            'pending_leave_approvals' => $this->pendingLeaveApprovals($user, $evaluatorResolutionService),
            'locations' => $this->locations(),
        ]);
    }

    /**
     * Lokasi kantor (radius/poligon) untuk digambar di minimap Home + cek "di kantor / di luar kantor" (§8.1.1 plan.md).
     *
     * @return array<int, array{id: int, name: string, type: string, lat: float, long: float, radius_meters: ?int, polygon: ?array}>
     */
    private function locations(): array
    {
        return Location::forMinimap();
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

        $expected = $approvedPlans->count() * 2;
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

    /** @return array{clocked_in: bool, clocked_out: bool, clock_in_time: ?string, clock_out_time: ?string} */
    private function attendanceToday(User $user): array
    {
        $today = Attendance::where('user_id', $user->id)->whereDate('date', now()->toDateString())->first();

        return [
            'clocked_in' => (bool) $today?->clock_in,
            'clocked_out' => (bool) $today?->clock_out,
            'clock_in_time' => $today?->clock_in?->format('H:i'),
            'clock_out_time' => $today?->clock_out?->format('H:i'),
        ];
    }

    /** Jumlah evaluatee (Kinerja utk Penilai 1/2, Integritas utk ketiga slot, §5.4/§5.4b mobile-app.md) yang belum tuntas — 1 layar gabungan, 1 angka. */
    private function pendingEvaluations(User $user, ?KpiPeriod $period, EvaluatorResolutionService $service, EvaluationProgressService $progress): int
    {
        // Penilaian rekan/bawahan hanya berjalan selama fase EVALUATION (§isScoringOpen).
        if (! $period || $period->status !== 'EVALUATION') {
            return 0;
        }

        return $service->evaluateesFor($user, $period->id)
            ->filter(fn (array $entry) => ! $progress->isFullyEvaluated($user, $entry['evaluee'], $entry['slot'], $period))
            ->count();
    }

    private function pendingLeaveApprovals(User $user, EvaluatorResolutionService $service): int
    {
        return LeaveRequest::whereIn('type', ['SAKIT', 'IZIN'])
            ->where('status', 'PENDING')
            ->get()
            ->filter(function (LeaveRequest $leaveRequest) use ($user, $service) {
                $atasanPertama = $service->resolveFor($leaveRequest->user, periodId: 0)[0]['evaluator'] ?? null;

                return $atasanPertama?->id === $user->id;
            })
            ->count();
    }
}
