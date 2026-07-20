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
 * §14.7/§16.7-16.8 mobile-app.md — Izin (Cuti/Sakit/Izin/Dinas Luar). DL tidak
 * punya endpoint submit dari sisi pegawai (§16.4 plan.md, input cuma di web HR).
 *
 * Sakit dan Izin disetujui atasan pertama pegawai (lewat aplikasi mobile, bukan
 * HR) — Cuti dan Dinas Luar tetap urusan HR (lihat CutiTable/DinasLuarTable web).
 */
class LeaveController extends Controller
{
    public function index(Request $request)
    {
        $data = $request->validate(['type' => ['nullable', 'in:CUTI,SAKIT,IZIN,DINAS_LUAR']]);

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
                'reason' => ['required', 'string'],
                'photo' => ['required', 'image', 'max:5120'],
            ]);
            $startDate = $data['start_date'];
            $endDate = $data['end_date'];
            $photoPath = $request->file('photo')->store('sick-notes', 'public');
        } else {
            $data = $request->validate([
                'date' => ['required', 'date'],
                'reason' => ['required', 'string'],
            ]);

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
            'reason' => $data['reason'],
            'attachment_path' => $photoPath,
            'status' => 'PENDING',
            'source' => LeaveRequest::SOURCE_APP,
        ]);

        return response()->json($leaveRequest, 201);
    }

    /**
     * Izin (hari ini/besok, tanpa surat) — tidak memotong jatah cuti/gaji,
     * tapi tetap perlu approval atasan langsung. Beda dari Cuti yang urusan HR.
     */
    public function storeIzin(Request $request)
    {
        $data = $request->validate([
            'date' => ['required', 'date'],
            'reason' => ['required', 'string'],
        ]);

        if (! in_array($data['date'], [now()->toDateString(), now()->addDay()->toDateString()], true)) {
            throw ValidationException::withMessages(['date' => ['Izin cuma bisa hari ini atau besok.']]);
        }

        $leaveRequest = LeaveRequest::create([
            'user_id' => $request->user()->id,
            'type' => LeaveRequest::TYPE_IZIN,
            'start_date' => $data['date'],
            'end_date' => $data['date'],
            'reason' => $data['reason'],
            'status' => 'PENDING',
            'source' => LeaveRequest::SOURCE_APP,
        ]);

        return response()->json($leaveRequest, 201);
    }

    public function sickPending(Request $request, EvaluatorResolutionService $service)
    {
        return response()->json(['data' => $this->pendingForSupervisor($request->user(), LeaveRequest::TYPE_SAKIT, $service)]);
    }

    public function approveSick(Request $request, LeaveRequest $leaveRequest, EvaluatorResolutionService $service, AttendanceService $attendanceService)
    {
        return $this->approveAsSupervisor($request, $leaveRequest, $service, $attendanceService);
    }

    public function rejectSick(Request $request, LeaveRequest $leaveRequest, EvaluatorResolutionService $service)
    {
        return $this->rejectAsSupervisor($request, $leaveRequest, $service);
    }

    public function izinPending(Request $request, EvaluatorResolutionService $service)
    {
        return response()->json(['data' => $this->pendingForSupervisor($request->user(), LeaveRequest::TYPE_IZIN, $service)]);
    }

    public function approveIzin(Request $request, LeaveRequest $leaveRequest, EvaluatorResolutionService $service, AttendanceService $attendanceService)
    {
        return $this->approveAsSupervisor($request, $leaveRequest, $service, $attendanceService);
    }

    public function rejectIzin(Request $request, LeaveRequest $leaveRequest, EvaluatorResolutionService $service)
    {
        return $this->rejectAsSupervisor($request, $leaveRequest, $service);
    }

    /** Sakit & Izin sama-sama disetujui atasan pertama pegawai — logic dipakai bareng. */
    private function pendingForSupervisor(User $supervisor, string $type, EvaluatorResolutionService $service)
    {
        $subordinateIds = User::whereIn('job_level', [2, 3, 4])
            ->where('id', '!=', $supervisor->id)
            ->get()
            ->filter(fn ($subordinate) => (collect($service->resolveFor($subordinate, periodId: 0))->first()['evaluator']?->id ?? null) === $supervisor->id)
            ->pluck('id');

        return LeaveRequest::with('user:id,name')
            ->where('type', $type)
            ->where('status', 'PENDING')
            ->whereIn('user_id', $subordinateIds)
            ->get();
    }

    private function approveAsSupervisor(Request $request, LeaveRequest $leaveRequest, EvaluatorResolutionService $service, AttendanceService $attendanceService)
    {
        $this->authorizeFirstSupervisor($request, $leaveRequest, $service);

        $leaveRequest->update(['status' => 'APPROVED', 'approved_by_id' => $request->user()->id]);
        $attendanceService->injectAttendanceForApprovedLeave($leaveRequest);

        return response()->json($leaveRequest->fresh());
    }

    private function rejectAsSupervisor(Request $request, LeaveRequest $leaveRequest, EvaluatorResolutionService $service)
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
        abort_unless(in_array($leaveRequest->type, [LeaveRequest::TYPE_SAKIT, LeaveRequest::TYPE_IZIN], true), 404);

        $firstSupervisor = collect($service->resolveFor($leaveRequest->user, periodId: 0))->first()['evaluator'] ?? null;

        abort_unless($firstSupervisor?->id === $request->user()->id, 403, 'Anda bukan atasan pertama pegawai ini.');
    }
}
