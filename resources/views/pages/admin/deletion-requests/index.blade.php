@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb :pageTitle="$title" />

    <livewire:admin.deletion-requests-table />
@endsection
