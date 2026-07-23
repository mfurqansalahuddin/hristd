<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\Location;
use App\Models\User;
use Illuminate\Support\Carbon;

/**
 * Seed absensi HARI INI saja (bukan 30 hari seperti AttendanceSeeder induk).
 * Jalankan tiap hari via `php artisan db:seed --class=DailyAttendanceSeeder`
 * kalau data absensi hari itu masih kosong (misal lupa generate otomatis).
 * Minggu diloncat, dan user yang sudah punya row untuk hari ini dilewati
 * (aman dijalankan berkali-kali).
 */
class DailyAttendanceSeeder extends AttendanceSeeder
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

        $seeded = 0;

        foreach ($users as $user) {
            if (Attendance::where('user_id', $user->id)->whereDate('date', $today)->exists()) {
                continue;
            }

            $homeLocation = $this->resolveHomeLocation($user, $locations);
            $this->seedDay($user, $today, $homeLocation, $locations);
            $seeded++;
        }

        $this->command?->info("Absensi hari ini ({$today->toDateString()}) diseed untuk {$seeded} staf (dari {$users->count()} total).");
    }
}
