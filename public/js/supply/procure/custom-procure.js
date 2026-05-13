$(document).ready(function() {
    const csrf = $('meta[name="csrf-token"]').attr('content');

    /**
     * Initializes a DataTable with a custom search/retrieve bar.
     * 
     * @param {string} tableId - The ID of the table element.
     * @param {string} searchBoxId - The ID for the custom search box container.
     * @param {string} inputName - The name attribute for the input field.
     * @param {string} btnId - The ID for the retrieve button.
     * @param {RegExp} regex - The regex pattern to enable the retrieve button.
     */
    function initTable(tableId, searchBoxId, inputName, btnId, regex) {
        const tableEl = $(tableId);
        if (!tableEl.length) return;

        const route = tableEl.data('route');
        
        const table = tableEl.DataTable({
            "dom": "<'dt--top-section'<'row'<'col-12 col-sm-6 d-flex justify-content-sm-start align-items-center'l><'col-12 col-sm-6 d-flex justify-content-sm-end justify-content-center mt-sm-0 mt-3'<'#" + searchBoxId + "'>>>>" +
                "<'table-responsive'tr>" +
                "<'dt--bottom-section d-sm-flex justify-content-sm-between text-center'<'dt--pages-count  mb-sm-0 mb-3'i><'dt--pagination'p>>",
            "oLanguage": {
                "oPaginate": {
                    "sPrevious": '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-arrow-left"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>',
                    "sNext": '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-arrow-right"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>'
                },
                "sInfo": "Showing page _PAGE_ of _PAGES_",
                "sLengthMenu": "Results :  _MENU_",
            },
            "stripeClasses": [],
            "lengthMenu": [5, 10, 20, 50],
            "pageLength": 5
        });

        // Inject custom search and retrieve form
        $('#' + searchBoxId).html(`
            <form action="${route}" method="POST" class="custom-search-wrapper">
                <input type="hidden" name="_token" value="${csrf}">
                <div class="search-input-container">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-search"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                    <input type="text" name="${inputName}" id="input-${btnId}" class="form-control" placeholder="Search or Enter Code...">
                </div>
                <button type="submit" id="${btnId}" class="btn btn-red" disabled>Retrieve</button>
            </form>
        `);

        // Link custom input to DataTable search and button state
        $('#input-' + btnId).on('input', function() {
            let val = $(this).val();
            
            // Filter DataTable
            table.search(val).draw();
            
            // Enable/Disable Retrieve button based on regex
            if (regex.test(val)) {
                $('#' + btnId).prop('disabled', false);
            } else {
                $('#' + btnId).prop('disabled', true);
            }
        });
    }

    // Initialize table for PO
    initTable('#po-table', 'po-search-box', 'po_unique_code', 'retrieve-po-btn', /^PO\d+$/);
});
