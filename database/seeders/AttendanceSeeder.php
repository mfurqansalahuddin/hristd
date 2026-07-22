<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\Location;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class AttendanceSeeder extends Seeder
{
    /**
     * Nama Department (top-level: Bagian/Cabang/Unit/SPI/PAL/Staf Ahli) => nama Location
     * tempat mereka absen, sesuai kondisi riil kantor Perumdam Tirta Daroy. Bagian Produksi
     * sengaja tidak di sini — stafnya di-split acak antara WTP Siron & WTP Lubok Batee di
     * resolveHomeLocation(). Direksi tidak disertakan sama sekali (lihat run()), dan
     * "Balai Kota Banda Aceh" sengaja tidak jadi home siapapun (kantor terdaftar tapi tak
     * ada staf yang berkantor di sana).
     *
     * @var array<string, string>
     */
    private const DEPARTMENT_LOCATIONS = [
        'Bagian Keuangan' => 'Kantor Pusat',
        'Bagian Umum' => 'Kantor Pusat',
        'Bagian Hubungan Pelanggan' => 'Kantor Pusat',
        'Unit Teknologi Informasi' => 'Kantor Pusat',
        'Satuan Pengawas Internal (SPI)' => 'Kantor Pusat',
        'Staf Ahli Bidang Administrasi' => 'Kantor Pusat',
        'Staf Ahli Bidang Teknik' => 'Kantor Pusat',
        'Bagian Perencanaan Teknik dan Pengawasan Teknik' => 'Kantor Pusat',
        'Bagian Transmisi dan Distribusi' => 'Kantor Pusat',
        'Cabang Teuku Nyak Arief' => 'Kantor Pusat',
        'Cabang Syiah Kuala' => 'Kantor Pusat',
        'Cabang Teuku Umar' => 'Kantor Cabang Selatan',
        'Cabang Sultan Iskandar Muda' => 'Kantor Cabang Selatan',
        'Bagian PAL (Pengolahan Air Limbah)' => 'Kantor Bagian PAL',
    ];

    /**
     * Seed 30 hari terakhir absensi untuk seluruh staf (Direksi dikecualikan — mereka tidak
     * absen, sama seperti DashboardController yang sudah mengecualikan job_level 1 dari semua
     * statistik). Senin-Sabtu, Minggu diloncat. Jam kerja Senin-Jumat 08:00-16:30, Sabtu
     * 08:00-12:00 — lihat plan.md §8.1. "Kantor asal" tiap staf mengikuti departemennya
     * (§8.1.1, lihat DEPARTMENT_LOCATIONS), bukan acak.
     */
    public function run(): void
    {
        $locations = Location::all()->keyBy('name');

        if ($locations->isEmpty()) {
            $this->command?->warn('Belum ada lokasi kantor (Manajemen Kantor) — absensi diseed tanpa koordinat.');
        }

        $users = User::where('job_level', '!=', 1)->get();

        foreach ($users as $user) {
            $homeLocation = $this->resolveHomeLocation($user, $locations);

            for ($daysAgo = 29; $daysAgo >= 0; $daysAgo--) {
                $date = Carbon::today()->subDays($daysAgo);

                if ($date->isSunday()) {
                    continue;
                }

                if (Attendance::where('user_id', $user->id)->whereDate('date', $date)->exists()) {
                    continue;
                }

                $this->seedDay($user, $date, $homeLocation, $locations);
            }
        }
    }

    /**
     * Departemen Seksi hanya boleh bersarang satu level di bawah Bagian/Cabang/SPI/PAL
     * (Department::PARENT_TYPES) jadi satu hop ->parent cukup untuk sampai ke departemen
     * top-level yang dipetakan di DEPARTMENT_LOCATIONS.
     */
    private function resolveHomeLocation(User $user, Collection $locations): ?Location
    {
        $department = $user->department;

        if (! $department) {
            return null;
        }

        $topDepartment = $department->type === 'SEKSI' ? $department->parent : $department;

        if (! $topDepartment) {
            return null;
        }

        if ($topDepartment->name === 'Bagian Produksi') {
            return $locations->get($user->id % 2 === 0 ? 'WTP Siron' : 'WTP Lubok Batee');
        }

        $locationName = self::DEPARTMENT_LOCATIONS[$topDepartment->name] ?? null;

        return $locationName ? $locations->get($locationName) : null;
    }

    private function seedDay(User $user, Carbon $date, ?Location $homeLocation, Collection $locations): void
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

        $clockInMatched = $clockInLat !== null && $locations->contains(fn (Location $l) => $l->containsPoint($clockInLat, $clockInLong));
        $clockOutMatched = $clockOutLat !== null && $locations->contains(fn (Location $l) => $l->containsPoint($clockOutLat, $clockOutLong));

        $status = $isLate ? 'TELAT' : ($isEarlyLeave ? 'PULANG_CEPAT' : 'HADIR');
        $isApel = $date->isMonday() && ! $isLate;

        // Samakan dengan AttendanceController: needsApproval = telat/pulang cepat ATAU di luar
        // seluruh lokasi kantor terdaftar (bukan cuma home location-nya).
        $needsApproval = $isLate || $isEarlyLeave || ! $clockInMatched || ! $clockOutMatched;

        $approvalReason = match (true) {
            $isLate => fake()->randomElement(['Macet di jalan', 'Urusan keluarga', 'Kendaraan mogok', 'Antar anak sekolah']),
            $isEarlyLeave => fake()->randomElement(['Antar anak sekolah', 'Urusan keluarga mendadak', 'Sakit ringan']),
            $needsApproval => 'Absen di luar lokasi kantor terdaftar',
            default => null,
        };

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
            'approval_reason' => $approvalReason,
            'supervisor_approval' => $needsApproval ? fake()->randomElement(['PENDING', 'APPROVED', 'REJECTED']) : 'APPROVED',
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
