<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\KpiMandatoryEvent;

class MandatoryEventController extends Controller
{
    public function index()
    {
        return view('pages.admin.mandatory-events.index', [
            'title' => 'Presensi Apel & Kegiatan',
        ]);
    }

    public function show(KpiMandatoryEvent $mandatoryEvent)
    {
        return view('pages.admin.mandatory-events.show', [
            'title' => 'Presensi: '.$mandatoryEvent->name,
            'event' => $mandatoryEvent,
        ]);
    }
}
