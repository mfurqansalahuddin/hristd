@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb :pageTitle="$title" />

    <livewire:admin.kpi-categories-panel />
@endsection
