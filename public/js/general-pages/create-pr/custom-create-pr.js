$(document).ready(function() {
    // Custom toggle for card collapse using jQuery explicitly 
    $(document).on('click', '.collapse-toggle', function(e) {
        e.preventDefault();
        var targetCard = $('#collapseCard1');
        $(this).toggleClass('rotate-arrow');
        targetCard.slideToggle(300);
    });


    // Add Description
    $(document).on('click', '.add-description-btn', function() {
        var currentRow = $(this).closest('tr.pr-item-row');
        var descriptionRow = currentRow.next('.pr-description-row');
        descriptionRow.removeClass('d-none');
    });

    // Remove Description
    $(document).on('click', '.remove-description-btn', function() {
        var descriptionRow = $(this).closest('tr.pr-description-row');
        descriptionRow.find('textarea').val('');
        descriptionRow.addClass('d-none');
    });

    // Add Item
    $(document).on('click', '.add-item-btn', function(e) {
        e.preventDefault();
        var tbody = $(this).closest('.card').find('tbody');
        var firstRow = tbody.find('tr.pr-item-row').first();
        var firstDescRow = tbody.find('tr.pr-description-row').first();
        
        var newRow = firstRow.clone();
        var newDescRow = firstDescRow.clone();
        
        newRow.find('input').val('');
        newRow.find('select').prop('selectedIndex', 0);
        newRow.find('.amount-display').text('₱ 0.00').attr('data-amount', 0);
        newDescRow.addClass('d-none').find('textarea').val('');
        
        tbody.append(newRow);
        tbody.append(newDescRow);
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

    // Generate initial values if they had any (will just be zero based on empty inputs)
    updateTotals();
});

$(document).on('click', '.dropdown-item', function(e) {
    e.preventDefault();
    var selectedText = $(this).text().trim();
    var button = $(this).closest('.btn-group').find('.dropdown-toggle');
    button.find('span').text(selectedText);
});
