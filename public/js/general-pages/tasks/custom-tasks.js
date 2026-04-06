// Open task detail modal when a table row is clicked
$(document).on('click', '.task-row', function () {
    const row = $(this);

    // Populate modal fields from data-* attributes
    $('#modal-sender-name').text(row.data('fullname'));
    $('#modal-sender-email').text(row.data('email'));
    $('#modal-date').text(row.data('date'));
    $('#modal-time').text(row.data('time'));
    $('#modal-description').text(row.data('description'));

    // Show the modal
    $('#taskDetailModal').modal('show');
});
