<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\KpiPeriod;
use App\Models\User;
use App\Models\ViolationReport;
use App\Services\EvaluatorResolutionService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/**
 * §14.5/§15.5 mobile-app.md sub-tab 5.5 — Aduan Disiplin Pakaian & Integritas (§8.4 plan.md).
 */
class ViolationController extends Controller
{
    public function store(Request $request, EvaluatorResolutionService $service)
    {
        $data = $request->validate([
            'reported_user_id' => ['required', 'integer', 'exists:users,id'],
            'category' => ['required', 'in:PAKAIAN_DINAS,INTEGRITAS'],
            'description' => ['required_if:category,INTEGRITAS', 'nullable', 'string'],
            'integrity_category_id' => ['required_if:category,INTEGRITAS', 'nullable', 'integer', 'exists:kpi_integrity_categories,id'],
            'photo' => ['required_if:category,PAKAIAN_DINAS', 'nullable', 'image', 'max:5120'],
            'incident_date' => ['required', 'date'],
        ]);

        if ($data['category'] === 'INTEGRITAS') {
            $reportedUser = User::findOrFail($data['reported_user_id']);
            $firstSupervisor = collect($service->resolveFor($reportedUser, periodId: 0))->first()['evaluator'] ?? null;

            abort_unless($firstSupervisor?->id === $request->user()->id, 403, 'Aduan Integritas hanya boleh dari atasan langsung.');
        }

        $period = KpiPeriod::current();

        if (! $period) {
            throw ValidationException::withMessages(['period_id' => ['Tidak ada periode KPI berjalan, tidak bisa mencatat aduan.']]);
        }

        $photoPath = $request->hasFile('photo') ? $request->file('photo')->store('violations', 'public') : null;

        $violation = ViolationReport::create([
            'reported_user_id' => $data['reported_user_id'],
            'reporter_id' => $request->user()->id,
            'period_id' => $period->id,
            'category' => $data['category'],
            'integrity_category_id' => $data['integrity_category_id'] ?? null,
            'description' => $data['description'] ?? null,
            'photo_path' => $photoPath,
            'incident_date' => $data['incident_date'],
            'status' => 'PENDING',
        ]);

        return response()->json($violation, 201);
    }
}
