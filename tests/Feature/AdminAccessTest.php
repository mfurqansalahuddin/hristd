<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('staff role (default) is denied access to the admin panel', function () {
    $staff = User::factory()->create(['role' => 'STAFF']);

    $this->actingAs($staff)->get('/admin')->assertForbidden();
});

test('admin kepegawaian and super admin can access the admin panel', function () {
    $adminKepegawaian = User::factory()->create(['role' => 'ADMIN_KEPEGAWAIAN']);
    $superAdmin = User::factory()->create(['role' => 'SUPER_ADMIN']);

    $this->actingAs($adminKepegawaian)->get('/admin')->assertOk();
    $this->actingAs($superAdmin)->get('/admin')->assertOk();
});
