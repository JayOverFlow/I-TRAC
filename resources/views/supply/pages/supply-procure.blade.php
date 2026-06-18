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
    <link rel="stylesheet" href="{{ asset('css/custom-code-input.css') }}">
@endpush

@section('content')
    <div class="procure-container layout-top-spacing w-100">
        <div class="col-xl-12 col-lg-12 col-sm-12 layout-spacing p-0">
            <div class="widget-content widget-content-area br-8">
                <table id="po-table" class="table dt-table-hover" style="width:100%"
                    data-route="{{ route('procure.retrieve.po') }}">
                    <thead>
                        <tr>
                            <th class="fw-bold black-text text-nowrap text-center" style="width: 15%">PO-ID</th>
                            <th class="fw-bold black-text" style="width: 50%">Title</th>
                            <th class="fw-bold black-text text-center" style="width: 25%">Date Created</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($pos as $po)
                            <tr class="clickable-row" data-id="{{ $po->po_id }}" data-review-route="{{ $po->hasDeliveryAttachment() ? route('show.delivery.attachment', $po->po_id) : route('show.po.review', $po->po_id) }}" style="cursor: pointer;">
                                <td class="text-center">{{ $po->po_id }}</td>
                                <td>{{ $po->po_title }}</td>
                                <td class="text-center">{{ $po->created_at ? $po->created_at->format('Y-m-d') : 'N/A' }}
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
