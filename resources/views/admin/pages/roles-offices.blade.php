{{-- Extend the main layout that you want to use --}}
@extends('admin.layout.admin-layout')

{{-- Define contents to show in the layout --}}
@section('title', 'Roles and Offices | I-TRAC')

@push('css')
    <!-- Page SPECIFIC css -->
    <link rel="stylesheet" href="{{ asset('css/admin/dashboard/page-specific/apexcharts.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin/dashboard/page-specific/dash_1.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin/dashboard/page-specific/datatables.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin/dashboard/page-specific/dt-global_style.css') }}">
    <style>
        /* Highlight clickable text during edit mode */
        #roles-table.table-edit-mode .editable-role-text:hover,
        #roles-table.table-edit-mode .editable-dept-text:hover {
            cursor: pointer;
            background-color: #f1f3f5;
        }

        /* Fix: prevent overflow clipping that forces dropdowns to open upward */
        .dt--top-section,
        .dt--top-section .row,
        .dt--top-section [class*="col-"],
        #dept-filter-container,
        #roles-table tbody td,
        #roles-table tbody tr,
        .dept-selection-wrapper,
        .existing-dept-selector {
            overflow: visible !important;
        }
    </style>

    <!-- CUSTOM css -->
    <link rel="stylesheet" href="{{ asset('css/admin/custom-dashboard.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/src/sweetalerts2/sweetalerts2.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/css/light/sweetalerts2/custom-sweetalert.css') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
@endpush

@section('content')
    @include('admin.partials.dashboard.dashboard-cards')

    @include('admin.partials.roles-offices.roles-table')

    {{-- Edit Role Modal --}}
    <div class="modal fade" id="editRoleModal" tabindex="-1" aria-labelledby="editRoleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content shadow-lg border-0 bg-white">
                <div class="modal-header border-bottom bg-light">
                    <h5 class="modal-title font-weight-bolder text-dark" id="editRoleModalLabel">Edit Role</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-dark">
                    <input type="hidden" id="edit-role-id">
                    <div class="mb-3">
                        <label class="form-label text-dark fw-bold">Role Name</label>
                        <input type="text" class="form-control" id="edit-role-name-input">
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-dark fw-bold">Assign to Department | Office</label>
                        <select class="form-select" id="edit-role-dept-select">
                            @foreach($departments as $dept)
                                <option value="{{ $dept->dep_id }}">{{ $dept->dep_name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer bg-light border-top">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="btn-update-role-save">Save changes</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Edit Department Modal --}}
    <div class="modal fade" id="editDeptModal" tabindex="-1" aria-labelledby="editDeptModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content shadow-lg border-0 bg-white">
                <div class="modal-header border-bottom bg-light">
                    <h5 class="modal-title font-weight-bolder text-dark" id="editDeptModalLabel">Edit Department</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-dark">
                    <input type="hidden" id="edit-dept-id">
                    <div class="mb-3">
                        <label class="form-label text-dark fw-bold">Department Name</label>
                        <input type="text" class="form-control text-dark" id="edit-dept-name-input">
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-dark fw-bold">Type</label>
                        <select class="form-select text-dark" id="edit-dept-type-select">
                            <option value="academic">Academic</option>
                            <option value="administrative">Administrative</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer bg-light border-top">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="btn-update-dept-save">Save changes</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('js')
    <!-- Page SPECIFIC js -->
    <script src="{{ asset('js/admin/dashboard/page-specific/apexcharts.min.js') }}" ></script>
    <script src="{{ asset('js/admin/dashboard/page-specific/dash_1.js') }}" ></script>
    <script src="{{ asset('js/admin/dashboard/page-specific/datatables.js') }}" ></script>
    <script src="{{ asset('plugins/src/sweetalerts2/sweetalerts2.min.js') }}"></script>
    <script>
        $('#roles-table').DataTable({
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

                // Global state for Edit Mode
                var isEditMode = false;

                function toggleEditMode(enable) {
                    isEditMode = enable;
                    if (enable) {
                        $('#edit-mode-btns').addClass('d-none');
                        $('#manage-mode-btns').removeClass('d-none');
                        $('#dept-filter-container').hide(); // Instant hide
                        $('#roles-table').addClass('table-edit-mode');
                        $('.inline-delete-btn, .inline-delete-dept-only-btn').removeClass('d-none');
                    } else {
                        $('#manage-mode-btns').addClass('d-none');
                        $('#edit-mode-btns').removeClass('d-none');
                        $('#dept-filter-container').show(); // Instant show
                        $('#roles-table').removeClass('table-edit-mode');
                        $('.inline-delete-btn, .inline-delete-dept-only-btn').addClass('d-none');
                        $('.new-row-pending').remove(); // Clear pending rows on cancel
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

                // Add Row Functionality
                $(document).on('click', '#btn-add-row', function() {
                    // Get template content
                    var template = document.getElementById('new-role-row-template').content.cloneNode(true);
                    // Prepend to TBODY
                    $('#roles-table tbody').prepend(template);
                });

                // Create New Department Toggle
                $(document).on('change', '.select-dept-existing', function() {
                    var $row = $(this).closest('tr');
                    if ($(this).val() === 'NEW') {
                        $row.find('.existing-dept-selector').addClass('d-none');
                        $row.find('.new-dept-interface').removeClass('d-none');
                    }
                });

                // Cancel New Department creation
                $(document).on('click', '.btn-cancel-new-dept', function() {
                    var $row = $(this).closest('tr');
                    $row.find('.new-dept-interface').addClass('d-none');
                    $row.find('.existing-dept-selector').removeClass('d-none');
                    $row.find('.select-dept-existing').val(''); // Reset select
                });

                // Ensure buttons stay visible on table redraw (search/filter)
                // AND ensure pending rows stay at the top
                table.on('draw', function() {
                    if (isEditMode) {
                        $('#roles-table').addClass('table-edit-mode');
                        $('.inline-delete-btn, .inline-delete-dept-only-btn').removeClass('d-none');
                        
                        // If we have pending rows, move them back to the top of the current view
                        var $pending = $('.new-row-pending');
                        if ($pending.length > 0) {
                            $('#roles-table tbody').prepend($pending);
                        }
                    } else {
                        $('#roles-table').removeClass('table-edit-mode');
                        $('.inline-delete-btn, .inline-delete-dept-only-btn').addClass('d-none');
                    }
                });


                // Wire department filter to column search
                $(document).on('change', '#department-filter', function() {
                    table.column(1).search(this.value).draw(); // column 1 is Department
                });

                // Delete Role Logic
                $(document).on('click', '.inline-delete-btn', function() {
                    var roleId = $(this).data('role-id');
                    var $row = $(this).closest('tr');

                    Swal.fire({
                        title: 'Delete what exactly?',
                        text: "You can delete just this role, or delete both the role and its associated department.",
                        icon: 'warning',
                        showCancelButton: true,
                        showDenyButton: true,
                        confirmButtonColor: '#e7515a', // Red for Role Only
                        denyButtonColor: '#8a2be2',    // Purple for Both
                        cancelButtonColor: '#888ea8',  // Gray
                        confirmButtonText: 'Role Only',
                        denyButtonText: 'Role & Department',
                        cancelButtonText: 'Cancel'
                    }).then((result) => {
                        // User clicked 'Role Only'
                        if (result.isConfirmed) {
                            executeDelete(roleId, false, $row);
                        } 
                        // User clicked 'Role & Department'
                        else if (result.isDenied) {
                            executeDelete(roleId, true, $row);
                        }
                    });
                });

                function executeDelete(roleId, deleteDepartment, $row) {
                    $.ajax({
                        url: "/admin/roles-offices/" + roleId,
                        method: 'DELETE',
                        data: {
                            _token: $('meta[name="csrf-token"]').attr('content'),
                            delete_department: deleteDepartment
                        },
                        success: function(response) {
                            if(response.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Deleted!',
                                    text: deleteDepartment ? 'Role and Department removed.' : 'Role removed successfully.',
                                });

                                if (deleteDepartment) {
                                    // Remove the entire row
                                    table.row($row).remove().draw(false);
                                } else {
                                    // Make the row look like an empty department dynamically
                                    $row.attr('data-role-id', '');
                                    
                                    // Update Role Column Text
                                    var $roleCell = $row.find('td:first-child .d-flex');
                                    $roleCell.html('<span class="role-text-val fw-regular text-muted fst-italic">No Role Assigned</span>');

                                    // Update Button in Dept Column
                                    var $btnCell = $row.find('td:last-child .d-flex');
                                    var deptId = $row.data('dep-id');
                                    
                                    // Remove the old Role delete button
                                    $btnCell.find('.inline-delete-btn').remove();

                                    // Inject the generic empty department delete button
                                    var newBtn = `
                                        <button class="btn btn-outline-danger btn-sm p-1 inline-delete-dept-only-btn" data-dep-id="${deptId}" title="Delete Empty Department">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-x"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                                        </button>
                                    `;
                                    $btnCell.append(newBtn);

                                    // Let DataTables know the inner HTML changed
                                    table.row($row).invalidate().draw(false);
                                }
                            } else {
                                Swal.fire('Error', response.message, 'error');
                            }
                        },
                        error: function(xhr) {
                            var errMsg = 'Failed to delete data.';
                            if(xhr.responseJSON && xhr.responseJSON.message) {
                                errMsg = xhr.responseJSON.message;
                            }
                            Swal.fire('Error', errMsg, 'error');
                        }
                    });
                }

                // Edit Role UI Logic
                $(document).on('click', '.editable-role-text', function() {
                    if (!isEditMode) return; // Prevent clicks when not editing globally

                    var $row = $(this).closest('tr');
                    var roleId = $row.data('role-id');
                    if (!roleId) return; // Ignore "No Role Assigned" clicks

                    var roleName = $(this).text().trim();
                    var deptId = $row.data('dep-id');

                    $('#edit-role-id').val(roleId);
                    $('#edit-role-name-input').val(roleName);
                    $('#edit-role-dept-select').val(deptId);

                    var modal = new bootstrap.Modal(document.getElementById('editRoleModal'));
                    modal.show();
                });

                // Edit Department UI Logic
                $(document).on('click', '.editable-dept-text', function() {
                    if (!isEditMode) return; // Prevent clicks when not editing globally

                    var $row = $(this).closest('tr');
                    var deptId = $row.data('dep-id');
                    var deptName = $(this).text().trim();
                    var deptType = $row.data('dep-type');

                    $('#edit-dept-id').val(deptId);
                    $('#edit-dept-name-input').val(deptName);
                    // Default fallback if type is missing/null in DB
                    $('#edit-dept-type-select').val(deptType ? deptType.toLowerCase() : 'academic'); 

                    var modal = new bootstrap.Modal(document.getElementById('editDeptModal'));
                    modal.show();
                });

                // Role Update AJAX Execution
                $(document).on('click', '#btn-update-role-save', function() {
                    var roleId = $('#edit-role-id').val();
                    var roleName = $('#edit-role-name-input').val().trim();
                    var deptId = $('#edit-role-dept-select').val();
                    var deptName = $('#edit-role-dept-select option:selected').text();

                    if (!roleName) {
                        Swal.fire('Validation Error', 'Role Name cannot be empty.', 'error');
                        return;
                    }

                    var $btn = $(this);
                    $btn.prop('disabled', true).text('Updating...');

                    $.ajax({
                        url: '/admin/roles-offices/' + roleId,
                        type: 'PUT',
                        data: {
                            _token: $('meta[name="csrf-token"]').attr('content'),
                            role_name: roleName,
                            department_id: deptId
                        },
                        success: function(response) {
                            $btn.prop('disabled', false).text('Save changes');
                            $('#editRoleModal').modal('hide');
                            
                            // Dynamically update UI without refresh
                            var $row = $('tr[data-role-id="' + roleId + '"]');
                            $row.find('.role-text-val').text(roleName);
                            // Update Department linkages
                            $row.attr('data-dep-id', deptId);
                            $row.find('.dep-text-val').text(deptName);
                            $row.find('td').eq(2).text(deptName); // Hidden Column for DataTables group
                            
                            var table = $('#roles-table').DataTable();
                            table.row($row).invalidate().draw(false);

                            Swal.fire({
                                icon: 'success',
                                title: 'Updated!',
                                text: 'The role has been successfully modified.',
                                timer: 1500,
                                showConfirmButton: false
                            });
                        },
                        error: function(xhr) {
                            $btn.prop('disabled', false).text('Save changes');
                            var msg = xhr.responseJSON?.message || 'Failed to update role.';
                            Swal.fire('Error', msg, 'error');
                        }
                    });
                });

                // Department Update AJAX Execution
                $(document).on('click', '#btn-update-dept-save', function() {
                    var deptId = $('#edit-dept-id').val();
                    var deptName = $('#edit-dept-name-input').val().trim();
                    var deptType = $('#edit-dept-type-select').val();

                    if (!deptName) {
                        Swal.fire('Validation Error', 'Department Name cannot be empty.', 'error');
                        return;
                    }

                    var $btn = $(this);
                    $btn.prop('disabled', true).text('Updating...');

                    $.ajax({
                        url: '/admin/departments/' + deptId,
                        type: 'PUT',
                        data: {
                            _token: $('meta[name="csrf-token"]').attr('content'),
                            dep_name: deptName,
                            dep_type: deptType
                        },
                        success: function(response) {
                            $btn.prop('disabled', false).text('Save changes');
                            $('#editDeptModal').modal('hide');
                            
                            var table = $('#roles-table').DataTable();
                            
                            // Find EVERY row sharing this department ID to update global spelling
                            $('tr[data-dep-id="' + deptId + '"]').each(function() {
                                var $row = $(this);
                                $row.attr('data-dep-type', deptType);
                                $row.data('dep-type', deptType); // VERY IMPORTANT: Update jQuery's internal object cache
                                $row.find('.dep-text-val').text(deptName);
                                $row.find('td').eq(2).text(deptName); // Hidden group column
                                table.row($row).invalidate();
                            });
                            
                            // Ensure the <select> dropdowns in Add/Edit modes have updated text
                            $('.select-dept-existing option[value="' + deptId + '"]').text(deptName);
                            $('#edit-role-dept-select option[value="' + deptId + '"]').text(deptName);

                            table.draw(false);

                            Swal.fire({
                                icon: 'success',
                                title: 'Updated!',
                                text: 'The department details have been applied everywhere.',
                                timer: 1500,
                                showConfirmButton: false
                            });
                        },
                        error: function(xhr) {
                            $btn.prop('disabled', false).text('Save changes');
                            var msg = xhr.responseJSON?.message || 'Failed to update department.';
                            Swal.fire('Error', msg, 'error');
                        }
                    });
                });

                // Delete Department Only Logic (For empty departments)
                $(document).on('click', '.inline-delete-dept-only-btn', function() {
                    var deptId = $(this).data('dep-id');
                    var $row = $(this).closest('tr');

                    Swal.fire({
                        title: 'Delete this department?',
                        text: "This department has no roles. You won't be able to revert this!",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#e7515a',
                        cancelButtonColor: '#888ea8',
                        confirmButtonText: 'Yes, delete it!'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $.ajax({
                                url: "/admin/departments/" + deptId,
                                method: 'DELETE',
                                data: {
                                    _token: $('meta[name="csrf-token"]').attr('content')
                                },
                                success: function(response) {
                                    if(response.success) {
                                        Swal.fire({
                                            icon: 'success',
                                            title: 'Deleted!',
                                            text: 'The empty department has been removed.',
                                        });
                                        // Remove row
                                        table.row($row).remove().draw(false);
                                    } else {
                                        Swal.fire('Error', response.message, 'error');
                                    }
                                },
                                error: function(xhr) {
                                    var errMsg = 'Failed to delete department.';
                                    if(xhr.responseJSON && xhr.responseJSON.message) {
                                        errMsg = xhr.responseJSON.message;
                                    }
                                    Swal.fire('Error', errMsg, 'error');
                                }
                            });
                        }
                    });
                });

                // Save Added Roles Logic
                $(document).on('click', '#btn-save-all', function() {
                    var newRoles = [];
                    var hasErrors = false;
                    var summaryList = [];
                    
                    // Iterate pending new rows inside the current tbody
                    $('#roles-table tbody tr.new-row-pending').each(function() {
                        var roleName = $(this).find('.input-role-name').val() || "";
                        var deptId = $(this).find('.select-dept-existing').val();
                        var newDeptName = "";
                        var newDeptType = "";
                        
                        // If they chose NEW department, fetch that name instead
                        if(deptId === 'NEW') {
                            newDeptName = $(this).find('.input-new-dept-name').val() || "";
                            newDeptType = $(this).find('.select-new-dept-type').val() || "";
                        }

                        var isEmptyRole = (roleName.trim() === '');
                        var isExistingDept = (deptId && deptId !== 'NEW');
                        var isCreatingDept = (deptId === 'NEW');
                        var isEmptyNewDept = (isCreatingDept && newDeptName.trim() === '');

                        // Error block: No role name and no new department (Did nothing)
                        if (isEmptyRole && isExistingDept) {
                            Swal.fire('Validation Error', 'You must specify a Role Name when assigning to an existing department.', 'error');
                            hasErrors = true;
                            return false; // Break $.each loop
                        }

                        // Error block: Selected New Dept but wrote no name
                        if (isEmptyNewDept) {
                            Swal.fire('Validation Error', 'You must specify a New Department Name.', 'error');
                            hasErrors = true;
                            return false;
                        }

                        // Error block: Nothing selected in dropdown
                        if (!deptId) {
                            Swal.fire('Validation Error', 'You must select a Department or choose to Create New.', 'error');
                            hasErrors = true;
                            return false;
                        }

                        // Build Summary String
                        if (isCreatingDept && !isEmptyRole) {
                            summaryList.push(`<li style="margin-bottom: 5px;"><b>Role:</b> ${roleName.trim()} <br><small class="text-muted">↳ in New Department: ${newDeptName.trim()}</small></li>`);
                        } else if (isCreatingDept && isEmptyRole) {
                            summaryList.push(`<li style="margin-bottom: 5px;"><b>New Department Only:</b> ${newDeptName.trim()}</li>`);
                        } else if (isExistingDept && !isEmptyRole) {
                            var existingDeptName = $(this).find('.select-dept-existing option:selected').text();
                            summaryList.push(`<li style="margin-bottom: 5px;"><b>Role:</b> ${roleName.trim()} <br><small class="text-muted">↳ in: ${existingDeptName}</small></li>`);
                        }

                        // Push to payload. Allows empty role name ONLY if creating a new dept.
                        newRoles.push({
                            role_name: roleName.trim(),
                            department_id: deptId,
                            new_department_name: newDeptName.trim(),
                            new_department_type: newDeptType
                        });
                    });

                    if(hasErrors) return; // Prevent Ajax call if validation failed

                    if(newRoles.length === 0) {
                        Swal.fire({icon: 'info', title: 'Nothing to save', text: 'No new rows have been added.'});
                        return;
                    }

                    // Dynamically build Review Modal based on what is being submitted
                    var modalTitle = 'Save Changes?';
                    var hasRoles = summaryList.some(text => text.includes('<b>Role:</b>'));
                    var hasDeptsOnly = summaryList.some(text => text.includes('<b>New Department Only:</b>'));

                    if (hasRoles && hasDeptsOnly) {
                        modalTitle = 'Save Roles & Departments?';
                    } else if (hasRoles) {
                        modalTitle = 'Save New Roles?';
                    } else if (hasDeptsOnly) {
                        modalTitle = 'Save New Departments?';
                    }

                    var modalHtml = `
                        <div style="max-height: 200px; overflow-y: auto; text-align: left; background: #f8f9fa; padding: 15px; border-radius: 5px; border: 1px solid #e0e6ed;">
                            <ul style="margin-bottom: 0; padding-left: 20px; font-size: 0.95em;">
                                ${summaryList.join('')}
                            </ul>
                        </div>
                        <p class="mt-3 text-muted" style="font-size: 0.85em;">Are you sure you want to proceed?</p>
                    `;

                    Swal.fire({
                        title: modalTitle,
                        html: modalHtml,
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonText: 'Yes, save changes!'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $.ajax({
                                url: "{{ route('admin.roles-offices.save') }}",
                                method: 'POST',
                                data: {
                                    _token: $('meta[name="csrf-token"]').attr('content'),
                                    new_roles: newRoles
                                },
                                success: function(response) {
                                    if(response.success) {
                                        Swal.fire({
                                            icon: 'success',
                                            title: 'Success!',
                                            text: 'New roles have been created.',
                                        }).then(() => {
                                            window.location.reload();
                                        });
                                    } else {
                                        Swal.fire('Error', response.message, 'error');
                                    }
                                },
                                error: function(xhr) {
                                    var errMsg = 'Failed to save new roles.';
                                    if(xhr.responseJSON && xhr.responseJSON.message) {
                                        errMsg = xhr.responseJSON.message;
                                    }
                                    Swal.fire('Error', errMsg, 'error');
                                }
                            });
                        }
                    });
                });
            }
        });
    </script>




    <!-- CUSTOM js -->
    
@endpush
