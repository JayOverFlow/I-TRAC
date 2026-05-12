{{-- Embed all roles as JSON for JS dependent dropdowns --}}
<script>
    var allRolesData = @json($allRoles);
</script>

{{-- Shared Controls for User View --}}
<div id="users-controls-container" class="d-flex align-items-center gap-3" style="display:none!important;">
    {{-- Department filter --}}
    <div class="d-flex align-items-center gap-2">
        <label class="mb-0" style="white-space:nowrap;font-size:.875rem;">Filter by Department</label>
        <select id="department-filter-users" class="form-select form-select-sm w-auto">
            <option value="">All Departments</option>
            @foreach($departments as $department)
                <option value="{{ $department->dep_name }}">{{ $department->dep_name }}</option>
            @endforeach
        </select>
    </div>

    {{-- View Mode Switcher --}}
    <div class="d-flex align-items-center gap-2">
        <label class="mb-0" style="white-space:nowrap;font-size:.875rem;">View Mode:</label>
        <select class="view-mode-toggle form-select form-select-sm" style="width: 130px; position: relative; z-index: 5;">
            <option value="users" selected>Users</option>
            <option value="roles">Roles</option>
        </select>
    </div>
</div>

{{-- Edit / Save / Cancel Buttons for User View --}}
<div id="btn-state-container-users" style="display:none!important;">
    {{-- Readonly mode: Edit button --}}
    <div id="edit-mode-btns-users">
        <button id="btn-edit-users" class="btn btn-primary d-flex align-items-center">
            <span>Edit</span>
        </button>
    </div>
    {{-- Edit mode: Save + Cancel buttons --}}
    <div id="manage-mode-btns-users" class="d-none animate__animated animate__fadeIn">
        <div class="d-flex align-items-center gap-2">
            <button id="btn-add-row-users" class="btn btn-success p-2" title="Add New Assignment">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-plus"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
            </button>
            <button id="btn-save-users" class="btn btn-info p-2" title="Save Changes">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-check"><polyline points="20 6 9 17 4 12"></polyline></svg>
            </button>
            <button id="btn-cancel-users" class="btn btn-danger p-2" title="Cancel">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-x"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
            </button>
        </div>
    </div>
</div>

{{-- Template for New Row (Appended via JS) --}}
<template id="new-user-row-template">
    <tr class="new-row-pending animate__animated animate__fadeInDown">
        <td colspan="2" class="position-relative align-middle py-2">
            {{-- Spacer to maintain row height --}}
            <div style="height: 32px; visibility: hidden;"></div>
            <div class="position-absolute table-edit-container" style="top:50%; transform:translateY(-50%); left:10px; z-index:10; width: calc(100% - 20px);">
                <select class="form-select form-select-sm select-user-new shadow-sm table-edit-select" style="width: 100%; font-size:0.85rem;">
                    <option value="" disabled selected>Select User</option>
                    @foreach($allUsers as $u)
                        <option value="{{ $u->user_id }}">{{ $u->user_lastname }}, {{ $u->user_firstname }}</option>
                    @endforeach
                </select>
            </div>
        </td>
        <td class="position-relative align-middle py-2">
            <div class="position-absolute table-edit-container" style="top:50%; transform:translateY(-50%); left:10px; z-index:10; width: calc(100% - 20px);">
                <select class="form-select form-select-sm role-assignment-select shadow-sm table-edit-select" disabled style="font-size:0.85rem;">
                    <option value="">— Select a Department First —</option>
                </select>
            </div>
        </td>
        <td class="position-relative align-middle py-2">
            <div class="position-absolute table-edit-container" style="top:50%; transform:translateY(-50%); left:10px; right:45px; z-index:10;">
                <select class="form-select form-select-sm dept-assignment-select shadow-sm table-edit-select" style="width: 100%; font-size:0.85rem;">
                    <option value="" disabled selected>Select Department</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept->dep_id }}">{{ $dept->dep_name }}</option>
                    @endforeach
                </select>
            </div>
            <button type="button" class="btn btn-outline-danger btn-sm p-1 btn-remove-new-row position-absolute" style="top:50%; transform:translateY(-50%); right:10px; z-index:10;" title="Remove Row">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-trash-2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>
            </button>
        </td>
    </tr>
</template>

<div style="margin-top: 20px;">
    <div class="col-xl-12 col-lg-12 col-sm-12 layout-spacing">
        <div class="widget-content widget-content-area br-8" style="min-height: 500px;">
            <table id="users-table" class="table dt-table-hover" style="width:100%">
                <thead>
                    <tr>
                        <th>First Name</th>
                        <th>Last Name</th>
                        <th>Role</th>
                        <th>Department</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                    <tr data-user-id="{{ $user->user_id }}" data-dep-id="{{ $user->dep_id }}">
                        <td class="align-middle py-2">{{ $user->user_firstname }}</td>
                        <td class="align-middle py-2">{{ $user->user_lastname }}</td>
                        
                        {{-- Role Column --}}
                        <td class="position-relative align-middle py-2">
                            <span class="readonly-data">{{ $user->role_name ?? '—' }}</span>
                            <div class="edit-data d-none position-absolute table-edit-container" style="top:50%; transform:translateY(-50%); left:10px; z-index:10; width: calc(100% - 20px);">
                                <select class="form-select form-select-sm role-assignment-select shadow-sm table-edit-select"
                                    data-user-id="{{ $user->user_id }}"
                                    data-original-role-id="{{ $user->role_id ?? '' }}"
                                    style="font-size:0.85rem;">
                                    <option value="">— Unassigned —</option>
                                </select>
                            </div>
                        </td>

                        {{-- Department Column --}}
                        <td class="position-relative align-middle py-2">
                            <span class="readonly-data">{{ $user->dep_name ?? '—' }}</span>
                            {{-- Edit state: Dropdown with strict bounds --}}
                            <div class="edit-data d-none position-absolute table-edit-container" style="top:50%; transform:translateY(-50%); left:10px; right:45px; z-index:10;">
                                <select class="form-select form-select-sm dept-assignment-select shadow-sm table-edit-select"
                                    data-original-dep-id="{{ $user->dep_id ?? '' }}"
                                    style="width: 100%; font-size:0.85rem;">
                                    <option value="">— No Department —</option>
                                    @foreach($departments as $dept)
                                        <option value="{{ $dept->dep_id }}" {{ $user->dep_id == $dept->dep_id ? 'selected' : '' }}>
                                            {{ $dept->dep_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center">No users found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
