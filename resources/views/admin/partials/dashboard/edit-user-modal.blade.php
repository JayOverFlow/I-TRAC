<!-- Edit User Modal -->
<div class="modal fade" id="editUserModal" tabindex="-1" role="dialog" aria-labelledby="editUserModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title font-weight-bold text-dark" id="editUserModalLabel">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-user-check me-2" style="vertical-align: middle;"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="8.5" cy="7" r="4"></circle><polyline points="17 11 19 13 23 9"></polyline></svg>
                    <span style="vertical-align: middle;">Manage User Account</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <form id="editUserForm" autocomplete="off">
                <div class="modal-body p-0">
                    <div class="row g-0">
                        <!-- Left Panel: Profile Context & Destructive Action -->
                        <div class="col-md-4 left-panel p-4 d-flex flex-column justify-content-between" style="background-color: #fafbfd; border-right: 1px solid #ebedf2; min-height: 480px;">
                            <div class="text-center">
                                <div class="position-relative d-inline-block mb-3">
                                    <img id="modal-avatar" alt="profile" src="{{ asset('img/profiles/blank.avif') }}" class="rounded-circle border" style="width: 110px; height: 110px; object-fit: cover; box-shadow: 0 4px 12px rgba(0,0,0,0.08);" />
                                </div>
                                <h5 id="modal-fullname" class="mb-1 font-weight-bold text-dark" style="font-size: 1.1rem; line-height: 1.3;">-</h5>
                                <div class="mb-3">
                                    <span id="modal-tupid-badge" class="badge badge-light-dark font-weight-bold" style="font-size: 0.75rem;">TUPT-ID: -</span>
                                </div>
                                
                                <hr class="my-3" style="border-color: #ebedf2;" />
                                
                                <!-- Read-Only Assignments -->
                                <div class="text-start">
                                    <div class="mb-3">
                                        <small class="text-muted text-uppercase font-weight-bold tracking-wider" style="font-size: 0.65rem;">Roles</small>
                                        <div id="modal-roles-list" class="mt-1 d-flex flex-column gap-1">
                                            <!-- Roles loaded dynamically -->
                                        </div>
                                    </div>
                                    
                                    <div>
                                        <small class="text-muted text-uppercase font-weight-bold tracking-wider" style="font-size: 0.65rem;">Office</small>
                                        <div id="modal-depts-list" class="mt-1 d-flex flex-column gap-1">
                                            <!-- Departments loaded dynamically -->
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Danger Zone (Aligned at Bottom, Borderless) -->
                            <div class="mt-auto pt-3 text-center">
                                <button type="button" id="btn-delete-user" class="btn btn-outline-danger btn-sm w-100" style="font-weight: 600; font-size: 0.75rem; letter-spacing: 0.3px; border-radius: 6px;">
                                    <span style="vertical-align: middle;">Delete Account</span>
                                </button>
                            </div>
                        </div>
                        
                        <!-- Right Panel: Tabs for Edit Forms (Flex layout to push buttons down) -->
                        <div class="col-md-8 p-4 d-flex flex-column justify-content-between" style="min-height: 480px;">
                            <div>
                                <!-- Tabs Navigation -->
                                <ul class="nav nav-tabs mb-4" id="editUserTabs" role="tablist" style="border-bottom: 2px solid #ebedf2;">
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link active font-weight-bold" id="general-tab" data-bs-toggle="tab" data-bs-target="#general-pane" type="button" role="tab" aria-controls="general-pane" aria-selected="true" style="border: none; border-bottom: 2px solid transparent; background: transparent; padding: 8px 16px; font-size: 0.9rem; color: #515365; transition: all 0.2s;">
                                            General Profile
                                        </button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link font-weight-bold" id="security-tab" data-bs-toggle="tab" data-bs-target="#security-pane" type="button" role="tab" aria-controls="security-pane" aria-selected="false" style="border: none; border-bottom: 2px solid transparent; background: transparent; padding: 8px 16px; font-size: 0.9rem; color: #515365; transition: all 0.2s;">
                                            Security Settings
                                        </button>
                                    </li>
                                </ul>
                                
                                <!-- Tabs Content -->
                                <div class="tab-content" id="editUserTabsContent">
                                    <!-- Tab 1: General Profile Form -->
                                    <div class="tab-pane fade show active" id="general-pane" role="tabpanel" aria-labelledby="general-tab">
                                        <input type="hidden" id="edit-user-id" name="user_id" />
                                        
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label for="edit-firstname" class="form-label font-weight-bold text-dark" style="font-size: 0.8rem;">First Name <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control" id="edit-firstname" name="firstname" placeholder="Enter first name" required style="border-radius: 6px; font-size: 0.875rem;" />
                                            </div>
                                            
                                            <div class="col-md-6">
                                                <label for="edit-middlename" class="form-label font-weight-bold text-dark" style="font-size: 0.8rem;">Middle Name</label>
                                                <input type="text" class="form-control" id="edit-middlename" name="middlename" placeholder="Enter middle name" style="border-radius: 6px; font-size: 0.875rem;" />
                                            </div>
                                            
                                            <div class="col-md-6">
                                                <label for="edit-lastname" class="form-label font-weight-bold text-dark" style="font-size: 0.8rem;">Last Name <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control" id="edit-lastname" name="lastname" placeholder="Enter last name" required style="border-radius: 6px; font-size: 0.875rem;" />
                                            </div>
                                            
                                            <div class="col-md-6">
                                                <label for="edit-suffix" class="form-label font-weight-bold text-dark" style="font-size: 0.8rem;">Suffix</label>
                                                <input type="text" class="form-control" id="edit-suffix" name="suffix" placeholder="e.g. Jr., III" style="border-radius: 6px; font-size: 0.875rem;" />
                                            </div>
                                            
                                            <div class="col-md-6">
                                                <label for="edit-tupid" class="form-label font-weight-bold text-dark" style="font-size: 0.8rem;">TUP-T ID</label>
                                                <input type="text" class="form-control" id="edit-tupid" name="tupid" placeholder="XXXXX-00-0000" required style="border-radius: 6px; font-size: 0.875rem;" />
                                            </div>
                                            
                                            <div class="col-md-6">
                                                <label for="edit-contactno" class="form-label font-weight-bold text-dark" style="font-size: 0.8rem;">Contact Number</label>
                                                <input type="text" class="form-control" id="edit-contactno" name="contactno" placeholder="Enter contact number" style="border-radius: 6px; font-size: 0.875rem;" />
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Tab 2: Security & Password Reset -->
                                    <div class="tab-pane fade" id="security-pane" role="tabpanel" aria-labelledby="security-tab">
                                        <div class="p-3 bg-light rounded-3 mb-4" style="border: 1px dashed #ced4da;">
                                            <h6 class="font-weight-bold text-dark mb-1" style="font-size: 0.85rem;">Administrative Password Reset</h6>
                                            <p class="text-muted mb-0" style="font-size: 0.75rem; line-height: 1.4;">
                                                Admins have the authority to change passwords directly because users do not have a self-service password recovery route. Set a new password below if required.
                                            </p>
                                        </div>
                                        
                                        <div class="row g-3">
                                            <div class="col-12">
                                                <label for="edit-new-password" class="form-label font-weight-bold text-dark" style="font-size: 0.8rem;">New Password</label>
                                                <div class="input-group">
                                                    <input type="password" class="form-control" id="edit-new-password" name="new_password" placeholder="Enter a secure new password" style="border-radius: 6px 0 0 6px; font-size: 0.875rem; border-right: none;" />
                                                    <button class="btn btn-outline-secondary" type="button" id="btn-toggle-password" style="border-radius: 0 6px 6px 0; border: 1px solid #ced4da; border-left: none; background-color: transparent;">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-eye"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                                                    </button>
                                                    <button class="btn btn-primary" type="submit" id="btn-submit-password" style="border-radius: 0 6px 6px 0; background-color: #00ab55; border-color: #00ab55; display: none; padding: 0 15px;">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-check"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                                    </button>
                                                </div>
                                                <div class="invalid-feedback" id="edit-new-password-feedback" style="display: none; color: #e7515a; font-size: 0.75rem; margin-top: 4px; font-weight: 600;">
                                                    Password must be between 8 and 128 characters long and contain at least one letter and one number.
                                                </div>
                                                <small class="text-muted mt-1 d-block" style="font-size: 0.7rem;">Leave blank if you do not wish to reset the password.</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Action Buttons Aligned with Left Panel's Delete Button (Borderless) -->
                            <div id="modal-main-actions" class="d-flex justify-content-end gap-2 mt-auto pt-3">
                                <button type="button" class="btn btn-outline-secondary font-weight-bold px-3 py-2" data-bs-dismiss="modal" style="font-size: 0.8rem; border-radius: 6px;">Cancel</button>
                                <button type="submit" class="btn btn-primary font-weight-bold px-3 py-2" style="font-size: 0.8rem; border-radius: 6px;">Save Changes</button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    #editUserModal .nav-tabs .nav-link.active {
        color: #4361ee !important;
        border-bottom: 2px solid #4361ee !important;
    }
    #editUserModal .nav-tabs .nav-link:hover {
        color: #4361ee;
    }
</style>

@include('admin.partials.dashboard.delete-confirm-modal')
