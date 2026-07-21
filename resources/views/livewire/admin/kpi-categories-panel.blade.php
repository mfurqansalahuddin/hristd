<div>
    @if (session('success'))
        <div class="mb-6">
            <x-ui.alert variant="success" :message="session('success')" />
        </div>
    @endif

    <div class="mb-6">
        <x-common.component-card title="Bobot & Kriteria Penilaian KPI" desc="Total seluruh baris di bawah (komponen tetap + kriteria tambahan yang aktif) harus selalu 100%.">
            <div class="overflow-hidden rounded-xl border border-gray-200 dark:border-gray-800">
                <div class="max-w-full overflow-x-auto custom-scrollbar">
                    <table class="w-full min-w-[900px]">
                        <thead>
                            <tr class="border-b border-gray-100 dark:border-gray-800">
                                <th class="px-5 py-3 text-left sm:px-6"><p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Nama Penilaian</p></th>
                                <th class="px-5 py-3 text-left sm:px-6"><p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Deskripsi</p></th>
                                <th class="px-5 py-3 text-left sm:px-6"><p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Bobot</p></th>
                                <th class="px-5 py-3 text-left sm:px-6"><p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Aktif</p></th>
                                <th class="px-5 py-3 text-left sm:px-6"><p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Aksi</p></th>
                            </tr>
                        </thead>
                        <tbody>
                            {{-- 5 komponen tetap --}}
                            @foreach ($weightRows as $weight)
                                <tr wire:key="component-{{ $weight->id }}" class="border-b border-gray-100 dark:border-gray-800">
                                    <td class="px-5 py-4 sm:px-6">
                                        <p class="text-sm font-medium text-gray-800 dark:text-white/90">{{ $weight->label() }}</p>
                                    </td>
                                    <td class="px-5 py-4 sm:px-6">
                                        <textarea rows="2" wire:model.live.debounce.500ms="componentEdits.{{ $weight->id }}.description"
                                            class="dark:bg-dark-900 shadow-theme-xs w-full min-w-[220px] resize-y rounded-lg border border-gray-300 bg-transparent px-3 py-2 text-sm text-gray-800 dark:border-gray-700 dark:text-white/90">{{ $componentEdits[$weight->id]['description'] }}</textarea>
                                    </td>
                                    <td class="px-5 py-4 sm:px-6">
                                        <div class="relative w-24">
                                            <input type="number" min="0" max="100" wire:model.live="componentEdits.{{ $weight->id }}.weight" value="{{ $componentEdits[$weight->id]['weight'] }}"
                                                class="dark:bg-dark-900 shadow-theme-xs h-9 w-full rounded-lg border border-gray-300 bg-transparent pr-6 pl-3 text-sm text-gray-800 dark:border-gray-700 dark:text-white/90" />
                                            <span class="pointer-events-none absolute top-1/2 right-2 -translate-y-1/2 text-sm text-gray-400">%</span>
                                        </div>
                                    </td>
                                    <td class="px-5 py-4 sm:px-6">
                                        <span class="text-sm text-gray-400" title="Komponen tetap, selalu aktif">Tetap</span>
                                    </td>
                                    <td class="px-5 py-4 sm:px-6">
                                        <button type="button" wire:click="updateComponentWeight({{ $weight->id }})"
                                            @disabled(! $this->isComponentDirty($weight->id))
                                            class="text-sm {{ $this->isComponentDirty($weight->id) ? 'text-brand-500 hover:underline' : 'text-gray-300 dark:text-gray-600 cursor-not-allowed' }}">
                                            Simpan
                                        </button>
                                    </td>
                                </tr>
                            @endforeach

                            {{-- kriteria penilaian tambahan (bebas ditambah/hapus HR) --}}
                            @foreach ($criteriaEdits as $criterionId => $edit)
                                <tr wire:key="criterion-{{ $criterionId }}" class="border-b border-gray-100 dark:border-gray-800">
                                    <td class="px-5 py-4 sm:px-6">
                                        <input type="text" wire:model.live.debounce.500ms="criteriaEdits.{{ $criterionId }}.name" value="{{ $edit['name'] }}" required
                                            class="dark:bg-dark-900 shadow-theme-xs h-9 w-full min-w-[160px] rounded-lg border border-gray-300 bg-transparent px-3 text-sm text-gray-800 dark:border-gray-700 dark:text-white/90" />
                                    </td>
                                    <td class="px-5 py-4 sm:px-6">
                                        <textarea rows="2" wire:model.live.debounce.500ms="criteriaEdits.{{ $criterionId }}.description" required
                                            class="dark:bg-dark-900 shadow-theme-xs w-full min-w-[220px] resize-y rounded-lg border border-gray-300 bg-transparent px-3 py-2 text-sm text-gray-800 dark:border-gray-700 dark:text-white/90">{{ $edit['description'] }}</textarea>
                                    </td>
                                    <td class="px-5 py-4 sm:px-6">
                                        <div class="relative w-24">
                                            <input type="number" min="1" max="100" wire:model.live="criteriaEdits.{{ $criterionId }}.weight" value="{{ $edit['weight'] }}" required
                                                class="dark:bg-dark-900 shadow-theme-xs h-9 w-full rounded-lg border border-gray-300 bg-transparent pr-6 pl-3 text-sm text-gray-800 dark:border-gray-700 dark:text-white/90" />
                                            <span class="pointer-events-none absolute top-1/2 right-2 -translate-y-1/2 text-sm text-gray-400">%</span>
                                        </div>
                                    </td>
                                    <td class="px-5 py-4 sm:px-6">
                                        <input type="checkbox" wire:model.live="criteriaEdits.{{ $criterionId }}.is_active" />
                                    </td>
                                    <td class="px-5 py-4 sm:px-6">
                                        <div class="flex items-center gap-3">
                                            <button type="button" wire:click="updateCriterion({{ $criterionId }})"
                                                @disabled(! $this->isCriterionDirty($criterionId))
                                                class="text-sm {{ $this->isCriterionDirty($criterionId) ? 'text-brand-500 hover:underline' : 'text-gray-300 dark:text-gray-600 cursor-not-allowed' }}">
                                                Simpan
                                            </button>
                                            @if ($confirmingCriterionId === $criterionId)
                                                <button type="button" wire:click="deleteCriterion({{ $criterionId }})"
                                                    class="text-sm font-semibold text-error-600 hover:underline">Yakin hapus?</button>
                                                <button type="button" wire:click="$set('confirmingCriterionId', null)"
                                                    class="text-sm text-gray-400 hover:underline">Batal</button>
                                            @else
                                                <button type="button" wire:click="$set('confirmingCriterionId', {{ $criterionId }})"
                                                    class="text-sm text-error-500 hover:underline">Hapus</button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="5" class="px-5 py-4 sm:px-6">
                                    <p class="text-sm text-gray-500 dark:text-gray-400">
                                        Total bobot:
                                        <span class="text-base font-semibold {{ $this->weightsTotal() === 100 ? 'text-success-500' : 'text-error-500' }}">
                                            {{ $this->weightsTotal() }}%
                                        </span>
                                        @if ($this->weightsTotal() !== 100)
                                            <span class="text-error-500"> — harus 100%, belum bisa dipakai untuk hitung skor akhir</span>
                                        @endif
                                    </p>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <form wire:submit="storeCriterion" class="mt-6 flex flex-wrap items-end gap-4">
                <div class="flex-1 min-w-[200px]">
                    <x-form.input name="newCriterionName" wire:model="newCriterionName" label="Nama Penilaian" required />
                </div>
                <div class="flex-1 min-w-[240px]">
                    <x-form.input name="newCriterionDescription" wire:model="newCriterionDescription" label="Deskripsi" required />
                </div>
                <x-form.input name="newCriterionWeight" type="number" wire:model="newCriterionWeight" label="Bobot" required />
                <button type="submit"
                    class="inline-flex items-center justify-center rounded-lg bg-brand-500 px-5 py-3.5 text-sm font-medium text-white shadow-theme-xs hover:bg-brand-600">
                    + Tambah Kriteria
                </button>
            </form>
        </x-common.component-card>
    </div>

    <div class="mb-6">
        <x-common.component-card title="Bobot Sumber Integritas" desc="Skor Integritas digabung dari 4 sumber ini (masing-masing dihitung sendiri, lalu dirata-rata tertimbang). Total harus 100%.">
            <div class="overflow-hidden rounded-xl border border-gray-200 dark:border-gray-800">
                <div class="max-w-full overflow-x-auto custom-scrollbar">
                    <table class="w-full min-w-[500px]">
                        <thead>
                            <tr class="border-b border-gray-100 dark:border-gray-800">
                                <th class="px-5 py-3 text-left sm:px-6"><p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Sumber</p></th>
                                <th class="px-5 py-3 text-left sm:px-6"><p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Bobot</p></th>
                                <th class="px-5 py-3 text-left sm:px-6"><p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Aksi</p></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($sourceWeightRows as $source)
                                <tr wire:key="source-{{ $source->id }}" class="border-b border-gray-100 dark:border-gray-800">
                                    <td class="px-5 py-4 sm:px-6">
                                        <p class="text-sm font-medium text-gray-800 dark:text-white/90">{{ $source->label() }}</p>
                                    </td>
                                    <td class="px-5 py-4 sm:px-6">
                                        <div class="relative w-24">
                                            <input type="number" min="0" max="100" wire:model.live="sourceWeightEdits.{{ $source->id }}.weight" value="{{ $sourceWeightEdits[$source->id]['weight'] }}"
                                                class="dark:bg-dark-900 shadow-theme-xs h-9 w-full rounded-lg border border-gray-300 bg-transparent pr-6 pl-3 text-sm text-gray-800 dark:border-gray-700 dark:text-white/90" />
                                            <span class="pointer-events-none absolute top-1/2 right-2 -translate-y-1/2 text-sm text-gray-400">%</span>
                                        </div>
                                    </td>
                                    <td class="px-5 py-4 sm:px-6">
                                        <button type="button" wire:click="updateSourceWeight({{ $source->id }})"
                                            @disabled(! $this->isSourceWeightDirty($source->id))
                                            class="text-sm {{ $this->isSourceWeightDirty($source->id) ? 'text-brand-500 hover:underline' : 'text-gray-300 dark:text-gray-600 cursor-not-allowed' }}">
                                            Simpan
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="3" class="px-5 py-4 sm:px-6">
                                    <p class="text-sm text-gray-500 dark:text-gray-400">
                                        Total bobot:
                                        <span class="text-base font-semibold {{ $this->sourceWeightsTotal() === 100 ? 'text-success-500' : 'text-error-500' }}">
                                            {{ $this->sourceWeightsTotal() }}%
                                        </span>
                                        @if ($this->sourceWeightsTotal() !== 100)
                                            <span class="text-error-500"> — harus 100%</span>
                                        @endif
                                    </p>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </x-common.component-card>
    </div>

    <div class="mb-6">
        <x-common.component-card title="Tambah Kategori Pengurang Integritas" desc="Tiap temuan tervalidasi dari salah satu dari 4 sumber Integritas pada kategori ini mengurangi skor kategori tsb sampai 0.">
            <form wire:submit="storeIntegrityCategory" class="flex flex-wrap items-end gap-4">
                <div class="flex-1 min-w-[200px]">
                    <x-form.input name="newName" wire:model="newName" label="Nama Kategori" required />
                </div>
                <x-form.input name="newDeductionValue" type="number" wire:model="newDeductionValue" label="Nilai Pengurang" required />
                <button type="submit"
                    class="inline-flex items-center justify-center rounded-lg bg-brand-500 px-5 py-3.5 text-sm font-medium text-white shadow-theme-xs hover:bg-brand-600">
                    Tambah
                </button>
            </form>
        </x-common.component-card>
    </div>

    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="max-w-full overflow-x-auto custom-scrollbar">
            <table class="w-full min-w-[700px]">
                <thead>
                    <tr class="border-b border-gray-100 dark:border-gray-800">
                        <th class="px-5 py-3 text-left sm:px-6"><p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Kategori</p></th>
                        <th class="px-5 py-3 text-left sm:px-6"><p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Nilai Pengurang</p></th>
                        <th class="px-5 py-3 text-left sm:px-6"><p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Aksi</p></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($edits as $categoryId => $edit)
                        <tr wire:key="integrity-{{ $categoryId }}" class="border-b border-gray-100 dark:border-gray-800">
                            <td class="px-5 py-4 sm:px-6">
                                <input type="text" wire:model.live.debounce.500ms="edits.{{ $categoryId }}.name" value="{{ $edit['name'] }}" required
                                    class="dark:bg-dark-900 shadow-theme-xs h-9 w-full min-w-[180px] rounded-lg border border-gray-300 bg-transparent px-3 text-sm text-gray-800 dark:border-gray-700 dark:text-white/90" />
                            </td>
                            <td class="px-5 py-4 sm:px-6">
                                <input type="number" wire:model.live="edits.{{ $categoryId }}.deduction_value" value="{{ $edit['deduction_value'] }}" min="1" max="100" required
                                    class="dark:bg-dark-900 shadow-theme-xs h-9 w-24 rounded-lg border border-gray-300 bg-transparent px-3 text-sm text-gray-800 dark:border-gray-700 dark:text-white/90" />
                            </td>
                            <td class="px-5 py-4 sm:px-6">
                                <div class="flex items-center gap-3">
                                    <button type="button" wire:click="updateIntegrityCategory({{ $categoryId }})"
                                        @disabled(! $this->isIntegrityDirty($categoryId))
                                        class="text-sm {{ $this->isIntegrityDirty($categoryId) ? 'text-brand-500 hover:underline' : 'text-gray-300 dark:text-gray-600 cursor-not-allowed' }}">
                                        Simpan
                                    </button>
                                    @if ($confirmingIntegrityId === $categoryId)
                                        <button type="button" wire:click="deleteIntegrityCategory({{ $categoryId }})"
                                            class="text-sm font-semibold text-error-600 hover:underline">Yakin hapus?</button>
                                        <button type="button" wire:click="$set('confirmingIntegrityId', null)"
                                            class="text-sm text-gray-400 hover:underline">Batal</button>
                                    @else
                                        <button type="button" wire:click="$set('confirmingIntegrityId', {{ $categoryId }})"
                                            class="text-sm text-error-500 hover:underline">Hapus</button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
