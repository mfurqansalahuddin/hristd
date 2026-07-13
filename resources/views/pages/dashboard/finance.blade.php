@extends('layouts.app')

@section('content')
  <div class="space-y-5">
    <div class="grid grid-cols-1 gap-5 xl:grid-cols-12">
      <div class="xl:col-span-6 2xl:col-span-5">
        <x-finance.total-balance-overview />
      </div>
      <div class="xl:col-span-6 2xl:col-span-7">
        <x-finance.finance-stats />
      </div>
    </div>
    <div class="grid grid-cols-1 gap-5 xl:grid-cols-12">
      <div class="col-span-12 space-y-5 xl:col-span-8">
        <x-finance.cashflow-overview />
        <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">
          <x-finance.spending-widget />
          <x-finance.quick-send />
        </div>
      </div>
      <div class="col-span-12 xl:col-span-4">
        <x-finance.my-cards />
      </div>
    </div>
    <x-finance.finance-transaction-table />
  </div>
@endsection
