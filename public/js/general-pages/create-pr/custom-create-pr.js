$(document).ready(function() {
    // Custom toggle for card collapse using jQuery explicitly 
    $(document).on('click', '.collapse-toggle', function(e) {
        e.preventDefault();
        var targetCard = $(this).closest('.card-body').find('.pr-collapse-area');
        $(this).toggleClass('rotate-arrow');
        targetCard.slideToggle(300);
    });

    // Add Description
    $(document).on('click', '.add-description-btn', function() {
        var currentRow = $(this).closest('tr.pr-item-row');
        var descriptionRow = currentRow.next('.pr-description-row');
        descriptionRow.removeClass('d-none');
        // Ensure the body starts visible
        descriptionRow.find('.description-body').show();
        descriptionRow.find('.description-arrow').css('transform', 'rotate(180deg)');
    });

    // Remove Description
    $(document).on('click', '.remove-description-btn', function(e) {
        e.stopPropagation(); // Prevent toggle from firing
        var descriptionRow = $(this).closest('tr.pr-description-row');
        descriptionRow.find('textarea').val('');
        descriptionRow.addClass('d-none');
    });

    // Toggle Description (Minimize/Maximize) - 100% jQuery Solution
    $(document).on('click', '.toggle-description-action', function(e) {
        var container = $(this).closest('.custom-description-container');
        var body = container.find('.description-body');
        var arrow = container.find('.description-arrow');
        
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
        var firstDescRow = tbody.find('tr.pr-description-row').first();
        
        var newRow = firstRow.clone();
        var newDescRow = firstDescRow.clone();
        
        // Clear inputs in basic row
        newRow.find('input').val('');
        newRow.find('select').prop('selectedIndex', 0);
        newRow.find('.amount-display').text('₱ 0.00').attr('data-amount', 0);
        
        // Show remove button for new rows using visibility hidden so width translates accurately
        newRow.find('.remove-row-btn').css('visibility', 'visible');
        
        // Reset description state
        newDescRow.addClass('d-none');
        newDescRow.find('textarea').val('');
        newDescRow.find('.description-body').show();
        newDescRow.find('.description-arrow').css('transform', 'rotate(180deg)');
        
        tbody.append(newRow);
        tbody.append(newDescRow);
        updateTotals();
    });

    // Remove Row
    $(document).on('click', '.remove-row-btn', function() {
        var row = $(this).closest('tr.pr-item-row');
        var descriptionRow = row.next('.pr-description-row');
        row.remove();
        descriptionRow.remove();
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
