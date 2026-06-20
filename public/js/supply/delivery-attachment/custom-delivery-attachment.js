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

    // Helper to get document type name
    function getDocumentTypeName(container) {
        if (container.hasClass('iar-container')) return 'Inspection and Acceptance Report';
        if (container.hasClass('ics-container')) return 'Inventory Custodian Slip';
        if (container.hasClass('par-container')) return 'Property Acknowledgement Receipt';
        if (container.hasClass('ris-container')) return 'Requisition and Issue Slip';
        if (container.hasClass('rsmi-container')) return 'Report of Supplies and Materials Issued';
        if (container.hasClass('rspi-container')) return 'Report of Semi-Expendable Property Issued';
        return 'document';
    }

    // Confirmation Alert for Save as Draft
    $(document).on('click', '.iar-container button[type="submit"], .ics-container button[type="submit"], .par-container button[type="submit"], .ris-container button[type="submit"], .rsmi-container button[type="submit"], .rspi-container button[type="submit"]', function(e) {
        e.preventDefault();
        var form = $(this).closest('form');
        var container = $(this).closest('.document-view-container');
        var docName = getDocumentTypeName(container);

        window.confirmAction({
            title: 'Save as Draft?',
            text: 'Are you sure you want to save this ' + docName + ' as draft?',
            icon: 'question',
            confirmButtonText: 'Yes, Save',
            cancelButtonText: 'Cancel',
            onConfirm: function() {
                form.find('.export-pdf-flag').val('0');
                form.submit();
            }
        });
    });

    // Confirmation Alert for Export as PDF
    $(document).on('click', '.iar-container a.btn-dark-red, .ics-container a.btn-dark-red, .par-container a.btn-dark-red, .ris-container a.btn-dark-red, .rsmi-container a.btn-dark-red, .rspi-container a.btn-dark-red', function(e) {
        e.preventDefault();
        var form = $(this).closest('form');
        var container = $(this).closest('.document-view-container');
        var docName = getDocumentTypeName(container);

        window.confirmAction({
            title: 'Export as PDF?',
            text: 'Are you sure you want to save and export this ' + docName + ' as a PDF?',
            icon: 'question',
            confirmButtonText: 'Yes, Export',
            cancelButtonText: 'Cancel',
            onConfirm: function() {
                form.find('.export-pdf-flag').val('1');
                form.submit();
            }
        });
    });

    // Show loading overlay when form is submitted
    $(document).on('submit', '.document-view-container form', function() {
        $('#form-loader-overlay').css('display', 'flex');
    });

    // Automatically trigger PDF download if session redirect is present
    var downloadTrigger = $('#download-pdf-trigger');
    if (downloadTrigger.length) {
        var downloadUrl = downloadTrigger.data('url');
        if (downloadUrl) {
            var triggerDownload = function() {
                window.location.href = downloadUrl;
            };

            if (document.readyState === 'complete') {
                setTimeout(triggerDownload, 500);
            } else {
                $(window).on('load', function() {
                    setTimeout(triggerDownload, 500);
                });
            }
        }
    }
});
