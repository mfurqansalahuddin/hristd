<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\KpiPeriod;
use App\Models\KpiPeriodPhase;

class KpiPeriodController extends Controller
{
    /** §15.4 mobile-app.md. */
    public function current()
    {
        $period = KpiPeriod::current();
        $activePhase = KpiPeriodPhase::currentFor($period);

        // response()->json(null) tidak pernah mengirim body `null` literal — Symfony
        // JsonResponse men-coerce top-level null jadi `{}` (JsonResponse::__construct,
        // `$data ??= new \ArrayObject()`). Bungkus dalam key `data` supaya null tetap
        // bisa dibedakan dari "ada periode" oleh client.
        return response()->json(['data' => $period ? [
            'id' => $period->id,
            'month' => $period->month,
            'year' => $period->year,
            'status' => $period->status,
            'active_phase' => $activePhase ? [
                'phase' => $activePhase->phase,
                'label' => $activePhase->label(),
                'end_date' => $activePhase->end_date->toDateString(),
                'days_remaining' => $activePhase->daysRemaining(),
                'show_banner' => $activePhase->showBanner(),
            ] : null,
        ] : null]);
    }
}
