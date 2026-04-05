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
            <button id="btn-save-all" class="btn btn-info p-2" title="Save Changes">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-check"><polyline points="20 6 9 17 4 12"></polyline></svg>
            </button>
            <button id="btn-cancel-edit" class="btn btn-danger p-2" title="Cancel">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-x"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
            </button>
        </div>
    </div>
</div>

<div style="margin-top: 20px;">
    <div class="col-xl-12 col-lg-12 col-sm-12 layout-spacing">
        <div class="widget-content widget-content-area br-8" style="min-height: 500px;">
            <table id="zero-config" class="table dt-table-hover" style="width:100%">
                <thead>
                    <tr>
                        <th>Role</th>
                        <th>Last Name</th>
                        <th>First Name</th>
                        <th>Email</th>
                        {{-- Hidden column for department filter --}}
                        <th style="display: none;">Department</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($roles as $role)
                    <tr data-role-id="{{ $role->role_id }}">
                        <td>{{ $role->role_name ?? '—' }}</td>
                        
                        {{-- Editable User Columns --}}
                        <td class="position-relative align-middle py-2">
                            <span class="readonly-data">{{ $role->user_lastname ?? '—' }}</span>
                            
                            {{-- Editing State: User Dropdown occupies the space when editing --}}
                            <div class="edit-data d-none position-absolute" style="top:50%; transform:translateY(-50%); left:10px; z-index: 10; min-width: 300px;">
                                <select class="form-select form-select-sm user-assignment-select shadow-sm" data-role-id="{{ $role->role_id }}" data-original-value="{{ $role->user_id ?? '' }}" style="font-size: 0.85rem;">
                                    <option value="">-- Unassigned --</option>
                                    @foreach($allUsers as $user)
                                        <option value="{{ $user->user_id }}" {{ $role->user_id == $user->user_id ? 'selected' : '' }}>
                                            {{ $user->user_lastname }}, {{ $user->user_firstname }} {{ $user->user_suffix }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </td>
                        <td class="position-relative align-middle py-2">
                            <span class="readonly-data">{{ $role->user_firstname ?? '—' }}</span>
                        </td>
                        <td class="position-relative align-middle py-2">
                            <span class="readonly-data">{{ $role->user_email ?? '—' }}</span>
                        </td>

                        {{-- Hidden column data --}}
                        <td style="display: none;">{{ $role->dep_name ?? 'N/A' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center">No roles found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
