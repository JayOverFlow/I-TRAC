{{-- Extend the main layout that you want to use --}}
@extends('layouts.procurement-layout')

{{-- Define contents to show in the layout --}}
@section('title', 'Dashboard | I-TRAC')

@push('css')
    <!-- Page SPECIFIC css -->
    <link rel="stylesheet" href="{{ asset('css/head/dashboard/page-specific/apexcharts.css') }}">
    <link rel="stylesheet" href="{{ asset('css/head/dashboard/page-specific/dash_1.css') }}">
    <link rel="stylesheet" href="{{ asset('css/head/dashboard/page-specific/datatables.css') }}">
    <link rel="stylesheet" href="{{ asset('css/head/dashboard/page-specific/dt-global_style.css') }}">

    <!-- CUSTOM css -->
    <link rel="stylesheet" href="{{ asset('css/head/dashboard/custom-dashboard.css') }}">
@endpush

@section('content')
    <div class="p-0">
        <div class="row">
            <div class="col-4">
                <div class="card h-100">
                    <div class="card-body row p-4">
                        <div class="col-4">
                            <img src="{{ asset('img/department.png') }}" alt="Department">
                        </div>
                        <div class="col-8 text-end">
                            <h5 class="card-title fw-bold mb-0">Department Budget</h5>
                            <p class="my-0 text-decoration-underline">user_department</p>
                            <h5 class="mb-0 fw-bold">₱<span>204,534.00</span></h5>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-4">
                <div class="card h-100">
                    <div class="card-body row p-4">
                        <div class="col-4">
                            <img src="{{ asset('img/utilized-budget.png') }}" alt="Utilized Budget">
                        </div>
                        <div class="col-8 text-end">
                            <h5 class="card-title fw-bold">Utilized Budget</h5>
                            <h5 class="mb-0 fw-bold">₱<span>204,534.00</span></h5>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-4">
                <div class="card h-100">
                    <div class="card-body row p-4">
                        <div class="col-4">
                            <img src="{{ asset('img/school-year.png') }}" alt="School Year">
                        </div>
                        <div class="col-8 text-end">
                            <h5 class="card-title fw-bold">School Year</h5>
                            <h5 class="mb-0 fw-bold">2026</span></h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="widget-content widget-content-area br-8 mt-3">
        <table id="zero-config" class="table table-striped dt-table-hover" style="width:100%">
            <thead>
                <tr>
                    <th class="fw-bold">TUP-ID</th>
                    <th class="fw-bold">Full Name</th>
                    <th class="fw-bold">Status</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>tup_id</td>
                    <td>full_name</td>
                    <td>Status</td>
                </tr>
                <tr>
                    <td>tup_id</td>
                    <td>full_name</td>
                    <td>Status</td>
                </tr>
                <tr>
                    <td>tup_id</td>
                    <td>full_name</td>
                    <td>Status</td>
                </tr>
                <tr>
                    <td>tup_id</td>
                    <td>full_name</td>
                    <td>Status</td>
                </tr>
                <tr>
                    <td>tup_id</td>
                    <td>full_name</td>
                    <td>Status</td>
                </tr>
                <tr>
                    <td>tup_id</td>
                    <td>full_name</td>
                    <td>Status</td>
                </tr>
                <tr>
                    <td>tup_id</td>
                    <td>full_name</td>
                    <td>Status</td>
                </tr>
            </tbody>
        </table>
    </div>
@endsection

@push('js')
    <!-- Page SPECIFIC js -->
    <script src="{{ asset('js/head/dashboard/page-specific/apexcharts.min.js') }}"></script>
    <script src="{{ asset('js/head/dashboard/page-specific/dash_1.js') }}"></script>
    <script src="{{ asset('js/head/dashboard/page-specific/datatables.js') }}"></script>
    <script>
        $('#zero-config').DataTable({
            "dom": "<'dt--top-section'<'row'<'col-12 col-sm-6 d-flex justify-content-sm-start justify-content-center'<'assigned-title'>><'col-12 col-sm-6 d-flex justify-content-sm-end justify-content-center mt-sm-0 mt-3'f>>>" +
                "<'table-responsive'tr>" +
                "<'dt--bottom-section d-sm-flex justify-content-sm-between text-center'<'dt--pages-count  mb-sm-0 mb-3'i><'dt--pagination'p>>",
            "oLanguage": {
                "oPaginate": {
                    "sPrevious": '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-arrow-left"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>',
                    "sNext": '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-arrow-right"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>'
                },
                "sInfo": "Showing page _PAGE_ of _PAGES_",
                "sSearch": '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-search"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>',
                "sSearchPlaceholder": "Search...",
            },
            "stripeClasses": [],
            "lengthMenu": [5, 10, 20, 50],
            "pageLength": 5,
            "initComplete": function() {
                $('.assigned-title').html('<h5 class="fw-bold mb-0 red-text-2">Assigned</h5>');
            }
        });
    </script>

    <!-- CUSTOM js -->
@endpush
