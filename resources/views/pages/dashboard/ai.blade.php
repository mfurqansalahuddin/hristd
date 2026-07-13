@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <x-ai-dashboard.ai-stats />
        <div class="grid grid-cols-1 gap-6 xl:grid-cols-12">
            <div class="xl:col-span-8">
                <x-ai-dashboard.user-and-revenue-stats />
            </div>
            <div class="xl:col-span-4">
                <x-ai-dashboard.api-token-usage />
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
            <x-ai-dashboard.user-analytics />
            <x-ai-dashboard.project-analytics />
        </div>
        <x-ai-dashboard.recent-transactions />
    </div>
@endsection
