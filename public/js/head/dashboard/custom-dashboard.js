document.addEventListener('DOMContentLoaded', function () {
    const generateReportLink = document.getElementById('generate-report-link');
    if (generateReportLink) {
        generateReportLink.addEventListener('click', function (e) {
            e.preventDefault();
            if (typeof window.confirmAction === 'function') {
                window.confirmAction({
                    title: 'Generate Utilized Budget Report?',
                    text: 'Do you want to download the Utilized Budget Report as a PDF?',
                    icon: 'question',
                    confirmButtonText: 'Download',
                    cancelButtonText: 'Cancel',
                    onConfirm: function () {
                        // Redirect to the download route
                        window.location.href = '/dashboard/export-ubr';
                    }
                });
            } else {
                // Fallback direct redirection if confirmAction is not loaded
                window.location.href = '/dashboard/export-ubr';
            }
        });
    }

    const viewAppLink = document.getElementById('view-procurement-plan-link');
    if (viewAppLink) {
        viewAppLink.addEventListener('click', function (e) {
            e.preventDefault();
            const appId = this.getAttribute('data-app-id');
            if (!appId) {
                window.location.href = '/dashboard?error_no_active_app=1';
                return;
            }
            if (typeof window.confirmAction === 'function') {
                window.confirmAction({
                    title: 'View Procurement Plan?',
                    text: 'Do you want to view the details of the active Annual Procurement Plan?',
                    icon: 'question',
                    confirmButtonText: 'View',
                    cancelButtonText: 'Cancel',
                    onConfirm: function () {
                        window.location.href = '/create-app/' + appId;
                    }
                });
            } else {
                window.location.href = '/create-app/' + appId;
            }
        });
    }
});
