<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\Location;
use App\Models\User;
use Illuminate\Support\Carbon;

/**
 * Seed absen PULANG hari ini untuk user yang sudah absen masuk (lewat
 * ClockInSeeder atau app beneran), niru alur asli AttendanceController::clockOut.
 * Jalankan sore: `php artisan db:seed --class=ClockOutSeeder`.
 * User yang belum clock_in atau sudah clock_out dilewati (aman diulang).
 */
class ClockOutSeeder extends AttendanceSeeder
{
    public function run(): void
    {
        $today = Carbon::today();

        $locations = Location::all()->keyBy('name');
        $attendances = Attendance::whereDate('date', $today)->whereNotNull('clock_in')->whereNull('clock_out')->get();

        $closeTime = $today->isSaturday() ? Attendance::JAM_PULANG_BATAS_SABTU : Attendance::JAM_PULANG_BATAS;
        $isEarly = now()->format('H:i:s') < $closeTime;
        $seeded = 0;

        foreach ($attendances as $attendance) {
            $user = User::find($attendance->user_id);
            $homeLocation = $this->resolveHomeLocation($user, $locations);
            [$lat, $long] = $homeLocation ? $this->pointFor($homeLocation, fake()->boolean(80)) : [null, null];
            $matched = $lat !== null && $locations->contains(fn (Location $l) => $l->containsPoint($lat, $long));
            $needsApproval = $isEarly || ! $matched;

            $attendance->update([
                'clock_out' => now(),
                'clock_out_lat' => $lat,
                'clock_out_long' => $long,
                'status' => $attendance->status === 'TELAT' ? 'TELAT' : ($isEarly ? 'PULANG_CEPAT' : $attendance->status),
                'approval_reason' => $attendance->approval_reason ?? ($needsApproval
                    ? fake()->randomElement(['Antar anak sekolah', 'Urusan keluarga mendadak', 'Sakit ringan'])
                    : null),
                'supervisor_approval' => $needsApproval ? 'PENDING' : $attendance->supervisor_approval,
            ]);
            $seeded++;
        }

        $this->command?->info("Absen pulang diseed untuk {$seeded} staf.");
    }
}
