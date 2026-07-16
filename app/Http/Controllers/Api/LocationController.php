<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserCurrentLocation;
use App\Services\LocationVisibilityService;
use Illuminate\Http\Request;

/**
 * §14.4, §15.3 mobile-app.md — Live Location ping + daftar rekan/pejabat sesuai visibility.
 */
class LocationController extends Controller
{
    public function ping(Request $request)
    {
        $data = $request->validate([
            'lat' => ['required', 'numeric'],
            'long' => ['required', 'numeric'],
        ]);

        // GPS berhenti otomatis di luar jam kerja (>17:00, §8.3 plan.md) — server no-op,
        // jangan hanya percaya client berhenti sendiri.
        if (now()->format('H:i') > '17:00') {
            return response()->json(['ok' => true]);
        }

        UserCurrentLocation::updateOrCreate(
            ['user_id' => $request->user()->id],
            ['lat' => $data['lat'], 'long' => $data['long'], 'last_updated_at' => now()]
        );

        return response()->json(['ok' => true]);
    }

    public function colleagues(Request $request, LocationVisibilityService $service)
    {
        $data = $request->validate([
            'scope' => ['nullable', 'in:peers,subordinates'],
            'level_filter' => ['nullable', 'in:kabag_kasi,everyone'],
            'department_id' => ['nullable', 'integer'],
        ]);

        $user = $request->user();

        if (($data['scope'] ?? 'peers') === 'subordinates' && (int) $user->job_level === 4) {
            abort(403, 'Staf tidak punya bawahan.');
        }

        $visibleIds = $service->visibleUserIdsFor(
            $user,
            scope: $data['scope'] ?? 'peers',
            levelFilter: $data['level_filter'] ?? null,
            departmentFilter: $data['department_id'] ?? null,
        );

        $colleagues = UserCurrentLocation::with('user')
            ->whereIn('user_id', $visibleIds)
            ->get()
            ->map(fn (UserCurrentLocation $location) => [
                'user_id' => $location->user_id,
                'name' => $location->user?->name,
                'lat' => (float) $location->lat,
                'long' => (float) $location->long,
                'last_updated_at' => $location->last_updated_at,
                'is_online' => $location->last_updated_at->gt(now()->subMinutes(3)),
            ]);

        return response()->json(['data' => $colleagues]);
    }
}
