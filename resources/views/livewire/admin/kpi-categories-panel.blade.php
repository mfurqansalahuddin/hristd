<div>
    @if (session('success'))
        <div class="mb-6">
            <x-ui.alert variant="success" :message="session('success')" />
        </div>
    @endif

    @error('weights')
        <div class="mb-6">
            <x-ui.alert variant="error" :message="$message" />
        </div>
    @enderror

    <div class="mb-6">
        <x-common.component-card title="Bobot Komponen Penilaian KPI" desc="Total kelima komponen di bawah harus selalu 100%.">
            <form wire:submit="updateWeights">
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-5">
                    @foreach ($weightRows as $weight)
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">{{ $weight->label() }}</label>
                            <div class="relative">
                                <input type="number" min="0" max="100" wire:model.live="weights.{{ $weight->id }}"
                                    class="dark:bg-dark-900 shadow-theme-xs h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 dark:border-gray-700 dark:text-white/90" />
                                <span class="pointer-events-none absolute top-1/2 right-4 -translate-y-1/2 text-sm text-gray-400">%</span>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-4 flex items-center gap-4">
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Total: <span class="font-medium {{ $this->weightsTotal() === 100 ? 'text-success-500' : 'text-error-500' }}">{{ $this->weightsTotal() }}%</span>
                    </p>
                    <button type="submit"
                        class="inline-flex items-center justify-center rounded-lg bg-brand-500 px-5 py-3.5 text-sm font-medium text-white shadow-theme-xs hover:bg-brand-600">
                        Simpan Bobot
                    </button>
                </div>
            </form>
        </x-common.component-card>
    </div>

    <div class="mb-6">
        <x-common.component-card title="Tambah Kategori Pengurang Integritas" desc="Tiap aduan atasan tervalidasi pada kategori ini mengurangi skor Integritas sebesar nilai pengurang.">
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
                                <input type="text" wire:model="edits.{{ $categoryId }}.name" value="{{ $edit['name'] }}" required
                                    class="dark:bg-dark-900 shadow-theme-xs h-9 w-full min-w-[180px] rounded-lg border border-gray-300 bg-transparent px-3 text-sm text-gray-800 dark:border-gray-700 dark:text-white/90" />
                            </td>
                            <td class="px-5 py-4 sm:px-6">
                                <input type="number" wire:model="edits.{{ $categoryId }}.deduction_value" value="{{ $edit['deduction_value'] }}" min="1" max="100" required
                                    class="dark:bg-dark-900 shadow-theme-xs h-9 w-24 rounded-lg border border-gray-300 bg-transparent px-3 text-sm text-gray-800 dark:border-gray-700 dark:text-white/90" />
                            </td>
                            <td class="px-5 py-4 sm:px-6">
                                <div class="flex items-center gap-3">
                                    <button type="button" wire:click="updateIntegrityCategory({{ $categoryId }})" class="text-sm text-brand-500 hover:underline">Simpan</button>
                                    <button type="button" wire:click="deleteIntegrityCategory({{ $categoryId }})" wire:confirm="Hapus kategori ini?"
                                        class="text-sm text-error-500 hover:underline">Hapus</button>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
