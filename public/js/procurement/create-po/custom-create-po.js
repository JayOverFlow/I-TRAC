$(document).ready(function() {
    if (typeof flatpickr !== "undefined") {
        flatpickr('.flatpickr-date');
    }

    function showJsToast(message) {
        $('#jsValidationToastMessage').text(message);
        var toastEl = document.getElementById('jsValidationToast');
        var toast = new bootstrap.Toast(toastEl, { delay: 5000 });
        toast.show();
    }

    // Custom toggle for card collapse using jQuery explicitly 
    $(document).on('click', '.collapse-toggle', function(e) {
        e.preventDefault();
        var targetCard = $(this).closest('.card-body').find('.po-collapse-area');
        $(this).toggleClass('rotate-arrow');
        targetCard.slideToggle(300);
    });

    // Add specification
    $(document).on('click', '.add-specification-btn', function() {
        var currentRow = $(this).closest('tr.po-item-row');
        var specificationRow = currentRow.next('.po-specification-row');
        specificationRow.removeClass('d-none');
        // Ensure the body starts visible
        specificationRow.find('.specification-body').show();
        specificationRow.find('.specification-arrow').css('transform', 'rotate(180deg)');
    });

    // Remove specification
    $(document).on('click', '.remove-specification-btn', function(e) {
        e.stopPropagation(); // Prevent toggle from firing
        var specificationRow = $(this).closest('tr.po-specification-row');
        specificationRow.find('textarea').val('');
        specificationRow.addClass('d-none');
    });

    // Toggle specification (Minimize/Maximize) - 100% jQuery Solution
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

    // Add Item (Specifically for Add Items card)
    $(document).on('click', '.add-item-btn', function(e) {
        e.preventDefault();
        var tbody = $('#tbody-add-items');
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

        tbody.append(newRow);
        tbody.append(newDescRow);
        
        manageAddItemsButtons();
        updateTotals();
    });

    // Dynamic Category Sorting
    $(document).on('change', '.category-select', function() {
        const category = $(this).val();
        const row = $(this).closest('tr.po-item-row');
        
        // Validation: Stock, Unit, Description, Qty must not be empty
        const stock = row.find('.stock-input').val()?.trim();
        const unit = row.find('.unit-select').val();
        const description = row.find('.description-input').val()?.trim();
        const qty = row.find('.qty-input').val()?.trim();

        if (category && category !== "" && (!stock || !unit || !description || !qty)) {
            showJsToast("Please fill in Stock, Unit, Item Description, and Qty before selecting a category.");
            $(this).val(""); // Reset to "Select"
            return;
        }

        const specRow = row.next('.po-specification-row');
        const isInAddItems = row.closest('tbody').attr('id') === 'tbody-add-items';
        const isOnlyRowInAddItems = $('#tbody-add-items tr.po-item-row').length === 1;

        let targetTbody = '';
        if (category === "supply_and_materials") targetTbody = '#tbody-supply';
        else if (category === "semi-expendable") targetTbody = '#tbody-semi-expendable';
        else if (category === "equipment") targetTbody = '#tbody-equipment';
        else targetTbody = '#tbody-add-items';

        if (targetTbody) {
            // If it's the only row in Add Items, clone it BEFORE moving it
            if (isInAddItems && isOnlyRowInAddItems && targetTbody !== '#tbody-add-items') {
                $('.add-item-btn').first().click();
            }

            // Move the row and its specification to the target table
            $(targetTbody).append(row).append(specRow);
            
            // If it moved to a categorized card, always show the remove button
            if (targetTbody !== '#tbody-add-items') {
                row.find('.remove-row-btn').css('visibility', 'visible');
            }
            
            manageAddItemsButtons();
            updateTotals();
        }
    });

    // Remove Row
    $(document).on('click', '.remove-row-btn', function() {
        var row = $(this).closest('tr.po-item-row');
        var specificationRow = row.next('.po-specification-row');
        var tbodyId = row.closest('tbody').attr('id');
        
        row.remove();
        specificationRow.remove();

        // If it was the last row in Add Items, add a fresh one
        if (tbodyId === 'tbody-add-items' && $('#tbody-add-items tr.po-item-row').length === 0) {
            $('.add-item-btn').first().click();
        }

        manageAddItemsButtons();
        updateTotals();
    });

    // Manage visibility of remove buttons in Add Items card
    function manageAddItemsButtons() {
        var addItemsRows = $('#tbody-add-items tr.po-item-row');
        if (addItemsRows.length === 1) {
            addItemsRows.find('.remove-row-btn').css('visibility', 'hidden');
        } else {
            // Only hide the first one if you want the "initial" row to be protected
            // Or just hide if count is 1. Let's hide the first one as per requirement.
            addItemsRows.each(function(index) {
                if (index === 0) {
                    $(this).find('.remove-row-btn').css('visibility', 'hidden');
                } else {
                    $(this).find('.remove-row-btn').css('visibility', 'visible');
                }
            });
        }
    }

    // Calculate Amount
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

    function updateTotals() {
        var totalAmount = 0;
        $('.po-card').each(function() {
            var cardTotal = 0;
            var rows = $(this).find('tr.po-item-row');
            var itemCount = rows.length;
            
            // Adjust count logic for Add Items card specifically if it has a blank row
            // But let's keep it simple: count all rows in the card
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

    // Submit button logic (Done)
    $(document).on('click', '#submit-po-btn', function() {
        $('#po_status').val('Submitted');
        const form = $('#po-form');
        form.submit();
    });

    // Cancel button logic
    $(document).on('click', '#cancel-po-btn', function() {
        const url = $(this).data('url');
        const form = $('#cancel-po-form');
        if (url) {
            form.attr('action', url);
            form.submit();
        }
    });
});

$(document).on('click', '.dropdown-item', function(e) {
    e.preventDefault();
    var selectedText = $(this).text().trim();
    var button = $(this).closest('.btn-group').find('.dropdown-toggle');
    button.find('span').text(selectedText);
});
