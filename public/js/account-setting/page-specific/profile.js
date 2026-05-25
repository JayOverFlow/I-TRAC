/**
 * Profile Tab JS
 * Handles:
 *  - Avatar upload overlay triggers and AJAX uploading
 *  - Inline password edit form toggling (Change Password ↔ Cancel)
 *  - Inline password saving via AJAX and error mapping
 *  - Form visibility and error cleaning
 */

document.addEventListener('DOMContentLoaded', function () {

    // ──────────────────────────────────────────────
    // 1. CSRF Token (required for POST requests)
    // ──────────────────────────────────────────────
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    // ──────────────────────────────────────────────
    // 2. Element References
    // ──────────────────────────────────────────────
    const avatarOverlayBtn  = document.getElementById('btn-avatar-overlay');
    const avatarFileInput   = document.getElementById('avatar-file-input');
    const avatarImg         = document.getElementById('profile-avatar-img');

    const changePasswordBtn = document.getElementById('change-password-btn');
    const savePasswordBtn   = document.getElementById('save-password-btn');
    const cancelPasswordBtn = document.getElementById('cancel-password-btn');

    const staticSection     = document.getElementById('password-static-section');
    const editSection       = document.getElementById('password-edit-section');

    // ──────────────────────────────────────────────
    // 3. Avatar Upload Trigger
    // ──────────────────────────────────────────────
    if (avatarOverlayBtn && avatarFileInput) {
        avatarOverlayBtn.addEventListener('click', function () {
            avatarFileInput.click();
        });

        avatarFileInput.addEventListener('change', async function () {
            const file = this.files[0];
            if (!file) return;

            const formData = new FormData();
            formData.append('profile_photo', file);
            formData.append('_token', csrfToken);

            avatarOverlayBtn.disabled = true;
            const originalSvg = avatarOverlayBtn.innerHTML;
            avatarOverlayBtn.innerHTML = `
                <div class="spinner-border spinner-border-sm text-white" role="status" style="width: 12px; height: 12px; border-width: 2px;"></div>
            `;

            try {
                const res  = await fetch('/account-settings/update-avatar', {
                    method:  'POST',
                    headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                    body:    formData,
                });
                const data = await res.json();

                if (res.ok && data.success) {
                    if (avatarImg) avatarImg.src = data.photo_url;
                    
                    // Also update the main header nav user avatar dynamically if present
                    const headerAvatar = document.querySelector('.navbar-item .avatar img');
                    if (headerAvatar) headerAvatar.src = data.photo_url;

                    showToast('Profile photo updated!', 'success');
                } else {
                    showToast(data.message ?? 'Upload failed. Supported formats: JPEG, PNG, WebP.', 'error');
                }
            } catch (err) {
                showToast('An error occurred during upload. Please try a different image.', 'error');
            } finally {
                avatarOverlayBtn.disabled = false;
                avatarOverlayBtn.innerHTML = originalSvg;
                avatarFileInput.value = '';
            }
        });
    }

    // ──────────────────────────────────────────────
    // 4. Password Form Toggles
    // ──────────────────────────────────────────────
    if (changePasswordBtn && editSection && staticSection) {
        changePasswordBtn.addEventListener('click', function () {
            staticSection.style.display = 'none';
            editSection.style.display = 'block';
            clearPasswordFields();
            clearFieldErrors();
        });
    }

    if (cancelPasswordBtn && editSection && staticSection) {
        cancelPasswordBtn.addEventListener('click', function () {
            editSection.style.display = 'none';
            staticSection.style.display = 'block';
            clearPasswordFields();
            clearFieldErrors();
        });
    }

    // ──────────────────────────────────────────────
    // 5. Password Eye Visibility Toggle
    // ──────────────────────────────────────────────
    document.querySelectorAll('.password-toggle-icon').forEach(icon => {
        icon.addEventListener('click', function (e) {
            e.preventDefault();
            const container = this.closest('.password-field');
            if (!container) return;
            const input = container.querySelector('input');
            if (!input) return;

            if (input.type === 'password') {
                input.type = 'text';
                this.classList.remove('feather-eye-off');
                this.classList.add('feather-eye');
                this.innerHTML = '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle>';
            } else {
                input.type = 'password';
                this.classList.remove('feather-eye');
                this.classList.add('feather-eye-off');
                this.innerHTML = '<path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line>';
            }
        });
    });

    // ──────────────────────────────────────────────
    // 6. Save Password (AJAX Submit)
    // ──────────────────────────────────────────────
    if (savePasswordBtn) {
        savePasswordBtn.addEventListener('click', async function () {
            clearFieldErrors();

            const currentPw = document.getElementById('input-current-password')?.value;
            const newPw     = document.getElementById('input-new-password')?.value;
            const confirmPw = document.getElementById('input-confirm-password')?.value;

            if (!currentPw || !newPw || !confirmPw) {
                const dummyErrors = {};
                if (!currentPw) dummyErrors.current_password = ['Current password is required.'];
                if (!newPw) dummyErrors.new_password = ['New password is required.'];
                if (!confirmPw) dummyErrors.confirm_password = ['Confirmation is required.'];
                showFieldErrors(dummyErrors);
                return;
            }

            if (newPw !== confirmPw) {
                showFieldErrors({ confirm_password: ['Passwords do not match.'] });
                return;
            }

            savePasswordBtn.disabled = true;
            savePasswordBtn.textContent = 'Saving...';

            try {
                const body = new URLSearchParams({
                    current_password: currentPw,
                    new_password:     newPw,
                    confirm_password: confirmPw,
                });

                const res  = await fetch('/account-settings/update-password', {
                    method:  'POST',
                    headers: { 'X-CSRF-TOKEN': csrfToken, 'Content-Type': 'application/x-www-form-urlencoded', 'Accept': 'application/json' },
                    body:    body.toString(),
                });

                const data = await res.json();

                if (!res.ok || !data.success) {
                    if (data.errors) {
                        showFieldErrors(data.errors);
                    } else {
                        showToast(data.message ?? 'Failed to update password.', 'error');
                    }
                    throw new Error('Password update failed');
                }

                showToast('Password updated successfully!', 'success');
                editSection.style.display = 'none';
                staticSection.style.display = 'block';
                clearPasswordFields();

            } catch (err) {
                // validation error already styled inline, ignore
            } finally {
                savePasswordBtn.disabled = false;
                savePasswordBtn.innerHTML = `
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-check-circle"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                    Save Changes
                `;
            }
        });
    }

    // ──────────────────────────────────────────────
    // Helpers
    // ──────────────────────────────────────────────

    function clearPasswordFields() {
        ['input-current-password', 'input-new-password', 'input-confirm-password'].forEach(id => {
            const el = document.getElementById(id);
            if (el) el.value = '';
        });
    }

    function clearFieldErrors() {
        document.querySelectorAll('.field-error-msg').forEach(el => el.remove());
        document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
    }

    /**
     * Display Laravel validation errors inline beneath the matching input.
     */
    function showFieldErrors(errors) {
        const keyMap = {
            current_password: 'input-current-password',
            new_password:     'input-new-password',
            confirm_password: 'input-confirm-password',
        };

        Object.entries(errors).forEach(([field, messages]) => {
            const inputId = keyMap[field] ?? field;
            const input   = document.getElementById(inputId);
            if (!input) return;

            input.classList.add('is-invalid');

            const msg      = document.createElement('small');
            msg.className  = 'field-error-msg text-danger';
            msg.textContent = Array.isArray(messages) ? messages[0] : messages;
            input.closest('.info-item')?.appendChild(msg);
        });
    }

    /**
     * Simple custom toast notification.
     */
    function showToast(message, type = 'success') {
        document.querySelectorAll('.profile-toast').forEach(t => t.remove());

        const toast       = document.createElement('div');
        toast.className   = `profile-toast profile-toast--${type}`;
        toast.textContent = message;

        Object.assign(toast.style, {
            position:     'fixed',
            bottom:       '24px',
            right:        '24px',
            padding:      '12px 20px',
            borderRadius: '8px',
            color:        '#fff',
            fontWeight:   '500',
            fontSize:     '14px',
            zIndex:       '9999',
            boxShadow:    '0 4px 16px rgba(0,0,0,0.18)',
            background:   type === 'success' ? '#22c55e' : '#ef4444',
            transition:   'opacity 0.3s ease',
            opacity:      '1',
        });

        document.body.appendChild(toast);

        setTimeout(() => {
            toast.style.opacity = '0';
            setTimeout(() => toast.remove(), 300);
        }, 3500);
    }

});
