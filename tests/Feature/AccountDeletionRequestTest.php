<?php

use App\Livewire\Admin\DeletionRequestsTable;
use App\Mail\AccountDeletionProcessed;
use App\Models\AccountDeletionRequest;
use App\Models\Attendance;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('halaman privacy policy dan form hapus akun render tanpa perlu login', function () {
    $this->get('/privacy-policy')->assertOk()->assertSee('Ajukan Penghapusan Akun', false);
    $this->get('/hapus-akun')->assertOk()->assertSee('Ajukan Penghapusan Akun');
});

test('form publik submit membuat request PENDING dan match user_id lewat email', function () {
    $user = User::factory()->create(['email' => 'pegawai@perumdamtirtadaroy.id']);

    $this->post('/hapus-akun', [
        'name' => 'Pegawai Uji',
        'email' => 'pegawai@perumdamtirtadaroy.id',
        'reason' => 'Sudah resign',
    ])->assertRedirect();

    $request = AccountDeletionRequest::first();
    expect($request->status)->toBe('PENDING')
        ->and($request->user_id)->toBe($user->id);
});

test('approve menghapus user, mencabut token, cascade ke data terkait, dan mengirim email', function () {
    Mail::fake();

    $admin = User::factory()->create(['role' => User::ROLE_SUPER_ADMIN]);
    $user = User::factory()->create(['email' => 'hapus@perumdamtirtadaroy.id']);
    $token = $user->createToken('mobile')->accessToken;
    Attendance::create([
        'user_id' => $user->id,
        'date' => '2026-07-01',
        'clock_in' => '2026-07-01 08:00:00',
        'status' => 'HADIR',
    ]);

    $deletionRequest = AccountDeletionRequest::create([
        'user_id' => $user->id,
        'name' => $user->name,
        'email' => $user->email,
        'status' => AccountDeletionRequest::STATUS_PENDING,
    ]);

    Livewire::actingAs($admin)->test(DeletionRequestsTable::class)
        ->call('openApprove', $deletionRequest->id)
        ->call('confirmApprove');

    expect(User::find($user->id))->toBeNull()
        ->and(\Laravel\Sanctum\PersonalAccessToken::find($token->id))->toBeNull()
        ->and(Attendance::where('user_id', $user->id)->count())->toBe(0)
        ->and($deletionRequest->fresh()->status)->toBe('APPROVED');

    Mail::assertQueued(AccountDeletionProcessed::class);
});

test('reject menyimpan alasan dan tidak menghapus user', function () {
    Mail::fake();

    $admin = User::factory()->create(['role' => User::ROLE_SUPER_ADMIN]);
    $user = User::factory()->create();

    $deletionRequest = AccountDeletionRequest::create([
        'user_id' => $user->id,
        'name' => $user->name,
        'email' => $user->email,
        'status' => AccountDeletionRequest::STATUS_PENDING,
    ]);

    Livewire::actingAs($admin)->test(DeletionRequestsTable::class)
        ->call('openReject', $deletionRequest->id)
        ->set('rejectReason', 'Belum bisa diverifikasi')
        ->call('confirmReject');

    expect(User::find($user->id))->not->toBeNull()
        ->and($deletionRequest->fresh()->status)->toBe('REJECTED')
        ->and($deletionRequest->fresh()->rejection_reason)->toBe('Belum bisa diverifikasi');

    Mail::assertQueued(AccountDeletionProcessed::class);
});
