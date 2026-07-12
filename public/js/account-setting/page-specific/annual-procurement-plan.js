$(document).ready(function () {
    // Initialize Datatables for Annual Procurement Plan
    if ($('#zero-config').length) {
        $('#zero-config').DataTable({
            "dom": "<'dt--top-section'<'row'<'col-12 col-sm-6 d-flex justify-content-sm-start align-items-center'<'custom-title'>><'col-12 col-sm-6 d-flex gap-3 justify-content-sm-end justify-content-center mt-sm-0 mt-3'<'custom-buttons'>f>>>" +
                "<'table-responsive'tr>" +
                "<'dt--bottom-section d-sm-flex justify-content-sm-between text-center'<'dt--pages-count  mb-sm-0 mb-3'i><'dt--pagination'p>>",
            "oLanguage": {
                "oPaginate": {
                    "sPrevious": '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-arrow-left"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>',
                    "sNext": '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-arrow-right"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>'
                },
                "sInfo": "Showing page _PAGE_ of _PAGES_",
                "sSearch": '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-search"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>',
                "sSearchPlaceholder": "Search...",
                "sLengthMenu": "Results :  _MENU_",
            },
            initComplete: function () {
                $('.custom-title').html('<h5 class="fw-bold mb-0" style="color: #8B0000;">Annual Procurement Plan</h5>');
                $('.custom-buttons').html(`
                    <div class="d-flex gap-2">
                        <button class="btn btn-dark-red d-flex align-items-center px-4 py-2 border-0" style="border-radius: 8px;" data-bs-toggle="modal" data-bs-target="#createAppModal">
                            <svg width="20" height="20" viewBox="0 0 20 21" fill="none" xmlns="http://www.w3.org/2000/svg" class="me-2">
                                <g clip-path="url(#clip0_1527_4830)">
                                    <path fill-rule="evenodd" clip-rule="evenodd" d="M6.25 13.125H3.75V11.8125H16.25V13.125H12.5V15.75H16.25V17.0625H12.5V19.6875H11.25V17.0625H7.5V19.6875H6.25V17.0625H3.75V15.75H6.25V13.125ZM7.5 13.125V15.75H11.25V13.125H7.5Z" fill="white"/>
                                    <path d="M5 0H11.875V1.3125H5C4.66848 1.3125 4.35054 1.45078 4.11612 1.69692C3.8817 1.94306 3.75 2.2769 3.75 2.625V18.375C3.75 18.7231 3.8817 19.0569 4.11612 19.3031C4.35054 19.5492 4.66848 19.6875 5 19.6875H15C15.3315 19.6875 15.6495 19.5492 15.8839 19.3031C16.1183 19.0569 16.25 18.7231 16.25 18.375V5.90625H17.5V18.375C17.5 19.0712 17.2366 19.7389 16.7678 20.2312C16.2989 20.7234 15.663 21 15 21H5C4.33696 21 3.70107 20.7234 3.23223 20.2312C2.76339 19.7389 2.5 19.0712 2.5 18.375V2.625C2.5 1.92881 2.76339 1.26113 3.23223 0.768845C3.70107 0.276562 4.33696 0 5 0V0Z" fill="white"/>
                                    <path d="M11.875 3.9375V0L17.5 5.90625H13.75C13.2527 5.90625 12.7758 5.69883 12.4242 5.32962C12.0725 4.9604 11.875 4.45964 11.875 3.9375V3.9375Z" fill="white"/>
                                </g>
                                <defs>
                                    <clipPath id="clip0_1527_4830">
                                        <rect width="20" height="21" fill="white"/>
                                    </clipPath>
                                </defs>
                            </svg>
                            <span class="fw-bold">Create</span>
                        </button>
                    </div>
                `);
            },
            "order": [],
            "stripeClasses": [],
            "lengthMenu": [5, 10, 20, 50],
            "pageLength": 5
        });
    }

    // Handle AJAX Form Submission for Modal
    const form = document.getElementById("createAppModalForm");
    const submitBtn = document.getElementById("btnCreatePlanSubmit");
    const appTitleInput = document.getElementById("app_title");
    const yearSelect = document.getElementById("year");
    const titleError = document.getElementById("app_title_error");
    const yearError = document.getElementById("year_error");

    if (form) {
        form.addEventListener("submit", function (e) {
            e.preventDefault();

            // Clear previous errors
            titleError.classList.add("d-none");
            yearError.classList.add("d-none");
            appTitleInput.classList.remove("is-invalid");
            yearSelect.classList.remove("is-invalid");

            submitBtn.disabled = true;

            const formData = new FormData(form);

            fetch(form.action, {
                method: "POST",
                headers: {
                    "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute("content"),
                    "Accept": "application/json"
                },
                body: formData
            })
            .then(async (response) => {
                const data = await response.json();
                if (response.ok && data.success) {
                    window.location.href = data.redirect;
                } else if (response.status === 422 && data.errors) {
                    if (data.errors.app_title) {
                        titleError.textContent = data.errors.app_title[0];
                        titleError.classList.remove("d-none");
                        appTitleInput.classList.add("is-invalid");
                    }
                    if (data.errors.year) {
                        yearError.textContent = data.errors.year[0];
                        yearError.classList.remove("d-none");
                        yearSelect.classList.add("is-invalid");
                    }
                } else {
                    alert(data.message || "An unexpected error occurred.");
                }
            })
            .catch(() => {
                alert("Network error. Please try again.");
            })
            .finally(() => {
                submitBtn.disabled = false;
            });
        });
    }
});
