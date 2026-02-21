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
            <input type="text" class="form-control form-control-sm" placeholder="Enter Role Name">
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
                            <button type="button" class="btn btn-sm btn-success btn-confirm-new-dept p-1 px-2" title="Create">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-check-circle"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                            </button>
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
                    </tr>
                </thead>
                <tbody>
                    @forelse($roles ?? [] as $role)
                    <tr>
                        <td>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="role-text-val fw-bold">{{ $role->role_name }}</span>
                                <button class="btn btn-outline-danger btn-sm p-1 inline-delete-btn d-none" title="Delete Role">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-x"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                                </button>
                            </div>
                        </td>
                        <td>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="dep-text-val">{{ $role->dep_name }}</span>
                                <button class="btn btn-outline-danger btn-sm p-1 inline-delete-btn d-none" title="Delete Association">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-x"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="2" class="text-center">No roles found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
