// Open task detail modal when a table row is clicked
$(document).on("click", ".task-row", function () {
    const row = $(this);

    // Populate modal fields from data-* attributes
    $("#modal-sender-name").text(row.data("fullname"));
    $("#modal-sender-email").text(row.data("email"));
    $("#modal-date").text(row.data("date"));
    $("#modal-time").text(row.data("time"));
    $("#modal-description").text(row.data("description"));

    // Update the Create PR button URL dynamically using task ID
    const taskId = row.data("task-id");
    if (taskId) {
        $("#modal-create-pr-btn").attr("href", "/create-pr/" + taskId);
    } else {
        // Fallback or disable if task ID is missing
        $("#modal-create-pr-btn").attr("href", "#");
    }

    // Show the modal
    $("#taskDetailModal").modal("show");
});
