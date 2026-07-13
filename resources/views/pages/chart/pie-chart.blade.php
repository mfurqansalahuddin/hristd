@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Pie chart" />
    <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">
        <x-common.component-card title="Donut Pie Chart 1">
            <!-- ====== Pie Chart One Start -->
            <div class="flex justify-center">
                <div id="chartSeven" class="chartDarkStyle"></div>
            </div>
            <!-- ====== Pie Chart One End -->
        </x-common.component-card>

        <x-common.component-card title="Donut Pie Chart 2">
            <!-- ====== Pie Chart Two Start -->
            <div class="flex justify-center">
                <div id="chartTwentyTwo" class="chartDarkStyle"></div>
            </div>
            <!-- ====== Pie Chart Two End -->
        </x-common.component-card>

        <x-common.component-card title="Donut Pie Chart 3">
            <!-- ====== Pie Chart Three Start -->
            <div class="flex justify-center">
                <div id="chartThirtyFive" class="chartDarkStyle"></div>
            </div>
            <!-- ====== Pie Chart Three End -->
        </x-common.component-card>  

        <x-common.component-card title="Donut Pie Chart 4">
            <!-- ====== Pie Chart Four Start -->
            <div class="flex justify-center">
                <div id="chartThirtyThree" class="chartDarkStyle w-full"></div>
            </div>
            <!-- ====== Pie Chart Four End -->
        </x-common.component-card>

        <x-common.component-card title="Semi Donut Chart">
            <!-- ====== Semi Donut FortyFour Start -->
            <div class="flex justify-center">
                <div id="chartThirtyFour" class="chartDarkStyle w-full"></div>
            </div>
            <!-- ====== Semi Donut FortyFour End -->
        </x-common.component-card>
    </div>
@endsection
