<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\KpiPeriod;
use App\Models\KpiPlan;
use App\Models\KpiPlanReview;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

/**
 * §14.5/§15.4 mobile-app.md sub-tab 5.1 — rencana kerja (Draft) + self-assessment (Working).
 */
class KpiPlanController extends Controller
{
    public function index(Request $request)
    {
        $data = $request->validate(['period_id' => ['required', 'integer']]);

        $plans = KpiPlan::where('user_id', $request->user()->id)->where('period_id', $data['period_id'])->get();

        $taskRequests = KpiPlanReview::where('user_id', $request->user()->id)
            ->where('period_id', $data['period_id'])
            ->where('action', 'TASK_REQUESTED')
            ->latest()
            ->get();

        return response()->json(['data' => $plans, 'task_requests' => $taskRequests]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'period_id' => ['required', 'integer', 'exists:kpi_periods,id'],
            'name' => ['required', 'string'],
            'target_description' => ['required', 'string'],
            'weight' => ['required', 'numeric', 'min:1', 'max:50'],
        ]);

        $period = KpiPeriod::findOrFail($data['period_id']);
        abort_unless($period->isPlanningOpen(), 422, 'Periode KPI sedang tidak dalam masa pengajuan rencana kerja.');

        $user = $request->user();
        $usedWeight = KpiPlan::where('user_id', $user->id)->where('period_id', $data['period_id'])->sum('weight');

        if ($usedWeight + $data['weight'] > 50) {
            throw ValidationException::withMessages(['weight' => ['Total bobot rencana kerja tidak boleh lebih dari 50.']]);
        }

        $plan = KpiPlan::create([
            'user_id' => $user->id,
            'period_id' => $data['period_id'],
            'name' => $data['name'],
            'target_description' => $data['target_description'],
            'weight' => $data['weight'],
            'status' => 'DRAFT',
        ]);

        return response()->json($plan, 201);
    }

    public function update(Request $request, KpiPlan $plan)
    {
        abort_unless($plan->user_id === $request->user()->id, 403);
        abort_unless(in_array($plan->status, ['DRAFT', 'REJECTED'], true), 422, 'Hanya rencana berstatus DRAFT/REJECTED yang bisa diedit.');
        abort_unless($plan->period->isPlanningOpen(), 422, 'Periode KPI sedang tidak dalam masa pengajuan rencana kerja.');

        $data = $request->validate([
            'name' => ['required', 'string'],
            'target_description' => ['required', 'string'],
            'weight' => ['required', 'numeric', 'min:1', 'max:50'],
        ]);

        $usedWeight = KpiPlan::where('user_id', $plan->user_id)
            ->where('period_id', $plan->period_id)
            ->where('id', '!=', $plan->id)
            ->sum('weight');

        if ($usedWeight + $data['weight'] > 50) {
            throw ValidationException::withMessages(['weight' => ['Total bobot rencana kerja tidak boleh lebih dari 50.']]);
        }

        $plan->update($data);

        return response()->json($plan->fresh());
    }

    public function destroy(Request $request, KpiPlan $plan)
    {
        abort_unless($plan->user_id === $request->user()->id, 403);
        abort_unless(in_array($plan->status, ['DRAFT', 'REJECTED'], true), 422, 'Hanya rencana berstatus DRAFT/REJECTED yang bisa dihapus.');
        abort_unless($plan->period->isPlanningOpen(), 422, 'Periode KPI sedang tidak dalam masa pengajuan rencana kerja.');

        $plan->delete();

        return response()->json(null, 204);
    }

    public function submit(Request $request)
    {
        $data = $request->validate(['period_id' => ['required', 'integer']]);

        $period = KpiPeriod::find($data['period_id']);
        abort_unless($period?->isPlanningOpen(), 422, 'Periode KPI sedang tidak dalam masa pengajuan rencana kerja.');

        $user = $request->user();
        $plans = KpiPlan::where('user_id', $user->id)->where('period_id', $data['period_id'])->whereIn('status', ['DRAFT', 'REJECTED']);

        if ($plans->count() === 0) {
            throw ValidationException::withMessages(['period_id' => ['Belum ada rencana kerja untuk disubmit.']]);
        }

        $plans->update(['status' => 'SUBMITTED']);

        return response()->json(['data' => KpiPlan::where('user_id', $user->id)->where('period_id', $data['period_id'])->get()]);
    }

    public function selfAssessment(Request $request, KpiPlan $plan)
    {
        abort_unless($plan->user_id === $request->user()->id, 403);
        abort_unless($plan->status === 'APPROVED', 422, 'Rencana kerja belum disetujui.');
        abort_unless($plan->period->isSelfAssessmentOpen(), 422, 'Periode KPI sedang tidak dalam masa self-assessment.');

        $data = $request->validate([
            'self_assessment_score' => ['required', 'integer', 'min:0', 'max:100'],
            'self_assessment_note' => ['nullable', 'string'],
            'self_assessment_photo' => ['nullable', 'image', 'max:5120'],
        ]);

        if ($request->hasFile('self_assessment_photo')) {
            if ($plan->self_assessment_photo_path) {
                Storage::disk('public')->delete($plan->self_assessment_photo_path);
            }
            $data['self_assessment_photo_path'] = $request->file('self_assessment_photo')->store('kpi-self-assessment', 'public');
        }
        unset($data['self_assessment_photo']);

        $plan->update($data);

        return response()->json($plan->fresh());
    }

    /**
     * Salin name+target_description+weight dari periode sebelumnya (§13 diskusi
     * 2026-07-16) — untuk pekerjaan yang polanya sama tiap bulan (mis. catat
     * meter), supaya tidak input ulang dari nol. Status selalu fresh DRAFT,
     * self-assessment tidak ikut disalin.
     */
    public function copyPrevious(Request $request)
    {
        $data = $request->validate(['period_id' => ['required', 'integer', 'exists:kpi_periods,id']]);

        $user = $request->user();
        $period = KpiPeriod::findOrFail($data['period_id']);
        abort_unless($period->isPlanningOpen(), 422, 'Periode KPI sedang tidak dalam masa pengajuan rencana kerja.');

        if (KpiPlan::where('user_id', $user->id)->where('period_id', $period->id)->exists()) {
            throw ValidationException::withMessages(['period_id' => ['Sudah ada rencana kerja periode ini, tidak bisa disalin.']]);
        }

        $previousPlans = $period->previous()
            ? KpiPlan::where('user_id', $user->id)->where('period_id', $period->previous()->id)->get()
            : collect();

        if ($previousPlans->isEmpty()) {
            throw ValidationException::withMessages(['period_id' => ['Tidak ada rencana kerja bulan lalu untuk disalin.']]);
        }

        $previousPlans->each(fn (KpiPlan $previous) => KpiPlan::create([
            'user_id' => $user->id,
            'period_id' => $period->id,
            'name' => $previous->name,
            'target_description' => $previous->target_description,
            'weight' => $previous->weight,
            'status' => 'DRAFT',
        ]));

        return response()->json(['data' => KpiPlan::where('user_id', $user->id)->where('period_id', $period->id)->get()], 201);
    }
}
