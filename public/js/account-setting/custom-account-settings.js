$(document).ready(function() {
    const table = $('#pr-table').DataTable();
    const searchBox = $('#custom-search-box');

    if (searchBox.length && table) {
        const route = $('#pr-table').data('route');
        const csrf = $('meta[name="csrf-token"]').attr('content');

        // Inject custom search and retrieve form with partitioned input fields
        searchBox.html(`
            <form id="retrieve-pr-form" action="${route}" method="POST" class="custom-search-wrapper">
                <input type="hidden" name="_token" value="${csrf}">
                <div class="search-input-container">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-search"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                    <div class="code-mask-input-group">
                        <span class="code-static-prefix">PR</span>
                        <span class="code-dash">-</span>
                        <input type="text" class="code-digit-input code-office-id" placeholder="000" maxlength="3">
                        <span class="code-dash">-</span>
                        <input type="text" class="code-digit-input code-app-code" placeholder="000000" maxlength="6">
                        <span class="code-dash">-</span>
                        <input type="text" class="code-digit-input code-pr-count" placeholder="000" maxlength="3">
                    </div>
                    <input type="hidden" name="pr_unique_code" id="pr-search-input">
                </div>
                <button type="submit" id="retrieve-btn" class="btn btn-red" disabled>Retrieve</button>
            </form>
        `);

        const $officeInput = searchBox.find('.code-office-id');
        const $appInput = searchBox.find('.code-app-code');
        const $countInput = searchBox.find('.code-pr-count');
        const $hiddenInput = $('#pr-search-input');
        const $retrieveBtn = $('#retrieve-btn');

        // Restrict input to digits only
        searchBox.on('keypress keydown', '.code-digit-input', function(e) {
            // Allow control keys (backspace, tab, delete, arrows)
            if (e.key === 'Backspace' || e.key === 'Tab' || e.key === 'Delete' || 
                e.key === 'ArrowLeft' || e.key === 'ArrowRight' || e.key === 'ArrowUp' || e.key === 'ArrowDown') {
                return;
            }
            // Transition to next field on dash, space, or Enter
            if (e.key === '-' || e.key === ' ' || e.key === 'Enter') {
                e.preventDefault();
                const $inputs = searchBox.find('.code-digit-input');
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

        // Auto-focus transitions
        searchBox.on('input', '.code-digit-input', function() {
            // Clean non-digits just in case
            $(this).val($(this).val().replace(/\D/g, ''));

            const $inputs = searchBox.find('.code-digit-input');
            const idx = $inputs.index(this);
            const val = $(this).val();
            const maxLen = parseInt($(this).attr('maxlength'));

            // Auto-focus next field when current field reaches its maximum length
            if (val.length >= maxLen && idx < $inputs.length - 1) {
                $inputs.eq(idx + 1).focus().select();
            }

            updatePrCode();
        });

        // Auto-focus previous field on Backspace in empty field
        searchBox.on('keydown', '.code-digit-input', function(e) {
            if (e.key === 'Backspace' && $(this).val() === '') {
                const $inputs = searchBox.find('.code-digit-input');
                const idx = $inputs.index(this);
                if (idx > 0) {
                    $inputs.eq(idx - 1).focus().select();
                }
            }
        });

        // Intelligent copy-paste handler
        searchBox.on('paste', '.code-digit-input', function(e) {
            e.preventDefault();
            const pastedText = (e.originalEvent.clipboardData || window.clipboardData).getData('text');
            const digits = pastedText.replace(/\D/g, '');

            if (digits.length >= 9) {
                // E.g., PR-3-202601-001 -> digits: 3202601001
                const prCount = digits.substring(digits.length - 3);
                const appCode = digits.substring(digits.length - 9, digits.length - 3);
                const officeId = digits.substring(0, digits.length - 9);

                $officeInput.val(officeId.padStart(3, '0'));
                $appInput.val(appCode);
                $countInput.val(prCount);

                updatePrCode();
                $countInput.focus();
            } else {
                // If pasted text is just a number, populate current field
                $(this).val(digits.substring(0, $(this).attr('maxlength')));
                updatePrCode();
            }
        });

        // Auto-pad office input on blur
        $officeInput.on('blur', function() {
            const val = $(this).val();
            if (val && val.length < 3) {
                $(this).val(val.padStart(3, '0'));
                updatePrCode();
            }
        });

        // Auto-pad count input on blur
        $countInput.on('blur', function() {
            const val = $(this).val();
            if (val && val.length < 3) {
                $(this).val(val.padStart(3, '0'));
                updatePrCode();
            }
        });

        // Reconstruct the full code and update Datatable search + Submit button state
        function updatePrCode() {
            const officeVal = $officeInput.val();
            const appVal = $appInput.val();
            const countVal = $countInput.val();

            // Construct formatted code: PR-{officeId}-{appCode}-{prCount}
            // If fields are partially filled, format with empty segments to allow search filtering
            let formatted = 'PR';
            if (officeVal || appVal || countVal) {
                formatted += '-' + officeVal;
            }
            if (appVal || countVal) {
                formatted += '-' + appVal;
            }
            if (countVal) {
                formatted += '-' + countVal;
            }

            $hiddenInput.val(formatted);

            // Filter DataTable with the active code/partial code in the first column
            table.column(0).search(formatted).draw();

            // Validate full format: PR-{3-digit-officeId}-{6-digits}-{3-digits}
            const prRegex = /^PR-\d{3}-\d{6}-\d{3}$/;
            if (prRegex.test(formatted)) {
                $retrieveBtn.prop('disabled', false);
            } else {
                $retrieveBtn.prop('disabled', true);
            }
        }
    }
});
