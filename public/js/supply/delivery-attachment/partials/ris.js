/**
 * Isolated logic for the Requisition and Issue Slip (RIS) partial.
 * Bound strictly within .ris-container to avoid side-effects.
 */

$(document).ready(function() {
    // ─── 1. Flatpickr Calendar Initialization ─────────────────────────────────
    if (typeof flatpickr !== "undefined") {
        flatpickr(".ris-container .flatpickr", {
            dateFormat: "Y-m-d",
            allowInput: true,
            disableMobile: "true"
        });
    }

    // ─── 2. Add Item Row (Scope-Locked to RIS Table) ──────────────────────────
    $(document).on('click', '.ris-container .add-item-btn', function(e) {
        e.preventDefault();
        
        var tbody = $(this).closest('.card').find('tbody');
        var firstRow = tbody.find('tr').first();
        
        if (firstRow.length === 0) return;

        // Clone the first row as the template
        var newRow = firstRow.clone();
        var newIndex = Date.now();

        // Update all field names and IDs with the new unique index
        newRow.find('input').each(function() {
            var input = $(this);
            var name = input.attr('name');
            if (name) {
                var newName = name.replace(/items\[\s*\d+\s*\]/, 'items[' + newIndex + ']');
                input.attr('name', newName);
            }

            // Update unique IDs for the availability radios
            var id = input.attr('id');
            if (id) {
                if (id.includes('yes')) {
                    input.attr('id', 'ris-available-yes-' + newIndex);
                } else if (id.includes('no')) {
                    input.attr('id', 'ris-available-no-' + newIndex);
                }
            }

            // Reset value or checked state
            if (input.attr('type') === 'radio') {
                input.prop('checked', false);
            } else {
                input.val('');
            }
        });

        // Append the new row to the table body
        tbody.append(newRow);
    });

    // ─── 3. Remove Item Row (Scope-Locked to RIS Table) ───────────────────────
    $(document).on('click', '.ris-container .remove-row-btn', function(e) {
        e.preventDefault();
        var tbody = $(this).closest('tbody');

        // Enforce the requirement that the table must always contain at least one row
        if (tbody.find('tr').length <= 1) {
            Swal.fire({
                icon: 'warning',
                title: 'Action Denied',
                text: 'The Requisition and Issue Slip must contain at least one item row.',
                confirmButtonColor: '#dc3545',
            });
            return;
        }

        $(this).closest('tr').remove();
    });
});
