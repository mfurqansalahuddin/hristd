<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;

class KpiPeriodController extends Controller
{
    public function index()
    {
        return view('pages.admin.kpi-periods.index', [
            'title' => 'Periode KPI',
        ]);
    }
}
