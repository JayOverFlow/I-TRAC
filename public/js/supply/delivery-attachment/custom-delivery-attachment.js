/**
 * Custom JavaScript for the Supply Delivery Attachment page.
 * Handles folder structure treeview selection and document view toggling.
 */

$(document).ready(function() {
    $('.document-node').on('click', function(e) {
        e.preventDefault();

        // Highlight selected node
        $('.document-node').removeClass('active-doc');
        $(this).addClass('active-doc');

        // Get target container ID
        var targetId = $(this).data('target');

        // Hide placeholder view card
        $('#placeholder-view-card').addClass('d-none').hide();

        // Hide all document view containers
        $('.document-view-container').addClass('d-none').hide();

        // Show targeted document view container
        $('#' + targetId).removeClass('d-none').show();
    });
});
