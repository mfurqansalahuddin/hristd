<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\Location;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class AttendanceSeeder extends Seeder
{
    /**
     * Nama lokasi kantor nyata yang sudah dibuat admin lewat halaman Manajemen Kantor
     * (§8.1.1 plan.md) — dipakai sebagai "kantor asal" pegawai secara bergilir, bukan
     * dibuat baru di sini supaya tidak menduplikasi/menimpa geofence poligon yang sudah
     * digambar admin.
     *
     * @var list<string>
     */
    private const LOCATION_NAMES = ['Kantor Pusat', 'Kantor Cabang Selatan', 'WTP Siron'];

    /**
     * Seed 30 hari terakhir absensi untuk seluruh pegawai yang sudah ada (Senin-Sabtu,
     * Minggu diloncat). Jam kerja Senin-Jumat 08:00-16:30, Sabtu 08:00-12:00 — lihat
     * plan.md §8.1. Variasi tepat waktu/terlambat/pulang cepat dan sebaran 3 lokasi
     * kantor (§8.1.1) dibuat acak supaya tab Kehadiran punya data yang representatif.
     */
    public function run(): void
    {
        $locations = Location::whereIn('name', self::LOCATION_NAMES)->get();

        if ($locations->isEmpty()) {
            $locations = Location::all();
        }

        if ($locations->isEmpty()) {
            $this->command?->warn('Belum ada lokasi kantor (Manajemen Kantor) — absensi diseed tanpa koordinat.');
        }

        $users = User::all();

        foreach ($users as $user) {
            $homeLocation = $locations->isNotEmpty() ? $locations[$user->id % $locations->count()] : null;

            for ($daysAgo = 29; $daysAgo >= 0; $daysAgo--) {
                $date = Carbon::today()->subDays($daysAgo);

                if ($date->isSunday()) {
                    continue;
                }

                if (Attendance::where('user_id', $user->id)->whereDate('date', $date)->exists()) {
                    continue;
                }

                $this->seedDay($user, $date, $homeLocation);
            }
        }
    }

    private function seedDay(User $user, Carbon $date, ?Location $homeLocation): void
    {
        $closeTime = $date->isSaturday() ? '12:00:00' : '16:30:00';

        $isLate = fake()->boolean(25);
        $clockIn = $isLate
            ? $date->copy()->setTimeFromTimeString('08:00:00')->addMinutes(fake()->numberBetween(1, 90))
            : $date->copy()->setTimeFromTimeString('07:45:00')->addMinutes(fake()->numberBetween(0, 15));

        $isEarlyLeave = fake()->boolean(20);
        $clockOut = $isEarlyLeave
            ? $date->copy()->setTimeFromTimeString($closeTime)->subMinutes(fake()->numberBetween(15, 90))
            : $date->copy()->setTimeFromTimeString($closeTime)->addMinutes(fake()->numberBetween(0, 30));

        [$clockInLat, $clockInLong] = $homeLocation ? $this->pointFor($homeLocation, fake()->boolean(80)) : [null, null];
        [$clockOutLat, $clockOutLong] = $homeLocation ? $this->pointFor($homeLocation, fake()->boolean(80)) : [null, null];

        $status = $isLate ? 'TELAT' : ($isEarlyLeave ? 'PULANG_CEPAT' : 'HADIR');
        $isApel = $date->isMonday() && ! $isLate;

        Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => $date->toDateString(),
            'clock_in' => $clockIn,
            'clock_in_lat' => $clockInLat,
            'clock_in_long' => $clockInLong,
            'clock_out' => $clockOut,
            'clock_out_lat' => $clockOutLat,
            'clock_out_long' => $clockOutLong,
            'is_apel' => $isApel,
            'status' => $status,
            'late_reason' => $isLate ? fake()->randomElement(['Macet di jalan', 'Urusan keluarga', 'Kendaraan mogok', 'Antar anak sekolah']) : null,
            'supervisor_approval' => $isLate ? fake()->randomElement(['PENDING', 'APPROVED', 'REJECTED']) : 'APPROVED',
        ]);
    }

    /**
     * Titik acak di sekitar lokasi. `$insideOffice` true → dicoba beberapa kali di dekat
     * titik pusat sampai benar-benar lolos `Location::containsPoint()` (perlu karena
     * geofence di sini berupa POLYGON tak beraturan, bukan lingkaran radius sederhana),
     * jatuh balik ke titik pusat kalau tetap gagal. False → digeser jauh (400m-3km) supaya
     * pasti di luar geofence, untuk memicu badge "Luar Lokasi Kantor".
     *
     * @return array{0: float, 1: float}
     */
    private function pointFor(Location $location, bool $insideOffice): array
    {
        [$centerLat, $centerLong] = $this->centerOf($location);

        if (! $insideOffice) {
            return $this->offset($centerLat, $centerLong, fake()->numberBetween(400, 3000));
        }

        for ($attempt = 0; $attempt < 15; $attempt++) {
            [$lat, $long] = $this->offset($centerLat, $centerLong, fake()->randomFloat(1, 0, 20));

            if ($location->containsPoint($lat, $long)) {
                return [$lat, $long];
            }
        }

        return [$centerLat, $centerLong];
    }

    /**
     * @return array{0: float, 1: float}
     */
    private function centerOf(Location $location): array
    {
        if ($location->type === 'POLYGON' && $location->polygon) {
            $lats = array_column($location->polygon, 'lat');
            $longs = array_column($location->polygon, 'lng');

            return [array_sum($lats) / count($lats), array_sum($longs) / count($longs)];
        }

        return [(float) $location->lat, (float) $location->long];
    }

    /**
     * @return array{0: float, 1: float}
     */
    private function offset(float $lat, float $long, float $distanceMeters): array
    {
        $angle = deg2rad(fake()->numberBetween(0, 359));
        $deltaLat = ($distanceMeters * cos($angle)) / 111320;
        $deltaLong = ($distanceMeters * sin($angle)) / (111320 * cos(deg2rad($lat)));

        return [$lat + $deltaLat, $long + $deltaLong];
    }
}
