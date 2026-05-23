/**
 * Isolated logic for the Report of Semi-Expendable Property Issue (RSPI) partial.
 * Bound strictly within .rspi-container to avoid side-effects.
 */

$(document).ready(function() {
    // ─── 1. Flatpickr Calendar Initialization ─────────────────────────────────
    if (typeof flatpickr !== "undefined") {
        flatpickr(".rspi-container .flatpickr", {
            dateFormat: "Y-m-d",
            allowInput: true,
            disableMobile: "true"
        });
    }

    // ─── 2. Add Item Row (Scope-Locked to RSPI Table) ──────────────────────────
    $(document).on('click', '.rspi-container #rspiAddItemBtn', function(e) {
        e.preventDefault();
        
        var tbody = $('#rspiItemsTbody');
        var firstRow = tbody.find('tr').first();
        
        if (firstRow.length === 0) return;

        // Clone the first row as the template
        var newRow = firstRow.clone();
        var newIndex = Date.now();

        // Update all field names to avoid inputs sharing the same post name array key
        newRow.find('input').each(function() {
            var input = $(this);
            var name = input.attr('name');
            if (name) {
                var newName = name.replace(/items\[\s*\d+\s*\]/, 'items[' + newIndex + ']');
                input.attr('name', newName);
            }
            input.val('');

            // Clean up flatpickr classes/attributes if any, and re-initialize
            if (input.hasClass('flatpickr')) {
                input.removeClass('flatpickr-input active').removeAttr('readonly');
                if (typeof flatpickr !== "undefined") {
                    flatpickr(input[0], {
                        dateFormat: "Y-m-d",
                        allowInput: true,
                        disableMobile: "true"
                    });
                }
            }
        });

        // Reset the static amount display
        newRow.find('.total-cost-display').text('₱ 0.00').attr('data-amount', '0');

        // Append the new row to the table body
        tbody.append(newRow);
    });

    // ─── 3. Remove Item Row (Scope-Locked to RSPI Table) ───────────────────────
    $(document).on('click', '.rspi-container .remove-row-btn', function(e) {
        e.preventDefault();
        var tbody = $('#rspiItemsTbody');

        // Enforce the requirement that the table must always contain at least one row
        if (tbody.find('tr').length <= 1) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'warning',
                    title: 'Action Denied',
                    text: 'The Report of Semi-Expendable Property Issue must contain at least one item row.',
                    confirmButtonColor: '#dc3545',
                });
            } else {
                alert('The Report of Semi-Expendable Property Issue must contain at least one item row.');
            }
            return;
        }

        $(this).closest('tr').remove();
    });

    // ─── 4. Dynamic Total Cost Calculation ────────────────────────────────────
    $(document).on('input', '.rspi-container .qty-input, .rspi-container .unit-cost-input', function() {
        var row = $(this).closest('tr');
        var qtyStr = row.find('.qty-input').val() || "0";
        var costStr = row.find('.unit-cost-input').val() || "0";
        
        // Remove any non-numeric except dot just in case
        qtyStr = qtyStr.replace(/[^0-9]/g, '');
        costStr = costStr.replace(/[^0-9.]/g, '');

        var qty = parseInt(qtyStr, 10);
        var cost = parseFloat(costStr);

        if (isNaN(qty)) qty = 0;
        if (isNaN(cost)) cost = 0;

        var total = qty * cost;

        // Format to 2 decimal places with commas
        var formattedTotal = total.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

        row.find('.total-cost-display').text('₱ ' + formattedTotal).attr('data-amount', total.toFixed(2));
    });
});
