$(document).ready(function() {
    const csrf = $('meta[name="csrf-token"]').attr('content');

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
            { bodyId: 'tbody-equipment', countId: 'count-equipment', totalId: 'total-equipment' }
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

        if (!targetBodyId) return;

        const targetTbody = $(`#${targetBodyId}`);
        const selectedItems = $('#tbody-uncategorized input[type="checkbox"]:checked');
        const itemCount = selectedItems.length;

        selectedItems.each(function() {
            const checkbox = $(this);
            const trItem = checkbox.closest('tr.po-item-row');
            const itemId = trItem.data('id');
            const trSpecs = $(`tr.po-specification-row[data-item-id="${itemId}"]`); 

            // Remove the checkbox td
            trItem.find('td:first').remove();

            // Adjust spec row colspan (from 3 to 2)
            trSpecs.find('td:first').attr('colspan', 2);

            // Set select value in the row and disable empty option
            const selectEl = trItem.find('.category-select');
            selectEl.find('option[value=""]').remove();
            selectEl.val(selectedCategory);

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

        let targetBodyId = '';
        if (selectedCategory === 'Supply and Materials') targetBodyId = 'tbody-supply-materials';
        else if (selectedCategory === 'Semi-Expendable') targetBodyId = 'tbody-semi-expendable';
        else if (selectedCategory === 'Equipment') targetBodyId = 'tbody-equipment';

        if (!targetBodyId) return;

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
});
