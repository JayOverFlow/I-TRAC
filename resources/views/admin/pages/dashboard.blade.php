{{-- Extend the main layout that you want to use --}}
@extends('admin.layout.admin-layout')

{{-- Define contents to show in the layout --}}
@section('title', 'Dashboard | I-TRAC')

@push('css')
    <!-- Page SPECIFIC css -->
    <link rel="stylesheet" href="{{ asset('css/admin/dashboard/page-specific/apexcharts.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin/dashboard/page-specific/dash_1.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin/dashboard/page-specific/datatables.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin/dashboard/page-specific/dt-global_style.css') }}">

    <!-- DARK MODE SPECIFIC css -->
    <link rel="stylesheet" href="{{ asset('css/admin/dashboard/page-specific/dark/dt-global_style.css') }}">

    <!-- SweetAlert2 css -->
    <link rel="stylesheet" href="{{ asset('plugins/src/sweetalerts2/sweetalerts2.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/css/light/sweetalerts2/custom-sweetalert.css') }}">

    <!-- CUSTOM css -->
    <link rel="stylesheet" href="{{ asset('css/admin/dashboard/custom-dashboard.css') }}">

@endpush

@section('content')
    @include('admin.partials.dashboard.dashboard-cards')

    @include('admin.partials.dashboard.dashboard-table')
@endsection

@push('js')
    <!-- Page SPECIFIC js -->
    <script src="{{ asset('plugins/src/sweetalerts2/sweetalerts2.min.js') }}"></script>
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

                // Wire department filter to column search
                $(document).on('change', '#department-filter', function() {
                    table.column(4).search(this.value).draw();
                });
            }
        });
    </script>

    <!-- CUSTOM js -->
    <script>
        var $activeEditBtn = null;

        $(document).on('input', '#edit-tupid', function() {
            this.value = this.value.toUpperCase().slice(0, 20);
        });

        $(document).on('click', '.btn-edit', function(e) {
            e.preventDefault();
            var $btn = $(this);
            $activeEditBtn = $btn;
            
            // Extract details from button attributes
            var id = $btn.data('id');
            var tupId = $btn.data('tupid');
            var firstName = $btn.data('firstname');
            var middleName = $btn.data('middlename');
            var lastName = $btn.data('lastname');
            var suffix = $btn.data('suffix');
            var contactNo = $btn.data('contactno');
            var email = $btn.data('email');
            var profilePhoto = $btn.data('profile-photo');
            
            // Handle lists parsed from json
            var roles = [];
            var departments = [];
            try {
                roles = typeof $btn.data('roles') === 'object' ? $btn.data('roles') : JSON.parse($btn.data('roles'));
            } catch (err) { roles = []; }
            
            try {
                departments = typeof $btn.data('departments') === 'object' ? $btn.data('departments') : JSON.parse($btn.data('departments'));
            } catch (err) { departments = []; }
            
            // Build full name
            var fullNameParts = [firstName, middleName, lastName];
            if (suffix) fullNameParts.push(suffix);
            var fullName = fullNameParts.filter(Boolean).join(' ');
            
            // Populating left static panel
            $('#modal-avatar').attr('src', profilePhoto);
            $('#modal-fullname').text(fullName);
            $('#modal-tupid-badge').text('TUPT-ID: ' + tupId);
            
            // Populating Roles
            var $rolesList = $('#modal-roles-list').empty();
            if (roles.length > 0) {
                roles.forEach(function(role) {
                    $rolesList.append($('<span class="text-dark font-weight-bold" style="font-size: 0.85rem;"></span>').text(role));
                });
            } else {
                $rolesList.append($('<span class="text-muted" style="font-size: 0.85rem; font-style: italic;">Unassigned</span>'));
            }
            
            // Populating Departments
            var $deptsList = $('#modal-depts-list').empty();
            if (departments.length > 0) {
                departments.forEach(function(dept) {
                    $deptsList.append($('<span class="text-dark font-weight-bold" style="font-size: 0.85rem;"></span>').text(dept));
                });
            } else {
                $deptsList.append($('<span class="text-muted" style="font-size: 0.85rem; font-style: italic;">N/A</span>'));
            }
            
            // Populating inputs in General Profile Tab
            $('#edit-user-id').val(id);
            $('#edit-firstname').val(firstName);
            $('#edit-middlename').val(middleName || '');
            $('#edit-lastname').val(lastName);
            $('#edit-suffix').val(suffix || '');
            $('#edit-tupid').val(tupId);
            $('#edit-contactno').val(contactNo || '');
            
            // Clear new password input, reset check action states
            $('#edit-new-password').val('');
            $('#btn-submit-password').hide();
            $('#btn-toggle-password').css('border-radius', '0 6px 6px 0');
            $('#modal-main-actions').attr('style', 'display: flex !important;');
            
            // Reset to first tab (safe for both BS4 and BS5)
            var $firstTab = $('#editUserTabs button:first');
            if ($firstTab.length > 0) {
                if (typeof bootstrap !== 'undefined' && bootstrap.Tab) {
                    var tab = new bootstrap.Tab($firstTab[0]);
                    tab.show();
                } else if ($.fn.tab) {
                    $firstTab.tab('show');
                } else {
                    $('#editUserTabs button').removeClass('active').attr('aria-selected', 'false');
                    $firstTab.addClass('active').attr('aria-selected', 'true');
                    $('.tab-content .tab-pane').removeClass('show active');
                    $($firstTab.attr('data-bs-target') || $firstTab.attr('data-target')).addClass('show active');
                }
            }
            
            // Show modal
            $('#editUserModal').modal('show');
        });
        
        function handleTabChange(targetId) {
            var $mainActions = $('#modal-main-actions');
            if (targetId === 'security-tab') {
                $mainActions.attr('style', 'display: none !important;');
            } else {
                $mainActions.attr('style', 'display: flex !important;');
            }
        }

        // Direct tab click listener for bulletproof reliability
        $(document).on('click', '#editUserTabs button', function () {
            handleTabChange($(this).attr('id'));
        });

        // Bootstrap transition event fallback listener
        $(document).on('shown.bs.tab', '#editUserTabs button', function (e) {
            handleTabChange($(e.target).attr('id'));
        });
        
        // Observe typing inside the password input to reveal check button
        $(document).on('input', '#edit-new-password', function() {
            var val = $(this).val().trim();
            var $submitBtn = $('#btn-submit-password');
            var $toggleBtn = $('#btn-toggle-password');
            
            // Clear any validation errors immediately as they type
            $(this).removeClass('is-invalid');
            $('#edit-new-password-feedback').hide();
            
            if (val.length > 0) {
                $submitBtn.show();
                $toggleBtn.css('border-radius', '0');
            } else {
                $submitBtn.hide();
                $toggleBtn.css('border-radius', '0 6px 6px 0');
            }
        });
        
        // Eye button toggle new password field visibility
        $(document).on('click', '#btn-toggle-password', function(e) {
            e.preventDefault();
            var $input = $('#edit-new-password');
            var isPassword = $input.attr('type') === 'password';
            $input.attr('type', isPassword ? 'text' : 'password');
            
            var $svg = $(this).find('svg');
            if (isPassword) {
                // Change to eye-off icon
                $svg.html('<path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line>');
            } else {
                // Change to normal eye icon
                $svg.html('<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle>');
            }
        });
        
        // Show Deletion Confirmation Modal
        $(document).on('click', '#btn-delete-user', function(e) {
            e.preventDefault();
            var userId = $('#edit-user-id').val();
            var userName = $('#modal-fullname').text();
            
            $('#delete-user-id').val(userId);
            $('#delete-user-name').text(userName);
            $('#admin-verify-password').val('');
            
            $('#deleteConfirmModal').modal('show');
        });
        
        // Helper function to update the user record details directly on the table row DOM in real-time
        function updateTableRowDOM($btn, firstname, middlename, lastname, suffix, tupid, contactno) {
            if (!$btn || $btn.length === 0) return;
            
            // 1. Update data attributes on edit element
            $btn.attr('data-firstname', firstname);
            $btn.attr('data-middlename', middlename || '');
            $btn.attr('data-lastname', lastname);
            $btn.attr('data-suffix', suffix || '');
            $btn.attr('data-tupid', tupid);
            $btn.attr('data-contactno', contactno || '');
            
            // 2. Build full name
            var nameParts = [firstname, middlename, lastname];
            if (suffix) nameParts.push(suffix);
            var fullName = nameParts.filter(Boolean).join(' ');
            
            // 3. Find parent row
            var $row = $btn.closest('tr');
            
            // 4. Update elements in Name column cells
            $row.find('h6.mb-0').text(fullName);
            $row.find('small.text-muted').text('TUPT-ID: ' + tupid);
            
            // 5. Update elements in Contact column cells
            var contactSpan = $row.find('td:nth-child(3) span[style*="vertical-align: middle"]');
            if (contactSpan.length > 0) {
                contactSpan.text(contactno || '—');
            }
            
            // 6. Update left static panel in the modal
            $('#modal-fullname').text(fullName);
            $('#modal-tupid-badge').text('TUPT-ID: ' + tupid);
        }

        // AJAX Submit General Profile Form
        $(document).on('submit', '#editUserForm', function(e) {
            e.preventDefault();
            
            var activeTab = $('#editUserTabs button.active').attr('id');
            if (activeTab === 'security-tab') {
                return;
            }
            
            var $form = $(this);
            var $submitBtn = $('#modal-main-actions button[type="submit"]');
            
            var firstnameVal = $('#edit-firstname').val();
            var middlenameVal = $('#edit-middlename').val();
            var lastnameVal = $('#edit-lastname').val();
            var suffixVal = $('#edit-suffix').val();
            var tupidVal = $('#edit-tupid').val();
            var contactnoVal = $('#edit-contactno').val();
            
            var payload = {
                _token: '{{ csrf_token() }}',
                user_id: $('#edit-user-id').val(),
                firstname: firstnameVal,
                middlename: middlenameVal,
                lastname: lastnameVal,
                suffix: suffixVal,
                tupid: tupidVal,
                contactno: contactnoVal
            };
            
            $submitBtn.prop('disabled', true).text('Saving...');
            
            $.ajax({
                url: '{{ route("admin.users.update") }}',
                type: 'POST',
                data: payload,
                success: function(response) {
                    $submitBtn.prop('disabled', false).text('Save Changes');
                    if (response.success) {
                        // Dynamically update the row on the DOM without closing the modal or reloading the page!
                        updateTableRowDOM($activeEditBtn, firstnameVal, middlenameVal, lastnameVal, suffixVal, tupidVal, contactnoVal);
                        
                        Swal.fire({
                            title: 'Updated!',
                            text: response.message,
                            icon: 'success',
                            confirmButtonColor: '#4361ee'
                        });
                    } else {
                        Swal.fire({
                            title: 'Error',
                            text: response.message || 'Failed to update profile.',
                            icon: 'error',
                            confirmButtonColor: '#4361ee'
                        });
                    }
                },
                error: function(xhr) {
                    $submitBtn.prop('disabled', false).text('Save Changes');
                    var errMsg = 'Failed to update profile. Please try again.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errMsg = xhr.responseJSON.message;
                    }
                    Swal.fire({
                        title: 'Error',
                        text: errMsg,
                        icon: 'error',
                        confirmButtonColor: '#4361ee'
                    });
                }
            });
        });
        
        // AJAX Submit Password Reset via Green Check Action
        $(document).on('click', '#btn-submit-password', function(e) {
            e.preventDefault();
            
            var password = $('#edit-new-password').val().trim();
            var userId = $('#edit-user-id').val();
            
            // Clear any old validation states
            $('#edit-new-password').removeClass('is-invalid');
            $('#edit-new-password-feedback').hide();
            
            if (password.length === 0) {
                $('#edit-new-password').addClass('is-invalid');
                $('#edit-new-password-feedback').text('Please enter a password before saving.').show();
                return;
            }
            
            if (password.length < 8 || password.length > 128 || !/^(?=.*[A-Za-z])(?=.*\d)/.test(password)) {
                $('#edit-new-password').addClass('is-invalid');
                $('#edit-new-password-feedback').text('Password must be between 8 and 128 characters long and contain at least one letter and one number.').show();
                return;
            }
            
            var $btn = $(this);
            $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>');
            
            $.ajax({
                url: '{{ route("admin.users.update-password") }}',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    user_id: userId,
                    new_password: password
                },
                success: function(response) {
                    $btn.prop('disabled', false).html('<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-check"><polyline points="20 6 9 17 4 12"></polyline></svg>');
                    if (response.success) {
                        $('#edit-new-password').val('');
                        $btn.hide();
                        $('#btn-toggle-password').css('border-radius', '0 6px 6px 0');
                        
                        Swal.fire({
                            title: 'Success',
                            text: response.message,
                            icon: 'success',
                            confirmButtonColor: '#4361ee'
                        });
                    } else {
                        $('#edit-new-password').addClass('is-invalid');
                        $('#edit-new-password-feedback').text(response.message || 'Failed to reset password.').show();
                    }
                },
                error: function(xhr) {
                    $btn.prop('disabled', false).html('<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-check"><polyline points="20 6 9 17 4 12"></polyline></svg>');
                    var errMsg = 'Failed to update password. Please try again.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errMsg = xhr.responseJSON.message;
                    }
                    $('#edit-new-password').addClass('is-invalid');
                    $('#edit-new-password-feedback').text(errMsg).show();
                }
            });
        });
        
        // AJAX Submit Admin Deletion Authorization
        $(document).on('submit', '#deleteConfirmForm', function(e) {
            e.preventDefault();
            
            var userId = $('#delete-user-id').val();
            var adminPassword = $('#admin-verify-password').val();
            
            var $form = $(this);
            var $submitBtn = $form.find('button[type="submit"]');
            
            $submitBtn.prop('disabled', true).text('Verifying...');
            
            $.ajax({
                url: '{{ route("admin.users.delete") }}',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    user_id: userId,
                    admin_password: adminPassword
                },
                success: function(response) {
                    $submitBtn.prop('disabled', false).text('Confirm & Delete');
                    if (response.success) {
                        $('#deleteConfirmModal').modal('hide');
                        $('#editUserModal').modal('hide');
                        
                        Swal.fire({
                            title: 'Deleted!',
                            text: response.message,
                            icon: 'success',
                            confirmButtonColor: '#4361ee'
                        }).then(function() {
                            window.location.reload();
                        });
                    } else {
                        Swal.fire({
                            title: 'Error',
                            text: response.message || 'Authorization failed.',
                            icon: 'error',
                            confirmButtonColor: '#4361ee'
                        });
                    }
                },
                error: function(xhr) {
                    $submitBtn.prop('disabled', false).text('Confirm & Delete');
                    var errMsg = 'Deletion failed. Incorrect password or server error.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errMsg = xhr.responseJSON.message;
                    }
                    Swal.fire({
                        title: 'Error',
                        text: errMsg,
                        icon: 'error',
                        confirmButtonColor: '#4361ee'
                    });
                }
            });
        });
    </script>
@endpush
