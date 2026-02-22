{{-- Extend the main layout that you want to use --}}
@extends('head/layout/head-layout')

{{-- Define contents to show in the layout --}}
@section('title', 'Assign Purchase Request | I-TRAC')

@push('css')
    <!-- Page SPECIFIC css -->
    <link rel="stylesheet" href="{{ asset('css/head/assign-pr/page-specific/datatables.css') }}">
    <link rel="stylesheet" href="{{ asset('css/head/assign-pr/page-specific/dt-global_style.css') }}">
    <link rel="stylesheet" href="{{ asset('css/head/assign-pr/page-specific/animate.css') }}">
    <link rel="stylesheet" href="{{ asset('css/head/assign-pr/page-specific/modal.css') }}">

    <!-- CUSTOM css -->
    <link rel="stylesheet" href="{{ asset('css/head/assign-pr/head-assign-pr.css') }}">
@endpush

@section('content')
    <div class="modal fade" id="exampleModalCenter" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document" style="max-width: 600px;">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold red-text-2" id="exampleModalCenterTitle">Assign Purchase
                        Request</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                        <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round" class="feather feather-x">
                            <line x1="18" y1="6" x2="6" y2="18"></line>
                            <line x1="6" y1="6" x2="18" y2="18"></line>
                        </svg>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group mb-3">
                        <input type="text" class="form-control" id="user-search-input"
                            placeholder="Search by Name/TUP ID">
                    </div>
                    <div class="table-responsive" style="height: 250px; overflow-y: auto;">
                        <table class="table table-hover user-list-table mb-0">
                            <thead>
                                <tr>
                                    <th scope="col" class="fw-bold">Name</th>
                                    <th scope="col" class="text-center fw-bold" style="width: 120px;">TUP ID
                                    </th>
                                </tr>
                            </thead>
                            <tbody id="user-list">
                                <tr class="user-list-item" style="cursor: pointer;">
                                    <td class="align-middle user-name">Patrick Justin Ariado</td>
                                    <td class="text-center align-middle">
                                        TUPT-123456
                                    </td>
                                </tr>
                                <tr class="user-list-item" style="cursor: pointer;">
                                    <td class="align-middle user-name">Pak Juna</td>
                                    <td class="text-center align-middle">
                                        TUPT-827482
                                    </td>
                                </tr>
                                <tr class="user-list-item" style="cursor: pointer;">
                                    <td class="align-middle user-name">Kemberlet Crissel</td>
                                    <td class="text-center align-middle">
                                        TUPT-283798
                                    </td>
                                </tr>
                                <tr class="user-list-item" style="cursor: pointer;">
                                    <td class="align-middle user-name">Jay Jay Bautista</td>
                                    <td class="text-center align-middle">
                                        TUPT-127651
                                    </td>
                                </tr>
                                <tr class="user-list-item" style="cursor: pointer;">
                                    <td class="align-middle user-name">Ron Eric Contis</td>
                                    <td class="text-center align-middle">
                                        TUPT-928273
                                    </td>
                                </tr>
                                <tr class="user-list-item" style="cursor: pointer;">
                                    <td class="align-middle user-name">Arthur Nery</td>
                                    <td class="text-center align-middle">
                                        TUPT-928371
                                    </td>
                                </tr>
                                <tr class="user-list-item" style="cursor: pointer;">
                                    <td class="align-middle user-name">The Beatles</td>
                                    <td class="text-center align-middle">
                                        TUPT-837463
                                    </td>
                                </tr>
                                <tr class="user-list-item" style="cursor: pointer;">
                                    <td class="align-middle user-name">Freddie Mercury</td>
                                    <td class="text-center align-middle">
                                        TUPT-938472
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer" style="border-top: none !important;">
                    <button type="button" class="btn btn-red" id="confirm-assign-btn" disabled>Assign</button>
                </div>
            </div>
        </div>
    </div>

    <div class="card allocated-budget-card mb-3">
        <div class="card-body d-flex justify-content-center justify-content-between align-items-center">
            <h5 class="card-title mb-0 white-text">ALLOCATED BUDGET</h5>
            <h5 class="card-title mb-0 white-text">PHP 12,345.00</h5>
        </div>
    </div>

    <div class="card px-0">
        <div class="card-body px-0">
            <h5 class="card-title red-text-2 fw-bold px-2 ms-4">ANNUAL PROCUREMENT PLAN</h5>
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
                                <input class="form-check-input item-checkbox" type="checkbox" value=""
                                    id="form-check-danger">
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
                                <input class="form-check-input item-checkbox" type="checkbox" value=""
                                    id="form-check-danger">
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
                                <input class="form-check-input item-checkbox" type="checkbox" value=""
                                    id="form-check-danger">
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
                                <input class="form-check-input item-checkbox" type="checkbox" value=""
                                    id="form-check-danger">
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
                                <input class="form-check-input item-checkbox" type="checkbox" value=""
                                    id="form-check-danger">
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
                                <input class="form-check-input item-checkbox" type="checkbox" value=""
                                    id="form-check-danger">
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
                <button class="btn btn-red btn-back me-3" id="assign-btn" data-bs-toggle="modal"
                    data-bs-target="#exampleModalCenter" disabled>Assign</button>
                <button class="btn btn-action btn-red btn-nxt" id="create-btn" disabled>Create</button>
            </div>
            <h5 class="text-end fw-bold black-text me-3">Total Amount: <span class="fw-normal">Php 20,000.00</span></h5>
        </div>
    </div>
@endsection

@push('js')
    <!-- Page SPECIFIC js -->
    <script src="{{ asset('js/head/assign-pr/page-specific/datatables.js') }}"></script>
    <script src="{{ asset('js/head/assign-pr/page-specific/scrollspyNav.js') }}"></script>

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
