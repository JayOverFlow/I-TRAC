document.addEventListener('DOMContentLoaded', function () {
    const utilizedBudgetCard = document.getElementById('utilized-budget-card');
    if (utilizedBudgetCard) {
        utilizedBudgetCard.addEventListener('click', function () {
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
});
