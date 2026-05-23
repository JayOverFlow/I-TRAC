{{-- Office filter: rendered in HTML, moved into DataTables toolbar via JS --}}
<div id="dept-filter-container" class="d-flex align-items-center gap-2" style="display:none!important;">
    <label class="mb-0" style="white-space:nowrap;font-size:.875rem;">Filter by Office</label>
    <select id="department-filter" class="form-select form-select-sm w-auto">
        <option value="">All Offices</option>
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
            <div class="dept-selection-wrapper">
                {{-- Existing Dept Dropdown --}}
                <div class="existing-dept-selector">
                    <select class="form-select form-select-sm select-dept-existing">
                        <option value="" disabled selected>Select Office</option>
                        <option value="NEW" class="fw-bold text-primary">Create New Office...</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->dep_id }}">{{ $dept->dep_name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- New Dept Interface (Hidden by default) --}}
                <div class="new-dept-interface d-none mt-1 p-2 rounded border bg-light">
                    <div class="mb-1">
                        <input type="text" class="form-control form-control-sm input-new-dept-name" placeholder="New Office Name">
                    </div>
                    <div class="d-flex align-items-center gap-2 w-100">
                        <div class="flex-grow-1">
                            <select class="form-select form-select-sm select-new-dept-parent">
                                <option value="" disabled selected>— Select Parent Office —</option>
                                @foreach($departments as $dept)
                                    @if(in_array((int)$dept->dep_id, [35, 36, 38, 40]))
                                        @php
                                            $displayName = $dept->dep_name;
                                            if ((int)$dept->dep_id === 36) {
                                                $displayName = 'Assistant Director for Academic Affairs';
                                            } elseif ((int)$dept->dep_id === 38) {
                                                $displayName = 'Assistant Director for Research & Extension';
                                            } elseif ((int)$dept->dep_id === 40) {
                                                $displayName = 'Assistant Director for Admin & Finance';
                                            }
                                        @endphp
                                        <option value="{{ $dept->dep_id }}">{{ $displayName }}</option>
                                    @endif
                                @endforeach
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
        <td>
            <input type="text" class="form-control form-control-sm input-role-name mb-1" placeholder="Enter Role Name (Optional for New Office)">
            <select class="form-select form-select-sm select-gen-role">
                <option value="Unassigned" selected>None</option>
                <option value="Head">Head</option>
                <option value="Procurement">Procurement</option>
                <option value="Supply">Supply</option>
            </select>
        </td>
    </tr>
</template>

<div style="margin-top: 20px;">
    <div class="col-xl-12 col-lg-12 col-sm-12 layout-spacing">
        <div class="widget-content widget-content-area br-8">
            <table id="roles-table" class="table dt-table-hover" style="width:100%; table-layout: fixed;">
                <thead>
                    <tr>
                        <th style="width: 50% !important;">Office</th>
                        <th style="width: 50% !important;">Role Name</th>
                        <th class="d-none">Hidden Dept Group</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($roles ?? [] as $role)
                    <tr data-role-id="{{ $role->role_id }}" data-dep-id="{{ $role->dep_id }}">
                        <td class="align-middle py-2">
                            <div class="dep-text-val editable-dept-text transition-all py-1 px-2 rounded" data-dep-id="{{ $role->dep_id }}" data-parent-dep-id="{{ $role->parent_dep_id }}">{{ $role->dep_name }}</div>
                        </td>
                        <td class="align-middle">
                            <div class="d-flex justify-content-between align-items-center w-100 h-100">
                                @if($role->role_id)
                                    <div class="role-text-val editable-role-text transition-all flex-grow-1 py-1 px-2 rounded" data-role-id="{{ $role->role_id }}" data-gen-role="{{ $role->gen_role }}">{{ $role->role_name }}</div>
                                    
                                    <div class="px-2">
                                        <button class="btn btn-outline-danger btn-sm p-1 inline-delete-btn d-none" data-role-id="{{ $role->role_id }}" title="Delete Role / Office">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-trash-2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>
                                        </button>
                                    </div>
                                @else
                                    <div class="role-text-val flex-grow-1 py-1 px-2 rounded no-role-assigned-wrapper d-flex justify-content-between align-items-center w-100">
                                        <span class="static-no-role-text text-muted fst-italic">No Role Assigned</span>
                                        <a href="javascript:void(0);" class="btn-inline-add-role d-none text-primary fst-italic" style="text-decoration: underline;">Add a Role</a>
                                    </div>
                                    <div class="inline-role-add-container d-none flex-grow-1 d-flex align-items-center gap-2 px-2">
                                        <input type="text" class="form-control form-control-sm inline-role-input" placeholder="Enter Role Name">
                                        <button type="button" class="btn btn-sm btn-cancel-inline-role p-1 border-0 shadow-none bg-transparent" title="Cancel">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-x text-danger"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                                        </button>
                                    </div>
                                    
                                    <div class="px-2">
                                        <button class="btn btn-outline-danger btn-sm p-1 inline-delete-dept-only-btn d-none" data-dep-id="{{ $role->dep_id }}" title="Delete Empty Office">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-trash-2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>
                                        </button>
                                    </div>
                                @endif
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
