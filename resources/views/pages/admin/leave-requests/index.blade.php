@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb :pageTitle="$title" />

    @if (session('success'))
        <div class="mb-6">
            <x-ui.alert variant="success" :message="session('success')" />
        </div>
    @endif

    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="max-w-full overflow-x-auto custom-scrollbar">
            <table class="w-full min-w-[900px]">
                <thead>
                    <tr class="border-b border-gray-100 dark:border-gray-800">
                        <th class="px-5 py-3 text-left sm:px-6"><p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Pegawai</p></th>
                        <th class="px-5 py-3 text-left sm:px-6"><p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Jenis</p></th>
                        <th class="px-5 py-3 text-left sm:px-6"><p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Rentang Tanggal</p></th>
                        <th class="px-5 py-3 text-left sm:px-6"><p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Status</p></th>
                        <th class="px-5 py-3 text-left sm:px-6"><p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Aksi</p></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($leaveRequests as $leaveRequest)
                        <tr class="border-b border-gray-100 dark:border-gray-800">
                            <td class="px-5 py-4 sm:px-6"><p class="text-gray-800 text-theme-sm dark:text-white/90">{{ $leaveRequest->user->name }}</p></td>
                            <td class="px-5 py-4 sm:px-6"><p class="text-gray-500 text-theme-sm dark:text-gray-400">{{ $leaveRequest->type }}</p></td>
                            <td class="px-5 py-4 sm:px-6">
                                <p class="text-gray-500 text-theme-sm dark:text-gray-400">
                                    {{ $leaveRequest->start_date->format('d M Y') }} - {{ $leaveRequest->end_date->format('d M Y') }}
                                </p>
                            </td>
                            <td class="px-5 py-4 sm:px-6">
                                <x-ui.badge :color="match ($leaveRequest->hr_final_status) {
                                    'APPROVED' => 'success',
                                    'REJECTED' => 'error',
                                    default => 'warning',
                                }">
                                    {{ $leaveRequest->hr_final_status }}
                                </x-ui.badge>
                            </td>
                            <td class="px-5 py-4 sm:px-6">
                                @if ($leaveRequest->hr_final_status === 'PENDING')
                                    <div class="flex items-center gap-3">
                                        <form method="POST" action="{{ route('admin.leave-requests.approve', $leaveRequest) }}">
                                            @csrf
                                            @method('PUT')
                                            <button type="submit" class="text-sm text-success-500 hover:underline">Approve</button>
                                        </form>
                                        <form method="POST" action="{{ route('admin.leave-requests.reject', $leaveRequest) }}">
                                            @csrf
                                            @method('PUT')
                                            <button type="submit" class="text-sm text-error-500 hover:underline">Tolak</button>
                                        </form>
                                    </div>
                                @else
                                    <span class="text-sm text-gray-400">-</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">
        {{ $leaveRequests->links() }}
    </div>
@endsection
