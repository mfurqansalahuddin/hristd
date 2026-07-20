<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DailyActivity;
use App\Models\KpiPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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

    public function update(Request $request, DailyActivity $activity)
    {
        abort_unless($activity->user_id === $request->user()->id, 403);
        abort_unless($this->withinEditWindow($activity), 422, 'Batas edit logbook sudah lewat.');

        $data = $request->validate([
            'description' => ['required', 'string'],
            'photo' => ['nullable', 'image', 'max:5120'],
        ]);

        if ($request->hasFile('photo')) {
            if ($activity->photo_path) {
                Storage::disk('public')->delete($activity->photo_path);
            }
            $activity->photo_path = $request->file('photo')->store('logbook', 'public');
        }

        $activity->description = $data['description'];
        $activity->save();

        return response()->json($activity);
    }

    public function destroy(Request $request, DailyActivity $activity)
    {
        abort_unless($activity->user_id === $request->user()->id, 403);
        abort_unless($this->withinEditWindow($activity), 422, 'Batas edit logbook sudah lewat.');

        if ($activity->photo_path) {
            Storage::disk('public')->delete($activity->photo_path);
        }
        $activity->delete();

        return response()->json(null, 204);
    }

    /** Sama seperti batas input H-2: entry hanya bisa diedit/dihapus selagi activity_date masih >= H-2. */
    private function withinEditWindow(DailyActivity $activity): bool
    {
        return $activity->activity_date->greaterThanOrEqualTo(now()->subDays(2)->startOfDay());
    }
}
