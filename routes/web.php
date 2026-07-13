<?php

use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\EmployeeController;
use App\Http\Controllers\Web\KpiPeriodController;
use App\Http\Controllers\Web\LeaveRequestController;
use App\Http\Controllers\Web\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('signin');
});

Route::get('/signin', [AuthController::class, 'showLoginForm'])->name('signin');
Route::post('/signin', [AuthController::class, 'login'])->name('signin.store');

Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// error pages
Route::get('/error-404', function () {
    return view('pages.errors.error-404', ['title' => 'Error 404']);
})->name('error-404');

Route::get('/error-500', function () {
    return view('pages.errors.error-500', ['title' => 'Error 500']);
})->name('error-500');

Route::get('/error-503', function () {
    return view('pages.errors.error-503', ['title' => 'Error 503']);
})->name('error-503');

// panel HRD (khusus admin)
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');

    Route::resource('employees', EmployeeController::class)->except('show');

    Route::get('/kpi-periods', [KpiPeriodController::class, 'index'])->name('kpi-periods.index');
    Route::post('/kpi-periods', [KpiPeriodController::class, 'store'])->name('kpi-periods.store');
    Route::put('/kpi-periods/{kpiPeriod}/status', [KpiPeriodController::class, 'updateStatus'])->name('kpi-periods.update-status');

    Route::get('/leave-requests', [LeaveRequestController::class, 'index'])->name('leave-requests.index');
    Route::put('/leave-requests/{leaveRequest}/approve', [LeaveRequestController::class, 'approve'])->name('leave-requests.approve');
    Route::put('/leave-requests/{leaveRequest}/reject', [LeaveRequestController::class, 'reject'])->name('leave-requests.reject');
});
