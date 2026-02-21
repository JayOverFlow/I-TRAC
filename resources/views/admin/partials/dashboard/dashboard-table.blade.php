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

<div style="margin-top: 20px;">
    <div class="col-xl-12 col-lg-12 col-sm-12 layout-spacing">
        <div class="widget-content widget-content-area br-8">
            <table id="zero-config" class="table dt-table-hover" style="width:100%">
                <thead>
                    <tr>
                        <th>TUPT-ID</th>
                        <th>First Name</th>
                        <th>Last Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Department | Office</th>
                        <th>Type</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users ?? [] as $user)
                    <tr>
                        <td>{{ $user->user_tupid ?? '—' }}</td>
                        <td>{{ $user->user_firstname ?? '—' }}</td>
                        <td>{{ $user->user_lastname ?? '—' }}</td>
                        <td>{{ $user->user_email ?? '—' }}</td>
                        <td>{{ $user->role_name ?? 'Unassigned' }}</td>
                        <td>{{ $user->dep_name ?? 'N/A' }}</td>
                        <td>{{ $user->user_type ?? '—' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center">No records found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
