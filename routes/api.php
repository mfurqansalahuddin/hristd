<?php

use App\Http\Controllers\Api\AttendanceController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\HomeController;
use App\Http\Controllers\Api\KpiApprovalController;
use App\Http\Controllers\Api\KpiEvaluationController;
use App\Http\Controllers\Api\KpiFinalScoreController;
use App\Http\Controllers\Api\KpiPeriodController;
use App\Http\Controllers\Api\KpiPlanController;
use App\Http\Controllers\Api\LeaveController;
use App\Http\Controllers\Api\LocationController;
use App\Http\Controllers\Api\LogbookController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\UserSearchController;
use App\Http\Controllers\Api\ViolationController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [UserController::class, 'show']);
    Route::get('/home', [HomeController::class, 'index']);

    Route::post('/attendance/clock-in', [AttendanceController::class, 'clockIn']);
    Route::post('/attendance/clock-out', [AttendanceController::class, 'clockOut']);
    Route::get('/attendance/history', [AttendanceController::class, 'history']);

    Route::post('/location/ping', [LocationController::class, 'ping']);
    Route::get('/location/colleagues', [LocationController::class, 'colleagues']);

    Route::get('/kpi/periods/current', [KpiPeriodController::class, 'current']);

    Route::get('/kpi/plans', [KpiPlanController::class, 'index']);
    Route::post('/kpi/plans', [KpiPlanController::class, 'store']);
    Route::put('/kpi/plans/{plan}', [KpiPlanController::class, 'update']);
    Route::post('/kpi/plans/submit', [KpiPlanController::class, 'submit']);
    Route::put('/kpi/plans/{plan}/self-assessment', [KpiPlanController::class, 'selfAssessment']);

    Route::get('/kpi/approvals', [KpiApprovalController::class, 'index']);
    Route::get('/kpi/approvals/{user}', [KpiApprovalController::class, 'show']);
    Route::post('/kpi/approvals/{user}', [KpiApprovalController::class, 'store']);

    Route::get('/kpi/evaluations/pending', [KpiEvaluationController::class, 'pending']);
    Route::get('/kpi/evaluations/{user}', [KpiEvaluationController::class, 'show']);
    Route::post('/kpi/evaluations/{user}', [KpiEvaluationController::class, 'store']);

    Route::get('/kpi/final-score', [KpiFinalScoreController::class, 'show']);

    Route::get('/users/search', [UserSearchController::class, 'index']);
    Route::post('/violations', [ViolationController::class, 'store']);

    Route::get('/logbook', [LogbookController::class, 'index']);
    Route::post('/logbook', [LogbookController::class, 'store']);
    Route::post('/profile/photo', [ProfileController::class, 'updatePhoto']);

    Route::get('/leave', [LeaveController::class, 'index']);
    Route::post('/leave/cuti', [LeaveController::class, 'storeCuti']);
    Route::post('/leave/sick', [LeaveController::class, 'storeSick']);
    Route::get('/leave/sick/pending', [LeaveController::class, 'sickPending']);
    Route::post('/leave/sick/{leaveRequest}/approve', [LeaveController::class, 'approveSick']);
    Route::post('/leave/sick/{leaveRequest}/reject', [LeaveController::class, 'rejectSick']);
});
