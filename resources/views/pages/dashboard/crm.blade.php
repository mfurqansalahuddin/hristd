@extends('layouts.app')

@section('content')
    <div class="grid grid-cols-12 gap-4 md:gap-6">
        <div class="col-span-12">
            <x-crm.crm-metrics />
        </div>

        <div class="col-span-12 xl:col-span-8">
            <x-crm.crm-statistics-chart />
        </div>

        <div class="col-span-12 xl:col-span-4">
            <x-crm.estimated-revenue-chart />
        </div>

        <div class="col-span-12 xl:col-span-6">
            <x-crm.sale-category-chart />
        </div>

        <div class="col-span-12 xl:col-span-6">
            <x-crm.upcoming-schedule />
        </div>

        <div class="col-span-12">
            <x-crm.crm-table />
        </div>
    </div>
@endsection
