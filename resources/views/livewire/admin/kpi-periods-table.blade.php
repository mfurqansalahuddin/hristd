<div>
    @if (session('success'))
        <div class="mb-6">
            <x-ui.alert variant="success" :message="session('success')" />
        </div>
    @endif

    @if ($scheduleWarning)
        <div class="mb-6">
            <x-ui.alert variant="warning" :message="$scheduleWarning" />
        </div>
    @endif

    <div class="mb-6">
        <x-common.component-card title="Buka Periode KPI Baru">
            @if (! $confirmingCreate)
                <form wire:submit="openCreateConfirm" class="flex flex-wrap items-end gap-4">
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Bulan</label>
                        <select wire:model="month"
                            class="dark:bg-dark-900 shadow-theme-xs h-11 w-40 rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 dark:border-gray-700 dark:text-white/90">
                            @foreach (range(1, 12) as $m)
                                <option value="{{ $m }}">{{ $m }}</option>
                            @endforeach
                        </select>
                    </div>
                    <x-form.input name="year" type="number" wire:model="year" label="Tahun" required />
                    <button type="submit"
                        class="inline-flex items-center justify-center rounded-lg bg-brand-500 px-5 py-3.5 text-sm font-medium text-white shadow-theme-xs hover:bg-brand-600">
                        Buka Periode
                    </button>
                </form>
            @else
                <div>
                    <p class="mb-4 text-sm text-gray-600 dark:text-gray-400">
                        Bobot komponen berikut akan dibekukan (snapshot) khusus untuk periode {{ $month }}/{{ $year }} —
                        perubahan master data setelah ini <strong>tidak</strong> akan mempengaruhi periode ini.
                    </p>

                    <div class="mb-4">
                        <p class="mb-1 text-sm font-medium text-gray-700 dark:text-gray-300">Bobot Komponen KPI</p>
                        <ul class="text-sm text-gray-600 dark:text-gray-400">
                            @foreach ($weightsPreview['component_weights'] ?? [] as $component => $weight)
                                <li>{{ \App\Models\KpiComponentWeight::LABELS[$component] ?? $component }}: {{ $weight }}%</li>
                            @endforeach
                        </ul>
                    </div>

                    <div class="mb-4">
                        <p class="mb-1 text-sm font-medium text-gray-700 dark:text-gray-300">Bobot Sumber Integritas</p>
                        <ul class="text-sm text-gray-600 dark:text-gray-400">
                            @foreach ($weightsPreview['integrity_source_weights'] ?? [] as $source => $weight)
                                <li>{{ \App\Models\KpiIntegritySourceWeight::LABELS[$source] ?? $source }}: {{ $weight }}%</li>
                            @endforeach
                        </ul>
                    </div>

                    <div class="mb-4">
                        <p class="mb-1 text-sm font-medium text-gray-700 dark:text-gray-300">Band Persentase Gaji</p>
                        <ul class="text-sm text-gray-600 dark:text-gray-400">
                            @foreach ($weightsPreview['salary_bands'] ?? [] as $band)
                                <li>{{ $band['min_score'] !== null ? "Skor > {$band['min_score']}" : 'Skor lainnya' }}: {{ $band['percentage'] }}% gaji</li>
                            @endforeach
                        </ul>
                    </div>

                    <div class="flex gap-3">
                        <button type="button" wire:click="store"
                            class="inline-flex items-center justify-center rounded-lg bg-brand-500 px-5 py-3.5 text-sm font-medium text-white shadow-theme-xs hover:bg-brand-600">
                            Setuju & Buka Periode
                        </button>
                        <button type="button" wire:click="cancelCreate"
                            class="inline-flex items-center justify-center rounded-lg bg-white px-5 py-3.5 text-sm font-medium text-gray-700 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-400 dark:ring-gray-700">
                            Batal
                        </button>
                    </div>
                </div>
            @endif
        </x-common.component-card>
    </div>

    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]" wire:loading.class="opacity-60">
        <div class="flex flex-col gap-3 border-b border-gray-100 p-4 sm:flex-row sm:flex-wrap sm:items-end sm:p-6 dark:border-gray-800">
            <x-common.data-table.per-page-select :options="$perPageOptions" />
            @if ($status)
                <button type="button" wire:click="resetFilters" class="text-sm text-gray-500 hover:underline sm:mb-3 dark:text-gray-400">Reset filter</button>
            @endif
        </div>

        <div class="max-w-full overflow-x-auto custom-scrollbar">
            <table class="w-full min-w-[700px]">
                <thead>
                    <tr class="border-b border-gray-100 dark:border-gray-800">
                        <x-common.data-table.th field="year" label="Periode" :sort="$sort" :direction="$direction" />
                        <x-common.data-table.th label="Status" :active="$status !== ''">
                            <select wire:model.live="status"
                                class="dark:bg-dark-900 shadow-theme-xs w-full rounded-lg border border-gray-300 bg-transparent px-2 py-1.5 text-xs text-gray-800 dark:border-gray-700 dark:text-white/90">
                                <option value="">- Semua -</option>
                                @foreach (['DRAFT', 'WORKING', 'EVALUATION', 'DISPUTE', 'CLOSED'] as $option)
                                    <option value="{{ $option }}">{{ $option }}</option>
                                @endforeach
                            </select>
                        </x-common.data-table.th>
                        <x-common.data-table.th label="Progres" />
                        <x-common.data-table.th label="Ubah Status" />
                    </tr>
                </thead>
                <tbody>
                    @foreach ($kpiPeriods as $period)
                        <tr wire:key="kpi-period-{{ $period->id }}" class="border-b border-gray-100 dark:border-gray-800">
                            <td class="px-5 py-4 sm:px-6"><p class="text-gray-800 text-theme-sm dark:text-white/90">{{ $period->month }}/{{ $period->year }}</p></td>
                            <td class="px-5 py-4 sm:px-6"><x-ui.badge color="primary">{{ $period->status }}</x-ui.badge></td>
                            <td class="px-5 py-4 sm:px-6 text-theme-sm text-gray-600 dark:text-gray-400">
                                @if ($period->status === 'DRAFT')
                                    @php($s = $draftStats[$period->id] ?? ['belum' => 0, 'menunggu' => 0, 'approved' => 0])
                                    <p>Belum mengisi: {{ $s['belum'] }}</p>
                                    <p>Menunggu approval: {{ $s['menunggu'] }}</p>
                                    <p>Sudah di-approve: {{ $s['approved'] }}</p>
                                    @if ($s['belum'] === 0)
                                        <p class="text-gray-400 italic dark:text-gray-500">Siap pindah ke fase Working</p>
                                    @endif
                                @elseif (in_array($period->status, ['EVALUATION', 'DISPUTE'], true))
                                    @php($s = $evaluationStats[$period->id] ?? ['lengkap' => 0, 'belum' => 0])
                                    <p>Sudah dinilai lengkap: {{ $s['lengkap'] }}</p>
                                    <p>Belum: {{ $s['belum'] }}</p>
                                @elseif ($period->status === 'CLOSED')
                                    <div class="flex flex-wrap gap-1">
                                        @foreach (\App\Models\KpiFinalScore::PREDIKAT_COLORS as $label => $color)
                                            <x-ui.badge color="{{ $color }}" size="sm">{{ $label }}: {{ $closedStats[$period->id][$label] ?? 0 }}</x-ui.badge>
                                        @endforeach
                                    </div>
                                @else
                                    -
                                @endif
                            </td>
                            <td class="px-5 py-4 sm:px-6">
                                <div class="flex items-center gap-2">
                                    <select wire:model="statusEdits.{{ $period->id }}"
                                        class="dark:bg-dark-900 shadow-theme-xs h-9 rounded-lg border border-gray-300 bg-transparent px-3 text-sm text-gray-800 dark:border-gray-700 dark:text-white/90">
                                        @foreach (['DRAFT', 'WORKING', 'EVALUATION', 'DISPUTE', 'CLOSED'] as $status)
                                            <option value="{{ $status }}" @selected($statusEdits[$period->id] === $status)>{{ $status }}</option>
                                        @endforeach
                                    </select>
                                    <button type="button" wire:click="updateStatus({{ $period->id }})" class="text-sm text-brand-500 hover:underline">Simpan</button>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="border-t border-gray-100 px-5 py-4 dark:border-gray-800">
            {{ $kpiPeriods->links() }}
        </div>
    </div>
</div>
