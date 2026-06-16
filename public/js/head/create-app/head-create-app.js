document.addEventListener("DOMContentLoaded", function () {
    // ─── DOM references ──────────────────────────────────────────────────────
    const form                  = document.getElementById("create-app-form");
    const intentInput           = document.getElementById("form-intent");
    const addProjectBtn         = document.getElementById("add-project-btn");
    const btnDone               = document.getElementById("btn-done");
    const btnDraft              = document.getElementById("btn-draft");
    const btnEdit               = document.getElementById("btn-edit-app");
    const projectItemsContainer = document.getElementById("project-items-container");
    const totalAmountDisplay    = document.getElementById("total-amount-display");

    // ─── Flatpickr init ──────────────────────────────────────────────────────
    function initFlatpickrForCard(card) {
        if (typeof flatpickr === "undefined") return;

        const startInput = card.querySelector('[data-field="start"]');
        const endInput = card.querySelector('[data-field="end"]');

        let startPicker = null;
        let endPicker = null;

        if (endInput && !endInput.disabled) {
            if (endInput._flatpickr) endInput._flatpickr.destroy();
            endPicker = flatpickr(endInput, {
                minDate: (startInput && startInput.value) ? startInput.value : "today",
                dateFormat: "Y-m-d"
            });
        }

        if (startInput && !startInput.disabled) {
            if (startInput._flatpickr) startInput._flatpickr.destroy();
            startPicker = flatpickr(startInput, {
                minDate: "today",
                dateFormat: "Y-m-d",
                onChange: function (selectedDates, dateStr) {
                    if (endPicker) {
                        endPicker.set("minDate", dateStr || "today");
                    }
                }
            });
        }
    }

    if (projectItemsContainer) {
        projectItemsContainer.querySelectorAll(".project-item-card").forEach((card) => {
            initFlatpickrForCard(card);
        });
    }

    // ─── Utility: total amount ────────────────────────────────────────────────
    function updateTotalAmount() {
        let total = 0;
        projectItemsContainer.querySelectorAll(".estimated-budget-input").forEach((input) => {
            const val = parseFloat(input.value);
            if (!isNaN(val)) total += val;
        });
        totalAmountDisplay.textContent = total.toLocaleString("en-US", {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
        });
    }

    // ─── Utility: re-index cards after removal ────────────────────────────────
    function reindexItems() {
        projectItemsContainer.querySelectorAll(".project-item-card").forEach((card, index) => {
            const itemNumberSpan = card.querySelector(".item-number-span");
            if (itemNumberSpan) itemNumberSpan.textContent = `Item ${index + 1}`;

            card.querySelectorAll("input[name]").forEach((input) => {
                const newName = input.getAttribute("name").replace(/items\[\d+\]/, `items[${index}]`);
                input.setAttribute("name", newName);
            });
        });
    }

    // ─── Error helpers ────────────────────────────────────────────────────────

    /**
     * Remove all error states from every card in the form.
     */
    function clearAllErrors() {
        form.querySelectorAll(".field-error").forEach((span) => {
            span.textContent = "";
            span.classList.add("d-none");
        });
        form.querySelectorAll(".is-invalid").forEach((el) => {
            el.classList.remove("is-invalid");
        });
    }

    /**
     * Display field-level errors returned by the Laravel controller.
     *
     * @param {Object} errors — shape: { "items.0.proj_title": ["msg"], ... }
     */
    function showErrors(errors) {
        const cards = projectItemsContainer.querySelectorAll(".project-item-card");

        Object.entries(errors).forEach(([key, messages]) => {
            // key format: "items.N.field_name"
            const parts = key.split(".");          // ["items", "0", "proj_title"]
            if (parts.length < 3 || parts[0] !== "items") return;

            const cardIndex = parseInt(parts[1], 10);
            const fieldName = parts[2];
            const message   = messages[0]; // show first message only

            const card = cards[cardIndex];
            if (!card) return;

            // For radio buttons — find the [data-field="covered"] wrapper
            if (fieldName === "covered") {
                const wrapper = card.querySelector('[data-field="covered"]');
                if (wrapper) {
                    wrapper.classList.add("is-invalid");
                    const parent = wrapper.closest(".form-group");
                    const errorSpan = parent ? parent.querySelector(".field-error") : null;
                    if (errorSpan) {
                        errorSpan.textContent = message;
                        errorSpan.classList.remove("d-none");
                    }
                }
                return;
            }

            // For all other inputs — find by data-field attribute
            const input = card.querySelector(`[data-field="${fieldName}"]`);
            if (!input) return;

            input.classList.add("is-invalid");

            // The error span is always the next sibling element after the input
            const errorSpan = input.nextElementSibling;
            if (errorSpan && errorSpan.classList.contains("field-error")) {
                errorSpan.textContent = message;
                errorSpan.classList.remove("d-none");
            }
        });
    }

    // ─── Toast Feedback helper ───────────────────────────────────────────────

    /**
     * Show a dynamic Bootstrap toast message in the toast-container
     */
    function showToast(message, type = "success") {
        const container = document.querySelector(".toast-container");
        if (!container) return;

        // Create a new toast element matching toast-feedback.blade.php styling
        const toastEl = document.createElement("div");
        toastEl.className = `toast align-items-center text-white bg-${type === 'success' ? 'success' : 'danger'} border-0 shadow-lg`;
        toastEl.setAttribute("role", "alert");
        toastEl.setAttribute("aria-live", "assertive");
        toastEl.setAttribute("aria-atomic", "true");

        const svgIcon = type === 'success'
            ? `<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-check-circle"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>`
            : `<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-alert-circle"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>`;

        toastEl.innerHTML = `
            <div class="d-flex">
                <div class="toast-body d-flex align-items-center gap-2">
                    ${svgIcon}
                    <div>${message}</div>
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        `;

        container.appendChild(toastEl);

        if (typeof bootstrap !== "undefined" && bootstrap.Toast) {
            const toast = new bootstrap.Toast(toastEl, { delay: 5000 });
            toast.show();

            // Clean up DOM once closed
            toastEl.addEventListener('hidden.bs.toast', function () {
                toastEl.remove();
            });
        }
    }

    // ─── AJAX form submission ─────────────────────────────────────────────────

    /**
     * Submit the form via fetch with the given intent.
     * @param {"done"|"draft"} intent
     */
    function submitForm(intent) {
        clearAllErrors();

        intentInput.value = intent;

        const formData = new FormData(form);

        // Disable buttons to prevent double-submission
        btnDone.disabled  = true;
        btnDraft.disabled = true;

        fetch(form.action, {
            method: "POST",
            headers: {
                "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute("content"),
                "Accept":       "application/json",
            },
            body: formData,
        })
        .then(async (response) => {
            const data = await response.json();

            if (response.ok && data.success) {
                // Success — redirect if URL provided, otherwise show banner
                if (data.redirect) {
                    window.location.href = data.redirect;
                } else {
                    showToast(data.message || "Saved successfully!", "success");
                }
                return;
            }

            if (response.status === 422 && data.errors) {
                // Validation errors — show inline per field
                showErrors(data.errors);
                showToast("Please fix the errors below and try again.", "error");
                return;
            }

            // Unexpected server error
            showToast("Something went wrong. Please try again.", "error");
        })
        .catch(() => {
            showToast("Network error. Please check your connection.", "error");
        })
        .finally(() => {
            btnDone.disabled  = false;
            btnDraft.disabled = false;
        });
    }

    // ─── Button listeners ─────────────────────────────────────────────────────

    if (btnDone) {
        btnDone.addEventListener("click", function () {
            window.confirmAction({
                title: 'Set as Completed?',
                text: 'Are you sure you want to set this APP as completed? You can edit it later if needed.',
                icon: 'question',
                confirmButtonText: 'Yes, Complete',
                cancelButtonText: 'Cancel',
                onConfirm: function() {
                    submitForm("done");
                }
            });
        });
    }

    if (btnEdit) {
        btnEdit.addEventListener("click", function () {
            // Hide Completed badge and Edit button
            const completedBadge = document.getElementById("completed-badge");
            if (completedBadge) completedBadge.classList.add("d-none");
            btnEdit.classList.add("d-none");

            // Show Done and Save as Draft buttons
            if (btnDone) {
                btnDone.classList.remove("d-none");
                btnDone.classList.add("d-inline-flex");
            }
            if (btnDraft) {
                btnDraft.classList.remove("d-none");
                btnDraft.classList.add("d-inline-flex");
            }

            // Enable form fields (skipping already assigned items)
            form.querySelectorAll("input, select, textarea").forEach((el) => {
                if (el.closest(".project-item-card.is-assigned")) {
                    return;
                }
                el.removeAttribute("disabled");
                el.removeAttribute("readonly");
            });

            // Show Add Project container
            const addProjectContainer = document.getElementById("add-project-btn-container");
            if (addProjectContainer) {
                addProjectContainer.classList.remove("d-none");
            }

            // Show Trash buttons if count > 1
            const projectCards = projectItemsContainer.querySelectorAll(".project-item-card");
            if (projectCards.length > 1) {
                projectCards.forEach((card) => {
                    if (card.classList.contains("is-assigned")) {
                        return;
                    }
                    const trashBtn = card.querySelector(".remove-project-btn");
                    if (trashBtn) trashBtn.classList.remove("d-none");
                });
            }

            // Re-initialize flatpickr on the now enabled inputs
            projectCards.forEach((card) => {
                initFlatpickrForCard(card);
            });
        });
    }

    if (btnDraft) {
        btnDraft.addEventListener("click", function () {
            window.confirmAction({
                title: 'Save as Draft?',
                text: 'Are you sure you want to save this Annual Procurement Plan as a draft? You will be able to edit and submit it later.',
                icon: 'info',
                confirmButtonText: 'Yes, Save as Draft',
                cancelButtonText: 'Cancel',
                onConfirm: function() {
                    submitForm("draft");
                }
            });
        });
    }

    // ─── Add Project btn ──────────────────────────────────────────────────────

    if (addProjectBtn && projectItemsContainer) {
        addProjectBtn.addEventListener("click", function () {
            const projectCards = projectItemsContainer.querySelectorAll(".project-item-card");
            if (projectCards.length === 0) return;

            const newIndex      = projectCards.length; // 0-based
            const newItemNumber = newIndex + 1;

            // Find a card that is not assigned to clone as a template, or clean it up
            const templateCard = projectItemsContainer.querySelector(".project-item-card:not(.is-assigned)") || projectCards[0];
            const newCard   = templateCard.cloneNode(true);

            // Clean template-specific assigned states
            newCard.classList.remove("is-assigned");
            newCard.querySelectorAll('input[type="hidden"]').forEach(el => el.remove());
            newCard.querySelectorAll('.badge').forEach(el => {
                if (el.textContent.trim() === 'Assigned') el.remove();
            });
            newCard.querySelectorAll('input, select, textarea').forEach(el => {
                el.removeAttribute("disabled");
                el.removeAttribute("readonly");
            });

            // Show the Trash button on cloned cards
            const trashBtn = newCard.querySelector(".remove-project-btn");
            if (trashBtn) trashBtn.classList.remove("d-none");

            // Update Item number text
            const itemNumberSpan = newCard.querySelector(".item-number-span");
            if (itemNumberSpan) itemNumberSpan.textContent = `Item ${newItemNumber}`;

            // Clear text / number inputs and reset error states
            newCard.querySelectorAll('input[type="text"], input[type="number"]').forEach((input) => {
                input.value = "";
                input.classList.remove("is-invalid");
            });

            // Clear all error spans
            newCard.querySelectorAll(".field-error").forEach((span) => {
                span.textContent = "";
                span.classList.add("d-none");
            });

            // Update radio button IDs and uncheck them
            newCard.querySelectorAll('input[type="radio"]').forEach((input) => {
                input.checked = false;
                input.classList.remove("is-invalid");

                // Re-ID: e.g. covered-yes-0 → covered-yes-1
                const baseId = input.id.replace(/-\d+$/, "");
                const newId  = `${baseId}-${newIndex}`;
                input.id     = newId;

                const label = newCard.querySelector(`label[for="${input.id.replace(`-${newIndex}`, `-${newIndex - 1}`)}"]`)
                           || newCard.querySelector(`label[for="${baseId}-${newIndex - 1}"]`);
                if (label) label.setAttribute("for", newId);
            });

            // Re-index all name attributes to new index
            newCard.querySelectorAll("input[name]").forEach((input) => {
                const newName = input.getAttribute("name").replace(/items\[\d+\]/, `items[${newIndex}]`);
                input.setAttribute("name", newName);
            });

            // Clear radio wrapper invalid state
            const coveredWrapper = newCard.querySelector('[data-field="covered"]');
            if (coveredWrapper) coveredWrapper.classList.remove("is-invalid");

            projectItemsContainer.appendChild(newCard);

            // Initialize flatpickr on the new date inputs
            initFlatpickrForCard(newCard);
        });
    }

    // ─── Total amount & Remove card (event delegation) ────────────────────────

    if (projectItemsContainer) {
        updateTotalAmount();

        projectItemsContainer.addEventListener("keydown", function (e) {
            if (e.target.classList.contains("estimated-budget-input")) {
                // Allow control keys
                if ([
                    'Backspace', 'Delete', 'Tab', 'Escape', 'Enter', 'NumLock',
                    'ArrowLeft', 'ArrowRight', 'ArrowUp', 'ArrowDown', 'Home', 'End'
                ].includes(e.key)) {
                    return;
                }
                
                // Allow command/control shortcut combinations
                if (e.ctrlKey || e.metaKey) {
                    return;
                }

                // Allow period/decimal point, but only if it's the first one
                if (e.key === '.') {
                    if (e.target.value.includes('.')) {
                        e.preventDefault();
                    }
                    return;
                }

                // Allow numbers only
                if (!/^[0-9]$/.test(e.key)) {
                    e.preventDefault();
                }
            }
        });

        projectItemsContainer.addEventListener("paste", function (e) {
            if (e.target.classList.contains("estimated-budget-input")) {
                const clipboardData = e.clipboardData || window.clipboardData;
                const pastedData = clipboardData.getData('text');
                
                // Allow only positive float or integer formats (e.g. 100, 100.5)
                if (!/^\d+(\.\d+)?$/.test(pastedData)) {
                    e.preventDefault();
                }
            }
        });

        projectItemsContainer.addEventListener("input", function (e) {
            if (e.target.classList.contains("estimated-budget-input")) {
                if (e.target.value !== "" && parseFloat(e.target.value) < 0) {
                    e.target.value = 0;
                }
                updateTotalAmount();
            }
        });

        projectItemsContainer.addEventListener("click", function (e) {
            const trashBtn = e.target.closest(".remove-project-btn");
            if (trashBtn) {
                const cardToRemove = trashBtn.closest(".project-item-card");
                if (cardToRemove) {
                    window.confirmAction({
                        title: 'Remove Project Item?',
                        text: 'Are you sure you want to remove this procurement project item from your plan? This will delete all entered data for this item.',
                        icon: 'warning',
                        confirmButtonText: 'Yes, Remove It',
                        cancelButtonText: 'Cancel',
                        onConfirm: function() {
                            cardToRemove.remove();
                            updateTotalAmount();
                            reindexItems();
                        }
                    });
                }
            }
        });
    }
});
