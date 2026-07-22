<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\KpiPeriod;
use App\Models\LeaveRequest;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        $today = today()->toDateString();

        // Direksi (job_level 1) bukan staff untuk urusan absensi/KPI (plan.md §5.2) — dikeluarkan
        // dari agregat ringkasan supaya tidak salah dihitung sebagai staf biasa.
        $staffIds = User::where('job_level', '!=', 1)->pluck('id');

        return view('pages.admin.dashboard', [
            'title' => 'Dashboard',
            'totalEmployees' => $staffIds->count(),
            'presentToday' => Attendance::whereDate('date', $today)->whereIn('user_id', $staffIds)->whereIn('status', ['HADIR', 'TELAT'])->count(),
            'lateToday' => Attendance::whereDate('date', $today)->whereIn('user_id', $staffIds)->where('status', 'TELAT')->count(),
            'pendingLeaveRequests' => LeaveRequest::whereIn('user_id', $staffIds)->where('status', 'PENDING')->count(),
            'currentPeriod' => KpiPeriod::orderByDesc('year')->orderByDesc('month')->first(),
        ]);
    }
}
