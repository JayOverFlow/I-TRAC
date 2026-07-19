document.addEventListener('DOMContentLoaded', function () {
    const generateReportButtons = document.querySelectorAll('[id="generate-report-link"]');
    const modalEl = document.getElementById('generateReportModal');
    const monthSelect = document.getElementById('filter-month-select');
    const appYearEl = document.getElementById('modal-app-year');

    // Setup Month Dropdown constraints
    if (modalEl && monthSelect && appYearEl) {
        const currentDate = new Date();
        const currentMonth = currentDate.getMonth() + 1; // 1-12
        const currentYear = currentDate.getFullYear();
        const appYear = parseInt(appYearEl.textContent.trim()) || currentYear;

        // Populate constraints (User cannot select the future months in the present app_year)
        const options = monthSelect.options;
        for (let i = 0; i < options.length; i++) {
            const optVal = parseInt(options[i].value);
            if (appYear === currentYear) {
                options[i].disabled = optVal > currentMonth;
            } else {
                options[i].disabled = false;
            }
        }

        // Present month is the initial select of dropdown
        if (appYear === currentYear) {
            monthSelect.value = currentMonth;
        } else {
            monthSelect.value = currentMonth <= 12 ? currentMonth : 1;
        }
    }

    // Modal Trigger
    generateReportButtons.forEach(button => {
        button.addEventListener('click', function (e) {
            e.preventDefault();
            if (modalEl) {
                const modal = new bootstrap.Modal(modalEl);
                modal.show();
            }
        });
    });

    // Modal Export Action
    const btnExport = document.getElementById('btn-export-report');
    if (btnExport && monthSelect) {
        btnExport.addEventListener('click', function () {
            const monthVal = monthSelect.value;
            window.location.href = `/dashboard/export-ubr?month=${monthVal}`;
            
            const modalInstance = bootstrap.Modal.getInstance(modalEl);
            if (modalInstance) {
                modalInstance.hide();
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
