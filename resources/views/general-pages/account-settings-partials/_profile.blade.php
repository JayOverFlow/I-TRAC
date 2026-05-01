{{-- 
    PROFILE TAB PARTIAL
    Phase 2: Backend logic wired up.
    - Inputs have name attributes for JS collection
    - Avatar src is dynamic (falls back to placeholder)
    - Hidden file input for avatar upload
    - Password fields have proper name attributes
--}}

<div class="tab-pane fade show active" id="animated-underline-profile" role="tabpanel" aria-labelledby="animated-underline-profile-tab">
    
    <!-- Profile Container: Matches the placement of the Inbox section -->
    <div class="profile-container" id="profile-section">

        <!-- LEFT SIDEBAR: Matches the sidebar in the UI mockup -->
        <div class="profile-sidebar">
            <!-- Red header banner -->
            <div class="profile-sidebar-header"></div>

            <!-- Overlapping Profile Avatar with double border -->
            <div class="profile-avatar-wrapper">
                <img 
                    id="profile-avatar-img"
                    src="{{ $user->user_profile_photo ? asset($user->user_profile_photo) : asset('img/profiles/blank.avif') }}" 
                    data-blank="{{ asset('img/profiles/blank.avif') }}"
                    alt="Profile Photo"
                >
                
                <!-- Hidden file input for avatar upload -->
                <input type="file" id="avatar-file-input" accept="image/jpeg,image/png,image/webp" style="display:none;">

                <!-- Edit Mode Buttons for Avatar -->
                <div class="avatar-edit-buttons edit-mode-only">
                    <button class="btn btn-upload-photo" id="btn-upload-photo" type="button">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-upload"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="17 8 12 3 7 8"></polyline><line x1="12" y1="3" x2="12" y2="15"></line></svg>
                        Upload photo
                    </button>
                    <small class="text-muted d-block text-center mt-1 mb-2" style="font-size: 0.75rem;">Supported: JPEG, PNG, WebP.<br>Other types (e.g. HEIC) are not supported.</small>
                    <button class="btn btn-delete-photo" id="btn-delete-photo" type="button">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-trash-2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>
                        Delete photo
                    </button>
                </div>
            </div>

            <!-- Name and Role Information (Hidden in Edit Mode per mockup) -->
            <div class="profile-sidebar-info view-mode-only">
                <h4 id="sidebar-fullname">{{ $user->user_fullname }}</h4>
                <p>{{ $user->roles->first()->role_name ?? 'Unassigned Role' }}</p>
            </div>
        </div>

        <!-- RIGHT MAIN CONTENT: Matches the "General Information" card in the UI mockup -->
        <div class="profile-main-content">
            
            <!-- Header with Title and secondary Edit Button -->
            <div class="profile-main-header">
                <h3>General Information</h3>
                <button class="btn btn-edit-profile shadow-none view-mode-only" id="edit-profile-btn" type="button">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-edit-3"><path d="M12 20h9"></path><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path></svg>
                    Edit Profile
                </button>
            </div>

            <!-- 2-Column Info Grid -->
            <div class="info-sections">
                
                <!-- Column 1: Personal Information -->
                <div class="info-column">
                    <h5 class="info-section-title">Personal Information</h5>
                    
                    <div class="info-item">
                        <span class="info-label">First Name</span>
                        <span class="info-value view-mode-only" id="view-firstname">{{ $user->user_firstname }}</span>
                        <input type="text" id="input-firstname" name="user_firstname" class="form-control edit-mode-only" value="{{ $user->user_firstname }}" placeholder="First Name">
                    </div>

                    <div class="info-item">
                        <span class="info-label">Middle Name</span>
                        <span class="info-value view-mode-only" id="view-middlename">{{ $user->user_middlename ?? 'N/A' }}</span>
                        <input type="text" id="input-middlename" name="user_middlename" class="form-control edit-mode-only" value="{{ $user->user_middlename }}" placeholder="Middle Name">
                    </div>

                    <div class="info-item">
                        <span class="info-label">Last Name</span>
                        <span class="info-value view-mode-only" id="view-lastname">{{ $user->user_lastname }}</span>
                        <input type="text" id="input-lastname" name="user_lastname" class="form-control edit-mode-only" value="{{ $user->user_lastname }}" placeholder="Last Name">
                    </div>

                    <div class="info-item">
                        <span class="info-label">Suffix</span>
                        <span class="info-value view-mode-only" id="view-suffix">{{ $user->user_suffix ?? 'N/A' }}</span>
                        <input type="text" id="input-suffix" name="user_suffix" class="form-control edit-mode-only" value="{{ $user->user_suffix }}" placeholder="Suffix (e.g. Jr.)">
                    </div>

                    <!-- Contact No. (Edit Mode Only) -->
                    <div class="info-item edit-mode-only">
                        <span class="info-label">Contact No.</span>
                        <input type="text" id="input-contactno" name="user_contactno" class="form-control" value="{{ $user->user_contactno }}" placeholder="0999-999-9999">
                    </div>
                </div>

                <!-- Column 2: Account Setup -->
                <div class="info-column">
                    <h5 class="info-section-title">Account Setup</h5>

                    <div class="info-item">
                        <span class="info-label">TUP-ID</span>
                        <span class="info-value view-mode-only">{{ $user->user_tupid }}</span>
                        <input type="text" class="form-control edit-mode-only" value="{{ $user->user_tupid }}" placeholder="TUP-ID" readonly>
                    </div>

                    <div class="info-item">
                        <span class="info-label">TUP Email</span>
                        <span class="info-value view-mode-only">{{ $user->user_email }}</span>
                        <input type="email" class="form-control edit-mode-only" value="{{ $user->user_email }}" placeholder="TUP Email" readonly>
                    </div>

                    <div class="info-item">
                        <span class="info-label">User Type</span>
                        <span class="info-value view-mode-only">{{ $user->user_type }}</span>
                        <input type="text" class="form-control edit-mode-only" value="{{ $user->user_type }}" placeholder="User Type" readonly>
                    </div>

                    <div class="info-item">
                        <span class="info-label">Department/Office</span>
                        <span class="info-value view-mode-only">
                            @if($user->departments->isNotEmpty())
                                {{ $user->departments->pluck('dep_name')->implode(', ') }}
                            @else
                                N/A
                            @endif
                        </span>
                        <input type="text" class="form-control edit-mode-only" value="{{ $user->departments->pluck('dep_name')->implode(', ') }}" placeholder="Department" readonly>
                    </div>

                    <div class="info-item view-mode-only">
                        <span class="info-label">Password</span>
                        <div class="password-field">
                            <span class="info-value">••••••••••••••••</span>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Expanded Password Section for Edit Mode -->
            {{-- Row 1: Current Password (full width, left column only) --}}
            <div class="info-sections edit-mode-only" style="margin-top: 0;">
                <div class="info-column">
                    <h5 class="info-section-title">Password</h5>
                    <div class="info-item">
                        <span class="info-label">Current Password</span>
                        <div class="password-field">
                            <input type="password" id="input-current-password" name="current_password" class="form-control" placeholder="••••••••••••••••" autocomplete="current-password">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-eye-off password-toggle-icon"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line></svg>
                        </div>
                    </div>
                </div>
                <div class="info-column" style="margin-top: 48px;">
                    {{-- Intentionally empty: aligns with the section title row --}}
                </div>
            </div>

            {{-- Row 2: New Password (left) | Confirm Password (right) --}}
            <div class="info-sections edit-mode-only" style="margin-top: 0;">
                <div class="info-column">
                    <div class="info-item">
                        <span class="info-label">New Password</span>
                        <div class="password-field">
                            <input type="password" id="input-new-password" name="new_password" class="form-control" placeholder="••••••••••••••••" autocomplete="new-password">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-eye-off password-toggle-icon"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line></svg>
                        </div>
                    </div>
                </div>
                <div class="info-column">
                    <div class="info-item">
                        <span class="info-label">Confirm Password</span>
                        <div class="password-field">
                            <input type="password" id="input-confirm-password" name="confirm_password" class="form-control" placeholder="••••••••••••••••" autocomplete="new-password">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-eye-off password-toggle-icon"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line></svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Edit Mode Footer Buttons -->
            <div class="profile-footer edit-mode-only">
                <button class="btn btn-go-back" id="go-back-btn" type="button">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-arrow-left"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
                    Go Back
                </button>
                <button class="btn btn-save-changes" id="btn-save-changes" type="button">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-check-circle"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                    Save Changes
                </button>
            </div>
        </div>

    </div>

</div>
