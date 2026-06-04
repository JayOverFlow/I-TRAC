/**
 * Isolated logic for the Inspection and Acceptance Report (IAR) partial.
 * Bound strictly within .iar-container to avoid side-effects.
 */

$(document).ready(function() {
    // ─── 1. Flatpickr Calendar Initialization ─────────────────────────────────
    if (typeof flatpickr !== "undefined") {
        flatpickr(".iar-container .flatpickr", {
            dateFormat: "Y-m-d",
            allowInput: true,
            disableMobile: "true"
        });
    }

    // ─── 2. Add Item Row (Scope-Locked to IAR Table) ──────────────────────────
    $(document).on('click', '.iar-container .add-item-btn', function(e) {
        e.preventDefault();
        
        var tbody = $(this).closest('.card').find('tbody');
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
        });

        // Append the new row to the table body
        tbody.append(newRow);
    });

    // ─── 3. Remove Item Row (Scope-Locked to IAR Table) ───────────────────────
    $(document).on('click', '.iar-container .remove-row-btn', function(e) {
        e.preventDefault();
        var tbody = $(this).closest('tbody');

        // Enforce the requirement that the table must always contain at least one row
        if (tbody.find('tr').length <= 1) {
            Swal.fire({
                icon: 'warning',
                title: 'Action Denied',
                text: 'The Inspection and Acceptance Report must contain at least one item row.',
                confirmButtonColor: '#dc3545',
            });
            return;
        }

        $(this).closest('tr').remove();
    });
});

