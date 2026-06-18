@php
    $allRoles = $user->roles;
    $activeRoleId = session('active_role_id') ?? ($allRoles->first()?->role_id ?? null);
    $activeRole = $allRoles->where('role_id', $activeRoleId)->first() ?? $allRoles->first();
    $userRoleGen = $activeRole?->gen_role ?? $user->user_type;

    // Dynamic Role-based border class (Head, Procurement, Supply vs Faculty/Staff)
    $avatarBorderClass = (in_array($userRoleGen, ['Head', 'Procurement', 'Supply']) || str_contains(strtolower($userRoleGen), 'head'))
        ? 'head-avatar'
        : 'faculty-avatar';
@endphp

<div class="tab-pane fade show active" id="pane-animated-underline-profile" role="tabpanel"
    aria-labelledby="animated-underline-profile-tab">

    <!-- Profile Container: Matches the placement of the Inbox section -->
    <div class="profile-container" id="profile-section">

        <!-- LEFT SIDEBAR: Matches the sidebar in the UI mockup -->
        <div class="profile-sidebar">
            <!-- Red header banner -->
            <div class="profile-sidebar-header"></div>

            <!-- Overlapping Profile Avatar with double border and overlay upload button -->
            <div class="profile-avatar-wrapper">
                <div class="avatar-image-container"
                    style="position: relative; display: block; width: 150px; height: 150px; margin: 0 auto;">
                    <img id="profile-avatar-img"
                        src="{{ $user->user_profile_photo ? asset($user->user_profile_photo) : asset('img/profiles/blank.avif') }}"
                        data-blank="{{ asset('img/profiles/blank.avif') }}" alt="Profile Photo"
                        class="{{ $avatarBorderClass }}"
                        style="width: 150px; height: 150px; border-radius: 50%; object-fit: cover;">

                    <!-- Circular overlay button for photo change (upload) -->
                    <button class="btn btn-avatar-overlay shadow-none" id="btn-avatar-overlay" type="button"
                        title="Change Photo">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                            stroke="#ffffff" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"
                            class="feather feather-upload">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                            <polyline points="17 8 12 3 7 8"></polyline>
                            <line x1="12" y1="3" x2="12" y2="15"></line>
                        </svg>
                    </button>
                </div>

                <!-- Hidden file input for avatar upload -->
                <input type="file" id="avatar-file-input" accept="image/jpeg,image/png,image/webp"
                    style="display:none;">
            </div>

            <!-- Name and Role Information (Always visible) -->
            <div class="profile-sidebar-info">
                <h4 id="sidebar-fullname">{{ $user->user_fullname }}</h4>
                <p class="sidebar-role">{{ $activeRole->role_name ?? $user->user_type }}</p>
            </div>
        </div>

        <!-- RIGHT MAIN CONTENT: Matches the "General Information" card in the UI mockup -->
        <div class="profile-main-content">

            <!-- Header with Title -->
            <div class="profile-main-header">
                <h3>General Information</h3>
            </div>

            <!-- 3-Column Info Grid -->
            <div class="info-sections">

                <!-- Column 1: Personal Information -->
                <div class="info-column">
                    <h5 class="info-section-title">Personal Information</h5>

                    <div class="info-item">
                        <span class="info-label">First Name</span>
                        <span class="info-value" id="view-firstname">{{ $user->user_firstname }}</span>
                    </div>

                    <div class="info-item">
                        <span class="info-label">Middle Name</span>
                        <span class="info-value" id="view-middlename">{{ $user->user_middlename ?? 'N/A' }}</span>
                    </div>

                    <div class="info-item">
                        <span class="info-label">Last Name</span>
                        <span class="info-value" id="view-lastname">{{ $user->user_lastname }}</span>
                    </div>

                    <div class="info-item">
                        <span class="info-label">Suffix</span>
                        <span class="info-value" id="view-suffix">{{ $user->user_suffix ?? 'N/A' }}</span>
                    </div>

                    <div class="info-item">
                        <span class="info-label">Contact No.</span>
                        <span class="info-value" id="view-contactno">{{ $user->user_contactno ?? 'N/A' }}</span>
                    </div>
                </div>

                <!-- Column 2: Account Setup -->
                <div class="info-column">
                    <h5 class="info-section-title">Account Setup</h5>

                    <div class="info-item">
                        <span class="info-label">TUPT-ID</span>
                        <span class="info-value">{{ $user->user_tupid }}</span>
                    </div>

                    <div class="info-item">
                        <span class="info-label">TUP Email</span>
                        <span class="info-value" style="word-break: break-all;">{{ $user->user_email }}</span>
                    </div>

                    <div class="info-item">
                        <span class="info-label">User Type</span>
                        <span class="info-value">{{ $user->user_type }}</span>
                    </div>

                    <div class="info-item">
                        <span class="info-label">Department/Office</span>
                        <span class="info-value">
                            @if($user->departments->isNotEmpty())
                                {{ $user->departments->pluck('dep_name')->implode(', ') }}
                            @else
                                N/A
                            @endif
                        </span>
                    </div>
                </div>

                <!-- Column 3: Password -->
                <div class="info-column">
                    <h5 class="info-section-title">Password</h5>

                    <!-- Static View Section -->
                    <div id="password-static-section" class="info-item">
                        <div class="password-field view-mode-password mb-3">
                            <span class="info-value">••••••••••••••••</span>
                        </div>
                        <button class="btn btn-change-password shadow-none" id="change-password-btn" type="button">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" class="feather feather-edit-3 mr-2">
                                <path d="M12 20h9"></path>
                                <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path>
                            </svg>
                            Change Password
                        </button>
                    </div>

                    <!-- Password Edit Section (hidden by default) -->
                    <div id="password-edit-section" style="display: none;">

                        <div class="info-item password-edit-item">
                            <span class="info-label">Current Password</span>
                            <div class="password-field">
                                <input type="password" id="input-current-password" name="current_password"
                                    class="form-control password-edit-input" placeholder="••••••••••••••••"
                                    autocomplete="current-password">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round" class="feather feather-eye-off password-toggle-icon">
                                    <path
                                        d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24">
                                    </path>
                                    <line x1="1" y1="1" x2="23" y2="23"></line>
                                </svg>
                            </div>
                        </div>

                        <div class="info-item password-edit-item">
                            <span class="info-label">Password</span>
                            <div class="password-field">
                                <input type="password" id="input-new-password" name="new_password"
                                    class="form-control password-edit-input" placeholder="••••••••••••••••"
                                    autocomplete="new-password">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round" class="feather feather-eye-off password-toggle-icon">
                                    <path
                                        d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24">
                                    </path>
                                    <line x1="1" y1="1" x2="23" y2="23"></line>
                                </svg>
                            </div>
                        </div>

                        <div class="info-item password-edit-item password-edit-item-last">
                            <span class="info-label">Confirm Password</span>
                            <div class="password-field">
                                <input type="password" id="input-confirm-password" name="confirm_password"
                                    class="form-control password-edit-input" placeholder="••••••••••••••••"
                                    autocomplete="new-password">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round" class="feather feather-eye-off password-toggle-icon">
                                    <path
                                        d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24">
                                    </path>
                                    <line x1="1" y1="1" x2="23" y2="23"></line>
                                </svg>
                            </div>
                        </div>

                        <div class="d-flex flex-column" style="width: 100%; gap: 6px;">
                            <button class="btn btn-save-password shadow-none" id="save-password-btn" type="button"
                                style="width: 100%;">
                                Save Changes
                            </button>
                            <button class="btn btn-cancel-password shadow-none" id="cancel-password-btn" type="button"
                                style="width: 100%;">
                                Cancel
                            </button>
                        </div>
                    </div>
                </div>

            </div>
        </div>

    </div>
</div>