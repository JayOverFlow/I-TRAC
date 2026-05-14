@extends('admin.layout.admin-layout')

@section('title', 'Admin Activity Logs | I-TRAC')

@push('css')
    <!-- Page SPECIFIC css -->
    <link rel="stylesheet" href="{{ asset('css/admin/dashboard/page-specific/datatables.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin/dashboard/page-specific/dt-global_style.css') }}">
    <!-- DARK MODE SPECIFIC css -->
    <link rel="stylesheet" href="{{ asset('css/admin/dashboard/page-specific/dark/dt-global_style.css') }}">

    <style>
        .log-action-badge {
            font-size: 10px;
            font-weight: 700;
            padding: 2px 8px;
            border-radius: 4px;
            text-transform: uppercase;
        }
        .badge-create { background-color: rgba(26, 188, 156, 0.1); color: #1abc9c; border: 1px solid #1abc9c; }
        .badge-update { background-color: rgba(255, 187, 0, 0.1); color: #ffbb00; border: 1px solid #ffbb00; }
        .badge-delete { background-color: rgba(231, 81, 90, 0.1); color: #e7515a; border: 1px solid #e7515a; }
        .badge-assign { background-color: rgba(67, 97, 238, 0.1); color: #4361ee; border: 1px solid #4361ee; }
        .badge-other { background-color: rgba(136, 142, 168, 0.1); color: #888ea8; border: 1px solid #888ea8; }

        /* Dark Mode Fixes */
        body.dark .filter-by-label {
            color: #A1A1AA !important;
        }
        body.dark .admin-name {
            font-weight: normal !important;
            color: #A1A1AA !important;
        }
        body.dark #action-filter {
            background-color: #15181D;
            border: 1px solid #1D2127;
            color: #F3F4F6;
        }
        body.dark #action-filter:focus {
            border-color: #B91C1C !important;
            color: #F3F4F6 !important;
            box-shadow: none !important;
        }
    </style>
@endpush

@section('content')
    {{-- Action Filter Container (Moved into DataTable via JS) --}}
    <div id="action-filter-container" style="display: none;">
        <div class="d-flex align-items-center">
            <label class="mb-0 me-2 filter-by-label">Filter by:</label>
            <select id="action-filter" class="form-select form-select-sm" style="width: 200px;">
                <option value="">All Actions</option>
                @php
                    $uniqueActions = $logs->pluck('log_action')->unique()->sort();
                @endphp
                @foreach($uniqueActions as $action)
                    @php $displayAction = str_replace('_', ' ', $action); @endphp
                    <option value="{{ $displayAction }}">{{ $displayAction }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="col-xl-12 col-lg-12 col-sm-12 layout-spacing">
        <div class="widget-content widget-content-area br-8">
            <h4 class="fw-bold mb-0 ps-4 pb-4 red-text-2">Activity Log</h4>
            <div class="table-responsive">
                <table id="activity-logs-table" class="table table-hover" style="width:100% !important;">
                    <thead>
                        <tr>
                            <th>Admin</th>
                            <th>Action</th>
                            <th>Summary</th>
                            <th>Details</th>
                            <th>Date & Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($logs as $log)
                            <tr>
                                <td>
                                    <p class="mb-0 admin-name">{{ $log->admin->admin_username ?? 'System' }}</p>
                                </td>
                                <td>{{ str_replace('_', ' ', $log->log_action) }}</td>
                                <td>{{ $log->log_short_description }}</td>
                                <td>{{ $log->log_full_description }}</td>
                                <td data-order="{{ $log->log_created_at }}">
                                    {{ \Carbon\Carbon::parse($log->log_created_at)->timezone('Asia/Manila')->format('M d, Y') }}
                                    <span style="margin-left: 30px;">{{ \Carbon\Carbon::parse($log->log_created_at)->timezone('Asia/Manila')->format('h:i A') }}</span>
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
    <script src="{{ asset('js/admin/dashboard/page-specific/datatables.js') }}"></script>
    <script>
        $(document).ready(function() {
            var table = $('#activity-logs-table').DataTable({
                "dom": "<'dt--top-section'<'row'<'col-12 col-sm-6 d-flex justify-content-sm-start justify-content-center'l><'col-12 col-sm-6 d-flex justify-content-sm-end justify-content-center mt-sm-0 mt-3'f>>>" +
                "<'table-responsive'tr>" +
                "<'dt--bottom-section d-sm-flex justify-content-sm-between text-center'<'dt--pages-count  mb-sm-0 mb-3'i><'dt--pagination'p>>",
                "oLanguage": {
                    "oPaginate": { "sPrevious": '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-arrow-left"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>', "sNext": '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-arrow-right"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>' },
                    "sInfo": "Showing page _PAGE_ of _PAGES_",
                    "sSearch": '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-search"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>',
                    "sSearchPlaceholder": "Search...",
                    "sLengthMenu": "_MENU_",
                },
                "stripeClasses": [],
                "lengthMenu": [10, 20, 50, 100],
                "pageLength": 10,
                "order": [[4, 'desc']],
                "initComplete": function() {
                    // Move Action Filter into the top toolbar
                    var $leftCol = $('.dt--top-section .col-12.col-sm-6:first-child');
                    $leftCol.html($('#action-filter-container').html());
                    
                    // Wire the filter using delegation on the container we just updated
                    $(document).on('change', '#action-filter', function() {
                        var val = $(this).val();
                        table.column(1).search(val ? '^' + val + '$' : '', true, false).draw();
                    });
                }
            });

            // Ensure table width is recalculated on window resize or drawer toggle
            $(window).on('resize', function() {
                table.columns.adjust();
            });
        });
    </script>
@endpush
