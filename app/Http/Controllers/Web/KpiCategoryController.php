<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;

class KpiCategoryController extends Controller
{
    public function index()
    {
        return view('pages.admin.kpi-categories.index', [
            'title' => 'Master Kategori KPI',
        ]);
    }
}
