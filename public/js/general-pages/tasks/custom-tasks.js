// Open task detail or redirect directly when a table row is clicked
$(document).on("click", ".task-row", function () {
    const taskId = $(this).data("task-id");
    window.location.href = "/create-pr/" + taskId;
});

// ── Panel toggle: Create button → show APP checklist ──────────────────────
$(document).on("click", "#btn-show-app-checklist", function () {
    $("#pr-list-panel").hide();
    $("#app-checklist-panel").show();

    // Set action to create and change button label
    $("#btn-create-from-checklist").attr("data-action", "create").text("Create");

    // Reset checklist state on each open
    $(".app-item-checkbox").prop("checked", false);
    $("#btn-create-from-checklist").prop("disabled", true);
});

// ── Panel toggle: Assign button → show APP checklist ──────────────────────
$(document).on("click", "#btn-assign-pr", function () {
    $("#pr-list-panel").hide();
    $("#app-checklist-panel").show();

    // Set action to assign and change button label
    $("#btn-create-from-checklist").attr("data-action", "assign").text("Assign");

    // Reset checklist state on each open
    $(".app-item-checkbox").prop("checked", false);
    $("#btn-create-from-checklist").prop("disabled", true);
});

// ── Panel toggle: breadcrumb Back → return to PR list ─────────────────────
$(document).on("click", "#btn-back-to-pr", function () {
    $("#app-checklist-panel").hide();
    $("#pr-list-panel").show();
});

// ── APP checklist: Submit selected items to initialize Task/PR ───────────
$(document).on("click", "#btn-create-from-checklist", function () {
    const action = $(this).attr("data-action") || "create";
    if (action === "assign") {
        // Reset modal states before opening
        $("#user-search-input").val("");
        $(".user-list-item").removeClass("active bg-light").css({"outline": "", "opacity": "1"});
        $("#confirm-assign-btn").prop("disabled", true);
        
        $("#exampleModalCenter").modal("show");
        return;
    }
    var selectedItemIds = [];
    $(".app-item-checkbox:checked:not(:disabled)").each(function () {
        selectedItemIds.push($(this).data("item-id"));
    });

    if (selectedItemIds.length === 0) return;

    var btn = $(this);
    btn.prop("disabled", true).text("Initializing...");

    $.ajax({
        url: "/tasks/create-from-app-items",
        type: "POST",
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content")
        },
        data: {
            items: selectedItemIds
        },
        success: function (response) {
            if (response.success && response.redirect_url) {
                window.location.href = response.redirect_url;
            } else {
                alert("Failed to initialize Purchase Request. Please try again.");
                btn.prop("disabled", false).text("Create");
            }
        },
        error: function (xhr) {
            console.error(xhr);
            alert("An error occurred while initializing. Please try again.");
            btn.prop("disabled", false).text("Create");
        }
    });
});

// Single Trash Icon Click Handler (UI Only)
$(document).on("click", ".btn-delete-task-single", function (e) {
    e.stopPropagation();
    const taskId = $(this).data("task-id");
    alert("Delete individual Purchase Request (ID: " + taskId + ") clicked. (UI only, deletion logic not yet implemented)");
});

// ── Assign Modal Selection and Search Logic ──────────────────────────────
$(document).on("click", ".user-list-item", function () {
    const isSelected = $(this).hasClass("active");
    const allItems = $(".user-list-item");

    if (isSelected) {
        $(this).removeClass("active bg-light").css("outline", "");
        allItems.css("opacity", "1");
        $("#confirm-assign-btn").prop("disabled", true);
    } else {
        allItems.removeClass("active bg-light").css({"outline": "", "opacity": "0.4"});
        $(this).addClass("active bg-light").css({
            "outline": "2px solid #C62742",
            "outlineOffset": "-2px",
            "opacity": "1"
        });
        $("#confirm-assign-btn").prop("disabled", false);
    }
});

// Dynamic Search Filter
$(document).on("input", "#user-search-input", function () {
    const filter = $(this).val().toLowerCase().trim();
    $(".user-list-item").each(function () {
        const text = $(this).text().toLowerCase();
        if (text.indexOf(filter) > -1) {
            $(this).removeClass("d-none");
        } else {
            $(this).addClass("d-none");
        }
    });
});

// Confirm Assign Button Submission
$(document).on("click", "#confirm-assign-btn", function () {
    // Collect item IDs from the checked checkboxes in the DataTable
    const itemIds = [];
    const table = $("#app-items-config").DataTable();
    table.$(".app-item-checkbox:checked:not(:disabled)").each(function () {
        itemIds.push($(this).data("item-id") || $(this).val());
    });

    const selectedUser = $(".user-list-item.active");
    const assignedTo = selectedUser.length ? selectedUser.data("user-id") : null;
    const assignedName = selectedUser.length ? selectedUser.find(".user-name").text().trim() : "the selected user";

    if (itemIds.length === 0 || !assignedTo) return;

    const btn = $(this);
    const url = $('meta[name="assign-pr-url"]').attr("content");
    const csrf = $('meta[name="csrf-token"]').attr("content");

    window.confirmAction({
        title: 'Confirm Assignment?',
        text: 'Are you sure you want to assign the selected procurement item(s) to ' + assignedName + '?',
        icon: 'question',
        confirmButtonText: 'Yes, Assign',
        cancelButtonText: 'Cancel',
        onConfirm: function() {
            btn.prop("disabled", true).text("Assigning...");
            $.ajax({
                url: url,
                type: "POST",
                headers: { "X-CSRF-TOKEN": csrf },
                data: {
                    assigned_to: assignedTo,
                    item_ids: itemIds
                },
                success: function (response) {
                    if (response.success) {
                        $("#exampleModalCenter").modal("hide");
                        location.reload();
                    } else {
                        alert(response.message || "Failed to assign Purchase Request. Please check your inputs.");
                        btn.prop("disabled", false).text("Assign");
                    }
                },
                error: function (xhr) {
                    console.error(xhr);
                    alert("An error occurred while assigning. Please try again.");
                    btn.prop("disabled", false).text("Assign");
                }
            });
        }
    });
});

