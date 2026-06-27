$(document).ready(function() {
    if (typeof flatpickr !== "undefined") {
        flatpickr('.flatpickr-date', {
            minDate: "today",
            dateFormat: "Y-m-d"
        });
    }

    // ─── Card collapse toggle ─────────────────────────────────────────────
    $(document).on('click', '.collapse-toggle', function(e) {
        e.preventDefault();
        var targetCard = $(this).closest('.card-body').find('.po-collapse-area');
        $(this).toggleClass('rotate-arrow');
        targetCard.slideToggle(300);
    });

    // ─── Specification: Add ───────────────────────────────────────────────
    $(document).on('click', '.add-specification-btn', function() {
        var currentRow = $(this).closest('tr.po-item-row');
        var specificationRow = currentRow.next('.po-specification-row');
        specificationRow.removeClass('d-none');
        specificationRow.find('.specification-body').show();
        specificationRow.find('.specification-arrow').css('transform', 'rotate(180deg)');
    });

    // ─── Specification: Remove ────────────────────────────────────────────
    $(document).on('click', '.remove-specification-btn', function(e) {
        e.stopPropagation();
        var specificationRow = $(this).closest('tr.po-specification-row');
        specificationRow.find('textarea').val('');
        specificationRow.addClass('d-none');
    });

    // ─── Specification: Toggle (Minimize/Maximize) ────────────────────────
    $(document).on('click', '.toggle-specification-action', function(e) {
        var container = $(this).closest('.custom-specification-container');
        var body = container.find('.specification-body');
        var arrow = container.find('.specification-arrow');
        
        body.slideToggle(300, function() {
            if ($(this).is(':visible')) {
                arrow.css('transform', 'rotate(180deg)');
            } else {
                arrow.css('transform', 'rotate(0deg)');
            }
        });
    });

    // ─── Add Item ─────────────────────────────────────────────────────────
    $(document).on('click', '.add-item-btn', function(e) {
        e.preventDefault();
        var tbody = $('#tbody-po-items');
        var firstRow = tbody.find('tr.po-item-row').first();
        var firstDescRow = tbody.find('tr.po-specification-row').first();
        
        var newRow = firstRow.clone();
        var newDescRow = firstDescRow.clone();

        // Generate a unique index for new rows to prevent overwriting in POST
        var newIndex = 'new_' + Date.now() + Math.floor(Math.random() * 1000);

        // Update all field names with the new unique index
        newRow.find('[name*="items["]').each(function() {
            var name = $(this).attr('name');
            var newName = name.replace(/items\[[^\]]+\]/, 'items[' + newIndex + ']');
            $(this).attr('name', newName);
        });

        newDescRow.find('[name*="items["]').each(function() {
            var name = $(this).attr('name');
            var newName = name.replace(/items\[[^\]]+\]/, 'items[' + newIndex + ']');
            $(this).attr('name', newName);
        });

        // Clear inputs in basic row
        newRow.find('input').not('[name*="app_item_id"]').val('');
        newRow.find('select').prop('selectedIndex', 0);
        newRow.find('.amount-display').text('₱ 0.00').attr('data-amount', 0);

        // Show remove button for new rows (clones)
        newRow.find('.remove-row-btn').css('visibility', 'visible');

        // Reset specification state
        newDescRow.addClass('d-none');
        newDescRow.find('textarea').val('');
        newDescRow.find('.specification-body').show();
        newDescRow.find('.specification-arrow').css('transform', 'rotate(180deg)');

        // Clear error states on cloned rows
        newRow.find('.is-invalid').removeClass('is-invalid');
        newRow.find('.field-error').text('').addClass('d-none');
        newDescRow.find('.is-invalid').removeClass('is-invalid');
        newDescRow.find('.field-error').text('').addClass('d-none');

        tbody.append(newRow);
        tbody.append(newDescRow);
        
        manageAddItemsButtons();
        updateTotals();
    });

    // ─── Remove Row ───────────────────────────────────────────────────────
    $(document).on('click', '.remove-row-btn', function() {
        var row = $(this).closest('tr.po-item-row');
        var specificationRow = row.next('.po-specification-row');
        var tbodyId = row.closest('tbody').attr('id');
        
        row.remove();
        specificationRow.remove();

        // If it was the last row, add a fresh one
        if (tbodyId === 'tbody-po-items' && $('#tbody-po-items tr.po-item-row').length === 0) {
            $('.add-item-btn').first().click();
        }

        manageAddItemsButtons();
        updateTotals();
    });

    // ─── Manage visibility of remove buttons ──────────────────────────────
    function manageAddItemsButtons() {
        var addItemsRows = $('#tbody-po-items tr.po-item-row');
        if (addItemsRows.length === 1) {
            addItemsRows.find('.remove-row-btn').css('visibility', 'hidden');
        } else {
            addItemsRows.each(function(index) {
                if (index === 0) {
                    $(this).find('.remove-row-btn').css('visibility', 'hidden');
                } else {
                    $(this).find('.remove-row-btn').css('visibility', 'visible');
                }
            });
        }
    }

    // ─── Format TIN inputs (XXX-XXX-XXX-XXX) ──────────────────────────────
    $(document).on('input', 'input[name="po_tin"], input[name="po_tuptin"]', function() {
        let value = $(this).val().replace(/\D/g, ''); // Remove all non-digits
        if (value.length > 12) {
            value = value.substring(0, 12);
        }
        
        let formatted = '';
        if (value.length > 0) {
            formatted += value.substring(0, 3);
        }
        if (value.length > 3) {
            formatted += '-' + value.substring(3, 6);
        }
        if (value.length > 6) {
            formatted += '-' + value.substring(6, 9);
        }
        if (value.length > 9) {
            formatted += '-' + value.substring(9, 12);
        }
        
        $(this).val(formatted);
    });

    // ─── Calculate Amount ─────────────────────────────────────────────────
    $(document).on('input', '.qty-input, .cost-input', function() {
        var row = $(this).closest('tr.po-item-row');
        var qty = parseFloat(row.find('.qty-input').val()) || 0;
        var cost = parseFloat(row.find('.cost-input').val()) || 0;
        var amount = qty * cost;
        
        var display = row.find('.amount-display');
        display.text('₱ ' + amount.toLocaleString('en-US', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        }));
        display.attr('data-amount', amount);
        
        updateTotals();
    });

    // ─── Update Totals ────────────────────────────────────────────────────
    function updateTotals() {
        var totalAmount = 0;
        $('.po-card').each(function() {
            var cardTotal = 0;
            var rows = $(this).find('tr.po-item-row');
            var itemCount = rows.length;
            
            $(this).find('.item-count').text(itemCount + (itemCount === 1 ? ' Item' : ' Items'));

            rows.find('.amount-display').each(function() {
                var val = parseFloat($(this).attr('data-amount')) || 0;
                cardTotal += val;
            });
            totalAmount += cardTotal;
            $(this).find('.project-total-amount').text('₱ ' + cardTotal.toLocaleString('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }));
        });
        $('#grand-total-amount').text('₱ ' + totalAmount.toLocaleString('en-US', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        }));
        $('#po_total_amount_input').val(totalAmount);
    }

    // Generate initial values
    manageAddItemsButtons();
    updateTotals();

    // ─── Error helpers ────────────────────────────────────────────────────

    /**
     * Remove all error states from the form.
     */
    function clearAllErrors() {
        $('#po-form .field-error').text('').addClass('d-none');
        $('#po-form .is-invalid').removeClass('is-invalid');
        $('#po-form .custom-specification-container').removeClass('is-invalid');
    }

    /**
     * Display field-level errors returned by the Laravel controller.
     * @param {Object} errors — shape: { "po_supplier": ["msg"], "items.0.unit": ["msg"], ... }
     */
    function showFieldErrors(errors) {
        $.each(errors, function(key, messages) {
            var msg = messages[0];

            // Header fields: po_supplier, po_address, etc.
            if (!key.startsWith('items.')) {
                var input = $('#po-form [data-field="' + key + '"]');
                if (input.length) {
                    input.addClass('is-invalid');
                    input.closest('.col-8').find('.field-error').text(msg).removeClass('d-none');
                } else {
                    showToast(msg, 'error');
                }
                return;
            }

            // Item fields: items.item_0.unit → index=item_0, field=unit
            var parts = key.split('.');
            if (parts.length < 3) return;
            var itemIndex = parts[1];
            var field = parts[2];

            var nameAttr = 'items[' + itemIndex + '][' + field + ']';
            var input = $('#po-form [name="' + nameAttr + '"]');
            if (!input.length) return;

            input.addClass('is-invalid');

            // For specification, find the span inside the spec row
            if (field === 'specification') {
                var specRow = input.closest('.po-specification-row');
                specRow.find('.field-error').text(msg).removeClass('d-none');
                
                // Ensure the specification row is visible if there's an error
                specRow.removeClass('d-none');
                specRow.find('.specification-body').show();
                specRow.find('.specification-arrow').css('transform', 'rotate(180deg)');
                
                // Add invalid class to container for visual feedback
                specRow.find('.custom-specification-container').addClass('is-invalid');
                return;
            }

            // For other fields, find the error span within the same <td>
            var errorSpan = input.closest('td').find('.field-error');
            if (errorSpan.length) {
                errorSpan.text(msg).removeClass('d-none');
            }
        });

        // Scroll to the first error field
        var firstInvalid = $('#po-form .is-invalid').first();
        if (firstInvalid.length) {
            $('html, body').animate({
                scrollTop: firstInvalid.offset().top - 150
            }, 500);
        }
    }

    // ─── Toast helper ─────────────────────────────────────────────────────

    /**
     * Show a dynamic Bootstrap toast message.
     */
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

    // ─── AJAX form submission ─────────────────────────────────────────────

    /**
     * Submit the PO form via fetch with FormData.
     * @param {string} intent  — 'Done' or 'Draft'
     */
    function submitPoForm(intent) {
        clearAllErrors();

        var form = document.getElementById('po-form');
        $('#po_status').val(intent);

        var formData = new FormData(form);

        // Disable buttons to prevent double-submission
        $('#submit-po-btn, #draft-po-btn').prop('disabled', true);

        fetch(form.action, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                'Accept': 'application/json'
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
                // Success — redirect if URL provided, otherwise show toast
                if (result.data.redirect) {
                    window.location.href = result.data.redirect;
                } else {
                    showToast(result.data.message || 'Saved!', 'success');
                }
                return;
            }

            if (result.status === 422 && result.data.errors) {
                // Validation errors — show inline per field
                showFieldErrors(result.data.errors);
                showToast('Please fix the errors below.', 'error');
                return;
            }

            // Other server errors (409, 500, etc.)
            showToast(result.data.message || 'Something went wrong.', 'error');
        })
        .catch(function() {
            showToast('Network error. Check your connection.', 'error');
        })
        .finally(function() {
            $('#submit-po-btn, #draft-po-btn').prop('disabled', false);
        });
    }

    // ─── Done button — strict validation ──────────────────────────────────
    $(document).on('click', '#submit-po-btn', function(e) {
        e.preventDefault();
        window.confirmAction({
            title: 'Complete Purchase Order?',
            text: 'Are you sure you want to mark this purchase order as complete?',
            icon: 'question',
            confirmButtonText: 'Yes, Complete',
            cancelButtonText: 'Cancel',
            onConfirm: function() {
                submitPoForm('Done');
            }
        });
    });

    // ─── Save as Draft — lenient validation ───────────────────────────────
    $(document).on('click', '#draft-po-btn', function(e) {
        e.preventDefault();
        window.confirmAction({
            title: 'Save as Draft?',
            text: 'Are you sure you want to save this purchase order as a draft?',
            icon: 'question',
            confirmButtonText: 'Yes, Save',
            cancelButtonText: 'Cancel',
            onConfirm: function() {
                submitPoForm('Draft');
            }
        });
    });

    // ─── Export PDF ───────────────────────────────────────────────────────
    $(document).on('click', '#export-po-btn', function(e) {
        e.preventDefault();
        var url = $(this).attr('href');
        if (url) {
            window.confirmAction({
                title: 'Export Purchase Order?',
                text: 'Are you sure you want to export this purchase order as a PDF?',
                icon: 'question',
                confirmButtonText: 'Yes, Export',
                cancelButtonText: 'Cancel',
                onConfirm: function() {
                    $('#form-loader-overlay').css('display', 'flex');
                    window.location.href = url;
                    setTimeout(function() {
                        $('#form-loader-overlay').hide();
                    }, 5000);
                }
            });
        }
    });
});

$(document).on('click', '.btn-group .dropdown-item', function(e) {
    e.preventDefault();
    var selectedText = $(this).text().trim();
    var button = $(this).closest('.btn-group').find('.dropdown-toggle');
    button.find('span').text(selectedText);
});
