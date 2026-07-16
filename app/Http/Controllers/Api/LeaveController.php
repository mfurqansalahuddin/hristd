<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LeaveRequest;
use App\Models\User;
use App\Services\AttendanceService;
use App\Services\EvaluatorResolutionService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/**
 * §14.7/§16.7-16.8 mobile-app.md — Izin (Cuti/Sakit/Dinas Luar). DL tidak
 * punya endpoint submit dari sisi pegawai (§16.4 plan.md, input cuma di web HR).
 */
class LeaveController extends Controller
{
    public function index(Request $request)
    {
        $data = $request->validate(['type' => ['nullable', 'in:CUTI,SAKIT,DINAS_LUAR']]);

        $leaveRequests = LeaveRequest::where('user_id', $request->user()->id)
            ->when($data['type'] ?? null, fn ($q, $type) => $q->where('type', $type))
            ->orderByDesc('start_date')
            ->get();

        return response()->json(['data' => $leaveRequests]);
    }

    public function storeCuti(Request $request)
    {
        $data = $request->validate([
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'reason' => ['required', 'string'],
        ]);

        $leaveRequest = LeaveRequest::create([
            'user_id' => $request->user()->id,
            'type' => LeaveRequest::TYPE_CUTI,
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'reason' => $data['reason'],
            'status' => 'PENDING',
            'source' => LeaveRequest::SOURCE_APP,
        ]);

        return response()->json($leaveRequest, 201);
    }

    public function storeSick(Request $request)
    {
        $isRange = $request->filled('start_date') || $request->filled('end_date');

        if ($isRange) {
            $data = $request->validate([
                'start_date' => ['required', 'date'],
                'end_date' => ['required', 'date', 'after_or_equal:start_date'],
                'photo' => ['required', 'image', 'max:5120'],
            ]);
            $startDate = $data['start_date'];
            $endDate = $data['end_date'];
            $photoPath = $request->file('photo')->store('sick-notes', 'public');
        } else {
            $data = $request->validate(['date' => ['required', 'date']]);

            if (! in_array($data['date'], [now()->toDateString(), now()->addDay()->toDateString()], true)) {
                throw ValidationException::withMessages(['date' => ['Sakit 1 hari cuma bisa hari ini atau besok.']]);
            }

            $startDate = $endDate = $data['date'];
            $photoPath = null;
        }

        $leaveRequest = LeaveRequest::create([
            'user_id' => $request->user()->id,
            'type' => LeaveRequest::TYPE_SAKIT,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'attachment_path' => $photoPath,
            'status' => 'PENDING',
            'source' => LeaveRequest::SOURCE_APP,
        ]);

        return response()->json($leaveRequest, 201);
    }

    public function sickPending(Request $request, EvaluatorResolutionService $service)
    {
        $subordinateIds = User::whereIn('job_level', [2, 3, 4])
            ->where('id', '!=', $request->user()->id)
            ->get()
            ->filter(fn ($subordinate) => (collect($service->resolveFor($subordinate, periodId: 0))->first()['evaluator']?->id ?? null) === $request->user()->id)
            ->pluck('id');

        $pending = LeaveRequest::where('type', LeaveRequest::TYPE_SAKIT)
            ->where('status', 'PENDING')
            ->whereIn('user_id', $subordinateIds)
            ->get();

        return response()->json(['data' => $pending]);
    }

    public function approveSick(Request $request, LeaveRequest $leaveRequest, EvaluatorResolutionService $service, AttendanceService $attendanceService)
    {
        $this->authorizeFirstSupervisor($request, $leaveRequest, $service);

        $leaveRequest->update(['status' => 'APPROVED', 'approved_by_id' => $request->user()->id]);
        $attendanceService->injectAttendanceForApprovedLeave($leaveRequest);

        return response()->json($leaveRequest->fresh());
    }

    public function rejectSick(Request $request, LeaveRequest $leaveRequest, EvaluatorResolutionService $service)
    {
        $this->authorizeFirstSupervisor($request, $leaveRequest, $service);

        $data = $request->validate(['rejection_reason' => ['required', 'string']]);

        $leaveRequest->update([
            'status' => 'REJECTED',
            'approved_by_id' => $request->user()->id,
            'rejection_reason' => $data['rejection_reason'],
        ]);

        return response()->json($leaveRequest->fresh());
    }

    private function authorizeFirstSupervisor(Request $request, LeaveRequest $leaveRequest, EvaluatorResolutionService $service): void
    {
        abort_unless($leaveRequest->type === LeaveRequest::TYPE_SAKIT, 404);

        $firstSupervisor = collect($service->resolveFor($leaveRequest->user, periodId: 0))->first()['evaluator'] ?? null;

        abort_unless($firstSupervisor?->id === $request->user()->id, 403, 'Anda bukan atasan pertama pegawai ini.');
    }
}
