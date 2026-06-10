$(document).ready(function() {
    // ─── Card collapse toggle ─────────────────────────────────────────────
    $(document).on('click', '.collapse-toggle', function(e) {
        e.preventDefault();
        var targetCard = $(this).closest('.card-body').find('.pr-collapse-area');
        $(this).toggleClass('rotate-arrow');
        targetCard.slideToggle(300);
    });

    // ─── Specification: Add ───────────────────────────────────────────────
    $(document).on('click', '.add-specification-btn', function() {
        var currentRow = $(this).closest('tr.pr-item-row');
        var specificationRow = currentRow.next('.pr-specification-row');
        specificationRow.removeClass('d-none');
        specificationRow.find('.specification-body').show();
        specificationRow.find('.specification-arrow').css('transform', 'rotate(180deg)');
    });

    // ─── Specification: Remove ────────────────────────────────────────────
    $(document).on('click', '.remove-specification-btn', function(e) {
        e.stopPropagation();
        var specificationRow = $(this).closest('tr.pr-specification-row');
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
        var tbody = $(this).closest('.card').find('tbody');
        var firstRow = tbody.find('tr.pr-item-row').first();
        var firstDescRow = tbody.find('tr.pr-specification-row').first();
        
        var newRow = firstRow.clone();
        var newDescRow = firstDescRow.clone();

        // Generate a unique index for new rows to prevent overwriting in POST
        var newIndex = 'new_' + Date.now() + Math.floor(Math.random() * 1000);

        // Update all field names with the new unique index
        newRow.find('[name*="items["]').each(function() {
            var name = $(this).attr('name');
            var newName = name.replace(/items\[\s*\d+\s*\]/, 'items[' + newIndex + ']');
            $(this).attr('name', newName);
        });

        newDescRow.find('[name*="items["]').each(function() {
            var name = $(this).attr('name');
            var newName = name.replace(/items\[\s*\d+\s*\]/, 'items[' + newIndex + ']');
            $(this).attr('name', newName);
        });

        // Clear inputs in basic row
        newRow.find('input').not('[name*="app_item_id"]').val('');
        newRow.find('select').prop('selectedIndex', 0);
        newRow.find('.amount-display').text('₱ 0.00').attr('data-amount', 0);

        // Show remove button for new rows
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
        updateTotals();
    });

    // ─── Remove Row ───────────────────────────────────────────────────────
    $(document).on('click', '.remove-row-btn', function() {
        var row = $(this).closest('tr.pr-item-row');
        var specificationRow = row.next('.pr-specification-row');
        row.remove();
        specificationRow.remove();
        updateTotals();
    });

    // ─── Calculate Amount ─────────────────────────────────────────────────
    $(document).on('input', '.qty-input, .cost-input', function() {
        var row = $(this).closest('tr.pr-item-row');
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
        $('.pr-card').each(function() {
            var cardTotal = 0;
            var rows = $(this).find('tr.pr-item-row');
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
        
        var grandTotalEl = $('#grand-total-amount');
        grandTotalEl.text('₱ ' + totalAmount.toLocaleString('en-US', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        }));

        // Retrieve allocated budget and apply text-danger class if total exceeds it
        var allocatedBudget = parseFloat($('#allocated-budget-title').attr('data-budget')) || 0;
        if (totalAmount > allocatedBudget) {
            grandTotalEl.addClass('text-danger');
        } else {
            grandTotalEl.removeClass('text-danger');
        }
    }

    // Generate initial values
    updateTotals();

    // ─── Error helpers ────────────────────────────────────────────────────

    /**
     * Remove all error states from the form.
     */
    function clearAllErrors() {
        $('#pr-form .field-error').text('').addClass('d-none');
        $('#pr-form .is-invalid').removeClass('is-invalid');
    }

    /**
     * Display field-level errors returned by the Laravel controller.
     * @param {Object} errors — shape: { "pr_section": ["msg"], "items.0.unit": ["msg"], ... }
     */
    function showFieldErrors(errors) {
        var rows = $('#pr-form .pr-item-row');

        $.each(errors, function(key, messages) {
            var msg = messages[0];

            // Header fields: pr_section, pr_purpose, pr_no
            if (!key.startsWith('items.')) {
                var input = $('#pr-form [data-field="' + key + '"]');
                if (input.length) {
                    input.addClass('is-invalid');
                    input.closest('.col-8').find('.field-error').text(msg).removeClass('d-none');
                }
                return;
            }

            // Item fields: items.0.unit → index=0, field=unit
            var parts = key.split('.');
            if (parts.length < 3) return;
            var idx = parts[1];
            var field = parts[2];

            var row;
            if (/^\d+$/.test(idx)) {
                row = rows.eq(parseInt(idx, 10));
            } else {
                // Find row dynamically via the custom string key
                var inputEl = $('#pr-form [name^="items[' + idx + ']"]').first();
                row = inputEl.closest('tr.pr-item-row');
            }
            if (!row || !row.length) return;

            var input = row.find('[data-field="' + field + '"]');
            if (!input.length) return;

            input.addClass('is-invalid');

            // For specification, find the span inside the spec row
            if (field === 'specification') {
                var specRow = row.next('.pr-specification-row');
                specRow.find('.field-error').text(msg).removeClass('d-none');
                return;
            }

            // For other fields, find the error span within the same <td>
            var errorSpan = input.closest('td').find('.field-error');
            if (errorSpan.length) {
                errorSpan.text(msg).removeClass('d-none');
            }
        });
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
     * Submit the PR form via fetch with FormData.
     * @param {string} intent  — 'submit' or 'draft'
     * @param {string} actionUrl — the route URL to POST to
     */
    function submitPrForm(intent, actionUrl) {
        clearAllErrors();

        // Perform budget validation before AJAX submission
        var allocatedBudget = parseFloat($('#allocated-budget-title').attr('data-budget')) || 0;
        var totalAmount = 0;
        var costExceeded = false;

        $('.pr-card').each(function() {
            $(this).find('tr.pr-item-row').each(function() {
                var qty = parseFloat($(this).find('.qty-input').val()) || 0;
                var cost = parseFloat($(this).find('.cost-input').val()) || 0;
                totalAmount += qty * cost;

                if (cost > allocatedBudget) {
                    costExceeded = true;
                    $(this).find('.cost-input').addClass('is-invalid');
                }
            });
        });

        if (costExceeded) {
            showToast('A unit cost exceeds the allocated budget of PHP ' + allocatedBudget.toLocaleString('en-US', { minimumFractionDigits: 2 }), 'error');
            return;
        }

        if (totalAmount > allocatedBudget) {
            showToast('The total amount of the Purchase Request (PHP ' + totalAmount.toLocaleString('en-US', { minimumFractionDigits: 2 }) + ') exceeds the allocated budget of PHP ' + allocatedBudget.toLocaleString('en-US', { minimumFractionDigits: 2 }), 'error');
            return;
        }

        var form = document.getElementById('pr-form');
        $('#pr-intent').val(intent);

        var formData = new FormData(form);

        // Disable buttons to prevent double-submission
        $('#submit-pr-btn, #export-pr-btn, #pr-form button[type="submit"]').prop('disabled', true);

        fetch(actionUrl, {
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
                // Success — download file if download_url provided, then redirect/show toast
                if (result.data.download_url) {
                    // Trigger download via a hidden iframe so it does not get cancelled by the redirect
                    var $iframe = $('<iframe>', {
                        src: result.data.download_url,
                        style: 'display:none'
                    }).appendTo('body');

                    // Wait long enough for mPDF to generate the PDF before redirecting
                    // (mPDF Excel-to-PDF conversion typically takes 2-5 seconds)
                    if (result.data.redirect) {
                        setTimeout(function() {
                            $iframe.remove();
                            window.location.href = result.data.redirect;
                        }, 5000);
                    } else {
                        setTimeout(function() {
                            $iframe.remove();
                        }, 10000);
                    }
                } else if (result.data.redirect) {
                    window.location.href = result.data.redirect;
                } else {
                    showToast(result.data.message || 'Saved!', 'success');
                }
                return;
            }

            if (result.status === 422 && result.data.errors) {
                // Validation errors — show inline per field
                console.warn('PR Validation Errors:', result.data.errors);
                showFieldErrors(result.data.errors);
                if (result.data.errors.general_budget) {
                    showToast(result.data.errors.general_budget[0], 'error');
                } else {
                    showToast('Please fix the errors below.', 'error');
                }
                return;
            }

            // Other server errors (409, 500, etc.)
            showToast(result.data.message || 'Something went wrong.', 'error');
        })
        .catch(function() {
            showToast('Network error. Check your connection.', 'error');
        })
        .finally(function() {
            $('#submit-pr-btn, #export-pr-btn, #pr-form button[type="submit"]').prop('disabled', false);
        });
    }

    // ─── Submit button — strict validation ────────────────────────────────
    $(document).on('click', '#submit-pr-btn', function(e) {
        e.preventDefault();
        var url = $(this).data('url');
        if (url) {
            submitPrForm('submit', url);
        }
    });

    // ─── Export button — strict validation and PDF trigger ────────────────
    $(document).on('click', '#export-pr-btn', function(e) {
        e.preventDefault();
        var url = $(this).data('url');
        if (url) {
            submitPrForm('submit', url);
        }
    });

    // ─── Save as Draft — prevent default, use AJAX ────────────────────────
    $('#pr-form').on('submit', function(e) {
        e.preventDefault();
        submitPrForm('draft', this.action);
    });

    // ─── Cancel button logic ──────────────────────────────────────────────
    $(document).on('click', '#cancel-pr-btn', function() {
        const url = $(this).data('url');
        const form = $('#cancel-pr-form');
        if (url) {
            form.attr('action', url);
            form.submit();
        }
    });
});
