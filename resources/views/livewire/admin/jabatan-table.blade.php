@php
    $typeLabels = \App\Models\Department::TYPE_LABELS;
@endphp
<div>
    @if (session('success'))
        <div class="mb-6">
            <x-ui.alert variant="success" :message="session('success')" />
        </div>
    @endif

    <div class="mb-4 flex justify-end">
        @unless ($showCreateForm)
            <button type="button" wire:click="openCreateForm"
                class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-brand-500 px-5 py-3.5 text-sm font-medium text-white shadow-theme-xs hover:bg-brand-600 sm:w-auto">
                Tambah Jabatan
            </button>
        @endunless
    </div>

    @if ($showCreateForm)
        <div class="mb-6 rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <h3 class="mb-4 text-base font-medium text-gray-800 dark:text-white/90">Tambah Jabatan Baru</h3>
            <form wire:submit="createJabatan" class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Nama Departemen/Jabatan</label>
                    <input type="text" wire:model="newName" placeholder="mis. Bagian Pemasaran"
                        class="dark:bg-dark-900 shadow-theme-xs h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 dark:border-gray-700 dark:text-white/90" />
                    @error('newName') <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Tipe</label>
                    <select wire:model.live="newType"
                        class="dark:bg-dark-900 shadow-theme-xs h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 dark:border-gray-700 dark:text-white/90">
                        <option value="">- Pilih -</option>
                        @foreach (\App\Models\Department::CREATABLE_TYPES as $type)
                            <option value="{{ $type }}">{{ $typeLabels[$type] }} ({{ $type }})</option>
                        @endforeach
                    </select>
                    @error('newType') <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Induk Departemen</label>
                    <select wire:model="newParentId"
                        class="dark:bg-dark-900 shadow-theme-xs h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 dark:border-gray-700 dark:text-white/90">
                        <option value="">- Pilih -</option>
                        @foreach ($parentOptions as $parent)
                            <option value="{{ $parent->id }}">{{ $parent->name }}</option>
                        @endforeach
                    </select>
                    @error('newParentId') <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Pegawai yang Menjabat</label>
                    <select wire:model="newUserId"
                        class="dark:bg-dark-900 shadow-theme-xs h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 dark:border-gray-700 dark:text-white/90">
                        <option value="">- Pilih pegawai -</option>
                        @foreach ($staff as $employee)
                            <option value="{{ $employee->id }}">{{ $employee->name }} ({{ $employee->nik }})</option>
                        @endforeach
                    </select>
                    @error('newUserId') <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror
                    <p class="mt-1.5 text-xs text-gray-400">Jabatan baru cuma bisa dibuat sekaligus dengan pegawai yang menjabatnya — pilih pegawai yang sudah ada.</p>
                </div>

                <div class="col-span-full flex justify-end gap-3">
                    <button type="button" wire:click="cancelCreateForm"
                        class="inline-flex items-center justify-center rounded-lg bg-white px-5 py-3.5 text-sm font-medium text-gray-700 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-400 dark:ring-gray-700">
                        Batal
                    </button>
                    <button type="submit"
                        class="inline-flex items-center justify-center rounded-lg bg-brand-500 px-5 py-3.5 text-sm font-medium text-white shadow-theme-xs hover:bg-brand-600">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    @endif

    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]" wire:loading.class="opacity-60">
        <div class="flex flex-col gap-3 border-b border-gray-100 p-4 sm:flex-row sm:flex-wrap sm:items-end sm:p-6 dark:border-gray-800">
            <x-common.data-table.per-page-select :options="$perPageOptions" />
            <div class="w-full sm:w-72">
                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Cari Departemen</label>
                <input type="text" wire:model.live.debounce.400ms="search" placeholder="Nama departemen"
                    class="dark:bg-dark-900 shadow-theme-xs h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 dark:border-gray-700 dark:text-white/90" />
            </div>
            @if ($search || $jobLevel || $status)
                <button type="button" wire:click="resetFilters" class="text-sm text-gray-500 hover:underline sm:mb-3 dark:text-gray-400">Reset filter</button>
            @endif
        </div>

        <div class="max-w-full overflow-x-auto custom-scrollbar">
            <table class="w-full min-w-[900px]">
                <thead>
                    <tr class="border-b border-gray-100 dark:border-gray-800">
                        <x-common.data-table.th field="level" label="Jabatan" :sort="$sort" :direction="$direction" :active="$jobLevel !== ''">
                            <select wire:model.live="jobLevel"
                                class="dark:bg-dark-900 shadow-theme-xs w-full rounded-lg border border-gray-300 bg-transparent px-2 py-1.5 text-xs text-gray-800 dark:border-gray-700 dark:text-white/90">
                                <option value="">- Semua -</option>
                                <option value="1">Direktur</option>
                                <option value="2">Kabag/Kacab/Kanit/Staf Ahli</option>
                                <option value="3">Kepala Seksi</option>
                            </select>
                        </x-common.data-table.th>
                        <x-common.data-table.th field="department_name" label="Departemen" :sort="$sort" :direction="$direction" />
                        <x-common.data-table.th field="holder_name" label="Pemegang" :sort="$sort" :direction="$direction" :active="$status !== ''">
                            <select wire:model.live="status"
                                class="dark:bg-dark-900 shadow-theme-xs w-full rounded-lg border border-gray-300 bg-transparent px-2 py-1.5 text-xs text-gray-800 dark:border-gray-700 dark:text-white/90">
                                <option value="">- Semua -</option>
                                <option value="occupied">Terisi</option>
                                <option value="vacant">Kosong</option>
                            </select>
                        </x-common.data-table.th>
                        <x-common.data-table.th label="Aksi" />
                    </tr>
                </thead>
                <tbody>
                    @forelse ($positions as $position)
                        @php $department = $position['department']; @endphp
                        <tr wire:key="position-{{ $department->id }}" class="border-b border-gray-100 dark:border-gray-800">
                            <td class="px-5 py-4 sm:px-6">
                                <x-ui.badge color="primary">{{ $typeLabels[$department->type] ?? $department->type }}</x-ui.badge>
                            </td>
                            <td class="px-5 py-4 sm:px-6">
                                <p class="text-gray-800 text-theme-sm dark:text-white/90">{{ $department->name }}</p>
                                @if ($department->parent)
                                    <p class="text-gray-400 text-theme-xs">{{ $department->parent->name }}</p>
                                @endif
                            </td>
                            <td class="px-5 py-4 sm:px-6">
                                @if ($position['holder'])
                                    <p class="text-gray-800 text-theme-sm dark:text-white/90">{{ $position['holder']->name }}</p>
                                    <p class="text-gray-400 text-theme-xs">NIK {{ $position['holder']->nik }}</p>
                                @else
                                    <x-ui.badge color="light">Kosong</x-ui.badge>
                                @endif
                            </td>
                            <td class="px-5 py-4 sm:px-6">
                                <div class="flex items-center gap-2">
                                    <select wire:model="selected.{{ $department->id }}"
                                        class="dark:bg-dark-900 shadow-theme-xs w-56 rounded-lg border border-gray-300 bg-transparent px-2 py-1.5 text-xs text-gray-800 dark:border-gray-700 dark:text-white/90">
                                        <option value="">- Pilih pegawai -</option>
                                        @foreach ($staff as $employee)
                                            <option value="{{ $employee->id }}">{{ $employee->name }} ({{ $employee->nik }})</option>
                                        @endforeach
                                    </select>
                                    <button type="button" wire:click="assign({{ $department->id }})"
                                        class="text-sm text-brand-500 hover:underline">
                                        {{ $position['holder'] ? 'Ganti' : 'Tetapkan' }}
                                    </button>
                                    @if ($position['holder'])
                                        <button type="button" wire:click="vacate({{ $department->id }})"
                                            wire:confirm="Kosongkan jabatan ini? Pemegang lama akan kembali menjadi Staf."
                                            class="text-sm text-error-500 hover:underline">
                                            Hapus
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-5 py-8 text-center text-sm text-gray-500 dark:text-gray-400">Tidak ada departemen yang cocok.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="border-t border-gray-100 px-5 py-4 dark:border-gray-800">
            {{ $positions->links() }}
        </div>
    </div>
</div>
