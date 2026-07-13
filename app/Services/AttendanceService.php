<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\LeaveRequest;
use Carbon\CarbonPeriod;

class AttendanceService
{
    /**
     * Suntikkan kehadiran 100% (status CUTI/SAKIT/DINAS_LUAR) untuk seluruh
     * rentang tanggal cuti yang telah disahkan HRD.
     */
    public function injectAttendanceForApprovedLeave(LeaveRequest $leaveRequest): void
    {
        foreach (CarbonPeriod::create($leaveRequest->start_date, $leaveRequest->end_date) as $date) {
            Attendance::updateOrCreate(
                ['user_id' => $leaveRequest->user_id, 'date' => $date->toDateString()],
                ['status' => $leaveRequest->type, 'supervisor_approval' => 'APPROVED']
            );
        }
    }
}
