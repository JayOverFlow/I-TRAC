/**
 * Isolated logic for the Property Acknowledgement Receipt (PAR) partial.
 * Bound strictly within .par-container to avoid side-effects.
 */

$(document).ready(function() {
    // ─── 1. Flatpickr Calendar Initialization ─────────────────────────────────
    if (typeof flatpickr !== "undefined") {
        flatpickr(".par-container .flatpickr", {
            dateFormat: "Y-m-d",
            allowInput: true,
            disableMobile: "true"
        });
    }

    // ─── 2. Add Item Row (Scope-Locked to PAR Table) ──────────────────────────
    $(document).on('click', '.par-container .add-item-btn', function(e) {
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

        // Append the new row to the table body
        tbody.append(newRow);
    });

    // ─── 3. Remove Item Row (Scope-Locked to PAR Table) ───────────────────────
    $(document).on('click', '.par-container .remove-row-btn', function(e) {
        e.preventDefault();
        var tbody = $(this).closest('tbody');

        // Enforce the requirement that the table must always contain at least one row
        if (tbody.find('tr').length <= 1) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'warning',
                    title: 'Action Denied',
                    text: 'The Property Acknowledgement Receipt must contain at least one item row.',
                    confirmButtonColor: '#dc3545',
                });
            } else {
                alert('The Property Acknowledgement Receipt must contain at least one item row.');
            }
            return;
        }

        $(this).closest('tr').remove();
    });

    // ─── 4. Input Sanitization (Quantity & Amount) ────────────────────────────
    $(document).on('input', '.par-container .qty-input', function() {
        this.value = this.value.replace(/[^0-9]/g, '');
    });

    $(document).on('input', '.par-container .amount-input', function() {
        this.value = this.value.replace(/[^0-9.]/g, '');
    });
});
