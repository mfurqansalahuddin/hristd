<?php

namespace App\Livewire\Admin;

use App\Models\KpiPeriod;
use App\Models\KpiPeriodPhase;
use Livewire\Component;

class KpiCycleBanner extends Component
{
    public function render()
    {
        $activePhase = KpiPeriodPhase::currentFor(KpiPeriod::current());

        return view('livewire.admin.kpi-cycle-banner', [
            'activePhase' => $activePhase?->showBanner() ? $activePhase : null,
        ]);
    }
}
