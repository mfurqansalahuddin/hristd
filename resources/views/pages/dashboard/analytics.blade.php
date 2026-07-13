@extends('layouts.app')

@section('content')
    <div class="grid grid-cols-12 gap-4 md:gap-6">
        <div class="col-span-12">
            <x-analytics.analytics-metrics />
        </div>

        <div class="col-span-12">
            <x-analytics.analytics-chart />
        </div>

        <div class="col-span-12 xl:col-span-7">
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <x-analytics.top-channel-card />
                <x-analytics.top-pages-card />
            </div>
        </div>

        <div class="col-span-12 xl:col-span-5">
            <x-analytics.active-user-chart />
        </div>

        <div class="col-span-12 xl:col-span-7">
            <x-analytics.acquisition-chart />
        </div>

        <div class="col-span-12 xl:col-span-5">
            <x-analytics.session-chart />
        </div>

        <div class="col-span-12 xl:col-span-5">
            <x-ecommerce.customer-demographic />
        </div>

        <div class="col-span-12 xl:col-span-7">
            <x-analytics.analytics-table />
        </div>
    </div>
@endsection
