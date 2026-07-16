<?php

use App\Models\Attendance;
use App\Models\Location;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

uses(RefreshDatabase::class);

afterEach(fn () => Carbon::setTestNow());

test('clock-in tepat waktu di dalam geofence langsung approved, tidak wajib alasan', function () {
    $office = Location::create(['name' => 'Kantor Pusat', 'type' => 'RADIUS', 'lat' => 5.5480, 'long' => 95.3238, 'radius_meters' => 100]);
    $user = User::factory()->create(['job_level' => 4]);
    Carbon::setTestNow(Carbon::create(2026, 7, 13, 7, 45)); // Senin, 07:45

    $this->actingAs($user, 'sanctum')->postJson('/api/attendance/clock-in', [
        'lat' => (float) $office->lat, 'long' => (float) $office->long,
    ])->assertOk()->assertJsonPath('matched_location', 'Kantor Pusat');

    $attendance = Attendance::where('user_id', $user->id)->first();
    expect($attendance->status)->toBe('HADIR')
        ->and($attendance->supervisor_approval)->toBe('APPROVED')
        ->and($attendance->is_apel)->toBeTrue(); // Senin & tepat waktu
});

test('clock-in telat wajib approval_reason, status PENDING menunggu HR', function () {
    $user = User::factory()->create(['job_level' => 4]);
    Carbon::setTestNow(Carbon::create(2026, 7, 14, 8, 30)); // Selasa, telat

    $this->actingAs($user, 'sanctum')->postJson('/api/attendance/clock-in', ['lat' => -6.2, 'long' => 106.8])
        ->assertStatus(422)->assertJsonValidationErrors('approval_reason');

    $this->actingAs($user, 'sanctum')->postJson('/api/attendance/clock-in', [
        'lat' => -6.2, 'long' => 106.8, 'approval_reason' => 'Macet',
    ])->assertOk();

    $attendance = Attendance::where('user_id', $user->id)->first();
    expect($attendance->status)->toBe('TELAT')
        ->and($attendance->supervisor_approval)->toBe('PENDING')
        ->and($attendance->is_apel)->toBeFalse();
});

test('clock-in dua kali di hari yang sama ditolak', function () {
    $office = Location::create(['name' => 'Kantor Pusat', 'type' => 'RADIUS', 'lat' => -6.2, 'long' => 106.8, 'radius_meters' => 100]);
    $user = User::factory()->create(['job_level' => 4]);
    Carbon::setTestNow(Carbon::create(2026, 7, 14, 7, 45));

    $this->actingAs($user, 'sanctum')->postJson('/api/attendance/clock-in', ['lat' => (float) $office->lat, 'long' => (float) $office->long])->assertOk();
    $this->actingAs($user, 'sanctum')->postJson('/api/attendance/clock-in', ['lat' => (float) $office->lat, 'long' => (float) $office->long])
        ->assertStatus(422)->assertJsonValidationErrors('clock_in');
});

test('clock-out sebelum clock-in ditolak', function () {
    $user = User::factory()->create(['job_level' => 4]);

    $this->actingAs($user, 'sanctum')->postJson('/api/attendance/clock-out', ['lat' => -6.2, 'long' => 106.8])
        ->assertStatus(422)->assertJsonValidationErrors('clock_in');
});

test('clock-out pulang cepat wajib approval_reason', function () {
    $office = Location::create(['name' => 'Kantor Pusat', 'type' => 'RADIUS', 'lat' => -6.2, 'long' => 106.8, 'radius_meters' => 100]);
    $user = User::factory()->create(['job_level' => 4]);
    Carbon::setTestNow(Carbon::create(2026, 7, 14, 7, 45));
    $coords = ['lat' => (float) $office->lat, 'long' => (float) $office->long];
    $this->actingAs($user, 'sanctum')->postJson('/api/attendance/clock-in', $coords)->assertOk();

    Carbon::setTestNow(Carbon::create(2026, 7, 14, 15, 0)); // pulang sebelum 16:30
    $this->actingAs($user, 'sanctum')->postJson('/api/attendance/clock-out', $coords)
        ->assertStatus(422)->assertJsonValidationErrors('approval_reason');

    $this->actingAs($user, 'sanctum')->postJson('/api/attendance/clock-out', [
        ...$coords, 'approval_reason' => 'Urusan keluarga',
    ])->assertOk();

    $attendance = Attendance::where('user_id', $user->id)->first();
    expect($attendance->status)->toBe('PULANG_CEPAT')->and($attendance->supervisor_approval)->toBe('PENDING');
});

test('riwayat absen mengembalikan data 1 bulan dengan badge lokasi & status', function () {
    $user = User::factory()->create(['job_level' => 4]);
    Attendance::factory()->create([
        'user_id' => $user->id, 'date' => '2026-07-01',
        'clock_in' => '2026-07-01 07:45:00', 'clock_out' => '2026-07-01 16:35:00',
    ]);
    Attendance::factory()->create([
        'user_id' => $user->id, 'date' => '2026-06-01',
        'clock_in' => '2026-06-01 07:45:00', 'clock_out' => '2026-06-01 16:35:00',
    ]);

    $response = $this->actingAs($user, 'sanctum')->getJson('/api/attendance/history?month=7&year=2026')->assertOk();

    expect($response->json('data'))->toHaveCount(1)
        ->and($response->json('data.0.status_masuk'))->toBe('TEPAT_WAKTU');
});
