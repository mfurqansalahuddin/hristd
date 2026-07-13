@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        {{-- Sale Stats --}}
        <x-sales.sale-stats />
        {{-- Revenue Breakdown --}}
        <x-sales.revenue-breakdown />
        <div class="grid grid-cols-1 gap-5 lg:grid-cols-2 xl:grid-cols-3">
            {{-- User Retention --}}
            <x-sales.user-retention />
            {{-- Sale by Channel --}}
            <x-sales.sale-by-channel />
            {{-- Sale by Country --}}
            <x-sales.sale-by-country />
        </div>
        {{-- Top Product Table --}}
        <x-sales.top-product-table />
    </div>
@endsection
