<?php

use App\Models\Attendance;
use App\Models\Department;
use App\Models\LeaveRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

afterEach(fn () => Carbon::setTestNow());

function makeStafDenganAtasanUntukIzin(): array
{
    $bagian = Department::create(['name' => 'Bagian Umum', 'type' => 'BAGIAN']);
    $seksi = Department::create(['name' => 'Seksi A', 'type' => 'SEKSI', 'parent_department_id' => $bagian->id]);
    $kasi = User::factory()->create(['job_level' => 3, 'department_id' => $seksi->id]);
    $staf = User::factory()->create(['job_level' => 4, 'department_id' => $seksi->id]);

    return compact('kasi', 'staf');
}

test('ajukan cuti tersimpan PENDING dari APP', function () {
    $user = User::factory()->create();

    $this->actingAs($user, 'sanctum')->postJson('/api/leave/cuti', [
        'start_date' => '2026-08-01', 'end_date' => '2026-08-03', 'reason' => 'Acara keluarga',
    ])->assertCreated();

    $leaveRequest = LeaveRequest::where('user_id', $user->id)->first();
    expect($leaveRequest->status)->toBe('PENDING')->and($leaveRequest->source)->toBe('APP');
});

test('sakit 1 hari cuma boleh hari ini atau besok, tanpa upload, tapi wajib alasan', function () {
    $user = User::factory()->create();
    Carbon::setTestNow(Carbon::create(2026, 7, 14));

    $this->actingAs($user, 'sanctum')->postJson('/api/leave/sick', ['date' => '2026-07-20', 'reason' => 'Demam'])
        ->assertStatus(422)->assertJsonValidationErrors('date');

    $this->actingAs($user, 'sanctum')->postJson('/api/leave/sick', ['date' => '2026-07-15'])
        ->assertStatus(422)->assertJsonValidationErrors('reason');

    $this->actingAs($user, 'sanctum')->postJson('/api/leave/sick', ['date' => '2026-07-15', 'reason' => 'Demam'])
        ->assertCreated();
});

test('sakit lebih dari 1 hari wajib upload surat dokter dan alasan', function () {
    Storage::fake('public');
    $user = User::factory()->create();

    $this->actingAs($user, 'sanctum')->postJson('/api/leave/sick', [
        'start_date' => '2026-07-14', 'end_date' => '2026-07-16', 'reason' => 'Tifus',
    ])->assertStatus(422)->assertJsonValidationErrors('photo');

    $this->actingAs($user, 'sanctum')->postJson('/api/leave/sick', [
        'start_date' => '2026-07-14', 'end_date' => '2026-07-16', 'photo' => UploadedFile::fake()->image('surat.jpg'),
    ])->assertStatus(422)->assertJsonValidationErrors('reason');

    $this->actingAs($user, 'sanctum')->postJson('/api/leave/sick', [
        'start_date' => '2026-07-14', 'end_date' => '2026-07-16', 'reason' => 'Tifus',
        'photo' => UploadedFile::fake()->image('surat.jpg'),
    ])->assertCreated();
});

test('izin cuma boleh hari ini atau besok dan wajib alasan, tanpa surat', function () {
    $user = User::factory()->create();
    Carbon::setTestNow(Carbon::create(2026, 7, 14));

    $this->actingAs($user, 'sanctum')->postJson('/api/leave/izin', ['date' => '2026-07-20', 'reason' => 'Urus KTP'])
        ->assertStatus(422)->assertJsonValidationErrors('date');

    $this->actingAs($user, 'sanctum')->postJson('/api/leave/izin', ['date' => '2026-07-15'])
        ->assertStatus(422)->assertJsonValidationErrors('reason');

    $this->actingAs($user, 'sanctum')->postJson('/api/leave/izin', ['date' => '2026-07-15', 'reason' => 'Urus KTP'])
        ->assertCreated();

    $leaveRequest = LeaveRequest::where('user_id', $user->id)->first();
    expect($leaveRequest->type)->toBe('IZIN')->and($leaveRequest->status)->toBe('PENDING');
});

test('atasan pertama lihat pengajuan izin bawahannya di izin/pending dan bisa approve', function () {
    ['kasi' => $kasi, 'staf' => $staf] = makeStafDenganAtasanUntukIzin();
    $leaveRequest = LeaveRequest::create([
        'user_id' => $staf->id, 'type' => 'IZIN', 'start_date' => '2026-07-14', 'end_date' => '2026-07-14',
        'reason' => 'Urus KTP', 'status' => 'PENDING', 'source' => 'APP',
    ]);

    $pending = $this->actingAs($kasi, 'sanctum')->getJson('/api/leave/izin/pending')->assertOk();
    expect($pending->json('data.0.user_id'))->toBe($staf->id);

    $this->actingAs($kasi, 'sanctum')->postJson("/api/leave/izin/{$leaveRequest->id}/approve")->assertOk();

    expect($leaveRequest->fresh()->status)->toBe('APPROVED')
        ->and(Attendance::where('user_id', $staf->id)->whereDate('date', '2026-07-14')->first()->status)->toBe('IZIN');
});

test('bukan atasan pertama tidak bisa approve/reject izin orang lain', function () {
    ['staf' => $staf] = makeStafDenganAtasanUntukIzin();
    $orangLain = User::factory()->create(['job_level' => 3]);
    $leaveRequest = LeaveRequest::create([
        'user_id' => $staf->id, 'type' => 'IZIN', 'start_date' => '2026-07-14', 'end_date' => '2026-07-14',
        'reason' => 'Urus KTP', 'status' => 'PENDING', 'source' => 'APP',
    ]);

    $this->actingAs($orangLain, 'sanctum')->postJson("/api/leave/izin/{$leaveRequest->id}/approve")->assertStatus(403);
    $this->actingAs($orangLain, 'sanctum')->postJson("/api/leave/izin/{$leaveRequest->id}/reject", ['rejection_reason' => 'x'])->assertStatus(403);
});

test('atasan pertama lihat pengajuan sakit bawahannya di sick/pending', function () {
    ['kasi' => $kasi, 'staf' => $staf] = makeStafDenganAtasanUntukIzin();
    LeaveRequest::create([
        'user_id' => $staf->id, 'type' => 'SAKIT', 'start_date' => '2026-07-14', 'end_date' => '2026-07-14',
        'status' => 'PENDING', 'source' => 'APP',
    ]);

    $response = $this->actingAs($kasi, 'sanctum')->getJson('/api/leave/sick/pending')->assertOk();

    expect($response->json('data.0.user_id'))->toBe($staf->id)
        ->and($response->json('data.0.user.name'))->toBe($staf->name);
});

test('approve sakit oleh atasan pertama auto-inject kehadiran', function () {
    ['kasi' => $kasi, 'staf' => $staf] = makeStafDenganAtasanUntukIzin();
    $leaveRequest = LeaveRequest::create([
        'user_id' => $staf->id, 'type' => 'SAKIT', 'start_date' => '2026-07-14', 'end_date' => '2026-07-14',
        'status' => 'PENDING', 'source' => 'APP',
    ]);

    $this->actingAs($kasi, 'sanctum')->postJson("/api/leave/sick/{$leaveRequest->id}/approve")->assertOk();

    expect($leaveRequest->fresh()->status)->toBe('APPROVED')
        ->and(Attendance::where('user_id', $staf->id)->whereDate('date', '2026-07-14')->first()->status)->toBe('SAKIT')
        ->and($staf->fresh()->leave_balance)->toBe($staf->leave_balance); // Sakit tidak memotong jatah cuti
});

test('bukan atasan pertama tidak bisa approve/reject sakit orang lain', function () {
    ['staf' => $staf] = makeStafDenganAtasanUntukIzin();
    $orangLain = User::factory()->create(['job_level' => 3]);
    $leaveRequest = LeaveRequest::create([
        'user_id' => $staf->id, 'type' => 'SAKIT', 'start_date' => '2026-07-14', 'end_date' => '2026-07-14',
        'status' => 'PENDING', 'source' => 'APP',
    ]);

    $this->actingAs($orangLain, 'sanctum')->postJson("/api/leave/sick/{$leaveRequest->id}/approve")->assertStatus(403);
    $this->actingAs($orangLain, 'sanctum')->postJson("/api/leave/sick/{$leaveRequest->id}/reject", ['rejection_reason' => 'x'])->assertStatus(403);
});

test('riwayat izin bisa difilter per type', function () {
    $user = User::factory()->create();
    LeaveRequest::create(['user_id' => $user->id, 'type' => 'CUTI', 'start_date' => '2026-07-01', 'end_date' => '2026-07-02', 'status' => 'APPROVED', 'source' => 'APP']);
    LeaveRequest::create(['user_id' => $user->id, 'type' => 'SAKIT', 'start_date' => '2026-07-10', 'end_date' => '2026-07-10', 'status' => 'APPROVED', 'source' => 'APP']);

    $response = $this->actingAs($user, 'sanctum')->getJson('/api/leave?type=SAKIT')->assertOk();

    expect($response->json('data'))->toHaveCount(1)->and($response->json('data.0.type'))->toBe('SAKIT');
});
