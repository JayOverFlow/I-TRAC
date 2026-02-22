document.addEventListener('DOMContentLoaded', function() {
    const assignBtn = document.querySelector('#assign-btn');
    const createBtn = document.querySelector('#create-btn');
    
    function updateButtonStates() {
        // Use DataTables API to query elements across all pages (even those not currently in the DOM)
        const table = $('#zero-config').DataTable();
        const checkedCount = table.$('.item-checkbox:checked').length;
        const isDisabled = checkedCount === 0;

        if (assignBtn) assignBtn.disabled = isDisabled;
        if (createBtn) createBtn.disabled = isDisabled;
    }

    document.addEventListener('change', function(event) {
        if (event.target && event.target.classList.contains('item-checkbox')) {
            updateButtonStates();
        }
    });

    // Initialize state on load
    updateButtonStates();

    // Modal User Selection Logic
    const searchInput = document.getElementById('user-search-input');
    const userList = document.getElementById('user-list');
    const confirmAssignBtn = document.getElementById('confirm-assign-btn');

    if (userList) {
        userList.addEventListener('click', function(event) {
            const item = event.target.closest('.user-list-item');
            if (!item) return;

            const isSelected = item.classList.contains('active');
            const allItems = document.querySelectorAll('.user-list-item');

            if (isSelected) {
                // Deselect current item
                item.classList.remove('active', 'bg-light');
                item.style.outline = '';
                
                // Make all items visible again
                allItems.forEach(otherItem => {
                    otherItem.style.opacity = '1';
                });

                if (confirmAssignBtn) confirmAssignBtn.disabled = true;
            } else {
                // Select a new item, deselect any previously selected item
                allItems.forEach(otherItem => {
                    // Deselect and slightly dim
                    otherItem.classList.remove('active', 'bg-light');
                    otherItem.style.outline = '';
                    otherItem.style.opacity = '0.4';
                });

                // Highlight chosen item fully
                item.classList.add('active', 'bg-light');
                item.style.outline = '2px solid #C62742';
                item.style.outlineOffset = '-2px';
                item.style.opacity = '1';

                if (confirmAssignBtn) confirmAssignBtn.disabled = false;
            }
        });
    }

    // Dynamic Search Filter Logic
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const filter = searchInput.value.toLowerCase().trim();
            const items = document.querySelectorAll('.user-list-item');

            items.forEach(item => {
                const text = item.textContent || item.innerText;

                // For table rows, we just toggle 'd-none' without enforcing d-flex
                if (text.toLowerCase().indexOf(filter) > -1) {
                    item.classList.remove('d-none');
                } else {
                    item.classList.add('d-none');
                }
            });
        });
    }

    // This is temporary for UI purposes
    // Make the modal initially visible
    // $('#exampleModalCenter').modal('show');
});