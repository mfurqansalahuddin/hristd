@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb :pageTitle="$title" />

    @if (session('success'))
        <div class="mb-6">
            <x-ui.alert variant="success" :message="session('success')" />
        </div>
    @endif

    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <p class="text-sm text-gray-500 dark:text-gray-400">Total Pegawai</p>
            <p class="mt-2 text-2xl font-semibold text-gray-800 dark:text-white/90">{{ $totalEmployees }}</p>
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <p class="text-sm text-gray-500 dark:text-gray-400">Hadir Hari Ini</p>
            <p class="mt-2 text-2xl font-semibold text-gray-800 dark:text-white/90">{{ $presentToday }}</p>
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <p class="text-sm text-gray-500 dark:text-gray-400">Telat Hari Ini</p>
            <p class="mt-2 text-2xl font-semibold text-gray-800 dark:text-white/90">{{ $lateToday }}</p>
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <p class="text-sm text-gray-500 dark:text-gray-400">Cuti Menunggu Approval</p>
            <p class="mt-2 text-2xl font-semibold text-gray-800 dark:text-white/90">{{ $pendingLeaveRequests }}</p>
        </div>
    </div>

    <div class="mt-6">
        <x-common.component-card title="Periode KPI Berjalan">
            @if ($currentPeriod)
                <p class="text-sm text-gray-600 dark:text-gray-300">
                    {{ $currentPeriod->month }}/{{ $currentPeriod->year }} &mdash;
                    <x-ui.badge color="primary">{{ $currentPeriod->status }}</x-ui.badge>
                </p>
            @else
                <p class="text-sm text-gray-500 dark:text-gray-400">Belum ada periode KPI yang dibuka.</p>
            @endif
        </x-common.component-card>
    </div>
@endsection
