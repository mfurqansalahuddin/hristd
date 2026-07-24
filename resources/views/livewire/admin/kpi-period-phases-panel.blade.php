<div>
    <x-ui.flash-success />

    <div class="mb-6">
        <x-common.component-card title="Master Fase KPI" desc="Atur tanggal awal & akhir tiap fase untuk periode terpilih. Buka/tutup fase (status periode) masih dilakukan manual di halaman Periode KPI.">
            <div class="mb-6 max-w-xs">
                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Periode</label>
                <select wire:model.live="periodId"
                    class="dark:bg-dark-900 shadow-theme-xs h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 dark:border-gray-700 dark:text-white/90">
                    @foreach ($periods as $period)
                        <option value="{{ $period->id }}">{{ $period->month }}/{{ $period->year }} ({{ $period->status }})</option>
                    @endforeach
                </select>
            </div>

            <div class="overflow-hidden rounded-xl border border-gray-200 dark:border-gray-800">
                <div class="max-w-full overflow-x-auto custom-scrollbar">
                    <table class="w-full min-w-[600px]">
                        <thead>
                            <tr class="border-b border-gray-100 dark:border-gray-800">
                                <th class="px-5 py-3 text-left sm:px-6"><p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Fase</p></th>
                                <th class="px-5 py-3 text-left sm:px-6"><p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Tanggal Awal</p></th>
                                <th class="px-5 py-3 text-left sm:px-6"><p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Tanggal Akhir</p></th>
                                <th class="px-5 py-3 text-left sm:px-6"><p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Aksi</p></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($phaseEdits as $phase => $edit)
                                <tr wire:key="phase-{{ $phase }}" class="border-b border-gray-100 dark:border-gray-800">
                                    <td class="px-5 py-4 sm:px-6">
                                        <p class="text-sm font-medium text-gray-800 dark:text-white/90">{{ $labels[$phase] ?? $phase }}</p>
                                    </td>
                                    <td class="px-5 py-4 sm:px-6">
                                        <div class="w-40" wire:ignore x-on:date-change="$wire.set('phaseEdits.{{ $phase }}.start_date', $event.detail.dateStr)">
                                            <x-form.date-picker id="phase-start-{{ $phase }}" :default-date="$edit['start_date'] ?: null" date-format="Y-m-d" placeholder="Pilih tanggal" />
                                        </div>
                                        @error("phaseEdits.{$phase}.start_date")
                                            <p class="mt-1 text-sm text-error-500">{{ $message }}</p>
                                        @enderror
                                    </td>
                                    <td class="px-5 py-4 sm:px-6">
                                        <div class="w-40" wire:ignore x-on:date-change="$wire.set('phaseEdits.{{ $phase }}.end_date', $event.detail.dateStr)">
                                            <x-form.date-picker id="phase-end-{{ $phase }}" :default-date="$edit['end_date'] ?: null" date-format="Y-m-d" placeholder="Pilih tanggal" />
                                        </div>
                                        @error("phaseEdits.{$phase}.end_date")
                                            <p class="mt-1 text-sm text-error-500">{{ $message }}</p>
                                        @enderror
                                    </td>
                                    <td class="px-5 py-4 sm:px-6">
                                        <button type="button" wire:click="updatePhase('{{ $phase }}')"
                                            @disabled(! $this->isPhaseDirty($phase))
                                            class="text-sm {{ $this->isPhaseDirty($phase) ? 'text-brand-500 hover:underline' : 'text-gray-300 dark:text-gray-600 cursor-not-allowed' }}">
                                            Simpan
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </x-common.component-card>
    </div>
</div>
