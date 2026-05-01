/**
 * Profile Tab JS
 * Handles:
 *  - View/Edit mode toggle
 *  - Password visibility toggle
 *  - Save Changes (profile info + password) via AJAX
 *  - Avatar upload and delete via AJAX
 */

document.addEventListener('DOMContentLoaded', function () {

    // ──────────────────────────────────────────────
    // 1. CSRF Token (required for all POST/DELETE requests)
    // ──────────────────────────────────────────────
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    // ──────────────────────────────────────────────
    // 2. Element References
    // ──────────────────────────────────────────────
    const profileSection    = document.getElementById('profile-section');
    const editBtn           = document.getElementById('edit-profile-btn');
    const goBackBtn         = document.getElementById('go-back-btn');
    const saveBtn           = document.getElementById('btn-save-changes');
    const uploadBtn         = document.getElementById('btn-upload-photo');
    const deletePhotoBtn    = document.getElementById('btn-delete-photo');
    const avatarFileInput   = document.getElementById('avatar-file-input');
    const avatarImg         = document.getElementById('profile-avatar-img');
    const sidebarFullname   = document.getElementById('sidebar-fullname');

    // ──────────────────────────────────────────────
    // 3. View ↔ Edit Mode Toggle
    // ──────────────────────────────────────────────
    if (editBtn && profileSection) {
        editBtn.addEventListener('click', function () {
            profileSection.classList.add('edit-mode');
            clearPasswordFields();
            clearFieldErrors();
        });
    }

    if (goBackBtn && profileSection) {
        goBackBtn.addEventListener('click', function () {
            profileSection.classList.remove('edit-mode');
            clearPasswordFields();
            clearFieldErrors();
        });
    }

    // ──────────────────────────────────────────────
    // 4. Password Visibility Toggle
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
                this.innerHTML = '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle>';
            } else {
                input.type = 'password';
                this.innerHTML = '<path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line>';
            }
        });
    });

    // ──────────────────────────────────────────────
    // 5. Save Changes Button
    // ──────────────────────────────────────────────
    if (saveBtn) {
        saveBtn.addEventListener('click', async function () {
            clearFieldErrors();
            saveBtn.disabled = true;
            saveBtn.textContent = 'Saving...';

            try {
                // Always save profile info
                await saveProfileInfo();

                // Only save password if any password field is filled
                const currentPw = document.getElementById('input-current-password')?.value;
                const newPw     = document.getElementById('input-new-password')?.value;
                const confirmPw = document.getElementById('input-confirm-password')?.value;

                if (currentPw || newPw || confirmPw) {
                    if (newPw !== confirmPw) {
                        showFieldErrors({ confirm_password: ['Passwords do not match.'] });
                        throw new Error('Validation failed');
                    }
                    await savePassword(currentPw, newPw, confirmPw);
                }

                showToast('Profile updated successfully!', 'success');
                profileSection.classList.remove('edit-mode');
                clearPasswordFields();

            } catch (err) {
                // Errors are already shown inline; do nothing extra
            } finally {
                saveBtn.disabled = false;
                saveBtn.innerHTML = `
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-check-circle"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                    Save Changes
                `;
            }
        });
    }

    // ──────────────────────────────────────────────
    // 6. Save Profile Info (AJAX)
    // ──────────────────────────────────────────────
    async function saveProfileInfo() {
        const body = new URLSearchParams({
            user_firstname:  document.getElementById('input-firstname')?.value  ?? '',
            user_middlename: document.getElementById('input-middlename')?.value ?? '',
            user_lastname:   document.getElementById('input-lastname')?.value   ?? '',
            user_suffix:     document.getElementById('input-suffix')?.value     ?? '',
            user_contactno:  document.getElementById('input-contactno')?.value  ?? '',
        });

        const res  = await fetch('/account-settings/update-profile', {
            method:  'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Content-Type': 'application/x-www-form-urlencoded' },
            body:    body.toString(),
        });

        const data = await res.json();

        if (!res.ok || !data.success) {
            if (data.errors) showFieldErrors(data.errors);
            throw new Error('Profile update failed');
        }

        // Update the sidebar name live
        const fn = document.getElementById('input-firstname')?.value ?? '';
        const mn = document.getElementById('input-middlename')?.value ?? '';
        const ln = document.getElementById('input-lastname')?.value ?? '';
        const sf = document.getElementById('input-suffix')?.value ?? '';
        if (sidebarFullname) {
            sidebarFullname.textContent = [fn, mn, ln, sf].filter(Boolean).join(' ');
        }

        // Update view-mode spans live
        setViewValue('view-firstname',  fn);
        setViewValue('view-middlename', mn || 'N/A');
        setViewValue('view-lastname',   ln);
        setViewValue('view-suffix',     sf || 'N/A');
    }

    // ──────────────────────────────────────────────
    // 7. Save Password (AJAX)
    // ──────────────────────────────────────────────
    async function savePassword(currentPw, newPw, confirmPw) {
        const body = new URLSearchParams({
            current_password: currentPw,
            new_password:     newPw,
            confirm_password: confirmPw,
        });

        const res  = await fetch('/account-settings/update-password', {
            method:  'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Content-Type': 'application/x-www-form-urlencoded' },
            body:    body.toString(),
        });

        const data = await res.json();

        if (!res.ok || !data.success) {
            if (data.errors) showFieldErrors(data.errors);
            throw new Error('Password update failed');
        }
    }

    // ──────────────────────────────────────────────
    // 8. Avatar Upload
    // ──────────────────────────────────────────────
    if (uploadBtn && avatarFileInput) {
        uploadBtn.addEventListener('click', function () {
            avatarFileInput.click();
        });

        avatarFileInput.addEventListener('change', async function () {
            const file = this.files[0];
            if (!file) return;

            const formData = new FormData();
            formData.append('profile_photo', file);
            formData.append('_token', csrfToken);

            uploadBtn.disabled = true;
            uploadBtn.textContent = 'Uploading...';

            try {
                const res  = await fetch('/account-settings/update-avatar', {
                    method:  'POST',
                    headers: { 'X-CSRF-TOKEN': csrfToken },
                    body:    formData,
                });
                const data = await res.json();

                if (res.ok && data.success) {
                    if (avatarImg) avatarImg.src = data.photo_url;
                    showToast('Profile photo updated!', 'success');
                } else {
                    showToast(data.message ?? 'Upload failed. Note: some image formats (e.g., HEIC, AVIF) are not supported. Please use JPEG, PNG, or WebP.', 'error');
                }
            } catch (err) {
                showToast('An error occurred during upload. Please ensure your image is a supported format like JPEG, PNG, or WebP.', 'error');
            } finally {
                uploadBtn.disabled = false;
                uploadBtn.innerHTML = `
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-upload"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="17 8 12 3 7 8"></polyline><line x1="12" y1="3" x2="12" y2="15"></line></svg>
                    Upload photo`;
                avatarFileInput.value = '';
            }
        });
    }

    // ──────────────────────────────────────────────
    // 9. Avatar Delete
    // ──────────────────────────────────────────────
    if (deletePhotoBtn) {
        deletePhotoBtn.addEventListener('click', async function () {
            const result = await Swal.fire({
                title: 'Are you sure?',
                text: "Do you want to remove your profile photo?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!'
            });

            if (!result.isConfirmed) return;

            deletePhotoBtn.disabled = true;

            try {
                const res  = await fetch('/account-settings/delete-avatar', {
                    method:  'DELETE',
                    headers: { 'X-CSRF-TOKEN': csrfToken, 'Content-Type': 'application/json' },
                });
                const data = await res.json();

                if (res.ok && data.success) {
                    // Reset to the default placeholder using data-blank attribute
                    if (avatarImg && avatarImg.dataset.blank) {
                        avatarImg.src = avatarImg.dataset.blank;
                    }
                    showToast('Profile photo removed.', 'success');
                } else {
                    showToast('Failed to remove photo.', 'error');
                }
            } catch (err) {
                showToast('An error occurred.', 'error');
            } finally {
                deletePhotoBtn.disabled = false;
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

    function setViewValue(id, value) {
        const el = document.getElementById(id);
        if (el) el.textContent = value;
    }

    function clearFieldErrors() {
        document.querySelectorAll('.field-error-msg').forEach(el => el.remove());
        document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
    }

    /**
     * Display Laravel validation errors inline beneath the matching input.
     * Keys like "user_firstname" map to input id "input-firstname".
     */
    function showFieldErrors(errors) {
        const keyMap = {
            user_firstname:   'input-firstname',
            user_middlename:  'input-middlename',
            user_lastname:    'input-lastname',
            user_suffix:      'input-suffix',
            user_contactno:   'input-contactno',
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

        // showToast('Please fix the errors below.', 'error');
        throw new Error('Validation failed');
    }

    /**
     * Simple toast notification.
     * Uses Bootstrap's toast if available, otherwise falls back to a custom element.
     */
    function showToast(message, type = 'success') {
        // Remove any existing toasts
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
