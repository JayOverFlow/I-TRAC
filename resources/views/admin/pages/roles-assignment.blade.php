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
    <link rel="stylesheet" href="{{ asset('plugins/src/sweetalerts2/sweetalerts2.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/css/light/sweetalerts2/custom-sweetalert.css') }}">
    
    <!-- DARK MODE SPECIFIC css -->
    <link rel="stylesheet" href="{{ asset('css/admin/dashboard/page-specific/dark/dt-global_style.css') }}">


    <!-- CUSTOM css -->
    <link rel="stylesheet" href="{{ asset('css/admin/dashboard/custom-dashboard.css') }}">

    <meta name="csrf-token" content="{{ csrf_token() }}">
@endpush

@section('content')
    @include('admin.partials.dashboard.dashboard-cards')

    {{-- Users View (Active by default) --}}
    <div id="users-view-container">
        @include('admin.partials.roles-assignment.users-table')
    </div>

    {{-- Roles View (Preserved logic) --}}
    <div id="roles-view-container" style="display: none;">
        @include('admin.partials.roles-assignment.roles-assignment-table')
    </div>
@endsection

@push('js')
    <!-- Page SPECIFIC js -->
    <script src="{{ asset('js/admin/dashboard/page-specific/apexcharts.min.js') }}" ></script>
    <script src="{{ asset('js/admin/dashboard/page-specific/dash_1.js') }}" ></script>
    <script src="{{ asset('js/admin/dashboard/page-specific/datatables.js') }}" ></script>
    <script src="{{ asset('plugins/src/sweetalerts2/sweetalerts2.min.js') }}"></script>
    <script>
        $(document).ready(function() {
            // --- USERS TABLE INITIALIZATION ---
            var usersTable = $('#users-table').DataTable({
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
                "order": [],
                "initComplete": function() {
                    var usersApi = this.api();

                    // Inject controls into left toolbar
                    var $leftCol = $('#users-view-container .dt--top-section .col-12.col-sm-6:first-child');
                    var $controls = $('#users-controls-container');
                    $controls.css('display', '');
                    $leftCol.append($controls);

                    // Inject Edit button into right toolbar
                    var $rightCol = $('#users-view-container .dt--top-section .col-12.col-sm-6:last-child');
                    var $btnContainer = $('#btn-state-container-users');
                    $btnContainer.css('display', '');
                    $rightCol.prepend($btnContainer);

                    // Department table filter
                    $(document).on('change', '#department-filter-users', function() {
                        usersTable.column(3).search(this.value).draw();
                    });

                    // ---- EDIT MODE LOGIC ----
                    function populateRoleDropdown($roleSelect, depId, selectedRoleId) {
                        $roleSelect.empty().append('<option value="">— Unassigned —</option>');
                        if (!depId) return;
                        $.each(allRolesData, function(i, role) {
                            if (role.role_dep_id_fk == depId) {
                                var selected = (role.role_id == selectedRoleId) ? 'selected' : '';
                                $roleSelect.append('<option value="' + role.role_id + '" ' + selected + '>' + role.role_name + '</option>');
                            }
                        });
                    }

                    function toggleUserEditMode(enable) {
                        if (enable) {
                            $('#edit-mode-btns-users').addClass('d-none');
                            $('#manage-mode-btns-users').removeClass('d-none');
                            $('#users-controls-container').hide();

                            usersApi.$('.readonly-data').addClass('d-none');
                            usersApi.$('.edit-data').removeClass('d-none');

                            // Pre-populate role dropdowns based on current department
                            usersApi.$('tr').each(function() {
                                var $deptSelect = $(this).find('.dept-assignment-select');
                                var $roleSelect = $(this).find('.role-assignment-select');
                                var originalDepId = $deptSelect.data('original-dep-id');
                                var originalRoleId = $roleSelect.data('original-role-id');
                                $deptSelect.val(originalDepId || '');
                                populateRoleDropdown($roleSelect, originalDepId, originalRoleId);
                            });
                        } else {
                            $('#manage-mode-btns-users').addClass('d-none');
                            $('#edit-mode-btns-users').removeClass('d-none');
                            $('#users-controls-container').show();

                            usersApi.$('.readonly-data').removeClass('d-none');
                            usersApi.$('.edit-data').addClass('d-none');
                        }
                    }

                    // Department change → repopulate roles
                    $(document).on('change', '.dept-assignment-select', function() {
                        var depId = $(this).val();
                        var $roleSelect = $(this).closest('tr').find('.role-assignment-select');
                        populateRoleDropdown($roleSelect, depId, null);
                    });

                    $(document).on('click', '#btn-edit-users', function() { toggleUserEditMode(true); });
                    $(document).on('click', '#btn-cancel-users', function() { toggleUserEditMode(false); });

                    // Save all user assignments
                    $(document).on('click', '#btn-save-users', function() {
                        Swal.fire({
                            title: 'Are you sure?',
                            text: 'Save all changed user assignments?',
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonText: 'Yes, save!'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                var assignments = [];
                                usersApi.$('tr').each(function() {
                                    var userId = $(this).data('user-id');
                                    var roleId = $(this).find('.role-assignment-select').val();
                                    if (userId !== undefined) {
                                        assignments.push({ user_id: userId, role_id: roleId || null });
                                    }
                                });

                                $.ajax({
                                    url: '{{ route("admin.roles-assignment.update-users") }}',
                                    method: 'POST',
                                    data: {
                                        _token: $('meta[name="csrf-token"]').attr('content'),
                                        assignments: assignments
                                    },
                                    success: function(response) {
                                        if (response.success) {
                                            Swal.fire('Updated!', 'User assignments saved.', 'success').then(() => window.location.reload());
                                        }
                                    },
                                    error: function() {
                                        Swal.fire('Error', 'Failed to save. Please try again.', 'error');
                                    }
                                });
                            }
                        });
                    });
                }
            });

            // --- ROLES TABLE INITIALIZATION (Original Logic) ---
            var rolesTable = $('#zero-config').DataTable({
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
                "order": [],
                "initComplete": function() {
                    var table = this.api();
                    var $leftCol = $('#roles-view-container .dt--top-section .col-12.col-sm-6:first-child');
                    var $controls = $('#roles-controls-container');
                    $controls.css('display', '');
                    $leftCol.append($controls);

                    var $rightCol = $('#roles-view-container .dt--top-section .col-12.col-sm-6:last-child');
                    var $btnContainer = $('#btn-state-container-roles');
                    $btnContainer.css('display', '');
                    $rightCol.prepend($btnContainer);

                    $(document).on('change', '#department-filter', function() {
                        table.column(4).search(this.value).draw();
                    });

                    // Edit Mode Logic
                    var isEditMode = false;
                    function toggleEditMode(enable) {
                        isEditMode = enable;
                        if (enable) {
                            $('#edit-mode-btns').addClass('d-none');
                            $('#manage-mode-btns').removeClass('d-none');
                            $('#roles-controls-container').hide();
                            table.$('.readonly-data').addClass('d-none');
                            table.$('.edit-data').removeClass('d-none');
                        } else {
                            $('#manage-mode-btns').addClass('d-none');
                            $('#edit-mode-btns').removeClass('d-none');
                            $('#roles-controls-container').show();
                            table.$('.readonly-data').removeClass('d-none');
                            table.$('.edit-data').addClass('d-none');
                            table.$('.user-assignment-select').each(function() {
                                $(this).val($(this).data('original-value'));
                            });
                        }
                    }

                    $(document).on('click', '#btn-edit-main', function() { toggleEditMode(true); });
                    $(document).on('click', '#btn-cancel-edit', function() { toggleEditMode(false); });

                    $(document).on('click', '#btn-save-all', function() {
                        Swal.fire({
                            title: 'Are you sure?',
                            text: "Save all changed assignments?",
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonText: 'Yes, save!'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                var assignments = [];
                                table.$('tr').each(function() {
                                    var roleId = $(this).data('role-id');
                                    var userId = $(this).find('.user-assignment-select').val();
                                    if (roleId) assignments.push({ role_id: roleId, user_id: userId || null });
                                });

                                $.ajax({
                                    url: "{{ route('admin.roles-assignment.update') }}",
                                    method: 'POST',
                                    data: { _token: $('meta[name="csrf-token"]').attr('content'), assignments: assignments },
                                    success: function(response) {
                                        if (response.success) {
                                            Swal.fire('Updated!', 'Assignments saved.', 'success').then(() => window.location.reload());
                                        }
                                    }
                                });
                            }
                        });
                    });
                }
            });

            // --- VIEW MODE SWITCHER LOGIC ---
            $(document).on('change', '.view-mode-toggle', function() {
                var mode = $(this).val();
                if (mode === 'users') {
                    $('#users-view-container').show();
                    $('#roles-view-container').hide();
                    // Sync dropdowns
                    $('.view-mode-toggle').val('users');
                    usersTable.columns.adjust().draw();
                } else {
                    $('#users-view-container').hide();
                    $('#roles-view-container').show();
                    // Sync dropdowns
                    $('.view-mode-toggle').val('roles');
                    rolesTable.columns.adjust().draw();
                }
            });
        });
    </script>

    <!-- CUSTOM js -->
    
@endpush
