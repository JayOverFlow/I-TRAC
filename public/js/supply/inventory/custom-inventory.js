$(document).ready(function () {
    // Active item and image gallery states
    var activeMrId = null;
    var activeImages = []; // List of {url, path} objects
    var activeImagePath = null; // The relative path of the currently viewed image
    var $activeRow = null; // Reference to the clicked table row to update its data attribute
    var editingQueueIndex = null; // Stores index of queue item currently being edited
    var shouldRestoreQueueModal = false; // Flag to indicate if we should reopen queue modal on hide

    // Setup CSRF token for all AJAX requests
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    // Dynamic toast message helper
    function showToast(message, type) {
        var toastId = 'dynamicToast_' + Date.now();
        var bgClass = type === 'success' ? 'bg-success' : 'bg-danger';
        var icon = type === 'success' ? 
            '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-check-circle"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>' :
            '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-alert-circle"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>';
        
        var html = `
            <div id="${toastId}" class="toast align-items-center text-white ${bgClass} border-0 shadow-lg" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body d-flex align-items-center gap-2">
                        ${icon}
                        <div>${message}</div>
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>
        `;
        
        $('.toast-container').append(html);
        var toastEl = document.getElementById(toastId);
        if (toastEl) {
            var toast = new bootstrap.Toast(toastEl, { delay: 5000 });
            toast.show();
            
            toastEl.addEventListener('hidden.bs.toast', function () {
                toastEl.remove();
            });
        }
    }

    function updateStatusClass(status) {
        var $select = $('#detailItemStatus');
        $select.removeClass('status-serviceable status-unserviceable status-missing');
        if (status === 'Serviceable') {
            $select.addClass('status-serviceable');
        } else if (status === 'Unserviceable') {
            $select.addClass('status-unserviceable');
        } else if (status === 'Missing') {
            $select.addClass('status-missing');
        }
        adjustSelectWidth();
    }

    function adjustSelectWidth() {
        var $select = $('#detailItemStatus');
        if (!$select.length) return;
        var text = $select.find('option:selected').text() || '● Serviceable';
        var $tempSpan = $('<span>')
            .text(text)
            .css({
                'font-size': $select.css('font-size') || '0.8rem',
                'font-weight': $select.css('font-weight') || 'bold',
                'visibility': 'hidden',
                'white-space': 'nowrap',
                'position': 'absolute'
            });
        $('body').append($tempSpan);
        var width = $tempSpan.width();
        $tempSpan.remove();
        $select.css('width', (width + 48) + 'px');
    }

    // Render image gallery and thumbnails in the modal
    function renderItemImages() {
        var primaryImg = document.getElementById('modalPrimaryImage');
        var noImgPlaceholder = document.getElementById('modalNoImagePlaceholder');
        var gridContainer = document.getElementById('modalImageGrid');
        var btnDelete = document.getElementById('btnDeleteActiveImage');
        var countText = document.getElementById('modalImageCountText');

        var totalImages = activeImages.length;
        if (countText) {
            countText.innerText = totalImages + '/5 Images';
        }

        if (totalImages > 0) {
            // Find active image object, default to first if not found/null
            var activeObj = activeImages.find(function (img) { return img.path === activeImagePath; });
            if (!activeObj) {
                activeObj = activeImages[0];
                activeImagePath = activeObj.path;
            }

            // Clear no-image stretching
            var card = document.querySelector('.main-image-viewport-card');
            var cardBody = document.querySelector('.main-image-viewport-card .card-body');
            if (card) {
                card.style.height = '';
                card.classList.remove('flex-grow-1');
                card.classList.add('mb-3');
            }
            if (cardBody) {
                cardBody.classList.remove('flex-grow-1');
            }

            // Show primary image with dynamic aspect ratio scaling
            primaryImg.onload = function() {
                var isPortrait = primaryImg.naturalHeight > primaryImg.naturalWidth;
                var card = document.querySelector('.main-image-viewport-card');
                var cardBody = document.querySelector('.main-image-viewport-card .card-body');
                if (card && cardBody && primaryImg.naturalHeight > 0) {
                    var aspectRatio = primaryImg.naturalWidth / primaryImg.naturalHeight;
                    if (isPortrait) {
                        cardBody.style.height = '400px';
                        cardBody.style.minHeight = '400px';
                        card.style.width = Math.round(400 * aspectRatio) + 'px';
                        card.style.margin = '0 auto';
                    } else {
                        cardBody.style.height = '250px';
                        cardBody.style.minHeight = '250px';
                        card.style.width = '100%';
                        card.style.margin = '';
                    }
                }
            };

            noImgPlaceholder.style.display = 'none';
            primaryImg.src = activeObj.url;
            primaryImg.style.display = 'block';
            if (btnDelete) $(btnDelete).hide(); // Disable delete button overlay

            if (primaryImg.complete) {
                primaryImg.onload();
            }

            // Render 4 slots below
            gridContainer.innerHTML = '';
            gridContainer.style.display = 'flex';

            // Filter non-active images
            var nonActiveImages = activeImages.filter(function (img) { return img.path !== activeImagePath; });

            // Fill 4 slots (Only images or empty slots, no Add icon slot)
            for (var i = 0; i < 4; i++) {
                if (i < nonActiveImages.length) {
                    // Image slot
                    var imgObj = nonActiveImages[i];
                    var slotHtml = `
                        <div class="image-grid-slot slot-image" data-path="${imgObj.path}">
                            <img src="${imgObj.url}" alt="Thumbnail">
                        </div>
                    `;
                    gridContainer.insertAdjacentHTML('beforeend', slotHtml);
                } else {
                    // Empty slot
                    var emptyHtml = '<div class="image-grid-slot slot-empty"></div>';
                    gridContainer.insertAdjacentHTML('beforeend', emptyHtml);
                }
            }
        } else {
            // No images available
            primaryImg.style.display = 'none';
            if (btnDelete) $(btnDelete).hide();
            noImgPlaceholder.style.display = 'block';
            gridContainer.style.display = 'none';
            var card = document.querySelector('.main-image-viewport-card');
            var cardBody = document.querySelector('.main-image-viewport-card .card-body');
            if (card) {
                card.style.width = '100%';
                card.style.margin = '';
                card.style.height = '100%';
                card.classList.add('flex-grow-1');
                card.classList.remove('mb-3');
            }
            if (cardBody) {
                cardBody.style.height = '';
                cardBody.style.minHeight = '';
                cardBody.classList.add('flex-grow-1');
            }
            activeImagePath = null;
        }
    }

    /*
    // Trigger file selection when clicking add image buttons
    $(document).on('click', '#btnPlaceholderAddImage, .slot-add', function () {
        $('#modalImageFileInput').click();
    });
    */

    // Handle click on image slot to swap active image
    $(document).on('click', '#modalImageGrid .slot-image', function () {
        activeImagePath = $(this).data('path');
        renderItemImages();
    });

    /*
    // Handle File Input Change (Upload)
    $(document).on('change', '#modalImageFileInput', function (e) {
        var file = e.target.files[0];
        if (!file) return;

        var inputEl = this;
        var formData = new FormData();
        formData.append('mr_id', activeMrId);
        formData.append('item_image', file);

        var $btnPlaceholder = $('#btnPlaceholderAddImage');
        var $slotAdd = $('#modalImageGrid .slot-add');
        var origPlaceholderHtml = $btnPlaceholder.html();
        var origSlotHtml = $slotAdd.html();

        if ($btnPlaceholder.is(':visible')) {
            $btnPlaceholder.prop('disabled', true).html('Uploading...');
        }
        if ($slotAdd.length) {
            $slotAdd.addClass('slot-uploading').html('<div class="spinner-border spinner-border-sm text-red" role="status" style="width: 14px; height: 14px;"></div>');
        }

        $.ajax({
            url: '/inventory/upload-image',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function (response) {
                if (response.status === 'success') {
                    showToast(response.message || 'Image uploaded successfully!', 'success');
                    
                    // Update state
                    activeImages = response.images;
                    
                    // Update table row data attribute so it persists if reopened
                    if ($activeRow) {
                        $activeRow.data('item-images', activeImages);
                    }

                    // Re-render gallery
                    renderItemImages();
                } else {
                    showToast(response.message || 'Failed to upload image.', 'danger');
                }
            },
            error: function (xhr) {
                var message = 'An error occurred during upload.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                }
                showToast(message, 'danger');
            },
            complete: function () {
                if ($btnPlaceholder.is(':visible')) {
                    $btnPlaceholder.prop('disabled', false).html(origPlaceholderHtml);
                }
                if ($slotAdd.length) {
                    $slotAdd.removeClass('slot-uploading').html(origSlotHtml);
                }
                inputEl.value = ''; // clear input
            }
        });
    });
    */

    /*
    // Handle Delete Image
    $(document).on('click', '#btnDeleteActiveImage', function () {
        if (!activeImagePath || !activeMrId) return;

        if (!confirm('Are you sure you want to delete this image?')) return;

        var $btn = $(this);
        $btn.prop('disabled', true);

        $.ajax({
            url: '/inventory/delete-image',
            type: 'POST',
            data: {
                mr_id: activeMrId,
                image_path: activeImagePath
            },
            success: function (response) {
                if (response.status === 'success') {
                    showToast(response.message || 'Image deleted successfully!', 'success');
                    
                    // Update state
                    activeImages = response.images;
                    
                    // Update table row data attribute
                    if ($activeRow) {
                        $activeRow.data('item-images', activeImages);
                    }

                    // Re-render gallery
                    renderItemImages();
                } else {
                    showToast(response.message || 'Failed to delete image.', 'danger');
                }
            },
            error: function (xhr) {
                var message = 'An error occurred during deletion.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                }
                showToast(message, 'danger');
            },
            complete: function () {
                $btn.prop('disabled', false);
            }
        });
    });
    */

    // Initialize Datatable
    $('#zero-config').DataTable({
        "dom": "<'dt--top-section'<'row'<'col-12 col-sm-6 d-flex justify-content-sm-start align-items-center'l><'col-12 col-sm-6 d-flex justify-content-sm-end justify-content-center mt-sm-0 mt-3'f>>>" +
            "<'table-responsive'tr>" +
            "<'dt--bottom-section d-sm-flex justify-content-sm-between text-center'<'dt--pages-count  mb-sm-0 mb-3'i><'dt--pagination'p>>",
        "oLanguage": {
            "oPaginate": {
                "sPrevious": '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-arrow-left"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>',
                "sNext": '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-arrow-right"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>'
            },
            "sInfo": "Showing page _PAGE_ of _PAGES_",
            "sSearch": '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-search"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>',
            "sSearchPlaceholder": "Search...",
            "sLengthMenu": "<h4 class='fw-bold mb-0 red-text-2'>Items</h4>",
        },
        "stripeClasses": [],
        "lengthMenu": [5, 10, 20, 50],
        "pageLength": 5,
        "initComplete": function () {
            var printBtn =
                '<button id="btnOpenPrintQueue"'
                + ' class="btn btn-dark-red fw-bold me-2 d-flex align-items-center flex-shrink-0"'
                + ' data-bs-toggle="modal" data-bs-target="#exportQueueModal">'
                + 'Export Queue'
                + ' <span id="queueBadgeCount" class="fw-bold ms-2">0</span>'
                + '</button>';
            $('.dataTables_filter')
                .css({ 'display': 'flex', 'align-items': 'center' })
                .prepend(printBtn);
        }
    });

    // Row click handler to open Details Modal
    $('#zero-config tbody').on('click', 'tr.inventory-row', function (e) {
        // Prevent click events if selecting text
        if (window.getSelection().toString()) return;

        var $row = $(this);

        // Extract data attributes
        var itemName = $row.data('item-name') || '—';
        var assignee = $row.data('assignee') || '—';
        var dateScanned = $row.data('date-scanned') || '—';
        var stock = $row.data('stock') || '—';
        var unit = $row.data('unit') || '—';
        var specification = $row.data('specification') || '—';
        var quantity = $row.data('quantity') || '—';
        var building = $row.data('building') || '—';
        var roomNo = $row.data('room-no') || '—';
        
        // Extract the unique item QR code data attribute from the clicked row
        var mrQrCode = $row.data('mr-qr-code') || '';

        // Populate active state variables
        $activeRow = $row;
        activeMrId = $row.data('mr-id');
        activeImages = $row.data('item-images') || [];
        activeImagePath = null; // Reset active image path to display the first image by default
        editingQueueIndex = null; // Reset edit queue state when opening a new detail modal
        $('#btnAddToQueue span').text('Add to Queue');

        // Populate detail fields
        $('#detailItemName').text(itemName);
        $('#detailAssignee').text(assignee);
        $('#detailDateScanned').text(dateScanned);
        $('#detailStock').text(stock);
        $('#detailUnit').text(unit);
        $('#detailSpecifications').text(specification);
        $('#detailQuantity').text(quantity);
        $('#detailBuilding').text(building);
        $('#detailRoom').text(roomNo);
        // Set the extracted QR code string and MR ID to the hidden form inputs
        $('#mr_qr_code').val(mrQrCode);
        $('#mr_id').val(activeMrId);

        // Set current status for dropdown
        var status = $row.attr('data-status') || 'Serviceable';
        $('#detailItemStatus').val(status);
        updateStatusClass(status);

        // Reset Tab State to first tab (Details)
        var detailsTabEl = document.querySelector('#details-tab');
        if (detailsTabEl) {
            var detailsTab = bootstrap.Tab.getOrCreateInstance(detailsTabEl);
            detailsTab.show();
        }

        // Reset Item Label form selections
        $('.size-card').removeClass('selected');
        $('.size-card .size-dim').removeClass('black-text');
        $('.size-card[data-size="Small"]').addClass('selected');
        $('.size-card[data-size="Small"]').find('.size-dim').addClass('black-text');
        $('#label_size').val('Small');

        // Re-enable the Create Item Label submit button (Small is the default)
        var $submitBtn = $('#createItemLabelForm button[type="submit"]');
        $submitBtn.prop('disabled', false).removeClass('opacity-50');

        $('.layout-card').removeClass('selected');
        $('.layout-card[data-layout="layout_1"]').addClass('selected');
        $('#qr_layout').val('layout_1');

        // Hide Layout 2 since Small is selected by default
        $('#layout-2-col').hide();

        $('.stepper-quantity').val('1');

        // Reset toggle to Individual Export mode
        $('.toggle-export-mode').removeClass('active');
        $('.toggle-export-mode[data-mode="individual"]').addClass('active');
        $('#export_mode').val('individual');
        $('#exportModeToggleContainer').removeClass('d-none');
        $('#paper-size-section').removeClass('d-none');
        $('#btnCreateItemLabel').removeClass('d-none');
        $('#btnAddToQueue').addClass('d-none');

        // Reset paper size selection to A4 (default)
        $('.paper-size-card').removeClass('selected');
        $('.paper-size-card[data-paper-size="A4"]').addClass('selected');
        $('#paper_size').val('A4');
        // Add to Queue is enabled by default
        $('#btnAddToQueue').prop('disabled', false);

        // Render Media/Image Gallery
        renderItemImages();

        // Show Bootstrap Modal
        var myModal = bootstrap.Modal.getOrCreateInstance(document.getElementById('itemDetailsModal'));
        myModal.show();
    });

    // Sizing selectors toggles
    $(document).on('click', '.size-card', function () {
        $('.size-card').removeClass('selected');
        $('.size-card .size-dim').removeClass('black-text');
        $(this).addClass('selected');
        $(this).find('.size-dim').addClass('black-text');
        var selectedSize = $(this).data('size');
        $('#label_size').val(selectedSize);

        // Dynamic QR Label Layout selection based on selected size
        if (selectedSize === 'Small') {
            $('#layout-2-col').hide();
            if ($('#qr_layout').val() === 'layout_2') {
                $('.layout-card').removeClass('selected');
                $('.layout-card[data-layout="layout_1"]').addClass('selected');
                $('#qr_layout').val('layout_1');
            }
        } else {
            $('#layout-2-col').show();
        }
    });

    // Layout selectors toggles
    $(document).on('click', '.layout-card', function () {
        $('.layout-card').removeClass('selected');
        $(this).addClass('selected');
        $('#qr_layout').val($(this).data('layout'));
    });

    // Paper size selector toggle
    $(document).on('click', '.paper-size-card', function() {
        $('.paper-size-card').removeClass('selected');
        $(this).addClass('selected');
        var ps = $(this).data('paper-size');
        $('#paper_size').val(ps);
    });

    // Stepper Quantity buttons logic
    $(document).on('click', '.btn-stepper-plus', function () {
        var $input = $(this).siblings('.stepper-quantity');
        var val = parseInt($input.val()) || 0;
        $input.val(val + 1);
    });

    $(document).on('click', '.btn-stepper-minus', function () {
        var $input = $(this).siblings('.stepper-quantity');
        var val = parseInt($input.val()) || 1;
        if (val > 1) {
            $input.val(val - 1);
        }
    });

    $(document).on('change', '.stepper-quantity', function () {
        var val = parseInt($(this).val()) || 1;
        if (val < 1) {
            val = 1;
        }
        $(this).val(val);
    });

    // -------------------------------------------------------------
    // Form Submission Handler for Item Label Generation (Batch PDF)
    // -------------------------------------------------------------
    // Intercepts the submit event of the Create Item Label form.
    // Sends a fetch request to the backend which generates batch PDFs
    // (one A6 page per 24 stickers). The backend returns a JSON array
    // of download URLs. We iterate through them and trigger a download
    // for each PDF file, then close the modal.
    $(document).on('submit', '#createItemLabelForm', function (e) {
        // Prevent default form page reload behavior
        e.preventDefault();

        // Retrieve the form action route URL and current serialized form values
        var actionUrl = $(this).attr('action');
        var queryParams = $(this).serialize();

        // Immediately hide the modal so the user isn't waiting on the UI
        var myModal = bootstrap.Modal.getInstance(document.getElementById('itemDetailsModal'));
        if (myModal) {
            myModal.hide();
        }

        // Show general form loader overlay
        $('#form-loader-overlay').css('display', 'flex');

        // Send a fetch GET request to the backend label generator endpoint.
        fetch(actionUrl + '?' + queryParams)
        .then(function (response) {
            if (!response.ok) {
                throw new Error('Server returned ' + response.status);
            }
            return response.blob();
        })
        .then(function (blob) {
            showToast('Item label PDF generated successfully!', 'success');
            var blobUrl = window.URL.createObjectURL(blob);
            var a = document.createElement('a');
            a.href = blobUrl;
            a.download = 'item_labels.pdf';
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            window.URL.revokeObjectURL(blobUrl);
            $('#form-loader-overlay').hide();
        })
        .catch(function (err) {
            console.error('Label generation failed:', err);
            showToast('Failed to generate labels. Please try again.', 'danger');
            $('#form-loader-overlay').hide();
        });
    });

    // Open fullscreen lightbox when clicking the primary image
    $(document).on('click', '#modalPrimaryImage', function () {
        var src = $(this).attr('src');
        if (!src) return;
        $('#lightboxImage').attr('src', src);
        $('#imageLightbox').removeClass('d-none').css('opacity', '1');
        $('body').addClass('modal-open'); // Prevent background scrolling
    });

    // Close lightbox when clicking the close button or outside the image
    $(document).on('click', '#imageLightbox', function (e) {
        if ($(e.target).is('#imageLightbox') || $(e.target).is('.lightbox-close')) {
            $('#imageLightbox').addClass('d-none');
            $('body').removeClass('modal-open');
        }
    });

    // Support closing with escape key
    $(document).on('keydown', function (e) {
        if (e.key === 'Escape' && !$('#imageLightbox').hasClass('d-none')) {
            $('#imageLightbox').addClass('d-none');
            $('body').removeClass('modal-open');
        }
    });

    // ---------------------------------------------------------------
    // EXPORT QUEUE
    // ---------------------------------------------------------------

    var queueAddUrl    = window.queueAddUrl;
    var queueGetUrl    = window.queueGetUrl;
    var queueClearUrl  = window.queueClearUrl;
    var queueExportUrl = window.queueExportUrl;
    var csrfToken      = $('meta[name="csrf-token"]').attr('content');

    /**
     * Fetch the current queue, update the badge count, and re-render the modal table.
     */
    function refreshQueueBadge() {
        $.getJSON(queueGetUrl, function (data) {
            var count = data.count || 0;
            $('#queueBadgeCount').text(count);

            // Re-render the modal table body
            var $tbody = $('#exportQueueTableBody');
            $tbody.empty();
            var totalQty = 0;

            if (!data.queue || data.queue.length === 0) {
                $('#exportQueueEmpty').show();
                $('#exportQueueTableWrap').hide();
                $('#btnExportQueuePdf').prop('disabled', true);
            } else {
                $('#exportQueueEmpty').hide();
                $('#exportQueueTableWrap').show();
                $('#btnExportQueuePdf').prop('disabled', false);

                $.each(data.queue, function (i, entry) {
                    var layoutLabel = (entry.qr_layout === 'layout_2') ? 'QR Code with Label' : 'QR Code Only';
                    totalQty += entry.sticker_quantity;
                    var row = '<tr class="queue-row" style="border-bottom: 1px solid #dee2e6; cursor: pointer;"'
                        + ' data-index="' + i + '"'
                        + ' data-mr-id="' + entry.mr_id + '"'
                        + ' data-size="' + entry.label_size + '"'
                        + ' data-layout="' + entry.qr_layout + '"'
                        + ' data-qty="' + entry.sticker_quantity + '">'
                        + '<td>'   + $('<span>').text(entry.mr_qr_code).html()       + '</td>'
                        + '<td class="fw-normal">' + $('<span>').text(entry.item_name).html() + '</td>'
                        + '<td class="text-center">' + entry.label_size                     + '</td>'
                        + '<td class="text-center">' + entry.sticker_quantity              + '</td>'
                        + '<td class="text-center">' + layoutLabel                         + '</td>'
                        + '<td class="text-center">'
                        +   '<button class="btn btn-sm btn-link p-0 btn-remove-queue-item" data-index="' + i + '" title="Remove">'
                        +     '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" class="bi bi-trash" viewBox="0 0 16 16">'
                        +       '<path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5Zm2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5Zm3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0V6Z"/>'
                        +       '<path d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1v1ZM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4H4.118ZM2.5 3h11V2h-11v1Z"/>'
                        +     '</svg>'
                        +   '</button>'
                        + '</td>'
                        + '</tr>';
                    $tbody.append(row);
                });
            }

            $('#queueTotalItems').text(count);
            $('#queueTotalQty').text(totalQty);
        });
    }

    // Load queue badge on page ready
    refreshQueueBadge();


    // Add to Export Queue button
    $(document).on('click', '#btnAddToQueue', function () {
        var mrId            = $('#mr_id').val();
        var labelSize       = $('#label_size').val();
        var qrLayout        = $('#qr_layout').val();
        var stickerQuantity = $('.stepper-quantity').val();

        if (!mrId) {
            showToast('No item selected.', 'danger');
            return;
        }

        var $btn = $(this);
        var isEditing = (editingQueueIndex !== null);
        var url = isEditing ? ('/inventory/queue/' + editingQueueIndex) : queueAddUrl;
        var type = isEditing ? 'PUT' : 'POST';

        $btn.prop('disabled', true).text(isEditing ? 'Updating…' : 'Adding…');

        $.ajax({
            url: url,
            type: type,
            data: {
                _token:           csrfToken,
                mr_id:            mrId,
                label_size:       labelSize,
                qr_layout:        qrLayout,
                sticker_quantity: stickerQuantity,
            },
            success: function (response) {
                if (response.status === 'success') {
                    showToast(response.message, 'success');
                    refreshQueueBadge();
                    // Close the item details modal
                    var myModal = bootstrap.Modal.getInstance(document.getElementById('itemDetailsModal'));
                    if (myModal) { myModal.hide(); }

                    // If we were editing, reopen the Export Queue modal after detail modal closes
                    if (isEditing) {
                        setTimeout(function () {
                            var queueModal = bootstrap.Modal.getOrCreateInstance(document.getElementById('exportQueueModal'));
                            queueModal.show();
                        }, 400);
                    }
                } else {
                    showToast(response.message || 'Failed to process request.', 'danger');
                }
            },
            error: function (xhr) {
                var msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Server error.';
                showToast(msg, 'danger');
            },
            complete: function () {
                var btnText = (editingQueueIndex !== null) ? 'Update Export Queue' : 'Add to Export Queue';
                $btn.prop('disabled', false).html(
                    '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16" class="me-2">'
                    + '<path d="M8 1a.5.5 0 0 1 .5.5v5h5a.5.5 0 0 1 0 1h-5v5a.5.5 0 0 1-1 0v-5h-5a.5.5 0 0 1 0-1h5v-5A.5.5 0 0 1 8 1"/>'
                    + '</svg><span>' + btnText + '</span>'
                );
            }
        });
    });

    // Row click handler for items in Export Queue modal
    $(document).on('click', '#exportQueueTableBody tr.queue-row', function (e) {
        // If clicking delete/remove, ignore
        if ($(e.target).closest('.btn-remove-queue-item').length) {
            e.stopPropagation();
            return;
        }

        var index = $(this).data('index');
        var mrId = $(this).data('mr-id');
        var size = $(this).data('size');
        var layout = $(this).data('layout');
        var qty = $(this).data('qty');

        // Hide export queue modal
        var queueModal = bootstrap.Modal.getInstance(document.getElementById('exportQueueModal'));
        if (queueModal) {
            queueModal.hide();
        }

        // Find the main inventory table row with this mr-id
        var $invRow = $('tr.inventory-row[data-mr-id="' + mrId + '"]');
        if ($invRow.length) {
            // Trigger row click to populate modal details
            $invRow.click();

            // Switch to Batch Export mode
            $('.toggle-export-mode').removeClass('active');
            $('.toggle-export-mode[data-mode="batch"]').addClass('active');
            $('#export_mode').val('batch');
            $('#exportModeToggleContainer').addClass('d-none');
            $('#paper-size-section').addClass('d-none');
            $('#btnCreateItemLabel').addClass('d-none');
            $('#btnAddToQueue').removeClass('d-none');

            // Set edit mode index
            editingQueueIndex = index;

            // Switch to Item Label tab (Tab 2)
            var itemLabelTabEl = document.querySelector('#item-label-tab');
            if (itemLabelTabEl) {
                var itemLabelTab = bootstrap.Tab.getOrCreateInstance(itemLabelTabEl);
                itemLabelTab.show();
            }

            // Pre-select label size card
            $('.size-card').removeClass('selected');
            $('.size-card .size-dim').removeClass('black-text');
            var $sizeCard = $('.size-card[data-size="' + size + '"]');
            $sizeCard.addClass('selected');
            $sizeCard.find('.size-dim').addClass('black-text');
            $('#label_size').val(size);

            // Toggle layout columns visibility depending on size choice
            if (size === 'Small') {
                $('#layout-2-col').hide();
            } else {
                $('#layout-2-col').show();
            }

            // Pre-select layout card
            $('.layout-card').removeClass('selected');
            $('.layout-card[data-layout="' + layout + '"]').addClass('selected');
            $('#qr_layout').val(layout);

            // Pre-fill quantity stepper
            $('.stepper-quantity').val(qty);

            // Change button text
            $('#btnAddToQueue span').text('Update Queue');
        } else {
            showToast('Item details could not be found in the current view.', 'danger');
        }
    });

    // Remove item from queue by index
    $(document).on('click', '.btn-remove-queue-item', function () {
        var index = $(this).data('index');
        var removeUrl = '/inventory/queue/' + index;

        $.ajax({
            url: removeUrl,
            type: 'DELETE',
            data: { _token: csrfToken },
            success: function () {
                refreshQueueBadge();
            },
            error: function () {
                showToast('Failed to remove item from queue.', 'danger');
            }
        });
    });

    // Clear entire queue
    $(document).on('click', '#btnClearQueue', function () {
        $.ajax({
            url: queueClearUrl,
            type: 'POST',
            data: { _token: csrfToken },
            success: function () {
                refreshQueueBadge();
            }
        });
    });

    // Refresh queue table when modal opens
    $('#exportQueueModal').on('show.bs.modal', function () {
        refreshQueueBadge();
    });

    // Toggle between Individual and Batch Export mode in details modal
    $(document).on('click', '.toggle-export-mode', function () {
        var mode = $(this).data('mode');
        $('.toggle-export-mode').removeClass('active');
        $(this).addClass('active');
        $('#export_mode').val(mode);

        if (mode === 'individual') {
            $('#paper-size-section').removeClass('d-none');
            $('#btnCreateItemLabel').removeClass('d-none');
            $('#btnAddToQueue').addClass('d-none');
        } else {
            $('#paper-size-section').addClass('d-none');
            $('#btnCreateItemLabel').addClass('d-none');
            $('#btnAddToQueue').removeClass('d-none');
        }
    });

    // Open Paper Size Selection Modal from Export Queue Modal
    $(document).on('click', '#btnExportQueuePdf', function () {
        // Hide Export Queue modal
        var queueModal = bootstrap.Modal.getInstance(document.getElementById('exportQueueModal'));
        if (queueModal) {
            queueModal.hide();
        }
        // Show Export Paper Size Modal
        var paperSizeModal = bootstrap.Modal.getOrCreateInstance(document.getElementById('exportPaperSizeModal'));
        paperSizeModal.show();
    });

    // Toggle selected paper size in export paper size modal
    $(document).on('click', '.export-paper-card', function () {
        $('.export-paper-card').removeClass('selected');
        $(this).addClass('selected');
        var ps = $(this).data('paper-size');
        $('#export_paper_size_choice').val(ps);
    });

    // Back/Cancel button on paper size modal: returns to export queue modal
    $(document).on('click', '#btnCancelPaperSize, .btn-close-custom-paper', function () {
        var paperSizeModal = bootstrap.Modal.getInstance(document.getElementById('exportPaperSizeModal'));
        if (paperSizeModal) {
            paperSizeModal.hide();
        }
        var queueModal = bootstrap.Modal.getOrCreateInstance(document.getElementById('exportQueueModal'));
        queueModal.show();
    });

    // Confirm Export Queue PDF with selected paper size
    $(document).on('click', '#btnConfirmExport', function () {
        var $btn = $(this);
        var selectedPaperSize = $('#export_paper_size_choice').val() || 'A4';
        var $spinner = $('#exportSpinner');

        $btn.prop('disabled', true);
        $spinner.removeClass('d-none');

        // Hide Paper Size Modal
        var paperSizeModal = bootstrap.Modal.getInstance(document.getElementById('exportPaperSizeModal'));
        if (paperSizeModal) {
            paperSizeModal.hide();
        }

        // Show general form loader overlay
        $('#form-loader-overlay').css('display', 'flex');

        fetch(queueExportUrl + '?paper_size=' + selectedPaperSize)
            .then(function (response) {
                if (!response.ok) { throw new Error('Server returned ' + response.status); }
                return response.blob();
            })
            .then(function (blob) {
                var blobUrl = window.URL.createObjectURL(blob);
                var a = document.createElement('a');
                a.href = blobUrl;
                a.download = 'export_queue.pdf';
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
                window.URL.revokeObjectURL(blobUrl);
                showToast('Export Queue PDF downloaded!', 'success');
                refreshQueueBadge(); // queue is cleared server-side after export
            })
            .catch(function (err) {
                console.error('Export queue failed:', err);
                showToast('Failed to export queue PDF. Please try again.', 'danger');
            })
            .finally(function () {
                $btn.prop('disabled', false);
                $spinner.addClass('d-none');
                $('#form-loader-overlay').hide();
            });
    });


    // Intercept click on Item Details Modal close button to return to the Export Queue modal when editing
    $(document).on('click', '#itemDetailsModal .btn-close-custom', function () {
        if (editingQueueIndex !== null) {
            shouldRestoreQueueModal = true;
        }
    });

    // Clean up viewport scale on modal hidden
    $('#itemDetailsModal').on('hidden.bs.modal', function () {
        var card = document.querySelector('.main-image-viewport-card');
        var cardBody = document.querySelector('.main-image-viewport-card .card-body');
        if (card) {
            card.style.width = '100%';
            card.style.margin = '';
            card.style.height = '';
            card.classList.remove('flex-grow-1');
            card.classList.add('mb-3');
        }
        if (cardBody) {
            cardBody.style.height = '250px';
            cardBody.style.minHeight = '250px';
            cardBody.classList.remove('flex-grow-1');
        }
        var wasEditing = (editingQueueIndex !== null);
        editingQueueIndex = null;
        $('#btnAddToQueue span').text('Add to Queue');

        if (wasEditing && shouldRestoreQueueModal) {
            shouldRestoreQueueModal = false;
            // Restore the export queue modal
            var queueModal = bootstrap.Modal.getOrCreateInstance(document.getElementById('exportQueueModal'));
            if (queueModal) {
                queueModal.show();
            }
        } else {
            shouldRestoreQueueModal = false;
        }
    });

    // Handle changing the item status
    $(document).on('change', '#detailItemStatus', function () {
        var newStatus = $(this).val();
        var previousStatus = $activeRow.attr('data-status') || 'Serviceable';

        if (newStatus === previousStatus) return;

        window.confirmAction({
            title: 'Change Item Status?',
            text: `Are you sure you want to change the status of this item to "${newStatus}"?`,
            icon: 'warning',
            confirmButtonText: 'Change',
            onConfirm: function () {
                $.ajax({
                    url: `/inventory/${activeMrId}/status`,
                    type: 'POST',
                    data: {
                        status: newStatus
                    },
                    success: function (response) {
                        if (response.success) {
                            showToast(response.message, 'success');
                            $activeRow.attr('data-status', newStatus);
                            updateStatusClass(newStatus);

                            // Update visual indicator dot in inventory table row
                            var colors = {
                                'Serviceable': '#00ab55',
                                'Unserviceable': '#e7515a',
                                'Missing': '#888ea8'
                            };
                            var $dot = $activeRow.find('td:nth-child(2) span.rounded-circle');
                            if ($dot.length) {
                                $dot.css('background-color', colors[newStatus]).attr('title', newStatus);
                            } else {
                                $activeRow.find('td:nth-child(2)').prepend(`<span class="d-inline-block rounded-circle me-2" style="width: 8px; height: 8px; vertical-align: middle; background-color: ${colors[newStatus]};" title="${newStatus}"></span>`);
                            }
                        } else {
                            showToast(response.message || 'Failed to update status.', 'error');
                            $('#detailItemStatus').val(previousStatus);
                            updateStatusClass(previousStatus);
                        }
                    },
                    error: function (xhr) {
                        var msg = xhr.responseJSON?.message || 'Something went wrong. Please try again.';
                        showToast(msg, 'error');
                        $('#detailItemStatus').val(previousStatus);
                        updateStatusClass(previousStatus);
                    }
                });
            },
            onCancel: function () {
                $('#detailItemStatus').val(previousStatus);
                updateStatusClass(previousStatus);
            }
        });
    });
});
