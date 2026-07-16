<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\KpiPeriod;
use App\Models\KpiPlan;
use App\Models\KpiPlanReview;
use App\Models\User;
use App\Services\EvaluatorResolutionService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/**
 * §14.5/§15.4 mobile-app.md sub-tab 5.3 — Approval Rencana Kinerja, khusus
 * atasan pertama (Penilai 1, §5.1 plan.md).
 */
class KpiApprovalController extends Controller
{
    public function index(Request $request, EvaluatorResolutionService $service)
    {
        $period = KpiPeriod::current();

        if (! $period) {
            return response()->json(['data' => []]);
        }

        $subordinates = $service->evaluateesFor($request->user(), $period->id)
            ->where('slot', 'PENILAI_1')
            ->filter(fn (array $entry) => KpiPlan::where('user_id', $entry['evaluee']->id)
                ->where('period_id', $period->id)
                ->where('status', 'SUBMITTED')
                ->exists())
            ->map(fn (array $entry) => $entry['evaluee'])
            ->values();

        return response()->json(['data' => $subordinates]);
    }

    public function show(Request $request, User $user, EvaluatorResolutionService $service)
    {
        $period = KpiPeriod::current();
        $this->authorizeFirstSupervisor($request->user(), $user, $period, $service);

        return response()->json(['data' => KpiPlan::where('user_id', $user->id)->where('period_id', $period?->id)->get()]);
    }

    public function store(Request $request, User $user, EvaluatorResolutionService $service)
    {
        $period = KpiPeriod::current();
        $this->authorizeFirstSupervisor($request->user(), $user, $period, $service);

        $data = $request->validate([
            'action' => ['required', 'in:APPROVED,REVISION_REQUESTED'],
            'comment' => ['nullable', 'string'],
        ]);

        KpiPlanReview::create([
            'period_id' => $period->id,
            'user_id' => $user->id,
            'reviewer_id' => $request->user()->id,
            'action' => $data['action'],
            'comment' => $data['comment'] ?? null,
        ]);

        KpiPlan::where('user_id', $user->id)->where('period_id', $period->id)->where('status', 'SUBMITTED')
            ->update(['status' => $data['action'] === 'APPROVED' ? 'APPROVED' : 'DRAFT']);

        return response()->json(['data' => KpiPlan::where('user_id', $user->id)->where('period_id', $period->id)->get()]);
    }

    private function authorizeFirstSupervisor(User $reviewer, User $subject, ?KpiPeriod $period, EvaluatorResolutionService $service): void
    {
        if (! $period) {
            throw ValidationException::withMessages(['period_id' => ['Tidak ada periode KPI berjalan.']]);
        }

        $firstSupervisor = $service->resolveFor($subject, $period->id)[0]['evaluator'] ?? null;

        abort_unless($firstSupervisor?->id === $reviewer->id, 403, 'Anda bukan atasan pertama pegawai ini.');
    }
}
