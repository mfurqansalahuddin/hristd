<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;

class KpiPeriodPhaseController extends Controller
{
    public function index()
    {
        return view('pages.admin.kpi-period-phases.index', [
            'title' => 'Master Fase',
        ]);
    }
}
