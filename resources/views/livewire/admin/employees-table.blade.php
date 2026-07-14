<div>
    @if (session('success'))
        <div class="mb-6">
            <x-ui.alert variant="success" :message="session('success')" />
        </div>
    @endif

    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]" wire:loading.class="opacity-60">
        <div class="flex flex-col gap-3 border-b border-gray-100 p-4 sm:flex-row sm:flex-wrap sm:items-end sm:justify-between sm:p-6 dark:border-gray-800">
            <div class="flex flex-col gap-3 sm:flex-row sm:flex-wrap sm:items-end">
                <x-common.data-table.per-page-select :options="$perPageOptions" />
                <div class="w-full sm:w-56">
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Cari</label>
                    <input type="text" wire:model.live.debounce.400ms="search" placeholder="Nama, NIK, username, email"
                        class="dark:bg-dark-900 shadow-theme-xs h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 dark:border-gray-700 dark:text-white/90" />
                </div>
                @if ($search || $jobLevel || $departmentId)
                    <button type="button" wire:click="resetFilters" class="text-sm text-gray-500 hover:underline sm:mb-3 dark:text-gray-400">Reset filter</button>
                @endif
            </div>

            <a href="{{ route('admin.employees.create') }}"
                class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-brand-500 px-5 py-3.5 text-sm font-medium text-white shadow-theme-xs hover:bg-brand-600 sm:w-auto">
                Tambah Pegawai
            </a>
        </div>

        <div class="max-w-full overflow-x-auto custom-scrollbar">
            <table class="w-full min-w-[900px]">
                <thead>
                    <tr class="border-b border-gray-100 dark:border-gray-800">
                        <x-common.data-table.th field="nik" label="NIK" :sort="$sort" :direction="$direction" />
                        <x-common.data-table.th field="name" label="Nama" :sort="$sort" :direction="$direction" />
                        <x-common.data-table.th label="Jabatan" :active="$jobLevel !== ''">
                            <select wire:model.live="jobLevel"
                                class="dark:bg-dark-900 shadow-theme-xs w-full rounded-lg border border-gray-300 bg-transparent px-2 py-1.5 text-xs text-gray-800 dark:border-gray-700 dark:text-white/90">
                                <option value="">- Semua -</option>
                                <option value="1">Direktur</option>
                                <optgroup label="Kabag/Kacab/Kanit (Level 2)">
                                    <option value="2">Semua Level 2 (setingkat)</option>
                                    @foreach (\App\Models\Department::JOB_LEVEL_TYPES[2] as $type)
                                        <option value="{{ $type }}">{{ \App\Models\Department::TYPE_LABELS[$type] }}</option>
                                    @endforeach
                                </optgroup>
                                <option value="3">Kepala Seksi</option>
                                <option value="4">Staf</option>
                            </select>
                        </x-common.data-table.th>
                        <x-common.data-table.th label="Departemen" :active="$departmentId !== ''">
                            <select wire:model.live="departmentId"
                                class="dark:bg-dark-900 shadow-theme-xs w-full rounded-lg border border-gray-300 bg-transparent px-2 py-1.5 text-xs text-gray-800 dark:border-gray-700 dark:text-white/90">
                                <option value="">- Semua -</option>
                                @foreach ($departments as $department)
                                    <option value="{{ $department->id }}">{{ $department->name }}</option>
                                @endforeach
                            </select>
                        </x-common.data-table.th>
                        <x-common.data-table.th field="employment_status" label="Status" :sort="$sort" :direction="$direction" />
                        <x-common.data-table.th label="Aksi" />
                    </tr>
                </thead>
                <tbody>
                    @forelse ($employees as $employee)
                        <tr wire:key="employee-{{ $employee->id }}" class="border-b border-gray-100 dark:border-gray-800">
                            <td class="px-5 py-4 sm:px-6"><p class="text-gray-500 text-theme-sm dark:text-gray-400">{{ $employee->nik }}</p></td>
                            <td class="px-5 py-4 sm:px-6">
                                <div class="flex items-center gap-3">
                                    <img src="{{ $employee->photoUrl() }}" alt="{{ $employee->name }}"
                                        class="h-8 w-8 rounded-full object-cover">
                                    <div>
                                        <span class="block font-medium text-gray-800 text-theme-sm dark:text-white/90">{{ $employee->name }}</span>
                                        <span class="block text-gray-500 text-theme-xs dark:text-gray-400">{{ $employee->email }}</span>
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-4 sm:px-6"><x-ui.badge color="primary">{{ $employee->jabatanLabel() }}</x-ui.badge></td>
                            <td class="px-5 py-4 sm:px-6"><p class="text-gray-500 text-theme-sm dark:text-gray-400">{{ $employee->department?->name ?? '-' }}</p></td>
                            <td class="px-5 py-4 sm:px-6"><x-ui.badge color="light">{{ $employee->employment_status }}</x-ui.badge></td>
                            <td class="px-5 py-4 sm:px-6">
                                <div class="flex items-center gap-3">
                                    <a href="{{ route('admin.employees.edit', $employee) }}" class="text-sm text-brand-500 hover:underline">Edit</a>
                                    <button type="button" wire:click="delete({{ $employee->id }})" wire:confirm="Hapus pegawai ini?"
                                        class="text-sm text-error-500 hover:underline">Hapus</button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-8 text-center text-sm text-gray-500 dark:text-gray-400">Tidak ada pegawai yang cocok.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="border-t border-gray-100 px-5 py-4 dark:border-gray-800">
            {{ $employees->links() }}
        </div>
    </div>
</div>
