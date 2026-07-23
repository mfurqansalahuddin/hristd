<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\LeaveRequest;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Collection;

class AttendanceService
{
    /**
     * Hari kerja (Senin-Jumat) dalam rentang tanggal — dipakai kuota cuti dan (via
     * KpiEvaluationService) rasio kehadiran bulanan. Sabtu sengaja dikeluarkan
     * (dikonfirmasi user): setengah hari, dihitung terpisah ke mekanisme gaji akhir
     * tahun yang di luar cakupan sistem ini — bukan bagian dari Kehadiran/Cuti bulanan.
     */
    public static function workingDaysBetween(Carbon $start, Carbon $end): Collection
    {
        return collect(CarbonPeriod::create($start, $end))->filter(fn (Carbon $date) => ! $date->isSunday() && ! $date->isSaturday())->values();
    }

    /**
     * Suntikkan kehadiran 100% (status CUTI/SAKIT/DINAS_LUAR) untuk seluruh
     * rentang tanggal cuti yang telah disahkan HRD. Untuk CUTI, jatah cuti
     * (`leave_balance`) dipotong sebesar hari kerja saja (Minggu tidak
     * dihitung) — Izin/Sakit/Dinas Luar tidak memotong jatah (§16.1).
     */
    public function injectAttendanceForApprovedLeave(LeaveRequest $leaveRequest): void
    {
        foreach (CarbonPeriod::create($leaveRequest->start_date, $leaveRequest->end_date) as $date) {
            Attendance::updateOrCreate(
                ['user_id' => $leaveRequest->user_id, 'date' => $date->toDateString()],
                ['status' => $leaveRequest->type, 'supervisor_approval' => 'APPROVED']
            );
        }

        if ($leaveRequest->type === LeaveRequest::TYPE_CUTI) {
            $workingDays = self::workingDaysBetween($leaveRequest->start_date, $leaveRequest->end_date)->count();
            $leaveRequest->user->decrement('leave_balance', $workingDays);
        }
    }
}
