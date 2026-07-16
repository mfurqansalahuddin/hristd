<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DailyActivity;
use App\Models\KpiPlan;
use Illuminate\Http\Request;

/** §14.6/§15.6 mobile-app.md — Logbook (§8.5 plan.md), input dibatasi H-2 mundur. */
class LogbookController extends Controller
{
    public function index(Request $request)
    {
        $month = (int) $request->query('month', now()->month);
        $year = (int) $request->query('year', now()->year);

        $activities = DailyActivity::where('user_id', $request->user()->id)
            ->whereYear('activity_date', $year)
            ->whereMonth('activity_date', $month)
            ->orderByDesc('activity_date')
            ->get();

        return response()->json(['data' => $activities]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'activity_date' => ['required', 'date', 'after_or_equal:'.now()->subDays(2)->toDateString(), 'before_or_equal:today'],
            'description' => ['required', 'string'],
            'photo' => ['nullable', 'image', 'max:5120'],
            'kpi_plan_id' => ['nullable', 'integer', 'exists:kpi_plans,id'],
        ]);

        if (! empty($data['kpi_plan_id'])) {
            $ownsPlan = KpiPlan::where('id', $data['kpi_plan_id'])->where('user_id', $request->user()->id)->exists();
            abort_unless($ownsPlan, 403, 'Rencana kerja bukan milik Anda.');
        }

        $activity = DailyActivity::create([
            'user_id' => $request->user()->id,
            'kpi_plan_id' => $data['kpi_plan_id'] ?? null,
            'activity_date' => $data['activity_date'],
            'description' => $data['description'],
            'photo_path' => $request->hasFile('photo') ? $request->file('photo')->store('logbook', 'public') : null,
        ]);

        return response()->json($activity, 201);
    }
}
