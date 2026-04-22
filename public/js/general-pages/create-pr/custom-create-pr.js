$(document).ready(function() {
    // Custom toggle for card collapse using jQuery explicitly 
    $(document).on('click', '.collapse-toggle', function(e) {
        e.preventDefault();
        var targetCard = $(this).closest('.card-body').find('.pr-collapse-area');
        $(this).toggleClass('rotate-arrow');
        targetCard.slideToggle(300);
    });

    // Add specification
    $(document).on('click', '.add-specification-btn', function() {
        var currentRow = $(this).closest('tr.pr-item-row');
        var specificationRow = currentRow.next('.pr-specification-row');
        specificationRow.removeClass('d-none');
        // Ensure the body starts visible
        specificationRow.find('.specification-body').show();
        specificationRow.find('.specification-arrow').css('transform', 'rotate(180deg)');
    });

    // Remove specification
    $(document).on('click', '.remove-specification-btn', function(e) {
        e.stopPropagation(); // Prevent toggle from firing
        var specificationRow = $(this).closest('tr.pr-specification-row');
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

    // Add Item
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

        tbody.append(newRow);
        tbody.append(newDescRow);
        updateTotals();
    });

    // Remove Row
    $(document).on('click', '.remove-row-btn', function() {
        var row = $(this).closest('tr.pr-item-row');
        var specificationRow = row.next('.pr-specification-row');
        row.remove();
        specificationRow.remove();
        updateTotals();
    });

    // Calculate Amount
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
        $('#grand-total-amount').text('₱ ' + totalAmount.toLocaleString('en-US', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        }));
    }

    // Generate initial values
    updateTotals();
});

$(document).on('click', '.dropdown-item', function(e) {
    e.preventDefault();
    var selectedText = $(this).text().trim();
    var button = $(this).closest('.btn-group').find('.dropdown-toggle');
    button.find('span').text(selectedText);
});
