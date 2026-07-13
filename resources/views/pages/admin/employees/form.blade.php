@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb :pageTitle="$title" />

    <x-common.component-card :title="$title">
        <form method="POST"
            action="{{ $employee->exists ? route('admin.employees.update', $employee) : route('admin.employees.store') }}">
            @csrf
            @if ($employee->exists)
                @method('PUT')
            @endif

            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                <x-form.input name="nik" label="NIK" :value="$employee->nik" required />
                <x-form.input name="name" label="Nama" :value="$employee->name" required />
                <x-form.input name="email" type="email" label="Email" :value="$employee->email" required />
                <x-form.input name="password" type="password" label="Password"
                    :placeholder="$employee->exists ? 'Kosongkan jika tidak diubah' : ''"
                    :required="! $employee->exists" />

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Departemen</label>
                    <select name="department_id"
                        class="dark:bg-dark-900 shadow-theme-xs h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 dark:border-gray-700 dark:text-white/90">
                        <option value="">- Tidak ada -</option>
                        @foreach ($departments as $department)
                            <option value="{{ $department->id }}" @selected($employee->department_id == $department->id)>
                                {{ $department->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Level Jabatan</label>
                    <select name="job_level" required
                        class="dark:bg-dark-900 shadow-theme-xs h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 dark:border-gray-700 dark:text-white/90">
                        <option value="1" @selected($employee->job_level == 1)>1 - Direksi</option>
                        <option value="2" @selected($employee->job_level == 2)>2 - Kabag/Kacab/Kanit/Staf Ahli</option>
                        <option value="3" @selected($employee->job_level == 3)>3 - Kasi</option>
                        <option value="4" @selected($employee->job_level == 4 || ! $employee->exists)>4 - Staf</option>
                    </select>
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Atasan Langsung</label>
                    <select name="direct_supervisor_id"
                        class="dark:bg-dark-900 shadow-theme-xs h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 dark:border-gray-700 dark:text-white/90">
                        <option value="">- Tidak ada -</option>
                        @foreach ($supervisors as $supervisor)
                            <option value="{{ $supervisor->id }}" @selected($employee->direct_supervisor_id == $supervisor->id)>
                                {{ $supervisor->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Atasan Final (Penilai Akhir)</label>
                    <select name="final_supervisor_id"
                        class="dark:bg-dark-900 shadow-theme-xs h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 dark:border-gray-700 dark:text-white/90">
                        <option value="">- Tidak ada -</option>
                        @foreach ($supervisors as $supervisor)
                            <option value="{{ $supervisor->id }}" @selected($employee->final_supervisor_id == $supervisor->id)>
                                {{ $supervisor->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Instansi</label>
                    <select name="instansi" required
                        class="dark:bg-dark-900 shadow-theme-xs h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 dark:border-gray-700 dark:text-white/90">
                        <option value="PERUMDAM_TD" @selected($employee->instansi == 'PERUMDAM_TD')>Perumdam Tirta Daroy</option>
                        <option value="KOPKARTIRTA" @selected($employee->instansi == 'KOPKARTIRTA')>Kopkar Tirta</option>
                    </select>
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Status Kepegawaian</label>
                    <select name="employment_status" required
                        class="dark:bg-dark-900 shadow-theme-xs h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 dark:border-gray-700 dark:text-white/90">
                        <option value="MAGANG" @selected($employee->employment_status == 'MAGANG')>Magang</option>
                        <option value="KONTRAK" @selected($employee->employment_status == 'KONTRAK')>Kontrak</option>
                        <option value="TETAP" @selected($employee->employment_status == 'TETAP' || ! $employee->exists)>Tetap</option>
                    </select>
                </div>

                <x-form.input name="leave_balance" type="number" label="Sisa Cuti"
                    :value="$employee->leave_balance ?? 12" required />

                <div class="flex items-center gap-2 pt-7">
                    <input type="checkbox" id="is_admin" name="is_admin" value="1" @checked($employee->is_admin)
                        class="h-4 w-4 rounded border-gray-300">
                    <label for="is_admin" class="text-sm font-medium text-gray-700 dark:text-gray-400">
                        Akses Panel HRD (Admin)
                    </label>
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
