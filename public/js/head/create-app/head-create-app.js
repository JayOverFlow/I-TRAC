document.addEventListener("DOMContentLoaded", function () {
    const addProjectBtn = document.getElementById("add-project-btn");
    const projectItemsContainer = document.getElementById(
        "project-items-container",
    );
    const totalAmountDisplay = document.getElementById("total-amount-display");

    // Function to calculate and update total amount
    function updateTotalAmount() {
        let total = 0;
        const budgetInputs = projectItemsContainer.querySelectorAll(
            ".estimated-budget-input",
        );

        budgetInputs.forEach((input) => {
            // Remove commas and parse as float
            let valStr = input.value.replace(/,/g, "");
            let val = parseFloat(valStr);
            if (!isNaN(val)) {
                total += val;
            }
        });

        // Format to standard 2 decimal places with commas (e.g., 12,345.00)
        totalAmountDisplay.textContent = total.toLocaleString("en-US", {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
        });
    }

    // Function to re-index item numbers
    function reindexItems() {
        const projectCards = projectItemsContainer.querySelectorAll(".project-item-card");
        projectCards.forEach((card, index) => {
            const itemNumberSpan = card.querySelector(".item-number-span");
            if (itemNumberSpan) {
                itemNumberSpan.textContent = `Item ${index + 1}`;
            }
        });
    }

    // Listener for Add Project Button
    if (addProjectBtn && projectItemsContainer) {
        addProjectBtn.addEventListener("click", function () {
            // Get all project cards to determine the new item number
            const projectCards =
                projectItemsContainer.querySelectorAll(".project-item-card");
            const newItemNumber = projectCards.length + 1;

            if (projectCards.length === 0) return; // Should have at least one to clone

            // Clone the first card (deep clone)
            const firstCard = projectCards[0];
            const newCard = firstCard.cloneNode(true);

            // Show the Trash button on cloned cards
            const trashBtn = newCard.querySelector(".remove-project-btn");
            if (trashBtn) {
                console.log("Showing trash button for new card:", newItemNumber);
                trashBtn.classList.remove("d-none");
                trashBtn.style.display = "block"; // Force visibility
            } else {
                console.error("Trash button not found in cloned card!");
            }

            // Update Item number text
            const itemNumberSpan = newCard.querySelector(".item-number-span");
            if (itemNumberSpan) {
                itemNumberSpan.textContent = `Item ${newItemNumber}`;
            }

            // Clear all text inputs
            const textInputs = newCard.querySelectorAll('input[type="text"]');
            textInputs.forEach((input) => {
                input.value = "";
            });

            // Uncheck all radio buttons and ensure they have unique IDs to label linking
            const radioInputs = newCard.querySelectorAll('input[type="radio"]');
            radioInputs.forEach((input) => {
                input.checked = false;

                const oldId = input.id;
                if (oldId) {
                    const newId = `${oldId}-${newItemNumber}`;
                    input.id = newId;

                    // Find the label that points to this input and update its 'for' attribute
                    const label = newCard.querySelector(
                        `label[for="${oldId}"]`,
                    );
                    if (label) {
                        label.setAttribute("for", newId);
                    }
                }

                input.name = `covered_early_procurement_${newItemNumber}`;
            });

            // Append the new card to the container
            projectItemsContainer.appendChild(newCard);

            // Re-initialize flatpickr on new inputs if flatpickr exists
            if (typeof flatpickr !== "undefined") {
                const newFlatpickrInputs = newCard.querySelectorAll(".flatpickr-date");
                newFlatpickrInputs.forEach((input) => {
                    input.id = ""; // Remove ID to avoid duplicates
                    flatpickr(input);
                });
            }
        });
    }

    // Listener for Total Amount & Removal (Event Delegation)
    if (projectItemsContainer) {
        projectItemsContainer.addEventListener("input", function (e) {
            if (e.target.classList.contains("estimated-budget-input")) {
                updateTotalAmount();
            }
        });

        projectItemsContainer.addEventListener("click", function (e) {
            // Check if either the button or the image inside it was clicked
            const trashBtn = e.target.closest(".remove-project-btn");
            if (trashBtn) {
                const cardToRemove = trashBtn.closest(".project-item-card");
                if (cardToRemove) {
                    cardToRemove.remove();
                    updateTotalAmount();
                    reindexItems();
                }
            }
        });
    }
});
