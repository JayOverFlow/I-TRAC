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
                .then((response) => response.json())
                .then((data) => {
                    if (data.success) {
                        // 4. Close the modal and reload the page to reflect changes
                        $("#exampleModalCenter").modal("hide");
                        location.reload();
                    }
                })
                .catch((error) => console.error("Assignment error:", error));
        });
    }
});
