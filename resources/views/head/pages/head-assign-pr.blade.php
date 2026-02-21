{{-- Extend the main layout that you want to use --}}
@extends('head/layout/head-layout')

{{-- Define contents to show in the layout --}}
@section('title', 'Assign Purchase Request | I-TRAC')

@push('css')
    <!-- Page SPECIFIC css -->
    <link rel="stylesheet" href="{{ asset('css/head/assign-pr/page-specific/datatables.css') }}">
    <link rel="stylesheet" href="{{ asset('css/head/assign-pr/page-specific/dt-global_style.css') }}">

    <!-- CUSTOM css -->
    <link rel="stylesheet" href="{{ asset('css/head/assign-pr/head-assign-pr.css') }}">
@endpush

@section('content')
    <div class="card allocated-budget-card mb-3">
        <div class="card-body d-flex justify-content-center justify-content-between align-items-center">
            <h5 class="card-title mb-0 white-text">ALLOCATED BUDGET</h5>
            <h5 class="card-title mb-0 white-text">PHP 12,345.00</h5>
        </div>
    </div>

    <div class="card px-0">
        <div class="card-body px-0">
            <h3 class="card-title red-text-2 fw-bold px-2 ms-4">ANNUAL PROCUREMENT PLAN</h3>
            <table id="zero-config" class="table table-striped dt-table-hover border-top-0"
                style="width:100%; border-top: none !important;">
                <thead>
                    <tr>
                        <th></th>
                        <th class="fw-bold">Code</th>
                        <th class="fw-bold">Procurement Program / Project</th>
                        <th class="fw-bold">Ads/Post of IB/REI</th>
                        <th class="fw-bold">Sub/Open of Bids date</th>
                        <th class="fw-bold">Notice of Award</th>
                        <th class="fw-bold">Contract Signing</th>
                        <th class="fw-bold">Total</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            <div class="form-check form-check-danger form-check-inline">
                                <input class="form-check-input" type="checkbox" value="" id="form-check-danger">
                            </div>
                        </td>
                        <td>1234</td>
                        <td>Physics Laboratory Equipment Supplies and Materials</td>
                        <td></td>
                        <td>March 11, 2025</td>
                        <td>March 12, 2025</td>
                        <td>March 14, 2025</td>
                        <td>Php 50,000.00</td>
                    </tr>
                    <tr>
                        <td>
                            <div class="form-check form-check-danger form-check-inline">
                                <input class="form-check-input" type="checkbox" value="" id="form-check-danger">
                            </div>
                        </td>
                        <td>5678</td>
                        <td>Folder</td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                    <tr>
                        <td>
                            <div class="form-check form-check-danger form-check-inline">
                                <input class="form-check-input" type="checkbox" value="" id="form-check-danger">
                            </div>
                        </td>
                        <td>9101</td>
                        <td>1 Steel Cabinet</td>
                        <td>June 2, 2025</td>
                        <td>June 10, 2025</td>
                        <td>June 13, 2025</td>
                        <td>June 14, 2025</td>
                        <td>Php 20,000.00</td>
                    </tr>
                    <tr>
                        <td>
                            <div class="form-check form-check-danger form-check-inline">
                                <input class="form-check-input" type="checkbox" value="" id="form-check-danger">
                            </div>
                        </td>
                        <td>9101</td>
                        <td>Microwave with birth certificate</td>
                        <td>June 2, 2025</td>
                        <td>June 10, 2025</td>
                        <td>June 13, 2025</td>
                        <td>June 14, 2025</td>
                        <td>Php 20,000.00</td>
                    </tr>
                    <tr>
                        <td>
                            <div class="form-check form-check-danger form-check-inline">
                                <input class="form-check-input" type="checkbox" value="" id="form-check-danger">
                            </div>
                        </td>
                        <td>9101</td>
                        <td>Licensed Professional Electric Fan</td>
                        <td>June 2, 2025</td>
                        <td>June 10, 2025</td>
                        <td>June 13, 2025</td>
                        <td>June 14, 2025</td>
                        <td>Php 20,000.00</td>
                    </tr>
                    <tr>
                        <td>
                            <div class="form-check form-check-danger form-check-inline">
                                <input class="form-check-input" type="checkbox" value="" id="form-check-danger">
                            </div>
                        </td>
                        <td>9101</td>
                        <td>Monitor with 67 years warranty</td>
                        <td>June 2, 2025</td>
                        <td>June 10, 2025</td>
                        <td>June 13, 2025</td>
                        <td>June 14, 2025</td>
                        <td>Php 20,000.00</td>
                    </tr>
                </tbody>
            </table>
            <div id="action-buttons" class="d-flex justify-content-start ms-3 mt-3 mt-sm-0">
                <button class="btn btn-red btn-back me-3">Assign</button>
                <button class="btn btn-action btn-red btn-nxt">Create</button>
            </div>
            <h5 class="text-end fw-bold black-text me-3">Total Amount: <span class="fw-normal">Php 20,000.00</span></h5>
        </div>
    </div>
@endsection

@push('js')
    <!-- Page SPECIFIC js -->
    <script src="{{ asset('js/head/assign-pr/page-specific/datatables.js') }}"></script>
    <script>
        $('#zero-config').DataTable({
            "columnDefs": [{
                    "targets": [0, 1, 3, 4, 5, 6, 7],
                    "className": "text-center align-middle"
                },
                {
                    "targets": 2,
                    "className": "text-start align-middle text-wrap",
                    "width": "25%"
                }
            ],
            "searching": false,
            "lengthChange": false,
            "info": false,
            "dom": "<'table-responsive border-top-0'<'row'<'col-12'>>tr>" +
                "<'dt--bottom-section d-sm-flex justify-content-sm-between align-items-center text-center mt-3'<'#actions-container'><'dt--pagination'p>>",
            "initComplete": function() {
                $('#action-buttons').appendTo('#actions-container');
            },
            "oLanguage": {
                "oPaginate": {
                    "sPrevious": '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-arrow-left"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>',
                    "sNext": '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-arrow-right"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>'
                }
            },
            "stripeClasses": [],
            "pageLength": 5
        });
    </script>

    <!-- CUSTOM js -->
    <script src="{{ asset('js/head/assign-pr/head-assign-pr.js') }}"></script>
@endpush
