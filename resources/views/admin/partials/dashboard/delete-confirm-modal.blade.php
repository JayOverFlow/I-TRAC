<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteConfirmModal" tabindex="-1" role="dialog" aria-labelledby="deleteConfirmModalLabel" aria-hidden="true" style="z-index: 1060;">
    <div class="modal-dialog modal-dialog-centered" role="document" style="max-width: 450px;">
        <div class="modal-content border-0" style="border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.15);">
            <div class="modal-header border-0 pt-4 px-4 pb-0">
                <h5 class="modal-title font-weight-bold text-danger" id="deleteConfirmModalLabel">
                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="feather feather-alert-triangle me-2" style="vertical-align: middle;"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
                    <span style="vertical-align: middle;">Confirm Deletion</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <form id="deleteConfirmForm" autocomplete="off">
                @csrf
                <input type="hidden" id="delete-user-id" name="user_id" />
                
                <div class="modal-body px-4 py-3">
                    <p class="text-muted mb-3" style="font-size: 0.875rem; line-height: 1.5;">
                        Are you sure you want to permanently delete <strong id="delete-user-name" class="text-dark">this account</strong>? This action is irreversible, and all assigned roles will become vacant.
                    </p>
                    
                    <div class="p-3 bg-light rounded-3 mb-3" style="border: 1px solid #ebedf2;">
                        <h6 class="font-weight-bold text-dark mb-1" style="font-size: 0.8rem;">Administrator Authorization Required</h6>
                        <p class="text-muted mb-0" style="font-size: 0.725rem; line-height: 1.4;">
                            To authorize this deletion, please enter your administrator account password below to confirm your identity.
                        </p>
                    </div>
                    
                    <div class="form-group mb-0">
                        <label for="admin-verify-password" class="form-label font-weight-bold text-dark" style="font-size: 0.75rem;">Admin Password</label>
                        <input type="password" class="form-control" id="admin-verify-password" name="admin_password" placeholder="Enter admin password" required style="border-radius: 6px; font-size: 0.875rem; padding: 10px 12px;" />
                    </div>
                </div>
                
                <div class="modal-footer border-0 px-4 pb-4 pt-0 d-flex justify-content-end gap-2">
                    <button type="button" class="btn btn-outline-secondary font-weight-bold px-3 py-2" data-bs-dismiss="modal" style="font-size: 0.8rem; border-radius: 6px;">Cancel</button>
                    <button type="submit" class="btn btn-danger font-weight-bold px-3 py-2" style="font-size: 0.8rem; border-radius: 6px; background-color: #e7515a; border-color: #e7515a;">Confirm & Delete</button>
                </div>
            </form>
        </div>
    </div>
</div>
