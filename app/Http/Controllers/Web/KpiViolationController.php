<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;

class KpiViolationController extends Controller
{
    public function index()
    {
        return view('pages.admin.kpi-violations.index', [
            'title' => 'Validasi Aduan',
        ]);
    }
}
