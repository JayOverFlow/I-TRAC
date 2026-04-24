// Open task detail modal when a table row is clicked
$(document).on("click", ".task-row", function () {
    const row = $(this);

    // Populate modal fields from data-* attributes
    $("#modal-sender-name").text(row.data("fullname"));
    $("#modal-sender-email").text(row.data("email"));
    $("#modal-date").text(row.data("date"));
    $("#modal-time").text(row.data("time"));
    $("#modal-description").text(row.data("description"));

    // Read task metadata
    const taskId = row.data("task-id");
    const taskType = row.data("task-type");
    const taskStatus = row.data("task-status");

    const btn = $("#modal-create-pr-btn");

    // Reset visibility and content
    btn.show();
    $("#modal-description").text(row.data("description"));

    if (taskStatus === "Approved") {
        btn.hide();
        $("#modal-description").append('<div class="mt-3 p-3 text-success border border-success rounded text-center fw-bold" style="background-color: rgba(25, 135, 84, 0.1);">This Purchase Request has been approved.</div>');
    } else {
        // Set button label and URL based on task type and status
        if (taskType === "PR Review") {
            btn.text("Review Purchase Request");
            btn.attr("href", "/pr-review/" + taskId);
        } else if (taskStatus === "Rejected") {
            btn.text("Revise Purchase Request");
            btn.attr("href", "/create-pr/" + taskId);
        } else {
            btn.text("Create Purchase Request");
            btn.attr("href", "/create-pr/" + taskId);
        }
    }

    // Show the modal
    $("#taskDetailModal").modal("show");
});
