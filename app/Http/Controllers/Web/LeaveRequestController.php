<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\LeaveRequest;
use App\Services\AttendanceService;

class LeaveRequestController extends Controller
{
    public function __construct(protected AttendanceService $attendanceService)
    {
    }

    public function index()
    {
        $leaveRequests = LeaveRequest::with('user')
            ->orderByRaw("hr_final_status = 'PENDING' desc")
            ->latest()
            ->paginate(15);

        return view('pages.admin.leave-requests.index', [
            'title' => 'Validasi Cuti',
            'leaveRequests' => $leaveRequests,
        ]);
    }

    public function approve(LeaveRequest $leaveRequest)
    {
        $leaveRequest->update(['hr_final_status' => 'APPROVED']);

        $this->attendanceService->injectAttendanceForApprovedLeave($leaveRequest);

        return back()->with('success', 'Cuti disetujui, kehadiran otomatis tercatat.');
    }

    public function reject(LeaveRequest $leaveRequest)
    {
        $leaveRequest->update(['hr_final_status' => 'REJECTED']);

        return back()->with('success', 'Cuti ditolak.');
    }
}
