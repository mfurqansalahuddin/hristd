@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb :pageTitle="$title" :items="[['label' => 'Presensi Apel & Kegiatan', 'url' => route('admin.mandatory-events.index')]]" />

    <livewire:admin.mandatory-event-attendance-form :event="$event" />
@endsection
