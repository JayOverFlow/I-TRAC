<!-- Reusable Action Confirmation SweetAlert2 Helper -->
<script>
    /**
     * Triggers a beautifully styled, themed SweetAlert2 dialog with confirm/cancel actions.
     * Exposes a reusable global helper for consistent UI/UX interactions.
     * 
     * @param {Object} options
     * @param {string} options.title - Header of the alert (e.g. "Save as Draft?")
     * @param {string} options.text - Descriptive message for the user's action
     * @param {string} [options.icon='warning'] - SweetAlert icon ('warning', 'info', 'question', 'success', 'error')
     * @param {string} [options.confirmButtonText='Yes'] - Text for the confirm button
     * @param {string} [options.cancelButtonText='Cancel'] - Text for the cancel button
     * @param {string} [options.confirmButtonColor] - Custom color for confirm button (defaults to theme dark-red)
     * @param {string} [options.cancelButtonColor] - Custom color for cancel button
     * @param {function} options.onConfirm - Callback triggered when the user confirms
     * @param {function} [options.onCancel] - Optional callback triggered when the user cancels
     */
    window.confirmAction = function (options) {
        if (typeof Swal === 'undefined') {
            console.error('SweetAlert2 (Swal) is not loaded on this page.');
            if (options.onConfirm) options.onConfirm();
            return;
        }

        const isDarkMode = document.body.classList.contains('dark');

        // Theme colors aligned with the app design system
        const themeRed = '#a30000'; // Dark red for primary actions/warnings
        const themeGray = '#6c757d'; // Slate gray for secondary/cancel actions
        
        Swal.fire({
            title: options.title || 'Are you sure?',
            text: options.text || '',
            icon: options.icon || 'warning',
            showCancelButton: true,
            confirmButtonColor: options.confirmButtonColor || themeRed,
            cancelButtonColor: options.cancelButtonColor || themeGray,
            confirmButtonText: options.confirmButtonText || 'Confirm',
            cancelButtonText: options.cancelButtonText || 'Cancel',
            reverseButtons: true,
            background: isDarkMode ? '#0e1726' : '#ffffff',
            color: isDarkMode ? '#f1f2f3' : '#3b3f5c',
            iconColor: options.iconColor || (options.icon === 'warning' ? '#e2a03f' : themeRed),
            customClass: {
                popup: 'swal-custom-popup',
                title: 'swal-custom-title fw-bold',
                htmlContainer: 'swal-custom-text',
                confirmButton: 'px-3 py-2 fw-bold text-white',
                cancelButton: 'px-3 py-2 fw-bold'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                if (typeof options.onConfirm === 'function') {
                    options.onConfirm();
                }
            } else if (result.isDismissed) {
                if (typeof options.onCancel === 'function') {
                    options.onCancel();
                }
            }
        });
    };
</script>
