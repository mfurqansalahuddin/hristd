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

        return view('pages.admin.dashboard', [
            'title' => 'Dashboard',
            'totalEmployees' => User::count(),
            'presentToday' => Attendance::whereDate('date', $today)->whereIn('status', ['HADIR', 'TELAT'])->count(),
            'lateToday' => Attendance::whereDate('date', $today)->where('status', 'TELAT')->count(),
            'pendingLeaveRequests' => LeaveRequest::where('hr_final_status', 'PENDING')->count(),
            'currentPeriod' => KpiPeriod::orderByDesc('year')->orderByDesc('month')->first(),
        ]);
    }
}
