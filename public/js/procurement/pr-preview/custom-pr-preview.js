$(document).ready(function () {
    // Collapse toggle for project title cards
    $(document).on('click', '.collapse-toggle', function (e) {
        e.preventDefault();
        var targetCard = $(this).closest('.card-body').find('.pr-collapse-area');
        $(this).toggleClass('rotate-arrow');
        targetCard.slideToggle(300);
    });
});
