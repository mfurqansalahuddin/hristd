@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb :pageTitle="$title" />

    <livewire:admin.kpi-period-phases-panel />
@endsection
