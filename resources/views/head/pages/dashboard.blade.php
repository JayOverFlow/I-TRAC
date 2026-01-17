{{-- Extend the main layout that you want to use --}}
@extends('head.layout.head-layout')

{{-- Define contents to show in the layout --}}
@section('title', 'Dashboard | I-TRAC')

@push('css')
    <!-- Page SPECIFIC css -->
    <link rel="stylesheet" href="{{ asset('css/head/dashboard/page-specific/apexcharts.css') }}">
    <link rel="stylesheet" href="{{ asset('css/head/dashboard/page-specific/dash_1.css') }}">

    <!-- CUSTOM css -->
    <link rel="stylesheet" href="{{ asset('css/head/dashboard/custom-dashboard.css') }}">
@endpush

@section('content')
    <h1 class="header-text">This is a sample content</h1>
    <p id="number">0</p>
    <button id="increment">+</button>
@endsection

@push('js')
    <!-- Page SPECIFIC js -->
    <script src="{{ asset('js/head/dashboard/page-specific/apexcharts.min.js') }}" ></script>
    <script src="{{ asset('js/head/dashboard/page-specific/dash_1.js') }}" ></script>

    <!-- CUSTOM js -->
    
@endpush
