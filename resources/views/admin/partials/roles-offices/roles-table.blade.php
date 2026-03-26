{{-- Department filter: rendered in HTML, moved into DataTables toolbar via JS --}}
<div id="dept-filter-container" class="d-flex align-items-center gap-2" style="display:none!important;">
    <label class="mb-0" style="white-space:nowrap;font-size:.875rem;">Filter by Department</label>
    <select id="department-filter" class="form-select form-select-sm w-auto">
        <option value="">All Departments</option>
        @foreach($departments as $department)
            <option value="{{ $department->dep_name }}">{{ $department->dep_name }}</option>
        @endforeach
    </select>
</div>

{{-- Button States --}}
<div id="btn-state-container" style="display:none!important;">
    {{-- Original Edit Button --}}
    <div id="edit-mode-btns">
        <button id="btn-edit-main" class="btn btn-primary d-flex align-items-center">
            <span>Edit</span>
        </button>
    </div>

    {{-- Active Management Buttons --}}
    <div id="manage-mode-btns" class="d-none animate__animated animate__fadeIn">
        <div class="d-flex align-items-center gap-2">
            <button id="btn-add-row" class="btn btn-success p-2" title="Add New Role">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-plus"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
            </button>
            <button id="btn-save-all" class="btn btn-info p-2" title="Save Changes">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-check"><polyline points="20 6 9 17 4 12"></polyline></svg>
            </button>
            <button id="btn-cancel-edit" class="btn btn-danger p-2" title="Cancel">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-x"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
            </button>
        </div>
    </div>
</div>

{{-- Template for New Row (Appended via JS) --}}
<template id="new-role-row-template">
    <tr class="new-row-pending animate__animated animate__fadeInDown">
        <td>
            <input type="text" class="form-control form-control-sm input-role-name" placeholder="Enter Role Name (Optional for New Dept)">
        </td>
        <td>
            <div class="dept-selection-wrapper">
                {{-- Existing Dept Dropdown --}}
                <div class="existing-dept-selector">
                    <select class="form-select form-select-sm select-dept-existing">
                        <option value="" disabled selected>Select Department | Office</option>
                        <option value="NEW" class="fw-bold text-primary">Create New Department...</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->dep_id }}">{{ $dept->dep_name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- New Dept Interface (Hidden by default) --}}
                <div class="new-dept-interface d-none mt-1 p-2 border rounded bg-light">
                    <div class="d-flex align-items-center gap-2">
                        <div style="flex: 2;">
                            <input type="text" class="form-control form-control-sm input-new-dept-name" placeholder="New Dept Name">
                        </div>
                        <div style="flex: 1.5;">
                            <select class="form-select form-select-sm select-new-dept-type">
                                <option value="academic">Academic</option>
                                <option value="administrative">Administrative</option>
                            </select>
                        </div>
                        <div class="d-flex gap-1">

                            <button type="button" class="btn btn-sm btn-secondary btn-cancel-new-dept p-1 px-2" title="Cancel">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-rotate-ccw"><polyline points="1 4 1 10 7 10"></polyline><path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"></path></svg>
                            </button>
                        </div>
                    </div>
                </div>

            </div>
        </td>
    </tr>
</template>



<div style="margin-top: 20px;">
    <div class="col-xl-12 col-lg-12 col-sm-12 layout-spacing">
        <div class="widget-content widget-content-area br-8">
            <table id="roles-table" class="table dt-table-hover" style="width:100%">
                <thead>
                    <tr>
                        <th>Role Name</th>
                        <th>Department | Office</th>
                        <th class="d-none">Hidden Dept Group</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($roles ?? [] as $role)
                    <tr data-role-id="{{ $role->role_id }}" data-dep-id="{{ $role->dep_id }}" data-dep-type="{{ $role->dep_type }}">
                        <td>
                            <div class="d-flex justify-content-between align-items-center w-100 h-100">
                                @if($role->role_id)
                                    <div class="role-text-val text-dark editable-role-text transition-all flex-grow-1 p-2 rounded" data-role-id="{{ $role->role_id }}">{{ $role->role_name }}</div>
                                @else
                                    <div class="role-text-val text-muted fst-italic flex-grow-1 p-2 rounded">No Role Assigned</div>
                                @endif
                            </div>
                        </td>
                        <td>
                            <div class="d-flex justify-content-between align-items-center w-100 h-100">
                                <div class="dep-text-val editable-dept-text transition-all flex-grow-1 p-2 rounded" data-dep-id="{{ $role->dep_id }}">{{ $role->dep_name }}</div>
                                
                                <div class="px-2">
                                    @if($role->role_id)
                                        <div class="d-flex align-items-center">
                                            <button class="btn btn-outline-danger btn-sm p-1 inline-delete-btn d-none" data-role-id="{{ $role->role_id }}" title="Delete Role / Department">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-trash-2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>
                                            </button>
                                        </div>
                                    @else
                                        <div class="d-flex align-items-center">
                                            <button class="btn btn-outline-danger btn-sm p-1 inline-delete-dept-only-btn d-none" data-dep-id="{{ $role->dep_id }}" title="Delete Empty Department">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-trash-2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>
                                            </button>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="d-none">{{ $role->dep_name }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="text-center">No roles found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
