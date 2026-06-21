/**
 * Isolated logic for the Report of Supplies and Materials Issued (RSMI) partial.
 * Bound strictly within .rsmi-container to avoid side-effects.
 */

$(document).ready(function() {
    // ─── 1. Flatpickr Calendar Initialization ─────────────────────────────────
    if (typeof flatpickr !== "undefined") {
        flatpickr(".rsmi-container .flatpickr", {
            dateFormat: "Y-m-d",
            altInput: true,
            altFormat: "F j, Y",
            allowInput: true,
            disableMobile: "true"
        });
    }

    // ─── 2. Calculation Helpers ───────────────────────────────────────────────
    function formatCurrency(value) {
        return "₱ " + parseFloat(value).toLocaleString("en-US", {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
        });
    }

    function calculateAmount(row) {
        var qtyInput = row.find(".qty-input");
        var unitCostInput = row.find(".unit-cost-input");
        var totalDisplay = row.find(".total-cost-display");

        if (qtyInput.length && unitCostInput.length && totalDisplay.length) {
            var qty = parseFloat(qtyInput.val()) || 0;
            var unitCost = parseFloat(unitCostInput.val()) || 0;
            var amount = qty * unitCost;

            totalDisplay.text(formatCurrency(amount));
            totalDisplay.attr("data-amount", amount);
        }
    }

    // ─── 3. Event Delegation for Calculations & Input Filters ────────────────
    $(document).on('input', '.rsmi-container .qty-input, .rsmi-container .unit-cost-input', function() {
        var row = $(this).closest('tr');
        calculateAmount(row);
    });

    $(document).on('input', '.rsmi-container .qty-input', function() {
        this.value = this.value.replace(/[^0-9]/g, '');
    });

    $(document).on('input', '.rsmi-container .unit-cost-input', function() {
        this.value = this.value.replace(/[^0-9.]/g, '');
    });

    // ─── 4. Add Item Row (Scope-Locked to RSMI Table) ──────────────────────────
    $(document).on('click', '#rsmiAddItemBtn', function(e) {
        e.preventDefault();
        
        var tbody = $('#rsmiItemsTbody');
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
            if (input.attr('type') !== 'hidden' || (input.attr('name') && input.attr('name').includes('rsmi_items_id'))) {
                input.val('');
            }
            input.removeClass('is-invalid');
        });

        // Also clean up error spans inside the cloned row
        newRow.find('.field-error').each(function() {
            var span = $(this);
            var forAttr = span.attr('data-valmsg-for');
            if (forAttr) {
                var newForAttr = forAttr.replace(/items\[\s*\d+\s*\]/, 'items[' + newIndex + ']');
                span.attr('data-valmsg-for', newForAttr);
            }
            span.text('').addClass('d-none');
        });

        // Reset the amount display
        var totalDisplay = newRow.find('.total-cost-display');
        if (totalDisplay.length) {
            totalDisplay.text('₱0.00');
            totalDisplay.attr('data-amount', '0');
        }

        // Append the new row to the table body
        tbody.append(newRow);
        
        // Re-apply indexing to be safe (in case order is important)
        updateRowIndices();
    });

    // ─── 5. Remove Item Row (Scope-Locked to RSMI Table) ───────────────────────
    $(document).on('click', '.rsmi-container .remove-row-btn', function(e) {
        e.preventDefault();
        var tbody = $('#rsmiItemsTbody');

        // Enforce the requirement that the table must always contain at least one row
        if (tbody.find('tr').length <= 1) {
            Swal.fire({
                icon: 'warning',
                title: 'Action Denied',
                text: 'The Report of Supplies and Materials Issued must contain at least one item row.',
                confirmButtonColor: '#dc3545',
            });
            return;
        }

        $(this).closest('tr').remove();
        updateRowIndices();
    });

    function updateRowIndices() {
        var tbody = $('#rsmiItemsTbody');
        if (!tbody.length) return;
        tbody.find('tr').each(function(index) {
            var row = $(this);
            row.find('input, select, textarea').each(function() {
                var input = $(this);
                var name = input.attr('name');
                if (name) {
                    var newName = name.replace(/items\[\s*\d+\s*\]/, 'items[' + index + ']');
                    input.attr('name', newName);
                }
            });
            row.find('.field-error').each(function() {
                var span = $(this);
                var forAttr = span.attr('data-valmsg-for');
                if (forAttr) {
                    var newForAttr = forAttr.replace(/items\[\s*\d+\s*\]/, 'items[' + index + ']');
                    span.attr('data-valmsg-for', newForAttr);
                }
            });
        });
    }

    // ─── 6. Validation Helpers ────────────────────────────────────────────────
    function convertLaravelKeyToInputName(key) {
        // Convert e.g., "items.0.rsmi_quantity" to "items[0][rsmi_quantity]"
        if (key.includes('.')) {
            var parts = key.split('.');
            var name = parts[0];
            for (var i = 1; i < parts.length; i++) {
                name += '[' + parts[i] + ']';
            }
            return name;
        }
        return key;
    }

    function showToast(message, type) {
        var bgClass = type === 'success' ? 'bg-success' : 'bg-danger';
        var toast = $(
            '<div class="toast align-items-center text-white ' + bgClass + ' border-0 shadow-lg" role="alert">' +
                '<div class="d-flex">' +
                    '<div class="toast-body">' + message + '</div>' +
                    '<button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>' +
                '</div>' +
            '</div>'
        );
        $('.toast-container').append(toast);
        var bsToast = new bootstrap.Toast(toast[0], { delay: 5000 });
        bsToast.show();
        toast.on('hidden.bs.toast', function() { toast.remove(); });
    }

    function clearRsmiErrors(form) {
        form.find('.is-invalid').removeClass('is-invalid');
        form.find('.field-error').text('').addClass('d-none');
    }

    function showRsmiErrors(form, errors) {
        $.each(errors, function(key, messages) {
            var inputName = convertLaravelKeyToInputName(key);
            var inputElement = form.find('[name="' + inputName + '"]');
            if (inputElement.length) {
                inputElement.addClass('is-invalid');
                var errorSpan = inputElement.siblings('.field-error');
                if (errorSpan.length) {
                    errorSpan.text(messages[0]).removeClass('d-none');
                }
            }
        });

        // Scroll to the first invalid input
        var firstInvalid = form.find('.is-invalid').first();
        if (firstInvalid.length) {
            $('html, body').animate({
                scrollTop: firstInvalid.offset().top - 150
            }, 500);
        }
    }

    // ─── 7. AJAX Form Submission Handler ──────────────────────────────────────
    $(document).on('submit', '.rsmi-container form', function(e) {
        e.preventDefault();
        var form = $(this);
        var formData = new FormData(form[0]);
        var submitBtn = form.find('button[type="submit"]');
        var exportBtn = form.find('a.btn-dark-red');

        // Disable submission triggers
        submitBtn.prop('disabled', true);
        exportBtn.addClass('disabled');

        // Show general form loader overlay
        $('#form-loader-overlay').css('display', 'flex');

        // Clear previous error messages
        clearRsmiErrors(form);

        fetch(form.attr('action'), {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': form.find('input[name="_token"]').val()
            },
            body: formData
        })
        .then(function(response) {
            return response.json().then(function(data) {
                return { status: response.status, ok: response.ok, data: data };
            });
        })
        .then(function(result) {
            if (result.ok && result.data.success) {
                // Success: Toast feedback and trigger pdf download if requested
                showToast(result.data.message, 'success');
                if (result.data.download_pdf) {
                    setTimeout(function() {
                        window.location.href = result.data.download_pdf;
                    }, 1000);
                }
                return;
            }

            if (result.status === 422 && result.data.errors) {
                // Validation error: render error feedback inline and show error toast
                showRsmiErrors(form, result.data.errors);
                showToast('Please check and correct the highlighted fields.', 'error');
                return;
            }

            // General/server error
            showToast(result.data.message || 'An unexpected error occurred while saving.', 'error');
        })
        .catch(function() {
            showToast('Could not connect to the server. Please check your network connection.', 'error');
        })
        .finally(function() {
            // Re-enable triggers and hide overlay loader
            submitBtn.prop('disabled', false);
            exportBtn.removeClass('disabled');
            $('#form-loader-overlay').hide();
        });
    });
});
