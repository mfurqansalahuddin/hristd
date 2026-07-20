<?php

use App\Models\Attendance;
use App\Models\Department;
use App\Models\KpiFinalScore;
use App\Models\KpiPeriod;
use App\Models\KpiPlan;
use App\Models\LeaveRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('direksi tidak punya kpi_phase (di luar cakupan kpi bulanan)', function () {
    KpiPeriod::create(['month' => 7, 'year' => 2026, 'status' => 'DRAFT']);
    $dirut = User::factory()->create(['job_level' => 1]);

    $this->actingAs($dirut, 'sanctum')->getJson('/api/home')
        ->assertOk()
        ->assertJsonPath('kpi_phase.my_step', null);
});

test('staf belum bikin rencana kerja -> NOT_STARTED', function () {
    KpiPeriod::create(['month' => 7, 'year' => 2026, 'status' => 'DRAFT']);
    $staf = User::factory()->create(['job_level' => 4]);

    $this->actingAs($staf, 'sanctum')->getJson('/api/home')
        ->assertOk()
        ->assertJsonPath('kpi_phase.my_step', 'NOT_STARTED');
});

test('staf sudah submit rencana kerja -> SUBMITTED', function () {
    $period = KpiPeriod::create(['month' => 7, 'year' => 2026, 'status' => 'DRAFT']);
    $staf = User::factory()->create(['job_level' => 4]);
    KpiPlan::create(['user_id' => $staf->id, 'period_id' => $period->id, 'target_description' => 'A', 'weight' => 30, 'status' => 'SUBMITTED']);

    $this->actingAs($staf, 'sanctum')->getJson('/api/home')
        ->assertOk()
        ->assertJsonPath('kpi_phase.my_step', 'SUBMITTED');
});

test('masa evaluation, self-assessment belum diisi -> NEEDS_SELF_ASSESSMENT', function () {
    $period = KpiPeriod::create(['month' => 7, 'year' => 2026, 'status' => 'EVALUATION']);
    $staf = User::factory()->create(['job_level' => 4]);
    KpiPlan::create(['user_id' => $staf->id, 'period_id' => $period->id, 'target_description' => 'A', 'weight' => 30, 'status' => 'APPROVED']);

    $this->actingAs($staf, 'sanctum')->getJson('/api/home')
        ->assertOk()
        ->assertJsonPath('kpi_phase.my_step', 'NEEDS_SELF_ASSESSMENT');
});

test('attendance_today mencerminkan status clock-in/out hari ini', function () {
    KpiPeriod::create(['month' => 7, 'year' => 2026, 'status' => 'DRAFT']);
    $staf = User::factory()->create(['job_level' => 4]);
    Attendance::factory()->create([
        'user_id' => $staf->id, 'date' => now()->toDateString(),
        'clock_in' => now()->toDateString().' 07:50:00', 'clock_out' => null,
    ]);

    $this->actingAs($staf, 'sanctum')->getJson('/api/home')
        ->assertOk()
        ->assertJsonPath('attendance_today.clocked_in', true)
        ->assertJsonPath('attendance_today.clocked_out', false);
});

test('last_month_score null kalau kpi_final_scores periode -1 belum ada', function () {
    KpiPeriod::create(['month' => 7, 'year' => 2026, 'status' => 'DRAFT']);
    $staf = User::factory()->create(['job_level' => 4]);

    $this->actingAs($staf, 'sanctum')->getJson('/api/home')
        ->assertOk()
        ->assertJsonPath('last_month_score', null);
});

test('last_month_score terisi kalau kpi_final_scores periode -1 sudah ada', function () {
    KpiPeriod::create(['month' => 7, 'year' => 2026, 'status' => 'DRAFT']);
    $previous = KpiPeriod::create(['month' => 6, 'year' => 2026, 'status' => 'CLOSED']);
    $staf = User::factory()->create(['job_level' => 4]);
    KpiFinalScore::create([
        'user_id' => $staf->id, 'period_id' => $previous->id,
        'score_kinerja' => 40, 'score_kehadiran' => 18, 'score_apel' => 5, 'score_pakaian' => 5, 'score_integritas' => 20,
        'grand_total_score' => 88,
    ]);

    $this->actingAs($staf, 'sanctum')->getJson('/api/home')
        ->assertOk()
        ->assertJsonPath('last_month_score', 88);
});

test('pending_evaluations nol selama fase DRAFT, terisi setelah EVALUATION', function () {
    $bagian = Department::create(['name' => 'Bagian Umum', 'type' => 'BAGIAN']);
    $seksi = Department::create(['name' => 'Seksi A', 'type' => 'SEKSI', 'parent_department_id' => $bagian->id]);
    $kasi = User::factory()->create(['job_level' => 3, 'department_id' => $seksi->id]);
    $staf = User::factory()->create(['job_level' => 4, 'department_id' => $seksi->id]);

    $draft = KpiPeriod::create(['month' => 7, 'year' => 2026, 'status' => 'DRAFT']);
    KpiPlan::create(['user_id' => $staf->id, 'period_id' => $draft->id, 'target_description' => 'A', 'weight' => 30, 'status' => 'APPROVED']);

    $this->actingAs($kasi, 'sanctum')->getJson('/api/home')
        ->assertOk()
        ->assertJsonPath('pending_evaluations', 0);

    $draft->update(['status' => 'EVALUATION']);

    $this->actingAs($kasi, 'sanctum')->getJson('/api/home')
        ->assertOk()
        ->assertJsonPath('pending_evaluations', 1);
});

test('pending_leave_approvals menghitung bawahan langsung dengan pengajuan sakit/izin PENDING', function () {
    KpiPeriod::create(['month' => 7, 'year' => 2026, 'status' => 'DRAFT']);
    $bagian = Department::create(['name' => 'Bagian Umum', 'type' => 'BAGIAN']);
    $seksi = Department::create(['name' => 'Seksi A', 'type' => 'SEKSI', 'parent_department_id' => $bagian->id]);
    $kasi = User::factory()->create(['job_level' => 3, 'department_id' => $seksi->id]);
    $staf = User::factory()->create(['job_level' => 4, 'department_id' => $seksi->id]);

    LeaveRequest::create([
        'user_id' => $staf->id, 'type' => 'SAKIT', 'start_date' => now()->toDateString(), 'end_date' => now()->toDateString(),
        'reason' => 'Demam', 'status' => 'PENDING', 'source' => 'APP',
    ]);
    LeaveRequest::create([
        'user_id' => $staf->id, 'type' => 'IZIN', 'start_date' => now()->toDateString(), 'end_date' => now()->toDateString(),
        'reason' => 'Urusan keluarga', 'status' => 'PENDING', 'source' => 'APP',
    ]);

    $this->actingAs($kasi, 'sanctum')->getJson('/api/home')
        ->assertOk()
        ->assertJsonPath('pending_leave_approvals', 2);
});
