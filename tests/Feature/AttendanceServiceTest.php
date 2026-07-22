<?php

use App\Models\LeaveRequest;
use App\Models\User;
use App\Services\AttendanceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

uses(RefreshDatabase::class);

test('workingDaysBetween cuma menghitung Senin-Jumat, Sabtu dan Minggu dilewati', function () {
    // 2026-07-06 (Senin) s.d. 2026-07-12 (Minggu) -> 5 hari kerja (Senin-Jumat).
    // Sabtu dikeluarkan (dikonfirmasi user: setengah hari, ke mekanisme gaji akhir tahun terpisah).
    $days = AttendanceService::workingDaysBetween(Carbon::create(2026, 7, 6), Carbon::create(2026, 7, 12));

    expect($days)->toHaveCount(5)
        ->and($days->contains(fn (Carbon $d) => $d->isSunday()))->toBeFalse()
        ->and($days->contains(fn (Carbon $d) => $d->isSaturday()))->toBeFalse();
});

test('approve cuti memotong jatah cuti sebesar hari kerja saja, bukan hari kalender', function () {
    $user = User::factory()->create(['leave_balance' => 12]);
    $leaveRequest = LeaveRequest::create([
        'user_id' => $user->id, 'type' => LeaveRequest::TYPE_CUTI,
        'start_date' => '2026-07-06', 'end_date' => '2026-07-10', // Senin-Jumat, tanpa Minggu -> tetap 5 hari kerja
        'status' => 'APPROVED', 'source' => 'APP',
    ]);

    (new AttendanceService)->injectAttendanceForApprovedLeave($leaveRequest);

    expect($user->fresh()->leave_balance)->toBe(7); // 12 - 5
});

test('approve cuti yang mencakup Sabtu & Minggu tidak ikut memotong jatah untuk 2 hari itu', function () {
    $user = User::factory()->create(['leave_balance' => 12]);
    $leaveRequest = LeaveRequest::create([
        'user_id' => $user->id, 'type' => LeaveRequest::TYPE_CUTI,
        'start_date' => '2026-07-06', 'end_date' => '2026-07-12', // Senin-Minggu: 5 hari kerja + Sabtu + Minggu
        'status' => 'APPROVED', 'source' => 'APP',
    ]);

    (new AttendanceService)->injectAttendanceForApprovedLeave($leaveRequest);

    expect($user->fresh()->leave_balance)->toBe(7); // 12 - 5, bukan 12 - 7 (hari kalender) ataupun 12 - 6
});

test('approve sakit/izin/dinas luar tidak memotong jatah cuti', function () {
    $user = User::factory()->create(['leave_balance' => 12]);

    foreach ([LeaveRequest::TYPE_SAKIT, LeaveRequest::TYPE_IZIN, LeaveRequest::TYPE_DINAS_LUAR] as $type) {
        $leaveRequest = LeaveRequest::create([
            'user_id' => $user->id, 'type' => $type,
            'start_date' => '2026-07-06', 'end_date' => '2026-07-07',
            'status' => 'APPROVED', 'source' => 'APP',
        ]);

        (new AttendanceService)->injectAttendanceForApprovedLeave($leaveRequest);
    }

    expect($user->fresh()->leave_balance)->toBe(12);
});
