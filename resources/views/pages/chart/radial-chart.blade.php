@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Radial chart" />
    <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">
        <x-common.component-card title="Radial Progress Chart">
            <!-- ====== Radial Chart Forty Start -->
            <div class="flex justify-center">
                <div id="chartForty" class="chartDarkStyle w-full"></div>
            </div>
            <!-- ====== Radial Chart Forty End -->
        </x-common.component-card>

        <x-common.component-card title="Semi Radial Bar Chart">
            <!-- ====== Radial Chart FortyOne Start -->
            <div class="relative flex justify-center">
                <div id="chartFortyOne" class="chartDarkStyle w-full"></div>
                <!-- <p class="absolute bottom-10.5 text-base font-medium text-gray-700 dark:text-white/90">
                    Salary
                </p> -->
            </div>
            <!-- ====== Radial Chart FortyOne End -->
        </x-common.component-card>

        <x-common.component-card title="Multi Ring Radial Chart">
            <!-- ====== Radial Chart FortyTwo Start -->
            <div class="flex justify-center">
                <div id="chartFortyTwo" class="chartDarkStyle w-full"></div>
            </div>
            <!-- ====== Radial Chart FortyTwo End -->
        </x-common.component-card>

        <x-common.component-card title="Partial Donut with Legend">
            <!-- ====== Donut FortyThree Start -->
            <div class="flex justify-center">
                <div id="chartFortyThree" class="chartDarkStyle w-full"></div>
            </div>
            <!-- ====== Donut FortyThree End -->
        </x-common.component-card>
    </div>
@endsection
