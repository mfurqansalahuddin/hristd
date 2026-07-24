<div>
    @if ($activePhase)
        <div class="border-b border-yellow-500 bg-yellow-50 px-4 py-3 text-center text-sm font-medium text-yellow-800 dark:border-yellow-500/30 dark:bg-yellow-500/15 dark:text-yellow-200 md:px-6">
            Fase "{{ $activePhase->label() }}" akan berakhir dalam {{ $activePhase->daysRemaining() }} hari (tanggal {{ $activePhase->end_date->format('d/m/Y') }}).
        </div>
    @endif
</div>
