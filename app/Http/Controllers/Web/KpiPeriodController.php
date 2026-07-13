<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\KpiPeriod;
use Illuminate\Http\Request;

class KpiPeriodController extends Controller
{
    public function index()
    {
        $kpiPeriods = KpiPeriod::orderByDesc('year')->orderByDesc('month')->paginate(15);

        return view('pages.admin.kpi-periods.index', [
            'title' => 'Periode KPI',
            'kpiPeriods' => $kpiPeriods,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'month' => ['required', 'integer', 'between:1,12'],
            'year' => ['required', 'integer', 'min:2020'],
        ]);

        KpiPeriod::create($data);

        return back()->with('success', 'Periode KPI berhasil dibuka.');
    }

    public function updateStatus(Request $request, KpiPeriod $kpiPeriod)
    {
        $data = $request->validate([
            'status' => ['required', 'string', 'in:DRAFT,EVALUATION,DISPUTE,CLOSED'],
        ]);

        $kpiPeriod->update($data);

        return back()->with('success', 'Status periode KPI diperbarui.');
    }
}
