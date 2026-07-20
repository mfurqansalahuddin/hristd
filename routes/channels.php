<?php

use App\Services\LocationVisibilityService;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// Live Location (§15.7 poin 6 plan.md).
Broadcast::channel('pejabat-locations', function ($user) {
    return app(LocationVisibilityService::class)->canViewPejabatChannel($user);
});

Broadcast::channel('department-locations.{departmentId}', function ($user, int $departmentId) {
    return app(LocationVisibilityService::class)->canViewDepartmentChannel($user, $departmentId);
});
