$(document).ready(function () {
    // ─── Card collapse toggle ─────────────────────────────────────────────
    $(document).on('click', '.collapse-toggle', function (e) {
        e.preventDefault();
        var targetCard = $(this).closest('.card-body').find('.pr-collapse-area');
        $(this).toggleClass('rotate-arrow');
        targetCard.slideToggle(300);
    });

    // ─── Specification: Add ───────────────────────────────────────────────
    $(document).on('click', '.add-specification-btn', function () {
        var currentRow = $(this).closest('tr.pr-item-row');
        var specificationRow = currentRow.next('.pr-specification-row');
        specificationRow.removeClass('d-none');
        specificationRow.find('.specification-body').show();
        specificationRow.find('.specification-arrow').css('transform', 'rotate(180deg)');
    });

    // ─── Specification: Remove ────────────────────────────────────────────
    $(document).on('click', '.remove-specification-btn', function (e) {
        e.stopPropagation();
        var specificationRow = $(this).closest('tr.pr-specification-row');
        specificationRow.find('textarea').val('');
        specificationRow.addClass('d-none');
    });

    // ─── Specification: Toggle (Minimize/Maximize) ────────────────────────
    $(document).on('click', '.toggle-specification-action', function (e) {
        var container = $(this).closest('.custom-specification-container');
        var body = container.find('.specification-body');
        var arrow = container.find('.specification-arrow');

        body.slideToggle(300, function () {
            if ($(this).is(':visible')) {
                arrow.css('transform', 'rotate(180deg)');
            } else {
                arrow.css('transform', 'rotate(0deg)');
            }
        });
    });

    // ─── Add Item ─────────────────────────────────────────────────────────
    $(document).on('click', '.add-item-btn', function (e) {
        e.preventDefault();
        var tbody = $(this).closest('.card').find('tbody');
        var firstRow = tbody.find('tr.pr-item-row').first();
        var firstDescRow = tbody.find('tr.pr-specification-row').first();
        var firstRemarksRow = tbody.find('tr.pr-remarks-row').first();

        var newRow = firstRow.clone();
        var newDescRow = firstDescRow.clone();
        var newRemarksRow = firstRemarksRow.clone();

        // Generate a unique index for new rows to prevent overwriting in POST
        var newIndex = 'new_' + Date.now() + Math.floor(Math.random() * 1000);

        // Update all field names with the new unique index
        newRow.find('[name*="items["]').each(function () {
            var name = $(this).attr('name');
            var newName = name.replace(/items\[\s*\d+\s*\]/, 'items[' + newIndex + ']');
            $(this).attr('name', newName);
        });

        newDescRow.find('[name*="items["]').each(function () {
            var name = $(this).attr('name');
            var newName = name.replace(/items\[\s*\d+\s*\]/, 'items[' + newIndex + ']');
            $(this).attr('name', newName);
        });

        newRemarksRow.find('[name*="items["]').each(function () {
            var name = $(this).attr('name');
            var newName = name.replace(/items\[\s*\d+\s*\]/, 'items[' + newIndex + ']');
            $(this).attr('name', newName);
        });

        // Clear inputs in basic row
        newRow.find('input').not('[name*="app_item_id"]').val('');
        newRow.find('input').attr('data-org-value', '');
        newRow.find('select').prop('selectedIndex', 0);
        newRow.find('.amount-display').text('₱ 0.00').attr('data-amount', 0);

        // Show remove button for new rows
        newRow.find('.remove-row-btn').css('visibility', 'visible');

        // Reset specification state
        newDescRow.addClass('d-none');
        newDescRow.find('textarea').val('').attr('data-org-value', '');
        newDescRow.find('.specification-body').show();
        newDescRow.find('.specification-arrow').css('transform', 'rotate(180deg)');

        // Reset remarks state
        newRemarksRow.addClass('d-none');
        newRemarksRow.find('textarea').val('').attr('data-org-value', '');

        // Clear error states on cloned rows
        newRow.find('.is-invalid').removeClass('is-invalid');
        newRow.find('.field-error').text('').addClass('d-none');
        newDescRow.find('.is-invalid').removeClass('is-invalid');
        newDescRow.find('.field-error').text('').addClass('d-none');

        tbody.append(newRow);
        tbody.append(newDescRow);
        tbody.append(newRemarksRow);
        updateTotals();
    });

    // ─── Remove Row ───────────────────────────────────────────────────────
    $(document).on('click', '.remove-row-btn', function () {
        var row = $(this).closest('tr.pr-item-row');
        var specificationRow = row.next('.pr-specification-row');
        var remarksRow = specificationRow.next('.pr-remarks-row');
        var tbody = row.closest('tbody');
        var remainingRows = tbody.find('tr.pr-item-row');

        if (remainingRows.length <= 1) {
            // Clear fields of the remaining one row
            row.find('input').not('[name*="app_item_id"]').val('');
            row.find('input').attr('data-org-value', '');
            row.find('.amount-display').text('₱ 0.00').attr('data-amount', 0);
            row.find('.is-invalid').removeClass('is-invalid');
            row.find('.field-error').text('').addClass('d-none');

            specificationRow.find('textarea').val('').attr('data-org-value', '');
            specificationRow.addClass('d-none');
            specificationRow.find('.is-invalid').removeClass('is-invalid');
            specificationRow.find('.field-error').text('').addClass('d-none');

            remarksRow.find('textarea').val('').attr('data-org-value', '');
            remarksRow.addClass('d-none');
        } else {
            // Remove the row
            row.remove();
            specificationRow.remove();
            remarksRow.remove();
        }
        updateTotals();
    });

    // ─── Calculate Amount ─────────────────────────────────────────────────
    $(document).on('input', '.qty-input, .cost-input', function () {
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
        $('.pr-card').each(function () {
            var cardTotal = 0;
            var rows = $(this).find('tr.pr-item-row');
            var itemCount = rows.length;

            $(this).find('.item-count').text(itemCount + (itemCount === 1 ? ' Item' : ' Items'));

            rows.find('.amount-display').each(function () {
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
        $('#pr-form .custom-specification-container').removeClass('is-invalid');
    }

    /**
     * Display field-level errors returned by the Laravel controller.
     * @param {Object} errors — shape: { "pr_section": ["msg"], "items.0.unit": ["msg"], ... }
     */
    function showFieldErrors(errors) {
        $.each(errors, function (key, messages) {
            var msg = messages[0];

            // Header fields: pr_section, pr_purpose, pr_no
            if (!key.startsWith('items.')) {
                var input = $('#pr-form [data-field="' + key + '"]');
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
            var input = $('#pr-form [name="' + nameAttr + '"]');
            if (!input.length) return;

            input.addClass('is-invalid');

            // For specification, find the span inside the spec row
            if (field === 'specification') {
                var specRow = input.closest('.pr-specification-row');
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
        var firstInvalid = $('#pr-form .is-invalid').first();
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
        toast.on('hidden.bs.toast', function () { toast.remove(); });
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

        $('.pr-card').each(function () {
            $(this).find('tr.pr-item-row').each(function () {
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

        // Validate remarks if this is the Head's edit view and the intent is submit/export
        var canHeadEdit = $('#pr-form').attr('data-can-head-edit') === 'true';
        if (canHeadEdit && intent === 'submit') {
            var remarksMissing = false;
            $('.pr-remarks-row').each(function () {
                // If this remarks row is visible, it means the item has modifications
                if (!$(this).hasClass('d-none')) {
                    var textarea = $(this).find('.remarks-input');
                    if (!textarea.val().trim()) {
                        remarksMissing = true;
                        textarea.addClass('is-invalid');
                        $(this).find('.field-error').text('Remarks are required for modified items.').removeClass('d-none');
                    }
                }
            });

            if (remarksMissing) {
                showToast('Remarks are required for all modified items.', 'error');
                // Scroll to the first error field
                var firstInvalid = $('#pr-form .is-invalid').first();
                if (firstInvalid.length) {
                    $('html, body').animate({
                        scrollTop: firstInvalid.offset().top - 150
                    }, 500);
                }
                return;
            }
        }

        var form = document.getElementById('pr-form');
        $('#pr-intent').val(intent);

        var formData = new FormData(form);

        // Disable buttons to prevent double-submission
        $('#submit-pr-btn, #export-pr-btn, #pr-form button[type="submit"]').prop('disabled', true);
        $('#form-loader-overlay').css('display', 'flex');

        fetch(actionUrl, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                'Accept': 'application/json'
            },
            body: formData
        })
            .then(function (response) {
                return response.json().then(function (data) {
                    return { status: response.status, ok: response.ok, data: data };
                });
            })
            .then(function (result) {
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
                            setTimeout(function () {
                                $iframe.remove();
                                window.location.href = result.data.redirect;
                            }, 5000);
                        } else {
                            setTimeout(function () {
                                $iframe.remove();
                                $('#form-loader-overlay').hide();
                            }, 10000);
                        }
                    } else if (result.data.redirect) {
                        window.location.href = result.data.redirect;
                    } else {
                        showToast(result.data.message || 'Saved!', 'success');
                        $('#form-loader-overlay').hide();
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
                    $('#form-loader-overlay').hide();
                    return;
                }

                // Other server errors (409, 500, etc.)
                showToast(result.data.message || 'Something went wrong.', 'error');
                $('#form-loader-overlay').hide();
            })
            .catch(function () {
                showToast('Network error. Check your connection.', 'error');
                $('#form-loader-overlay').hide();
            })
            .finally(function () {
                $('#submit-pr-btn, #export-pr-btn, #pr-form button[type="submit"]').prop('disabled', false);
            });
    }

    // ─── Submit button — strict validation ────────────────────────────────
    $(document).on('click', '#submit-pr-btn', function (e) {
        e.preventDefault();
        var url = $(this).data('url');
        if (url) {
            window.confirmAction({
                title: 'Complete Purchase Request?',
                text: 'Are you sure you want to mark this purchase request as complete?',
                icon: 'question',
                confirmButtonText: 'Yes, Complete',
                cancelButtonText: 'Cancel',
                onConfirm: function () {
                    submitPrForm('submit', url);
                }
            });
        }
    });

    // ─── Export button — strict validation and PDF trigger ────────────────
    $(document).on('click', '#export-pr-btn', function (e) {
        e.preventDefault();
        var url = $(this).data('url');
        if (url) {
            window.confirmAction({
                title: 'Export Purchase Request?',
                text: 'Are you sure you want to export this purchase request as a PDF?',
                icon: 'question',
                confirmButtonText: 'Yes, Export',
                cancelButtonText: 'Cancel',
                onConfirm: function () {
                    submitPrForm('submit', url);
                }
            });
        }
    });

    // ─── Save as Draft — prevent default, use AJAX ────────────────────────
    $('#pr-form').on('submit', function (e) {
        e.preventDefault();
        var form = this;
        window.confirmAction({
            title: 'Save as Draft?',
            text: 'Are you sure you want to save this purchase request as a draft?',
            icon: 'question',
            confirmButtonText: 'Yes, Save',
            cancelButtonText: 'Cancel',
            onConfirm: function () {
                submitPrForm('draft', form.action);
            }
        });
    });

    // ─── Cancel button logic ──────────────────────────────────────────────
    $(document).on('click', '#cancel-pr-btn', function () {
        const url = $(this).data('url');
        const form = $('#cancel-pr-form');
        if (url) {
            window.confirmAction({
                title: 'Cancel Purchase Request?',
                text: 'Are you sure you want to cancel this purchase request?',
                icon: 'warning',
                confirmButtonText: 'Yes, Cancel',
                cancelButtonText: 'No, Keep It',
                onConfirm: function () {
                    form.attr('action', url);
                    form.submit();
                }
            });
        }
    });

    // ─── Export PDF Again logic ───────────────────────────────────────────
    $(document).on('click', '#export-again-btn', function (e) {
        e.preventDefault();
        var url = $(this).attr('href');
        if (url) {
            window.confirmAction({
                title: 'Export PDF Again?',
                text: 'Are you sure you want to export this purchase request as a PDF again?',
                icon: 'question',
                confirmButtonText: 'Yes, Export',
                cancelButtonText: 'Cancel',
                onConfirm: function () {
                    $('#form-loader-overlay').css('display', 'flex');
                    window.location.href = url;
                    setTimeout(function() {
                        $('#form-loader-overlay').hide();
                    }, 5000);
                }
            });
        }
    });

    // ─── Stepper real-time polling ────────────────────────────────────────
    /**
     * Polls GET /create-pr/{task_id}/stepper-status every POLL_INTERVAL ms.
     * Surgically patches only the <li> elements whose state actually changed —
     * no full page reload, no DOM flicker.
     *
     * Behaviour:
     *  - Only runs on pages that render the stepper (#stepper-list).
     *  - Reads the polling URL from data-stepper-url (set by the blade).
     *  - Pauses automatically when the browser tab is hidden (visibilitychange)
     *    and fires an immediate catch-up poll when the user returns.
     *  - Stops itself permanently on 401 (session expired) or 5xx errors.
     */
    (function initStepperPoller() {
        var POLL_INTERVAL = 30000;          // milliseconds between polls
        var $list = $('#stepper-list');

        // Only activate on pages that actually render the stepper
        if (!$list.length) return;

        var pollUrl = $list.data('stepper-url');
        var timerId = null;
        var stopped = false;

        // ── SVG icons (must match the blade templates exactly) ────────────
        var CHECK_ICON =
            '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" ' +
            'stroke-width="3" stroke-linecap="round" stroke-linejoin="round" class="text-white">' +
            '<polyline points="20 6 9 17 4 12"></polyline></svg>';

        var PARTIAL_ICON =
            '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" ' +
            'stroke-width="3" stroke-linecap="round" stroke-linejoin="round" class="text-white">' +
            '<circle cx="12" cy="12" r="9"></circle>' +
            '<line x1="12" y1="3" x2="12" y2="21"></line></svg>';

        /**
         * Derives the CSS classes and icon SVG for a single step.
         * @param  {Object}  step      — one element from the JSON steps array
         * @param  {boolean} isLatest  — whether this step is the current active one
         * @returns {{ itemClass: string, circleClass: string, icon: string }}
         */
        function resolveClasses(step, isLatest) {
            if (isLatest && step.partial) {
                return {
                    itemClass: 'stepper-item latest partial',
                    circleClass: 'stepper-circle active-partial',
                    icon: PARTIAL_ICON
                };
            }
            if (isLatest) {
                return {
                    itemClass: 'stepper-item latest',
                    circleClass: 'stepper-circle active-latest',
                    icon: CHECK_ICON
                };
            }
            if (step.active) {
                return {
                    itemClass: 'stepper-item completed',
                    circleClass: 'stepper-circle active-historic',
                    icon: CHECK_ICON
                };
            }
            return {
                itemClass: 'stepper-item pending',
                circleClass: 'stepper-circle pending',
                icon: ''
            };
        }

        /**
         * Fetch fresh step data and surgically patch any changed <li> elements.
         */
        function poll() {
            fetch(pollUrl, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin'  // forward the Laravel session cookie
            })
                .then(function (res) {
                    // Stop polling permanently on auth / server errors
                    if (res.status === 401 || res.status >= 500) {
                        stopped = true;
                        clearInterval(timerId);
                        console.warn('[Stepper] Polling stopped — HTTP ' + res.status);
                        return null;
                    }
                    return res.json();
                })
                .then(function (data) {
                    if (!data || !Array.isArray(data.steps) || !data.steps.length) return;

                    var steps = data.steps;
                    var latestActiveIndex = data.latestActiveIndex;

                    steps.forEach(function (step, idx) {
                        var $li = $list.find('[data-index="' + idx + '"]');
                        if (!$li.length) return;

                        var isLatest = (idx === latestActiveIndex);
                        var cls = resolveClasses(step, isLatest);

                        // Patch <li> classes (preserve data-index attribute)
                        if ($li.attr('class') !== cls.itemClass) {
                            $li.attr('class', cls.itemClass).attr('data-index', idx);
                        }

                        // Patch the circle: class + inner SVG icon
                        var $circle = $li.find('[class*="stepper-circle"]').first();
                        if ($circle.attr('class') !== cls.circleClass) {
                            $circle.attr('class', cls.circleClass);
                        }
                        if ($circle.html().trim() !== cls.icon.trim()) {
                            $circle.html(cls.icon);
                        }

                        // Patch label text
                        var $label = $li.find('.stepper-label');
                        if ($label.text() !== step.label) {
                            $label.text(step.label);
                        }

                        // Patch date text
                        var $date = $li.find('.stepper-date');
                        var dateText = step.date || 'Pending';
                        if ($date.text() !== dateText) {
                            $date.text(dateText);
                        }

                        // Patch sub-steps if any
                        var $subContainer = $li.find('.stepper-sub-container');
                        if (step.sub_steps && step.sub_steps.length) {
                            var subHtml = '';
                            step.sub_steps.forEach(function (sub) {
                                var subCircleClass = 'pending';
                                var subStatusClass = 'status-pending';
                                var innerIcon = '';
                                if (sub.active) {
                                    if (sub.partial) {
                                        subCircleClass = 'active-partial';
                                        subStatusClass = 'status-partial';
                                        innerIcon = '<svg width="8" height="8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" class="text-white"><circle cx="12" cy="12" r="9"></circle><line x1="12" y1="3" x2="12" y2="21"></line></svg>';
                                    } else if (isLatest) {
                                        subCircleClass = 'active-latest';
                                        subStatusClass = 'status-active';
                                        innerIcon = '<svg width="8" height="8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" class="text-white"><polyline points="20 6 9 17 4 12"></polyline></svg>';
                                    } else {
                                        subCircleClass = 'active-historic';
                                        subStatusClass = 'status-active';
                                        innerIcon = '<svg width="8" height="8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" class="text-white"><polyline points="20 6 9 17 4 12"></polyline></svg>';
                                    }
                                }

                                subHtml += '<div class="stepper-sub-item">';
                                subHtml += '  <div class="stepper-sub-line"></div>';
                                subHtml += '  <div class="stepper-sub-circle ' + subCircleClass + '">' + innerIcon + '</div>';
                                subHtml += '  <span class="stepper-sub-title d-block fw-semibold" style="font-size: 0.8rem; margin-left: 0.25rem;">';
                                subHtml += '    ' + sub.prefix;
                                subHtml += '  </span>';
                                subHtml += '  <span class="stepper-sub-status d-block text-uppercase ' + subStatusClass + '" style="font-size: 0.7rem; font-weight: 600; margin-left: 0.25rem;">';
                                subHtml += '    ' + sub.label;
                                subHtml += '  </span>';
                                if (sub.date) {
                                    subHtml += '  <span class="stepper-sub-date d-block" style="font-size: 0.7rem; margin-left: 0.25rem;">' + sub.date + '</span>';
                                }
                                subHtml += '</div>';
                            });

                            if (!$subContainer.length) {
                                $li.find('.stepper-content').first().append('<div class="stepper-sub-container mt-2">' + subHtml + '</div>');
                            } else {
                                var cleanExisting = $subContainer.html().replace(/\s+/g, ' ').trim();
                                var cleanNew = subHtml.replace(/\s+/g, ' ').trim();
                                if (cleanExisting !== cleanNew) {
                                    $subContainer.html(subHtml);
                                }
                            }
                        } else {
                            if ($subContainer.length) {
                                $subContainer.remove();
                            }
                        }
                    });
                })
                .catch(function (err) {
                    // Network errors — log silently; will retry on the next interval tick
                    console.warn('[Stepper] Poll error:', err.message);
                });
        }

        // ── Start polling ─────────────────────────────────────────────────
        timerId = setInterval(poll, POLL_INTERVAL);

        // ── Visibility API: pause when tab is hidden, resume on return ────
        document.addEventListener('visibilitychange', function () {
            if (stopped) return;

            if (document.hidden) {
                clearInterval(timerId);
                timerId = null;
            } else {
                poll();                                     // immediate catch-up
                timerId = setInterval(poll, POLL_INTERVAL); // restart regular interval
            }
        });
    })();
    // ─── End stepper polling ──────────────────────────────────────────────

    // ─── PR Remarks on Head Edit Change Detection ─────────────────────────
    var canHeadEdit = $('#pr-form').attr('data-can-head-edit') === 'true';
    if (canHeadEdit) {
        function checkRowModification(itemRow) {
            var specRow = itemRow.next('.pr-specification-row');
            var remarksRow = specRow.next('.pr-remarks-row');
            
            if (!remarksRow.length) return;

            var isModified = false;

            // Check unit
            var unitInput = itemRow.find('[data-field="unit"]');
            if (unitInput.length && (unitInput.val() || '') !== (unitInput.attr('data-org-value') || '')) {
                isModified = true;
            }

            // Check description
            var descInput = itemRow.find('[data-field="description"]');
            if (descInput.length && (descInput.val() || '') !== (descInput.attr('data-org-value') || '')) {
                isModified = true;
            }

            // Check quantity
            var qtyInput = itemRow.find('[data-field="quantity"]');
            var currentQty = qtyInput.val() || '';
            var orgQty = qtyInput.attr('data-org-value') || '';
            if (qtyInput.length) {
                if (currentQty !== orgQty && (parseFloat(currentQty) !== parseFloat(orgQty))) {
                    if (currentQty !== '' || orgQty !== '') {
                        isModified = true;
                    }
                }
            }

            // Check cost
            var costInput = itemRow.find('[data-field="cost"]');
            var currentCost = costInput.val() || '';
            var orgCost = costInput.attr('data-org-value') || '';
            if (costInput.length) {
                if (currentCost !== orgCost && (parseFloat(currentCost) !== parseFloat(orgCost))) {
                    if (currentCost !== '' || orgCost !== '') {
                        isModified = true;
                    }
                }
            }

            // Check specification
            var specTextarea = specRow.find('[data-field="specification"]');
            if (specTextarea.length && (specTextarea.val() || '') !== (specTextarea.attr('data-org-value') || '')) {
                isModified = true;
            }

            if (isModified) {
                remarksRow.removeClass('d-none');
            } else {
                remarksRow.addClass('d-none');
                remarksRow.find('.remarks-input').val('');
            }
        }

        // Listen to changes on inputs in the item row and specification row
        $(document).on('input change', 'tr.pr-item-row input, tr.pr-specification-row textarea', function () {
            var input = $(this);
            var itemRow;
            if (input.closest('tr').hasClass('pr-item-row')) {
                itemRow = input.closest('tr');
            } else {
                itemRow = input.closest('tr').prev('tr.pr-item-row');
            }
            checkRowModification(itemRow);
        });

        // Handle when a specification is removed via the close button
        $(document).on('click', '.remove-specification-btn', function () {
            var specRow = $(this).closest('tr.pr-specification-row');
            var itemRow = specRow.prev('tr.pr-item-row');
            setTimeout(function () {
                checkRowModification(itemRow);
            }, 50);
        });

        // Handle the undo/revert action for modified items
        $(document).on('click', '.revert-item-btn', function (e) {
            e.preventDefault();
            var remarksRow = $(this).closest('.pr-remarks-row');
            var specRow = remarksRow.prev('.pr-specification-row');
            var itemRow = specRow.prev('tr.pr-item-row');

            // Revert all inputs in the item row to their original values and trigger input/change
            itemRow.find('input[data-org-value]').each(function () {
                var orgVal = $(this).attr('data-org-value') || '';
                $(this).val(orgVal).removeClass('is-invalid');
            });
            itemRow.find('.field-error').addClass('d-none').text('');

            // Revert the specification textarea to its original value
            var specTextarea = specRow.find('textarea[data-org-value]');
            if (specTextarea.length) {
                var specOrgVal = specTextarea.attr('data-org-value') || '';
                specTextarea.val(specOrgVal).removeClass('is-invalid');
                // If the original specification was empty, hide the specification body and collapse/reset the card arrow
                if (!specOrgVal) {
                    specRow.find('.specification-body').hide();
                    specRow.find('.specification-arrow').removeClass('open');
                } else {
                    specRow.find('.specification-body').show();
                    specRow.find('.specification-arrow').addClass('open');
                }
            }
            specRow.find('.field-error').addClass('d-none').text('');

            // Clear the remarks textarea, hide error state, and hide the remarks row
            var remarksTextarea = remarksRow.find('.remarks-input');
            remarksTextarea.val('').removeClass('is-invalid');
            remarksRow.find('.field-error').text('').addClass('d-none');
            remarksRow.addClass('d-none');

            // Recalculate amount and totals
            var qtyInput = itemRow.find('.qty-input');
            var costInput = itemRow.find('.cost-input');
            var amountInput = itemRow.find('.amount-input');
            
            var qty = parseFloat(qtyInput.val()) || 0;
            var cost = parseFloat(costInput.val()) || 0;
            var amount = qty * cost;
            amountInput.val(amount.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
            
            updateTotals();
        });
    }
});

