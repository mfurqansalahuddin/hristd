@extends('layouts.app')

@php
    use App\Models\Department;
    use App\Models\User;
@endphp

@section('content')
    <x-common.page-breadcrumb :pageTitle="$title" :items="[['label' => 'Data Pegawai', 'url' => route('admin.employees.index')]]" />

    <x-common.component-card :title="$title">
        <form method="POST" enctype="multipart/form-data"
            action="{{ $employee->exists ? route('admin.employees.update', $employee) : route('admin.employees.store') }}"
            x-data="{
                departments: {{ Js::from($departments) }},
                typesByLevel: {{ Js::from(Department::JOB_LEVEL_TYPES) }},
                jobLevel: '{{ old('job_level', (string) ($employee->job_level ?? 4)) }}',
                departmentId: '{{ old('department_id', (string) ($employee->department_id ?? '')) }}',
                typeLabels: {{ Js::from(Department::TYPE_LABELS) }},
                get filteredDepartments() {
                    const types = this.typesByLevel[this.jobLevel] || [];
                    return this.departments.filter(d => types.includes(d.type));
                },
                get selectedDepartment() {
                    return this.departments.find(d => d.id == this.departmentId) || null;
                },
                get specificJabatan() {
                    if (! this.selectedDepartment || ! ['1', '2', '3'].includes(String(this.jobLevel))) {
                        return null;
                    }
                    return this.typeLabels[this.selectedDepartment.type] || null;
                }
            }">
            @csrf
            @if ($employee->exists)
                @method('PUT')
            @endif

            <div class="mb-5 flex items-center gap-4">
                <img src="{{ $employee->exists ? $employee->photoUrl() : 'data:image/svg+xml;base64,' . base64_encode('<svg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 40 40\'><rect width=\'40\' height=\'40\' rx=\'20\' fill=\'#465fff\'/><text x=\'50%\' y=\'50%\' dy=\'.35em\' text-anchor=\'middle\' font-family=\'sans-serif\' font-size=\'16\' fill=\'#fff\'>?</text></svg>') }}"
                    alt="Foto pegawai" class="h-16 w-16 rounded-full object-cover">
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Foto Pegawai</label>
                    <input type="file" name="photo" accept="image/*"
                        class="block text-sm text-gray-600 file:mr-3 file:rounded-lg file:border-0 file:bg-brand-50 file:px-4 file:py-2 file:text-sm file:font-medium file:text-brand-500 dark:text-gray-400 dark:file:bg-white/5 dark:file:text-brand-400">
                </div>
            </div>

            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                <x-form.input name="nik" label="NIK" :value="$employee->nik" required />
                <x-form.input name="username" label="Username" :value="$employee->username" required />
                <x-form.input name="name" label="Nama" :value="$employee->name" required />
                <x-form.input name="email" type="email" label="Email" :value="$employee->email" required />
                <x-form.input name="password" type="password" label="Password"
                    :placeholder="$employee->exists ? 'Kosongkan jika tidak diubah' : ''"
                    :required="! $employee->exists" />

                @if ($employee->exists)
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Jabatan</label>
                        <select name="job_level" required x-model="jobLevel" @change="departmentId = ''"
                            class="dark:bg-dark-900 shadow-theme-xs h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 dark:border-gray-700 dark:text-white/90">
                            @foreach (array_reverse(User::JOB_LEVEL_LABELS, true) as $level => $label)
                                <option value="{{ $level }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                @else
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Jabatan</label>
                        <input type="text" readonly disabled value="Staf"
                            class="dark:bg-dark-900 shadow-theme-xs h-11 w-full rounded-lg border border-gray-300 bg-gray-50 px-4 py-2.5 text-sm text-gray-500 dark:border-gray-700 dark:text-gray-400" />
                        <input type="hidden" name="job_level" value="4">
                        <p class="mt-1.5 text-xs text-gray-400">Pegawai baru selalu mulai sebagai Staf. Untuk menetapkan jabatan (Kabag/Kacab/Kanit/Kasi/Direktur), gunakan menu Manajemen Jabatan setelah pegawai dibuat.</p>
                    </div>
                @endif

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Departemen</label>
                    <select name="department_id" required x-model="departmentId"
                        class="dark:bg-dark-900 shadow-theme-xs h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 dark:border-gray-700 dark:text-white/90">
                        <option value="">- Pilih -</option>
                        <template x-for="dept in filteredDepartments" :key="dept.id">
                            <option :value="dept.id" :selected="dept.id == departmentId"
                                x-text="dept.occupied_by ? dept.name + ' — sudah dijabat: ' + dept.occupied_by : dept.name"></option>
                        </template>
                    </select>
                    <p class="mt-1.5 text-xs text-gray-400" x-show="selectedDepartment && selectedDepartment.occupied_by"
                        x-text="'Sudah dijabat: ' + (selectedDepartment ? selectedDepartment.occupied_by : '') + '. Tetap bisa disimpan — pastikan pemegang lama sudah dipindah kalau ini mutasi.'"></p>
                </div>

                <div x-show="specificJabatan">
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Jabatan Spesifik</label>
                    <input type="text" readonly disabled :value="specificJabatan"
                        class="dark:bg-dark-900 shadow-theme-xs h-11 w-full rounded-lg border border-gray-300 bg-gray-50 px-4 py-2.5 text-sm text-gray-500 dark:border-gray-700 dark:text-gray-400" />
                </div>

                <div x-show="selectedDepartment && selectedDepartment.parent_name">
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Induk Departemen</label>
                    <input type="text" readonly disabled :value="selectedDepartment ? selectedDepartment.parent_name : ''"
                        class="dark:bg-dark-900 shadow-theme-xs h-11 w-full rounded-lg border border-gray-300 bg-gray-50 px-4 py-2.5 text-sm text-gray-500 dark:border-gray-700 dark:text-gray-400" />
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Instansi</label>
                    <select name="instansi" required
                        class="dark:bg-dark-900 shadow-theme-xs h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 dark:border-gray-700 dark:text-white/90">
                        <option value="PERUMDAM_TD" @selected($employee->instansi == 'PERUMDAM_TD')>Perumdam Tirta Daroy</option>
                        <option value="KOPKARTIRDA" @selected($employee->instansi == 'KOPKARTIRDA')>KOPKARTIRDA</option>
                    </select>
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Status Kepegawaian</label>
                    <select name="employment_status" required
                        class="dark:bg-dark-900 shadow-theme-xs h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 dark:border-gray-700 dark:text-white/90">
                        <option value="PRAMAGANG" @selected($employee->employment_status == 'PRAMAGANG')>Pramagang</option>
                        <option value="MAGANG" @selected($employee->employment_status == 'MAGANG')>Magang</option>
                        <option value="KONTRAK" @selected($employee->employment_status == 'KONTRAK')>Kontrak</option>
                        <option value="PEGAWAI_80" @selected($employee->employment_status == 'PEGAWAI_80')>Pegawai 80% (transisi Koperasi → Tirta Daroy)</option>
                        <option value="TETAP" @selected($employee->employment_status == 'TETAP' || ! $employee->exists)>Tetap</option>
                    </select>
                </div>

                <x-form.input name="leave_balance" type="number" label="Sisa Cuti"
                    :value="$employee->leave_balance ?? 12" required />

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Role Akses</label>
                    <select name="role" required
                        class="dark:bg-dark-900 shadow-theme-xs h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 dark:border-gray-700 dark:text-white/90">
                        @foreach (\App\Models\User::ROLE_LABELS as $value => $label)
                            <option value="{{ $value }}" @selected($employee->role == $value || (! $employee->exists && $value === \App\Models\User::ROLE_STAFF))>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <a href="{{ route('admin.employees.index') }}"
                    class="inline-flex items-center justify-center rounded-lg bg-white px-5 py-3.5 text-sm font-medium text-gray-700 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-400 dark:ring-gray-700">
                    Batal
                </a>
                <button type="submit"
                    class="inline-flex items-center justify-center rounded-lg bg-brand-500 px-5 py-3.5 text-sm font-medium text-white shadow-theme-xs hover:bg-brand-600">
                    Simpan
                </button>
            </div>
        </form>
    </x-common.component-card>
@endsection
