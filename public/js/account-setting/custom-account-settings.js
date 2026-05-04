$(document).ready(function() {
    const table = $('#pr-table').DataTable();
    const searchBox = $('#custom-search-box');

    if (searchBox.length && table) {
        const route = $('#pr-table').data('route');
        const csrf = $('meta[name="csrf-token"]').attr('content');

        // Inject custom search and retrieve form
        searchBox.html(`
            <form id="retrieve-pr-form" action="${route}" method="POST" class="custom-search-wrapper">
                <input type="hidden" name="_token" value="${csrf}">
                <div class="search-input-container">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-search"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                    <input type="text" name="pr_unique_code" id="pr-search-input" class="form-control" placeholder="Search or Enter PR Code...">
                </div>
                <button type="submit" id="retrieve-btn" class="btn btn-red" disabled>Retrieve</button>
            </form>
        `);

        // Link custom input to DataTable search and button state
        $('#pr-search-input').on('input', function() {
            let val = $(this).val();
            
            // Filter DataTable
            table.search(val).draw();
            
            // Enable/Disable Retrieve button based on regex (PR followed by digits)
            const prRegex = /^PR\d+$/;
            if (prRegex.test(val)) {
                $('#retrieve-btn').prop('disabled', false);
            } else {
                $('#retrieve-btn').prop('disabled', true);
            }
        });
    }
});
