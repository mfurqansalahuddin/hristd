<?php

use App\Http\Controllers\Web\AttendanceController;
use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\CutiController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\DinasLuarController;
use App\Http\Controllers\Web\EmployeeController;
use App\Http\Controllers\Web\JabatanController;
use App\Http\Controllers\Web\KpiCategoryController;
use App\Http\Controllers\Web\KpiFinalScoreController;
use App\Http\Controllers\Web\KpiPeriodController;
use App\Http\Controllers\Web\KpiViolationController;
use App\Http\Controllers\Web\LocationController;
use App\Http\Controllers\Web\ProfileController;
use App\Http\Controllers\Web\SakitController;
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
    Route::get('/jabatan', [JabatanController::class, 'index'])->name('jabatan.index');
    Route::resource('locations', LocationController::class)->except('show');

    Route::get('/attendances', [AttendanceController::class, 'index'])->name('attendances.index');

    Route::get('/kpi-periods', [KpiPeriodController::class, 'index'])->name('kpi-periods.index');

    Route::get('/kpi-categories', [KpiCategoryController::class, 'index'])->name('kpi-categories.index');

    Route::get('/kpi-violations', [KpiViolationController::class, 'index'])->name('kpi-violations.index');

    Route::get('/kpi-final-scores', [KpiFinalScoreController::class, 'index'])->name('kpi-final-scores.index');
    Route::get('/kpi-final-scores/export', [KpiFinalScoreController::class, 'export'])->name('kpi-final-scores.export');

    Route::get('/cuti', [CutiController::class, 'index'])->name('cuti.index');
    Route::get('/sakit', [SakitController::class, 'index'])->name('sakit.index');
    Route::get('/dinas-luar', [DinasLuarController::class, 'index'])->name('dinas-luar.index');
});
