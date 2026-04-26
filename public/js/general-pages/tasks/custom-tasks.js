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

    const createBtn = $("#modal-create-pr-btn");
    const viewBtn = $("#modal-view-pr-btn");

    // Reset visibility and content
    createBtn.show();
    viewBtn.hide();
    
    // Clear and set description to avoid multiple appendings
    $("#modal-description").html(row.data("description"));

    if (taskStatus === "Approved") {
        createBtn.hide();

        if (taskType === "PR Review") {
            viewBtn.show();
            viewBtn.attr("href", "/pr-review/" + taskId);
        }

        $("#modal-description").append('<div class="mt-3 p-3 text-success border border-success rounded text-center fw-bold" style="background-color: rgba(25, 135, 84, 0.1);">This Purchase Request has been approved.</div>');
    } else {
        // Set button label and URL based on task type and status
        if (taskType === "PR Review") {
            createBtn.text("Review Purchase Request");
            createBtn.attr("href", "/pr-review/" + taskId);
        } else if (taskStatus === "Rejected") {
            createBtn.text("Revise Purchase Request");
            createBtn.attr("href", "/create-pr/" + taskId);
        } else {
            createBtn.text("Create Purchase Request");
            createBtn.attr("href", "/create-pr/" + taskId);
        }
    }

    // Show the modal
    $("#taskDetailModal").modal("show");
});
