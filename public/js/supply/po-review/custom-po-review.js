$(document).ready(function() {
    // Helper: format number to 2 decimal places
    function formatMoney(amount) {
        return parseFloat(amount).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    // Helper: extract amount from the text node (e.g. "1,234.50" -> 1234.50)
    function parseAmount(text) {
        return parseFloat(text.replace(/,/g, '')) || 0;
    }

    // Dynamic Toast Notification
    function showToast(message, type = 'success') {
        const toastContainer = $('.toast-container');
        if (!toastContainer.length) {
            $('body').append('<div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 9999;"></div>');
        }
        
        const toastId = 'toast-' + Date.now();
        const bgClass = type === 'success' ? 'bg-success' : 'bg-danger';
        const icon = type === 'success' ? 
            `<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-check-circle"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>` :
            `<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-alert-circle"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>`;

        const toastHtml = `
            <div id="${toastId}" class="toast align-items-center text-white ${bgClass} border-0 shadow-lg" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body d-flex align-items-center gap-2">
                        ${icon}
                        <div>${message}</div>
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>
        `;

        $('.toast-container').append(toastHtml);
        const toastEl = document.getElementById(toastId);
        const toast = new bootstrap.Toast(toastEl, { delay: 5000 });
        toast.show();
        
        toastEl.addEventListener('hidden.bs.toast', function () {
            $(toastEl).remove();
        });
    }

    // Update the selection count text
    function updateSelectionCount() {
        const checkedCount = $('#tbody-uncategorized input[type="checkbox"]:checked').length;
        
        // Find the text element. It's the h6 element before the 'Clear' link
        $('.po-card').first().find('h6.black-text').text(checkedCount + ' items selected');
        
        // Enable/disable Apply button
        const selectedCategory = $('#assign-category-select').val();
        if (checkedCount > 0 && selectedCategory) {
            $('#apply-btn').prop('disabled', false);
        } else {
            $('#apply-btn').prop('disabled', true);
        }
    }

    // Update category totals (count and amount)
    function updateCategoryTotals() {
        const categories = [
            { bodyId: 'tbody-supply-materials', countId: 'count-supply-materials', totalId: 'total-supply-materials' },
            { bodyId: 'tbody-semi-expendable', countId: 'count-semi-expendable', totalId: 'total-semi-expendable' },
            { bodyId: 'tbody-equipment', countId: 'count-equipment', totalId: 'total-equipment' },
            { bodyId: 'tbody-not-delivered', countId: 'count-not-delivered', totalId: 'total-not-delivered' }
        ];

        categories.forEach(cat => {
            let count = 0;
            let total = 0;
            $(`#${cat.bodyId} .po-item-row`).each(function() {
                count++;
                const amountText = $(this).find('td').eq(5).text(); // Index 5 is Amount column for categorized rows
                total += parseAmount(amountText);
            });
            $(`#${cat.countId}`).text(count + ' Item/s');
            $(`#${cat.totalId}`).text('₱ ' + formatMoney(total));
        });
    }

    // Uncategorized table Checkbox change
    $(document).on('change', '#tbody-uncategorized input[type="checkbox"]', function() {
        updateSelectionCount();
    });

    // Clear selection click
    $('.link-underline-danger').on('click', function(e) {
        e.preventDefault();
        $('#tbody-uncategorized input[type="checkbox"]').prop('checked', false);
        updateSelectionCount();
    });

    // Top "Assign category" dropdown change
    $('#assign-category-select').on('change', function() {
        updateSelectionCount();
    });

    // "Apply" button click
    $('#apply-btn').on('click', function() {
        const selectedCategory = $('#assign-category-select').val();
        if (!selectedCategory) return;

        let targetBodyId = '';
        if (selectedCategory === 'Supply and Materials') targetBodyId = 'tbody-supply-materials';
        else if (selectedCategory === 'Semi-Expendable') targetBodyId = 'tbody-semi-expendable';
        else if (selectedCategory === 'Equipment') targetBodyId = 'tbody-equipment';
        else if (selectedCategory === 'Not Delivered') targetBodyId = 'tbody-not-delivered';

        if (!targetBodyId) return;

        const targetTbody = $(`#${targetBodyId}`);
        const selectedItems = $('#tbody-uncategorized input[type="checkbox"]:checked');
        const itemCount = selectedItems.length;

        selectedItems.each(function() {
            const checkbox = $(this);
            const trItem = checkbox.closest('tr.po-item-row');
            const itemId = trItem.data('id');
            const trSpecs = $(`tr.po-specification-row[data-item-id="${itemId}"]`); 

            // Clean up any old distribution rows
            $(`.qty-distribution-header-row[data-item-id="${itemId}"]`).remove();
            $(`.qty-distribution-row[data-item-id="${itemId}"]`).remove();

            // Remove the checkbox td
            trItem.find('td:first').remove();

            // Adjust spec row colspan (from 3 to 2)
            trSpecs.find('td:first').attr('colspan', 2);

            // Set select value in the row and disable empty option
            const selectEl = trItem.find('.category-select');
            selectEl.find('option[value=""]').remove();
            selectEl.val(selectedCategory);

            // Add or remove action button and adjust spec colspan depending on category
            if (selectedCategory !== 'Not Delivered') {
                if (trItem.find('.assign-item-btn').length === 0) {
                    const dId   = trItem.data('id');
                    const dDesc = trItem.find('td').eq(2).text().trim();
                    const dQty  = trItem.find('td').eq(3).text().trim();
                    trItem.append(`
                        <td class="px-1 text-center">
                            <button type="button" class="btn border btn-white assign-item-btn" title="Assign Item"
                                data-item-id="${dId}"
                                data-item-desc="${dDesc}"
                                data-item-qty="${dQty}">
                                <img src="/img/Assign.svg" width="16" height="16" alt="Assign">
                            </button>
                        </td>
                    `);
                }
                trSpecs.find('td:last').attr('colspan', 5);
            } else {
                trItem.find('.assign-item-btn').closest('td').remove();
                trSpecs.find('td:last').attr('colspan', 4);
            }

            // Move to target table
            targetTbody.append(trItem);
            if (trSpecs.length) {
                targetTbody.append(trSpecs);
            }
        });

        // Show success feedback toast
        showToast(`Successfully categorized ${itemCount} item${itemCount > 1 ? 's' : ''} as ${selectedCategory}.`, 'success');

        // Reset
        $('#assign-category-select').val('');
        updateSelectionCount();
        updateCategoryTotals();

        // If declaration is already checked, re-evaluate or uncheck it if items are left
        if ($('#form-check-danger').is(':checked') && $('#tbody-uncategorized .po-item-row').length > 0) {
            $('#form-check-danger').prop('checked', false);
            $('#generate-btn').prop('disabled', true);
        }
    });

    // Row dropdown change (Individual categorization or moving between categories)
    $(document).on('change', '.category-select', function() {
        const select = $(this);
        const selectedCategory = select.val();
        const trItem = select.closest('tr.po-item-row');
        const itemId = trItem.data('id');
        const trSpecs = $(`tr.po-specification-row[data-item-id="${itemId}"]`);

        const currentTbodyId = trItem.closest('tbody').attr('id');
        let targetBodyId = '';
        if (selectedCategory === 'Supply and Materials') targetBodyId = 'tbody-supply-materials';
        else if (selectedCategory === 'Semi-Expendable') targetBodyId = 'tbody-semi-expendable';
        else if (selectedCategory === 'Equipment') targetBodyId = 'tbody-equipment';
        else if (selectedCategory === 'Not Delivered') targetBodyId = 'tbody-not-delivered';

        if (!targetBodyId) return;

        // If it's already in the target category, do nothing
        if (currentTbodyId === targetBodyId) return;

        // Clean up any old distribution rows
        $(`.qty-distribution-header-row[data-item-id="${itemId}"]`).remove();
        $(`.qty-distribution-row[data-item-id="${itemId}"]`).remove();

        const targetTbody = $(`#${targetBodyId}`);

        // Check if coming from uncategorized
        if (trItem.closest('tbody').attr('id') === 'tbody-uncategorized') {
            // Remove the checkbox td
            trItem.find('td:first').remove();

            // Adjust spec row colspan (from 3 to 2)
            trSpecs.find('td:first').attr('colspan', 2);

            // Remove empty option so they can't uncategorize
            select.find('option[value=""]').remove();
        }

        // Add or remove action button and adjust spec colspan depending on category
        if (selectedCategory !== 'Not Delivered') {
            if (trItem.find('.assign-item-btn').length === 0) {
                const dId   = trItem.data('id');
                const dDesc = trItem.find('td').eq(2).text().trim();
                const dQty  = trItem.find('td').eq(3).text().trim();
                trItem.append(`
                    <td class="px-1 text-center">
                        <button type="button" class="btn border btn-white assign-item-btn" title="Assign Item"
                            data-item-id="${dId}"
                            data-item-desc="${dDesc}"
                            data-item-qty="${dQty}">
                            <img src="/img/Assign.svg" width="16" height="16" alt="Assign">
                        </button>
                    </td>
                `);
            }
            trSpecs.find('td:last').attr('colspan', 5);
        } else {
            trItem.find('.assign-item-btn').closest('td').remove();
            trSpecs.find('td:last').attr('colspan', 4);
        }

        // Move to target table
        targetTbody.append(trItem);
        if (trSpecs.length) {
            targetTbody.append(trSpecs);
        }

        // Show success feedback toast
        showToast(`Successfully categorized item as ${selectedCategory}.`, 'success');

        updateSelectionCount();
        updateCategoryTotals();

        // If declaration is already checked, re-evaluate or uncheck it if items are left
        if ($('#form-check-danger').is(':checked') && $('#tbody-uncategorized .po-item-row').length > 0) {
            $('#form-check-danger').prop('checked', false);
            $('#generate-btn').prop('disabled', true);
        }
    });

    // Continue to generate button
    $('#form-check-danger').on('change', function() {
        if ($(this).is(':checked')) {
            // Check if there are still items in #tbody-uncategorized
            const uncategorizedCount = $('#tbody-uncategorized .po-item-row').length;
            if (uncategorizedCount > 0) {
                $(this).prop('checked', false);
                showToast('All items must be categorized before proceeding.', 'error');
                $('#generate-btn').prop('disabled', true);
                return;
            }
            $('#generate-btn').prop('disabled', false);
        } else {
            $('#generate-btn').prop('disabled', true);
        }
    });

    // Initialize counts on page load
    updateCategoryTotals();

    // ─── Assign Item to Departments (Supply and Materials only) ─────────────

    let currentItemId = null;

    // Recalculate total assigned; toggle check icon color and Assign button state
    function updateTotalAssigned() {
        const cap = parseInt($('#total-qty-cap').text(), 10) || 0;
        let total = 0;
        let allDeptsValid = true;

        $('#assign-dept-tbody .dept-row').each(function () {
            const qty = parseInt($(this).find('.dept-qty').val(), 10) || 0;
            total += qty;

            const name = $(this).find('.dept-name').val().trim();
            if (qty > 0 && name.length < 2) {
                allDeptsValid = false;
            }
        });
        $('#total-assigned-display').text(total);

        const fulfilled = (total === cap && cap > 0 && allDeptsValid);
        $('#confirm-assign-btn').prop('disabled', !fulfilled);

        if (fulfilled) {
            $('#assign-check-icon').attr('src', '/img/green-check.svg');
        } else {
            $('#assign-check-icon').attr('src', '/img/gray-check.svg');
        }
    }

    // Open modal — only wired to Supply and Materials assign buttons
    // Falls back to reading row cells so dynamically moved items also work
    $(document).on('click', '#tbody-supply-materials .assign-item-btn', function () {
        const btn = $(this);
        const row = btn.closest('tr.po-item-row');

        currentItemId = btn.data('item-id') || row.data('id');
        const desc    = btn.data('item-desc') || row.find('td').eq(2).text().trim();
        const qty     = parseInt(btn.data('item-qty') || row.find('td').eq(3).text().trim(), 10);

        $('#modal-item-desc').text(desc);
        $('#modal-item-qty').text(qty);
        $('#total-qty-cap').text(qty);

        // Clone the input row template, then empty the tbody
        const rowTemplate = $('#assign-dept-tbody .dept-row:first').clone();
        rowTemplate.find('input').val('');
        $('#assign-dept-tbody').empty();

        // Check for existing distribution rows in the table
        const existingRows = $(`.qty-distribution-row[data-item-id="${currentItemId}"]`);
        if (existingRows.length > 0) {
            existingRows.each(function () {
                const name = $(this).find('.qty-dept-name').text().trim();
                const qtyVal  = $(this).find('.qty-dept-qty').text().trim();
                
                const newRow = rowTemplate.clone();
                newRow.find('.dept-name').val(name);
                newRow.find('.dept-qty').val(qtyVal);
                $('#assign-dept-tbody').append(newRow);
            });
        } else {
            // Add a single empty row if no existing distributions
            $('#assign-dept-tbody').append(rowTemplate.clone());
        }

        updateTotalAssigned();

        new bootstrap.Modal(document.getElementById('assignDeptModal')).show();
    });

    // Add a department row
    $(document).on('click', '#add-dept-row-btn', function () {
        const clone = $('#assign-dept-tbody .dept-row:first').clone();
        clone.find('input').val('');
        $('#assign-dept-tbody').append(clone);
    });

    // Remove a department row (keep at least one)
    $(document).on('click', '.remove-dept-row-btn', function () {
        if ($('#assign-dept-tbody .dept-row').length > 1) {
            $(this).closest('.dept-row').remove();
            updateTotalAssigned();
        }
    });

    // Restrict quantity input to positive integers only (no letters, signs, or dots)
    $(document).on('keypress', '#assign-dept-tbody .dept-qty', function (e) {
        if (e.which < 48 || e.which > 57) {
            e.preventDefault();
        }
    });

    $(document).on('input', '#assign-dept-tbody .dept-qty', function () {
        this.value = this.value.replace(/[^0-9]/g, '');
        updateTotalAssigned();
    });

    $(document).on('input', '#assign-dept-tbody .dept-name', function () {
        updateTotalAssigned();
    });

    // Confirm Assign — render distribution summary below the item's spec rows
    $(document).on('click', '#confirm-assign-btn', function () {
        const distributions = [];
        $('#assign-dept-tbody .dept-row').each(function () {
            const name = $(this).find('.dept-name').val().trim();
            const qty  = parseInt($(this).find('.dept-qty').val(), 10) || 0;
            if (name.length >= 2 && qty > 0) distributions.push({ name, qty });
        });

        if (!distributions.length) return;

        // Replace any prior distribution for this item
        $(`.qty-distribution-header-row[data-item-id="${currentItemId}"]`).remove();
        $(`.qty-distribution-row[data-item-id="${currentItemId}"]`).remove();

        const rowsToInsert = [];

        // 1. Get header row from Blade template
        const headerTemplate = document.getElementById('qty-distribution-header-template');
        if (headerTemplate) {
            const headerClone = document.importNode(headerTemplate.content, true);
            $(headerClone).find('.qty-distribution-header-row').attr('data-item-id', currentItemId);
            rowsToInsert.push(headerClone);
        }

        // 2. Get distribution row template and clone for each department
        const rowTemplate = document.getElementById('qty-distribution-row-template');
        if (rowTemplate) {
            distributions.forEach(d => {
                const rowClone = document.importNode(rowTemplate.content, true);
                const tr = $(rowClone).find('.qty-distribution-row');
                tr.attr('data-item-id', currentItemId);
                tr.find('.qty-dept-name').text(d.name);
                tr.find('.qty-dept-qty').text(d.qty);
                rowsToInsert.push(rowClone);
            });
        }

        // Insert after last spec row, or directly after the item row
        const lastSpec = $(`.po-specification-row[data-item-id="${currentItemId}"]`).last();
        let insertAfterEl = lastSpec.length ? lastSpec : $(`.po-item-row[data-id="${currentItemId}"]`);

        // Insert all rows in order
        rowsToInsert.reverse().forEach(clone => {
            insertAfterEl.after(clone);
        });

        bootstrap.Modal.getInstance(document.getElementById('assignDeptModal')).hide();
        showToast('Quantity successfully distributed.', 'success');
    });

    // ─── Assign Item to Users (Semi-Expendables and Equipment) ─────────────

    // Recalculate total assigned for users modal
    function updateUserTotalAssigned() {
        const cap = parseInt($('#total-user-qty-cap').text(), 10) || 0;
        let total = 0;
        let allUsersSelected = true;

        $('#assign-user-tbody .user-row').each(function () {
            const qty = parseInt($(this).find('.user-qty').val(), 10) || 0;
            total += qty;

            const userSelected = $(this).find('.user-select').val();
            if (qty > 0 && !userSelected) {
                allUsersSelected = false;
            }
        });

        $('#total-user-assigned-display').text(total);

        const fulfilled = (total === cap && cap > 0 && allUsersSelected);
        $('#confirm-user-assign-btn').prop('disabled', !fulfilled);

        if (fulfilled) {
            $('#user-assign-check-icon').attr('src', '/img/green-check.svg');
        } else {
            $('#user-assign-check-icon').attr('src', '/img/gray-check.svg');
        }
    }

    // Open modal — wired to Semi-Expendables and Equipment assign buttons
    $(document).on('click', '#tbody-semi-expendable .assign-item-btn, #tbody-equipment .assign-item-btn', function () {
        const btn = $(this);
        const row = btn.closest('tr.po-item-row');

        currentItemId = btn.data('item-id') || row.data('id'); // Fix: Set currentItemId
        const desc    = btn.data('item-desc') || row.find('td').eq(2).text().trim();
        const qty     = parseInt(btn.data('item-qty') || row.find('td').eq(3).text().trim(), 10);

        $('#modal-user-item-desc').text(desc);
        $('#modal-user-item-qty').text(qty);
        $('#total-user-qty-cap').text(qty);

        // Clone the input row template, then empty the tbody
        const rowTemplate = $('#assign-user-tbody .user-row:first').clone();
        rowTemplate.find('input, select').val('');
        $('#assign-user-tbody').empty();

        // Check for existing distribution rows in the table
        const existingRows = $(`.qty-distribution-row[data-item-id="${currentItemId}"]`);
        if (existingRows.length > 0) {
            existingRows.each(function () {
                const userId = $(this).attr('data-user-id');
                const name = $(this).find('.qty-dept-name').text().trim();
                const qtyVal = $(this).find('.qty-dept-qty').text().trim();

                const newRow = rowTemplate.clone();
                if (userId) {
                    newRow.find('.user-select').val(userId);
                } else {
                    // Fallback to match option text if data-user-id is not set
                    newRow.find('.user-select option').each(function() {
                        if ($(this).text().trim() === name) {
                            $(this).prop('selected', true);
                            return false;
                        }
                    });
                }
                newRow.find('.user-qty').val(qtyVal);
                $('#assign-user-tbody').append(newRow);
            });
        } else {
            // Add a single empty row if no existing distributions
            $('#assign-user-tbody').append(rowTemplate.clone());
        }

        updateUserTotalAssigned();

        new bootstrap.Modal(document.getElementById('assignUserModal')).show();
    });

    // Add a user row by cloning the first row
    $(document).on('click', '#add-user-row-btn', function () {
        const clone = $('#assign-user-tbody .user-row:first').clone();
        clone.find('input, select').val('');
        $('#assign-user-tbody').append(clone);
    });

    // Remove a user row (keep at least one)
    $(document).on('click', '.remove-user-row-btn', function () {
        if ($('#assign-user-tbody .user-row').length > 1) {
            $(this).closest('.user-row').remove();
            updateUserTotalAssigned();
        }
    });

    // Restrict quantity input to positive integers only (no letters, signs, or dots)
    $(document).on('keypress', '#assign-user-tbody .user-qty', function (e) {
        if (e.which < 48 || e.which > 57) {
            e.preventDefault();
        }
    });

    $(document).on('input', '#assign-user-tbody .user-qty', function () {
        this.value = this.value.replace(/[^0-9]/g, '');
        updateUserTotalAssigned();
    });

    $(document).on('change', '#assign-user-tbody .user-select', function () {
        updateUserTotalAssigned();
    });

    // Confirm User Assign — render distribution summary below the item's spec rows
    $(document).on('click', '#confirm-user-assign-btn', function () {
        const distributions = [];
        $('#assign-user-tbody .user-row').each(function () {
            const selectEl = $(this).find('.user-select');
            const userId = selectEl.val();
            const name = selectEl.find('option:selected').text().trim();
            const qty  = parseInt($(this).find('.user-qty').val(), 10) || 0;
            if (userId && name && name !== 'Select User' && qty > 0) {
                distributions.push({ userId, name, qty });
            }
        });

        if (!distributions.length) return;

        // Replace any prior distribution for this item
        $(`.qty-distribution-header-row[data-item-id="${currentItemId}"]`).remove();
        $(`.qty-distribution-row[data-item-id="${currentItemId}"]`).remove();

        const rowsToInsert = [];

        // 1. Get header row from Blade template
        const headerTemplate = document.getElementById('qty-distribution-header-template');
        if (headerTemplate) {
            const headerClone = document.importNode(headerTemplate.content, true);
            $(headerClone).find('.qty-distribution-header-row').attr('data-item-id', currentItemId);
            rowsToInsert.push(headerClone);
        }

        // 2. Get distribution row template and clone for each user
        const rowTemplate = document.getElementById('qty-distribution-row-template');
        if (rowTemplate) {
            distributions.forEach(d => {
                const rowClone = document.importNode(rowTemplate.content, true);
                const tr = $(rowClone).find('.qty-distribution-row');
                tr.attr('data-item-id', currentItemId);
                tr.attr('data-user-id', d.userId); // Store selected User ID!
                tr.find('.qty-dept-name').text(d.name);
                tr.find('.qty-dept-qty').text(d.qty);
                rowsToInsert.push(rowClone);
            });
        }

        // Insert after last spec row, or directly after the item row
        const lastSpec = $(`.po-specification-row[data-item-id="${currentItemId}"]`).last();
        let insertAfterEl = lastSpec.length ? lastSpec : $(`.po-item-row[data-id="${currentItemId}"]`);

        // Insert all rows in order
        rowsToInsert.reverse().forEach(clone => {
            insertAfterEl.after(clone);
        });

        bootstrap.Modal.getInstance(document.getElementById('assignUserModal')).hide();
        showToast('Quantity successfully distributed.', 'success');
    });
});
