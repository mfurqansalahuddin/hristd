@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb :pageTitle="$title" />

    @if (session('success'))
        <div class="mb-6">
            <x-ui.alert variant="success" :message="session('success')" />
        </div>
    @endif

    <div class="mb-6">
        <x-common.component-card title="Buka Periode KPI Baru">
            <form method="POST" action="{{ route('admin.kpi-periods.store') }}" class="flex flex-wrap items-end gap-4">
                @csrf
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Bulan</label>
                    <select name="month" required
                        class="dark:bg-dark-900 shadow-theme-xs h-11 w-40 rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 dark:border-gray-700 dark:text-white/90">
                        @foreach (range(1, 12) as $m)
                            <option value="{{ $m }}" @selected($m == now()->month)>{{ $m }}</option>
                        @endforeach
                    </select>
                </div>
                <x-form.input name="year" type="number" label="Tahun" :value="now()->year" required />
                <button type="submit"
                    class="inline-flex items-center justify-center rounded-lg bg-brand-500 px-5 py-3.5 text-sm font-medium text-white shadow-theme-xs hover:bg-brand-600">
                    Buka Periode
                </button>
            </form>
        </x-common.component-card>
    </div>

    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="max-w-full overflow-x-auto custom-scrollbar">
            <table class="w-full min-w-[700px]">
                <thead>
                    <tr class="border-b border-gray-100 dark:border-gray-800">
                        <th class="px-5 py-3 text-left sm:px-6"><p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Periode</p></th>
                        <th class="px-5 py-3 text-left sm:px-6"><p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Status</p></th>
                        <th class="px-5 py-3 text-left sm:px-6"><p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Ubah Status</p></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($kpiPeriods as $period)
                        <tr class="border-b border-gray-100 dark:border-gray-800">
                            <td class="px-5 py-4 sm:px-6"><p class="text-gray-800 text-theme-sm dark:text-white/90">{{ $period->month }}/{{ $period->year }}</p></td>
                            <td class="px-5 py-4 sm:px-6"><x-ui.badge color="primary">{{ $period->status }}</x-ui.badge></td>
                            <td class="px-5 py-4 sm:px-6">
                                <form method="POST" action="{{ route('admin.kpi-periods.update-status', $period) }}" class="flex items-center gap-2">
                                    @csrf
                                    @method('PUT')
                                    <select name="status"
                                        class="dark:bg-dark-900 shadow-theme-xs h-9 rounded-lg border border-gray-300 bg-transparent px-3 text-sm text-gray-800 dark:border-gray-700 dark:text-white/90">
                                        @foreach (['DRAFT', 'EVALUATION', 'DISPUTE', 'CLOSED'] as $status)
                                            <option value="{{ $status }}" @selected($period->status == $status)>{{ $status }}</option>
                                        @endforeach
                                    </select>
                                    <button type="submit" class="text-sm text-brand-500 hover:underline">Simpan</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">
        {{ $kpiPeriods->links() }}
    </div>
@endsection
