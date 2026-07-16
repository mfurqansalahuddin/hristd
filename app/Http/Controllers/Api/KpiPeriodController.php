<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\KpiPeriod;

class KpiPeriodController extends Controller
{
    /** §15.4 mobile-app.md. */
    public function current()
    {
        $period = KpiPeriod::current();

        // response()->json(null) tidak pernah mengirim body `null` literal — Symfony
        // JsonResponse men-coerce top-level null jadi `{}` (JsonResponse::__construct,
        // `$data ??= new \ArrayObject()`). Bungkus dalam key `data` supaya null tetap
        // bisa dibedakan dari "ada periode" oleh client.
        return response()->json(['data' => $period ? [
            'id' => $period->id,
            'month' => $period->month,
            'year' => $period->year,
            'status' => $period->status,
        ] : null]);
    }
}
