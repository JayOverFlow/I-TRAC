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
            let val = parseFloat(input.value);
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

    // Function to re-index item numbers and name attributes
    function reindexItems() {
        const projectCards = projectItemsContainer.querySelectorAll(".project-item-card");
        projectCards.forEach((card, index) => {
            // Update item number display
            const itemNumberSpan = card.querySelector(".item-number-span");
            if (itemNumberSpan) {
                itemNumberSpan.textContent = `Item ${index + 1}`;
            }

            // Update all input name attributes to use the correct index
            const inputs = card.querySelectorAll("input[name]");
            inputs.forEach((input) => {
                const currentName = input.getAttribute("name");
                // Replace items[X][field] with items[newIndex][field]
                const newName = currentName.replace(/items\[\d+\]/, `items[${index}]`);
                input.setAttribute("name", newName);
            });
        });
    }

    // Listener for Add Project Button
    if (addProjectBtn && projectItemsContainer) {
        addProjectBtn.addEventListener("click", function () {
            const projectCards =
                projectItemsContainer.querySelectorAll(".project-item-card");
            const newItemNumber = projectCards.length + 1;
            const newIndex = projectCards.length; // 0-based index for name attributes

            if (projectCards.length === 0) return;

            // Clone the first card (deep clone)
            const firstCard = projectCards[0];
            const newCard = firstCard.cloneNode(true);

            // Show the Trash button on cloned cards
            const trashBtn = newCard.querySelector(".remove-project-btn");
            if (trashBtn) {
                trashBtn.classList.remove("d-none");
            }

            // Update Item number text
            const itemNumberSpan = newCard.querySelector(".item-number-span");
            if (itemNumberSpan) {
                itemNumberSpan.textContent = `Item ${newItemNumber}`;
            }

            // Clear all text and number inputs
            const textInputs = newCard.querySelectorAll('input[type="text"], input[type="number"]');
            textInputs.forEach((input) => {
                input.value = "";
            });

            // Uncheck all radio buttons and update name attributes with new index
            const radioInputs = newCard.querySelectorAll('input[type="radio"]');
            radioInputs.forEach((input) => {
                input.checked = false;

                const oldId = input.id;
                if (oldId) {
                    const newId = `${oldId}-${newItemNumber}`;
                    input.id = newId;

                    const label = newCard.querySelector(`label[for="${oldId}"]`);
                    if (label) {
                        label.setAttribute("for", newId);
                    }
                }
            });

            // Update all input name attributes to use the new index
            const allInputs = newCard.querySelectorAll("input[name]");
            allInputs.forEach((input) => {
                const currentName = input.getAttribute("name");
                const newName = currentName.replace(/items\[\d+\]/, `items[${newIndex}]`);
                input.setAttribute("name", newName);
            });

            // Append the new card to the container
            projectItemsContainer.appendChild(newCard);

            // Re-initialize flatpickr on new inputs if flatpickr exists
            if (typeof flatpickr !== "undefined") {
                const newFlatpickrInputs = newCard.querySelectorAll(".flatpickr-date");
                newFlatpickrInputs.forEach((input) => {
                    // Remove any existing flatpickr instance data
                    if (input._flatpickr) {
                        input._flatpickr.destroy();
                    }
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
