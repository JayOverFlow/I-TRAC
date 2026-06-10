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
                "sLengthMenu": "<h4 class='fw-bold mb-3 red-text-2'>Purchase Orders</h4>",
            },
            "stripeClasses": [],
            "lengthMenu": [5, 10, 20, 50],
            "pageLength": 5
        });

        if (btnId === 'retrieve-po-btn') {
            // Inject custom search and retrieve form with partitioned input fields for PO
            $('#' + searchBoxId).html(`
                <form action="${route}" method="POST" class="custom-search-wrapper">
                    <input type="hidden" name="_token" value="${csrf}">
                    <div class="search-input-container">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-search"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                        <div class="code-mask-input-group">
                            <span class="code-static-prefix">PO</span>
                            <span class="code-dash">-</span>
                            <input type="text" class="code-digit-input code-office-id" placeholder="00" maxlength="4">
                            <span class="code-dash">-</span>
                            <input type="text" class="code-digit-input code-po-pr" placeholder="000000000" maxlength="9">
                            <span class="code-dash">-</span>
                            <input type="text" class="code-digit-input code-po-count" placeholder="000" maxlength="3">
                        </div>
                        <input type="hidden" name="${inputName}" id="input-${btnId}">
                    </div>
                    <button type="submit" id="${btnId}" class="btn btn-red" disabled>Retrieve</button>
                </form>
            `);

            const $container = $('#' + searchBoxId);
            const $officeInput = $container.find('.code-office-id');
            const $prInput = $container.find('.code-po-pr');
            const $countInput = $container.find('.code-po-count');
            const $hiddenInput = $('#input-' + btnId);
            const $retrieveBtn = $('#' + btnId);

            // Restrict input to digits only & navigation
            $container.on('keypress keydown', '.code-digit-input', function(e) {
                // Allow control keys (backspace, tab, delete, arrows)
                if (e.key === 'Backspace' || e.key === 'Tab' || e.key === 'Delete' || 
                    e.key === 'ArrowLeft' || e.key === 'ArrowRight' || e.key === 'ArrowUp' || e.key === 'ArrowDown') {
                    return;
                }
                // Transition to next field on dash, space, or Enter
                if (e.key === '-' || e.key === ' ' || e.key === 'Enter') {
                    e.preventDefault();
                    const $inputs = $container.find('.code-digit-input');
                    const idx = $inputs.index(this);
                    if (idx < $inputs.length - 1) {
                        $inputs.eq(idx + 1).focus().select();
                    }
                    return;
                }
                // Block non-digit keys
                if (!/^\d$/.test(e.key)) {
                    e.preventDefault();
                }
            });

            // Auto-focus next field on length limit
            $container.on('input', '.code-digit-input', function() {
                // Clean non-digits just in case
                $(this).val($(this).val().replace(/\D/g, ''));

                const $inputs = $container.find('.code-digit-input');
                const idx = $inputs.index(this);
                const val = $(this).val();
                const maxLen = parseInt($(this).attr('maxlength'));

                // Auto-focus next field when current field reaches its maximum length
                if (val.length >= maxLen && idx < $inputs.length - 1) {
                    $inputs.eq(idx + 1).focus().select();
                }

                updatePoCode();
            });

            // Auto-focus previous field on Backspace in empty field
            $container.on('keydown', '.code-digit-input', function(e) {
                if (e.key === 'Backspace' && $(this).val() === '') {
                    const $inputs = $container.find('.code-digit-input');
                    const idx = $inputs.index(this);
                    if (idx > 0) {
                        $inputs.eq(idx - 1).focus().select();
                    }
                }
            });

            // Intelligent copy-paste handler
            $container.on('paste', '.code-digit-input', function(e) {
                e.preventDefault();
                const pastedText = (e.originalEvent.clipboardData || window.clipboardData).getData('text');
                const digits = pastedText.replace(/\D/g, '');

                if (digits.length >= 12) {
                    // E.g. PO-3-202601001-001 -> digits: 3202601001001
                    const poCount = digits.substring(digits.length - 3);
                    const prCode = digits.substring(digits.length - 12, digits.length - 3);
                    const officeId = digits.substring(0, digits.length - 12);

                    $officeInput.val(officeId);
                    $prInput.val(prCode);
                    $countInput.val(poCount);

                    updatePoCode();
                    $countInput.focus();
                } else {
                    // If pasted text is just a number, populate current field
                    $(this).val(digits.substring(0, $(this).attr('maxlength')));
                    updatePoCode();
                }
            });

            // Reconstruct the full code and update Datatable search + Submit button state
            function updatePoCode() {
                const officeVal = $officeInput.val();
                const prVal = $prInput.val();
                const countVal = $countInput.val();

                // Construct formatted code: PO-{officeId}-{prCode}-{poCount}
                // If fields are partially filled, format with empty segments to allow search filtering
                let formatted = 'PO';
                if (officeVal || prVal || countVal) {
                    formatted += '-' + officeVal;
                }
                if (prVal || countVal) {
                    formatted += '-' + prVal;
                }
                if (countVal) {
                    formatted += '-' + countVal;
                }

                $hiddenInput.val(formatted);

                // Filter DataTable with the active code/partial code
                table.search(formatted).draw();

                // Validate full format: PO-{officeId}-{9-digits}-{3-digits}
                const poRegex = /^PO-\d+-\d{9}-\d{3}$/;
                if (poRegex.test(formatted)) {
                    $retrieveBtn.prop('disabled', false);
                } else {
                    $retrieveBtn.prop('disabled', true);
                }
            }
        } else {
            // Standard fallback form layout for other inputs
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

            $('#input-' + btnId).on('input', function() {
                let val = $(this).val();
                let isValid = regex.test(val);
                table.search(val).draw();
                if (isValid) {
                    $('#' + btnId).prop('disabled', false);
                } else {
                    $('#' + btnId).prop('disabled', true);
                }
            });
        }
    }

    // Initialize table for PO (Updated regex pattern for backfill compatibility)
    initTable('#po-table', 'po-search-box', 'po_unique_code', 'retrieve-po-btn', /^PO-\d{9}-\d{3}$/);

    // Handle PO review redirection
    $(document).on('click', '.clickable-row', function() {
        const route = $(this).data('review-route');
        if (route) {
            window.location.href = route;
        }
    });
});
