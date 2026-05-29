document.addEventListener("DOMContentLoaded", function () {
    const assignBtn = document.querySelector("#assign-btn");
    const createBtn = document.querySelector("#create-btn");

    function updateButtonStates() {
        // Use DataTables API to query elements across all pages (even those not currently in the DOM)
        const table = $("#zero-config").DataTable();
        const checkedCount = table.$(".item-checkbox:checked").length;
        const isDisabled = checkedCount === 0;

        if (assignBtn) assignBtn.disabled = isDisabled;
        if (createBtn) createBtn.disabled = isDisabled;
    }

    document.addEventListener("change", function (event) {
        if (event.target && event.target.classList.contains("item-checkbox")) {
            updateButtonStates();
        }
    });

    // Initialize state on load
    updateButtonStates();

    // Modal User Selection Logic
    const searchInput = document.getElementById("user-search-input");
    const userList = document.getElementById("user-list");
    const confirmAssignBtn = document.getElementById("confirm-assign-btn");

    if (userList) {
        userList.addEventListener("click", function (event) {
            const item = event.target.closest(".user-list-item");
            if (!item) return;

            const isSelected = item.classList.contains("active");
            const allItems = document.querySelectorAll(".user-list-item");

            if (isSelected) {
                // Deselect current item
                item.classList.remove("active", "bg-light");
                item.style.outline = "";

                // Make all items visible again
                allItems.forEach((otherItem) => {
                    otherItem.style.opacity = "1";
                });

                if (confirmAssignBtn) confirmAssignBtn.disabled = true;
            } else {
                // Select a new item, deselect any previously selected item
                allItems.forEach((otherItem) => {
                    // Deselect and slightly dim
                    otherItem.classList.remove("active", "bg-light");
                    otherItem.style.outline = "";
                    otherItem.style.opacity = "0.4";
                });

                // Highlight chosen item fully
                item.classList.add("active", "bg-light");
                item.style.outline = "2px solid #C62742";
                item.style.outlineOffset = "-2px";
                item.style.opacity = "1";

                if (confirmAssignBtn) confirmAssignBtn.disabled = false;
            }
        });
    }

    // Dynamic Search Filter Logic
    if (searchInput) {
        searchInput.addEventListener("input", function () {
            const filter = searchInput.value.toLowerCase().trim();
            const items = document.querySelectorAll(".user-list-item");

            items.forEach((item) => {
                const text = item.textContent || item.innerText;

                // For table rows, we just toggle 'd-none' without enforcing d-flex
                if (text.toLowerCase().indexOf(filter) > -1) {
                    item.classList.remove("d-none");
                } else {
                    item.classList.add("d-none");
                }
            });
        });
    }

    // Assign Button Submission Logic
    if (confirmAssignBtn) {
        confirmAssignBtn.addEventListener("click", function () {
            // 1. Collect item IDs from all checked checkboxes (across all DataTable pages)
            const table = $("#zero-config").DataTable();
            const itemIds = [];
            table.$(".item-checkbox:checked").each(function () {
                itemIds.push(this.dataset.itemId);
            });

            // 2. Get the selected subordinate user's ID
            const selectedUser = document.querySelector(
                ".user-list-item.active",
            );
            const assignedTo = selectedUser
                ? selectedUser.dataset.userId
                : null;

            if (itemIds.length === 0 || !assignedTo) return;

            // 3. POST to the store route with CSRF header
            const url = document.querySelector(
                'meta[name="assign-pr-url"]',
            ).content;
            const csrf = document.querySelector(
                'meta[name="csrf-token"]',
            ).content;

            const formData = new FormData();
            formData.append("assigned_to", assignedTo);
            itemIds.forEach((id) => formData.append("item_ids[]", id));

            fetch(url, {
                method: "POST",
                headers: { "X-CSRF-TOKEN": csrf },
                body: formData,
            })
                .then(async (response) => {
                    if (!response.ok) {
                        const errorData = await response.json().catch(() => ({}));
                        throw new Error(errorData.message || "Failed to assign Purchase Request. Please check your inputs.");
                    }
                    return response.json();
                })
                .then((data) => {
                    if (data.success) {
                        // Close the modal and reload the page to reflect changes
                        $("#exampleModalCenter").modal("hide");
                        location.reload();
                    } else {
                        throw new Error(data.message || "An error occurred during assignment.");
                    }
                })
                .catch((error) => {
                    console.error("Assignment error:", error);
                    $("#exampleModalCenter").modal("hide");
                    showToast('error', error.message || 'An error occurred while assigning the PR.');
                });
        });
    }

    function showToast(type, message) {
        const container = document.querySelector('.toast-container');
        if (!container) return;

        const isSuccess = type === 'success';
        const bgClass = isSuccess ? 'bg-success' : 'bg-danger';
        const icon = isSuccess 
            ? '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-check-circle"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>'
            : '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-alert-circle"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>';

        const toastHtml = `
            <div class="toast align-items-center text-white ${bgClass} border-0 shadow-lg" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body d-flex align-items-center gap-2">
                        ${icon}
                        <div>${message}</div>
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>
        `;
        
        container.insertAdjacentHTML('beforeend', toastHtml);
        const toastEl = container.lastElementChild;
        const toast = new bootstrap.Toast(toastEl, { delay: 5000 });
        toast.show();
        
        toastEl.addEventListener('hidden.bs.toast', () => {
            toastEl.remove();
        });
    }
});
