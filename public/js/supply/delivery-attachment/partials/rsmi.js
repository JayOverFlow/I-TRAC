document.addEventListener("DOMContentLoaded", function () {
    const tableBody = document.getElementById("rsmiItemsTbody");
    const addItemBtn = document.getElementById("rsmiAddItemBtn");

    // Initialize Flatpickr for date fields
    if (typeof flatpickr !== 'undefined') {
        flatpickr(".rsmi-container .flatpickr", {
            dateFormat: "Y-m-d",
            altInput: true,
            altFormat: "F j, Y",
            allowInput: true
        });
    }

    // Helper to format currency
    function formatCurrency(value) {
        return "₱ " + parseFloat(value).toLocaleString("en-US", {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
        });
    }

    // Calculate row amount
    function calculateAmount(row) {
        const qtyInput = row.querySelector(".qty-input");
        const unitCostInput = row.querySelector(".unit-cost-input");
        const totalDisplay = row.querySelector(".total-cost-display");

        if (qtyInput && unitCostInput && totalDisplay) {
            const qty = parseFloat(qtyInput.value) || 0;
            const unitCost = parseFloat(unitCostInput.value) || 0;
            const amount = qty * unitCost;

            totalDisplay.textContent = formatCurrency(amount);
            totalDisplay.setAttribute("data-amount", amount);
        }
    }

    // Event delegation for calculating amounts and removing rows
    if (tableBody) {
        tableBody.addEventListener("input", function (e) {
            if (e.target.classList.contains("qty-input") || e.target.classList.contains("unit-cost-input")) {
                const row = e.target.closest("tr");
                calculateAmount(row);
            }
        });

        tableBody.addEventListener("click", function (e) {
            const removeBtn = e.target.closest(".remove-row-btn");
            if (removeBtn) {
                const row = e.target.closest("tr");
                if (tableBody.querySelectorAll("tr").length > 1) {
                    row.remove();
                    updateRowIndices();
                }
            }
        });
    }

    // Function to update input names with correct indices
    function updateRowIndices() {
        if (!tableBody) return;
        const rows = tableBody.querySelectorAll("tr");
        rows.forEach((row, index) => {
            const inputs = row.querySelectorAll("input, select, textarea");
            inputs.forEach(input => {
                const name = input.getAttribute("name");
                if (name) {
                    const newName = name.replace(/items\[\d+\]/, `items[${index}]`);
                    input.setAttribute("name", newName);
                }
            });
        });
    }

    // Get remove icon src from existing row if possible
    let removeIconSrc = "/img/remove.svg"; // fallback
    if (tableBody) {
        const firstRemoveImg = tableBody.querySelector('.remove-row-btn img');
        if (firstRemoveImg && firstRemoveImg.getAttribute('src')) {
            removeIconSrc = firstRemoveImg.getAttribute('src');
        }
    }

    // Add new row
    if (addItemBtn && tableBody) {
        addItemBtn.addEventListener("click", function () {
            const rows = tableBody.querySelectorAll("tr");
            const newIndex = rows.length;

            const tr = document.createElement("tr");
            tr.innerHTML = `
                <td class="px-1">
                    <input type="hidden" name="items[${newIndex}][rsmi_items_id]" value="">
                    <input type="text" class="form-control form-control-sm text-center"
                        name="items[${newIndex}][ris_no]">
                </td>
                <td class="px-1">
                    <input type="text" class="form-control form-control-sm text-center"
                        name="items[${newIndex}][responsibility_center_code]">
                </td>
                <td class="px-1">
                    <input type="text" class="form-control form-control-sm text-center"
                        name="items[${newIndex}][stock_no]">
                </td>
                <td class="px-1">
                    <input type="text" class="form-control form-control-sm"
                        name="items[${newIndex}][item_description]">
                </td>
                <td class="px-1">
                    <input type="text" class="form-control form-control-sm text-center"
                        name="items[${newIndex}][unit]">
                </td>
                <td class="px-1">
                    <input type="text" class="form-control form-control-sm text-center qty-input"
                        name="items[${newIndex}][qty_issued]" oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                </td>
                <td class="px-1">
                    <input type="text" class="form-control form-control-sm text-center unit-cost-input"
                        name="items[${newIndex}][unit_cost]" data-field="unit_cost"
                        oninput="this.value = this.value.replace(/[^0-9.]/g, '')">
                </td>
                <td class="px-1 text-center">
                    <span class="total-cost-display fw-bold" data-amount="0">₱
                        0.00</span>
                </td>
                <td class="p-0">
                    <button type="button"
                        class="btn border-0 bg-transparent text-black fw-bold remove-row-btn p-0 ms-2">
                        <img src="${removeIconSrc}" alt="Remove">
                    </button>
                </td>
            `;
            tableBody.appendChild(tr);
            updateRowIndices();
        });
    }
});
