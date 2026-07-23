<?php

use App\Http\Controllers\Web\AccountDeletionController;
use App\Http\Controllers\Web\AttendanceController;
use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\CutiController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\DeletionRequestController;
use App\Http\Controllers\Web\DinasLuarController;
use App\Http\Controllers\Web\EmployeeController;
use App\Http\Controllers\Web\JabatanController;
use App\Http\Controllers\Web\KpiCategoryController;
use App\Http\Controllers\Web\KpiFinalScoreController;
use App\Http\Controllers\Web\KpiPeriodController;
use App\Http\Controllers\Web\KpiViolationController;
use App\Http\Controllers\Web\LocationController;
use App\Http\Controllers\Web\MandatoryEventController;
use App\Http\Controllers\Web\ProfileController;
use App\Http\Controllers\Web\SakitController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('signin');
});

Route::get('/signin', [AuthController::class, 'showLoginForm'])->name('signin');
Route::post('/signin', [AuthController::class, 'login'])->name('signin.store');

Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::get('/privacy-policy', fn () => view('pages.privacy-policy'))->name('privacy-policy');

Route::get('/hapus-akun', [AccountDeletionController::class, 'create'])->name('account-deletion.create');
Route::post('/hapus-akun', [AccountDeletionController::class, 'store'])->name('account-deletion.store');

// error pages (preview routes; real exceptions auto-render resources/views/errors/*)
Route::get('/error-403', fn () => view('errors.403'))->name('error-403');
Route::get('/error-404', fn () => view('errors.404'))->name('error-404');
Route::get('/error-419', fn () => view('errors.419'))->name('error-419');
Route::get('/error-500', fn () => view('errors.500'))->name('error-500');
Route::get('/error-503', fn () => view('errors.503'))->name('error-503');

// panel HRD (khusus admin)
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');

    Route::resource('employees', EmployeeController::class)->except('show');
    Route::get('/jabatan', [JabatanController::class, 'index'])->name('jabatan.index');
    Route::resource('locations', LocationController::class)->except('show');

    Route::get('/attendances', [AttendanceController::class, 'index'])->name('attendances.index');

    Route::get('/apel-kegiatan', [MandatoryEventController::class, 'index'])->name('mandatory-events.index');
    Route::get('/apel-kegiatan/{mandatoryEvent}', [MandatoryEventController::class, 'show'])->name('mandatory-events.show');

    Route::get('/kpi-periods', [KpiPeriodController::class, 'index'])->name('kpi-periods.index');

    Route::get('/kpi-categories', [KpiCategoryController::class, 'index'])->name('kpi-categories.index');

    Route::get('/kpi-violations', [KpiViolationController::class, 'index'])->name('kpi-violations.index');

    Route::get('/kpi-final-scores', [KpiFinalScoreController::class, 'index'])->name('kpi-final-scores.index');
    Route::get('/kpi-final-scores/export', [KpiFinalScoreController::class, 'export'])->name('kpi-final-scores.export');

    Route::get('/cuti', [CutiController::class, 'index'])->name('cuti.index');
    Route::get('/sakit', [SakitController::class, 'index'])->name('sakit.index');
    Route::get('/dinas-luar', [DinasLuarController::class, 'index'])->name('dinas-luar.index');

    Route::get('/deletion-requests', [DeletionRequestController::class, 'index'])->name('deletion-requests.index');
});
