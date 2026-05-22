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

<div style="margin-top: 20px;">
    <div class="col-xl-12 col-lg-12 col-sm-12 layout-spacing">
        <div class="widget-content widget-content-area br-8">
            <table id="zero-config" class="table table-bordered table-hover" style="width:100%">
                <thead>
                    <tr>
                        <th scope="col">Name</th>
                        <th scope="col">Role</th>
                        <th scope="col">Contact</th>
                        <th class="text-center" scope="col" style="width: 100px;">Action</th>
                        <th class="d-none" scope="col">Office</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users ?? [] as $user)
                    <tr>
                        <td class="align-middle">
                            <div class="d-flex align-items-center gap-3">
                                <div class="avatar me-2">
                                    <img alt="avatar" src="{{ $user->user_profile_photo ? asset($user->user_profile_photo) : asset('img/profiles/blank.avif') }}" class="rounded-circle" style="width: 42px; height: 42px; object-fit: cover;" />
                                </div>
                                <div class="media-body align-self-center">
                                    <h6 class="mb-0 font-weight-bold" style="color: var(--black-color, #3b3f5c);">
                                        {{ trim(implode(' ', array_filter([$user->user_firstname, $user->user_middlename, $user->user_lastname, $user->user_suffix]))) }}
                                    </h6>
                                    <small class="text-muted">TUPT-ID: {{ $user->user_tupid }}</small>
                                </div>
                            </div>
                        </td>
                        <td class="align-middle">
                            <div class="d-flex flex-column">
                                @forelse($user->roles as $role)
                                    <span class="text-dark" style="font-size: 0.875rem;">{{ $role }}</span>
                                @empty
                                    <span class="text-muted" style="font-size: 0.875rem;">Unassigned</span>
                                @endforelse
                            </div>
                        </td>
                        <td class="align-middle">
                            <div class="d-flex flex-column">
                                <span class="text-dark font-weight-bold" style="font-size: 0.85rem;">{{ $user->user_email }}</span>
                                <span class="text-muted" style="font-size: 0.75rem;">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-phone me-1" style="vertical-align: middle;"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg>
                                    <span style="vertical-align: middle;">{{ $user->user_contactno ?? '—' }}</span>
                                </span>
                            </div>
                        </td>
                        <td class="text-center align-middle">
                            <div class="action-btns">
                                <a href="javascript:void(0);" class="action-btn btn-edit bs-tooltip" 
                                   data-toggle="tooltip" 
                                   data-placement="top" 
                                   title="Edit" 
                                   style="color: var(--primary-color, #4361ee); background: transparent; border: none; padding: 0; display: inline-block; transition: all 0.2s ease;"
                                   data-id="{{ $user->user_id }}"
                                   data-tupid="{{ $user->user_tupid }}"
                                   data-firstname="{{ $user->user_firstname }}"
                                   data-middlename="{{ $user->user_middlename }}"
                                   data-lastname="{{ $user->user_lastname }}"
                                   data-suffix="{{ $user->user_suffix }}"
                                   data-contactno="{{ $user->user_contactno }}"
                                   data-email="{{ $user->user_email }}"
                                   data-profile-photo="{{ $user->user_profile_photo ? asset($user->user_profile_photo) : asset('img/profiles/blank.avif') }}"
                                   data-roles="{{ json_encode($user->roles) }}"
                                   data-departments="{{ json_encode($user->departments) }}">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-edit-2"><path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"></path></svg>
                                </a>
                            </div>
                        </td>
                        <td class="d-none">
                            {{ implode(', ', $user->departments) ?: 'N/A' }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center">No records found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@include('admin.partials.dashboard.edit-user-modal')
