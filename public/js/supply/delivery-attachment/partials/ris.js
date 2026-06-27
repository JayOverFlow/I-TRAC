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
            disableMobile: "true",
            minDate: "today"
        });
    }

    // ─── 2. Add Item Row (Scope-Locked to RIS Table) ──────────────────────────
    $(document).on('click', '.ris-container .add-item-btn', function(e) {
        e.preventDefault();
        
        var tbody = $(this).closest('.card').find('tbody');
        var firstRow = tbody.find('tr.ris-item-row').first();
        var firstSpecRow = tbody.find('tr.specification-row').first();
        
        if (firstRow.length === 0) return;

        // Clone the item row
        var newRow = firstRow.clone();
        var newIndex = Date.now();

        // Update all field names and IDs with the new unique index
        newRow.find('input, textarea, select').each(function() {
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

        // Clone the specification row if it exists
        var newSpecRow = null;
        if (firstSpecRow.length > 0) {
            newSpecRow = firstSpecRow.clone();
            // Hide the new spec row and reset textarea
            newSpecRow.addClass('d-none');
            newSpecRow.find('.specification-body').css('display', 'none');
            newSpecRow.find('.specification-arrow').css('transform', '');
            newSpecRow.find('textarea').each(function() {
                var input = $(this);
                var name = input.attr('name');
                if (name) {
                    var newName = name.replace(/items\[\s*\d+\s*\]/, 'items[' + newIndex + ']');
                    input.attr('name', newName);
                }
                input.val('').removeClass('is-invalid');
            });
            newSpecRow.find('.field-error').each(function() {
                var span = $(this);
                var forAttr = span.attr('data-valmsg-for');
                if (forAttr) {
                    var newForAttr = forAttr.replace(/items\[\s*\d+\s*\]/, 'items[' + newIndex + ']');
                    span.attr('data-valmsg-for', newForAttr);
                }
                span.text('').addClass('d-none');
            });
        }

        // Append the new row and spec row to the table body
        tbody.append(newRow);
        if (newSpecRow) {
            tbody.append(newSpecRow);
        }
    });

    // ─── 3. Allow Radio Buttons to be Deselected (to reset to NULL) ───────────
    $(document).on('mousedown', '.ris-container input[type="radio"]', function() {
        var radio = $(this);
        radio.data('already-checked', radio.prop('checked'));
    });

    $(document).on('click', '.ris-container input[type="radio"]', function() {
        var radio = $(this);
        if (radio.data('already-checked')) {
            radio.prop('checked', false);
            radio.data('already-checked', false);
            radio.trigger('change');
        } else {
            var name = radio.attr('name');
            if (name) {
                $('input[name="' + name + '"]').data('already-checked', false);
            }
            radio.data('already-checked', true);
        }
    });

    // ─── 4. Remove Item Row (Scope-Locked to RIS Table) ───────────────────────
    $(document).on('click', '.ris-container .remove-row-btn', function(e) {
        e.preventDefault();
        var tbody = $(this).closest('tbody');

        // Enforce the requirement that the table must always contain at least one row
        if (tbody.find('tr.ris-item-row').length <= 1) {
            Swal.fire({
                icon: 'warning',
                title: 'Action Denied',
                text: 'The Requisition and Issue Slip must contain at least one item row.',
                confirmButtonColor: '#dc3545',
            });
            return;
        }

        var row = $(this).closest('tr');
        var specRow = row.next('.specification-row');
        row.remove();
        if (specRow.length) {
            specRow.remove();
        }
    });

    // ─── 5. Validation Helpers ────────────────────────────────────────────────
    function convertLaravelKeyToInputName(key) {
        // Convert e.g., "items.0.ris_quantity" to "items[0][ris_quantity]"
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

    function clearRisErrors(form) {
        form.find('.is-invalid').removeClass('is-invalid');
        form.find('.field-error').text('').addClass('d-none');
    }

    function showRisErrors(form, errors) {
        $.each(errors, function(key, messages) {
            var inputName = convertLaravelKeyToInputName(key);
            var inputElement = form.find('[name="' + inputName + '"]');
            if (inputElement.length) {
                inputElement.addClass('is-invalid');
                if (inputElement.hasClass('specification-textarea')) {
                    var specRow = inputElement.closest('tr.specification-row');
                    specRow.removeClass('d-none');
                    specRow.find('.specification-body').show();
                    specRow.find('.specification-arrow').css('transform', 'rotate(180deg)');
                }
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

    // ─── 6. AJAX Form Submission Handler ──────────────────────────────────────
    $(document).on('submit', '.ris-container form', function(e) {
        e.preventDefault();
        var form = $(this);
        var formData = new FormData(form[0]);
        var submitBtn = form.find('button[type="submit"]');
        var exportBtn = form.find('a.btn-dark-red');
        var isDownloading = false;

        // Disable submission triggers
        submitBtn.prop('disabled', true);
        exportBtn.addClass('disabled');

        // Show general form loader overlay
        $('#form-loader-overlay').css('display', 'flex');

        // Clear previous error messages
        clearRisErrors(form);

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
                    isDownloading = true;
                    setTimeout(function() {
                        window.location.href = result.data.download_pdf;
                        setTimeout(function() {
                            $('#form-loader-overlay').hide();
                        }, 5000);
                    }, 1000);
                }
                return;
            }

            if (result.status === 422 && result.data.errors) {
                // Validation error: render error feedback inline and show error toast
                showRisErrors(form, result.data.errors);
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
            if (!isDownloading) {
                $('#form-loader-overlay').hide();
            }
        });
    });
});
