{{-- Extend the main layout that you want to use --}}
@extends('layouts.supply-layout')

{{-- Define contents to show in the layout --}}
@section('title', 'Procure | I-TRAC')

@push('css')
    <!-- Page SPECIFIC css -->
    <link rel="stylesheet" href="{{ asset('css/head/dashboard/page-specific/datatables.css') }}">
    <link rel="stylesheet" href="{{ asset('css/head/dashboard/page-specific/dt-global_style.css') }}">
    <link rel="stylesheet" href="{{ asset('css/head/dashboard/page-specific/dark/datatables.css') }}">
    <link rel="stylesheet" href="{{ asset('css/head/dashboard/page-specific/dark/dt-global_style.css') }}">

    <!-- CUSTOM css -->
    <link rel="stylesheet" href="{{ asset('css/supply/procure/custom-procure.css') }}">
@endpush

@section('content')
    <div class="procure-container layout-top-spacing w-100">
        <div class="col-xl-12 col-lg-12 col-sm-12 layout-spacing p-0">
            <div class="widget-content widget-content-area br-8">
                <h4 class="fw-bold red-text px-0">Purchase Orders</h4>
                <table id="po-table" class="table table-striped dt-table-hover" style="width:100%"
                    data-route="{{ route('procure.retrieve.po') }}">
                    <thead>
                        <tr>
                            <th class="fw-bold black-text text-nowrap text-center" style="width: 15%">PO-ID</th>
                            <th class="fw-bold black-text" style="width: 50%">Title</th>
                            <th class="fw-bold black-text text-center" style="width: 25%">Date Created</th>
                            <th class="fw-bold black-text text-nowrap text-center" style="width: 10%">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($pos as $po)
                            <tr class="clickable-row" data-id="{{ $po->po_id }}">
                                <td class="text-center">{{ $po->po_id }}</td>
                                <td>{{ $po->po_title }}</td>
                                <td class="text-center">{{ $po->created_at ? $po->created_at->format('Y-m-d') : 'N/A' }}
                                </td>
                                <td class="text-center">
                                    <button class="btn bg-transparent p-0 border-0 shadow-none" title="View Purchase Order">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="19" height="20"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                            stroke-linecap="round" stroke-linejoin="round" class="feather feather-eye"
                                            style="color: #4361ee;">
                                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                            <circle cx="12" cy="12" r="3"></circle>
                                        </svg>
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@push('js')
    <!-- Page SPECIFIC js -->
    <script src="{{ asset('js/head/dashboard/page-specific/datatables.js') }}"></script>

    <!-- CUSTOM js -->
    <script src="{{ asset('js/supply/procure/custom-procure.js') }}"></script>
@endpush
