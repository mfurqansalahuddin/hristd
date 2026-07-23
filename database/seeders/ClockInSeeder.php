<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\Location;
use App\Models\User;
use Illuminate\Support\Carbon;

/**
 * Seed absen MASUK hari ini saja (clock_out dibiarkan kosong), niru alur asli
 * AttendanceController::clockIn — supaya absen pulang bisa dites manual lewat
 * app dengan GPS beneran. Jalankan pagi: `php artisan db:seed --class=ClockInSeeder`.
 * User yang sudah absen masuk hari ini dilewati (aman diulang).
 */
class ClockInSeeder extends AttendanceSeeder
{
    public function run(): void
    {
        $today = Carbon::today();

        if ($today->isSunday()) {
            $this->command?->warn('Hari ini Minggu, absensi tidak diseed.');

            return;
        }

        $locations = Location::all()->keyBy('name');
        $users = User::where('job_level', '!=', 1)->get();

        $isLate = now()->format('H:i:s') > Attendance::JAM_MASUK_BATAS;
        $seeded = 0;

        foreach ($users as $user) {
            if (Attendance::where('user_id', $user->id)->whereDate('date', $today)->whereNotNull('clock_in')->exists()) {
                continue;
            }

            $homeLocation = $this->resolveHomeLocation($user, $locations);
            [$lat, $long] = $homeLocation ? $this->pointFor($homeLocation, fake()->boolean(80)) : [null, null];
            $matched = $lat !== null && $locations->contains(fn (Location $l) => $l->containsPoint($lat, $long));
            $needsApproval = $isLate || ! $matched;

            Attendance::updateOrCreate(
                ['user_id' => $user->id, 'date' => $today->toDateString()],
                [
                    'clock_in' => now(),
                    'clock_in_lat' => $lat,
                    'clock_in_long' => $long,
                    'status' => $isLate ? 'TELAT' : 'HADIR',
                    'is_apel' => $today->isMonday() && ! $isLate,
                    'approval_reason' => $needsApproval
                        ? fake()->randomElement(['Macet di jalan', 'Urusan keluarga', 'Kendaraan mogok', 'Antar anak sekolah'])
                        : null,
                    'supervisor_approval' => $needsApproval ? 'PENDING' : 'APPROVED',
                ]
            );
            $seeded++;
        }

        $this->command?->info("Absen masuk diseed untuk {$seeded} staf (dari {$users->count()} total).");
    }
}
