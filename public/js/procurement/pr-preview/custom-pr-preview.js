$(document).ready(function () {
    // Collapse toggle for project title cards
    $(document).on('click', '.collapse-toggle', function (e) {
        e.preventDefault();
        var targetCard = $(this).closest('.card-body').find('.pr-collapse-area');
        $(this).toggleClass('rotate-arrow');
        targetCard.slideToggle(300);
    });

    // Switch toggle logic
    $(document).on('change', '#form-custom-switch-inner-label', function () {
        if ($(this).is(':checked')) {
            $(this).closest('.inner-label-toggle').addClass('show');
            $('#pr-items-container').hide();
            $('#po-items-container').fadeIn();
        } else {
            $(this).closest('.inner-label-toggle').removeClass('show');
            $('#po-items-container').hide();
            $('#pr-items-container').fadeIn();
        }
    });
});
