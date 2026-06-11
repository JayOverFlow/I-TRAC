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

    // Automatically click and expand the folder for active document on page load/redirect
    var activeDoc = $('#treeviewFolderStructureEx').data('active-document');
    if (activeDoc) {
        var docNode = $('[data-target="' + activeDoc + '"]');
        if (docNode.length) {
            docNode.click();
            // Also expand parent folder if collapsed
            var folderCollapse = docNode.closest('.treeview-collapse');
            if (folderCollapse.length && !folderCollapse.hasClass('show')) {
                var collapsibleTrigger = $('[data-bs-target="#' + folderCollapse.attr('id') + '"]');
                collapsibleTrigger.removeClass('collapsed').attr('aria-expanded', 'true');
                folderCollapse.addClass('show');
            }
        }
    }
});
