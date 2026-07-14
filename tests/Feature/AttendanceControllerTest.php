<?php

use App\Models\Attendance;
use App\Models\Location;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('kehadiran page shows status and lokasi badges computed from koordinat', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $office = Location::create([
        'name' => 'Kantor Pusat',
        'type' => 'RADIUS',
        'lat' => 5.5480,
        'long' => 95.3238,
        'radius_meters' => 100,
    ]);

    $onTime = User::factory()->create(['name' => 'Budi Ontime']);
    Attendance::factory()->create([
        'user_id' => $onTime->id,
        'date' => '2026-07-14',
        'clock_in' => '2026-07-14 07:45:00',
        'clock_in_lat' => $office->lat,
        'clock_in_long' => $office->long,
        'clock_out' => '2026-07-14 16:45:00',
        'clock_out_lat' => $office->lat,
        'clock_out_long' => $office->long,
    ]);

    $late = User::factory()->create(['name' => 'Siti Telat']);
    Attendance::factory()->create([
        'user_id' => $late->id,
        'date' => '2026-07-14',
        'clock_in' => '2026-07-14 08:30:00',
        'clock_in_lat' => -6.2,
        'clock_in_long' => 106.8,
        'clock_out' => '2026-07-14 16:00:00',
        'clock_out_lat' => -6.2,
        'clock_out_long' => 106.8,
    ]);

    $response = $this->actingAs($admin)->get('/admin/attendances?date=2026-07-14');

    $response->assertOk();
    $response->assertSeeText('Budi Ontime');
    $response->assertSeeText('Tepat Waktu');
    $response->assertSeeText('Siti Telat');
    $response->assertSeeText('Terlambat');
    $response->assertSeeText('Kantor Pusat');
    $response->assertSeeText('Luar Lokasi Kantor');
});

test('filter status terlambat hanya menampilkan pegawai yang terlambat', function () {
    $admin = User::factory()->create(['is_admin' => true]);

    $onTime = User::factory()->create(['name' => 'Budi Ontime']);
    Attendance::factory()->create(['user_id' => $onTime->id, 'date' => '2026-07-14', 'clock_in' => '2026-07-14 07:45:00']);

    $late = User::factory()->create(['name' => 'Siti Telat']);
    Attendance::factory()->create(['user_id' => $late->id, 'date' => '2026-07-14', 'clock_in' => '2026-07-14 08:30:00']);

    $response = $this->actingAs($admin)->get('/admin/attendances?date=2026-07-14&status=TERLAMBAT');

    $response->assertOk();
    $response->assertSeeText('Siti Telat');
    $response->assertDontSeeText('Budi Ontime');
});

test('search kehadiran menyaring berdasarkan nama atau NIK', function () {
    $admin = User::factory()->create(['is_admin' => true]);

    $budi = User::factory()->create(['name' => 'Budi Ontime', 'nik' => '1001']);
    Attendance::factory()->create(['user_id' => $budi->id, 'date' => '2026-07-14']);

    $siti = User::factory()->create(['name' => 'Siti Telat', 'nik' => '1002']);
    Attendance::factory()->create(['user_id' => $siti->id, 'date' => '2026-07-14']);

    $byName = $this->actingAs($admin)->get('/admin/attendances?date=2026-07-14&search=budi');
    $byName->assertOk()->assertSeeText('Budi Ontime')->assertDontSeeText('Siti Telat');

    $byNik = $this->actingAs($admin)->get('/admin/attendances?date=2026-07-14&search=1002');
    $byNik->assertOk()->assertSeeText('Siti Telat')->assertDontSeeText('Budi Ontime');
});

test('containsPoint mendeteksi titik di dalam dan di luar radius', function () {
    $location = Location::create([
        'name' => 'Kantor Pusat',
        'type' => 'RADIUS',
        'lat' => 5.5480,
        'long' => 95.3238,
        'radius_meters' => 100,
    ]);

    expect($location->containsPoint(5.5480, 95.3238))->toBeTrue()
        ->and($location->containsPoint(-6.2, 106.8))->toBeFalse();
});
