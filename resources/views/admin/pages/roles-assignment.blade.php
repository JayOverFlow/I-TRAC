{{-- Extend the main layout that you want to use --}}
@extends('admin.layout.admin-layout')

{{-- Define contents to show in the layout --}}
@section('title', 'Roles Assignment | I-TRAC')

@push('css')
    <!-- Page SPECIFIC css -->
    <link rel="stylesheet" href="{{ asset('css/admin/dashboard/page-specific/apexcharts.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin/dashboard/page-specific/dash_1.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin/dashboard/page-specific/datatables.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin/dashboard/page-specific/dt-global_style.css') }}">

    <!-- CUSTOM css -->
    <link rel="stylesheet" href="{{ asset('css/admin/custom-dashboard.css') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
@endpush

@section('content')
    @include('admin.partials.dashboard.dashboard-cards')

    @include('admin.partials.roles-assignment.roles-assignment-table')
@endsection

@push('js')
    <!-- Page SPECIFIC js -->
    <script src="{{ asset('js/admin/dashboard/page-specific/apexcharts.min.js') }}" ></script>
    <script src="{{ asset('js/admin/dashboard/page-specific/dash_1.js') }}" ></script>
    <script src="{{ asset('js/admin/dashboard/page-specific/datatables.js') }}" ></script>
    <script>
        $('#zero-config').DataTable({
            "dom": "<'dt--top-section'<'row'<'col-12 col-sm-6 d-flex justify-content-sm-start justify-content-center'><'col-12 col-sm-6 d-flex justify-content-sm-end justify-content-center mt-sm-0 mt-3'f>>>" +
            "<'table-responsive'tr>" +
            "<'dt--bottom-section d-sm-flex justify-content-sm-between text-center'<'dt--pages-count  mb-sm-0 mb-3'i><'dt--pagination'p>>",
            "oLanguage": {
                "oPaginate": { "sPrevious": '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-arrow-left"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>', "sNext": '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-arrow-right"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>' },
                "sInfo": "Showing page _PAGE_ of _PAGES_",
                "sSearch": '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-search"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>',
                "sSearchPlaceholder": "Search...",
               "sLengthMenu": "Results :  _MENU_",
            },
            "stripeClasses": [],
            "lengthMenu": [7, 10, 20, 50],
            "pageLength": 10,
            "initComplete": function() {
                var table = this.api();

                // Move the pre-rendered department filter into DataTables' left toolbar col
                var $leftCol = $('.dt--top-section .col-12.col-sm-6:first-child');
                var $filter = $('#dept-filter-container');
                $filter.css('display', '');   // unhide it
                $leftCol.append($filter);

                // Move Button State Container next to search bar
                var $rightCol = $('.dt--top-section .col-12.col-sm-6:last-child');
                var $btnContainer = $('#btn-state-container');
                $btnContainer.css('display', ''); // unhide it
                $rightCol.prepend($btnContainer); // place before search bar

                // Wire department filter to column search (Column index is 4 - starting from 0)
                $(document).on('change', '#department-filter', function() {
                    table.column(4).search(this.value).draw();
                });

                // Global state for Edit Mode
                var isEditMode = false;

                function toggleEditMode(enable) {
                    isEditMode = enable;
                    if (enable) {
                        $('#edit-mode-btns').addClass('d-none');
                        $('#manage-mode-btns').removeClass('d-none');
                        $('#dept-filter-container').hide(); // Hide filter in edit mode
                        
                        // Show dropdowns, hide readonly text
                        table.$('.readonly-data').addClass('d-none');
                        table.$('.edit-data').removeClass('d-none');
                    } else {
                        $('#manage-mode-btns').addClass('d-none');
                        $('#edit-mode-btns').removeClass('d-none');
                        $('#dept-filter-container').show(); // Restore filter

                        // Show readonly text, hide dropdowns
                        table.$('.readonly-data').removeClass('d-none');
                        table.$('.edit-data').addClass('d-none');

                        // Reset selection values to match original data
                        table.$('.user-assignment-select').each(function() {
                            $(this).val($(this).data('original-value'));
                        });
                    }
                }

                // Wire Edit button to switch modes
                $(document).on('click', '#btn-edit-main', function() {
                    toggleEditMode(true);
                });

                // Wire Cancel button to revert modes
                $(document).on('click', '#btn-cancel-edit', function() {
                    toggleEditMode(false);
                });

                // Save All Logic
                $(document).on('click', '#btn-save-all', function() {
                    if (!confirm("Are you sure you want to update these role assignments?")) {
                        return;
                    }

                    var assignments = [];
                    
                    // Iterate over all rows in the DataTables API (even those not on current page)
                    table.$('tr').each(function() {
                        var roleId = $(this).data('role-id');
                        var userId = $(this).find('.user-assignment-select').val();
                        
                        if (roleId) {
                            assignments.push({
                                role_id: roleId,
                                user_id: userId || null
                            });
                        }
                    });

                    // Fire AJAX to saving Route
                    $.ajax({
                        url: "{{ route('admin.roles-assignment.update') }}",
                        method: 'POST',
                        data: {
                            _token: $('meta[name="csrf-token"]').attr('content'),
                            assignments: assignments
                        },
                        success: function(response) {
                            if (response.success) {
                                // Reload page on success to redraw table freshly
                                window.location.reload();
                            } else {
                                alert("Something went wrong!");
                            }
                        },
                        error: function() {
                            alert("Failed to update assignments. Please try again.");
                        }
                    });
                });
            }
        });
    </script>

    <!-- CUSTOM js -->
    
@endpush
