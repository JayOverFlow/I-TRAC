$(document).ready(function () {
    // Active item and image gallery states
    var activeMrId = null;
    var activeImages = []; // List of {url, path} objects
    var activeImagePath = null; // The relative path of the currently viewed image
    var $activeRow = null; // Reference to the clicked table row to update its data attribute

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
        "pageLength": 5
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

        $('.stepper-quantity').val('15');

        // Reset paper size selection to A4
        $('.paper-size-card').removeClass('selected');
        $('.paper-size-card[data-paper-size="A6"]').addClass('selected');
        $('#paper_size').val('A6');

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

    // Paper size selectors toggles
    $(document).on('click', '.paper-size-card', function() {
        $('.paper-size-card').removeClass('selected');
        $(this).addClass('selected');
        $('#paper_size').val($(this).data('paper-size'));
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

        // Send a fetch GET request to the backend label generator endpoint.
        // The backend returns JSON: { pdf_urls: ["/img/qr_stickers/file1.pdf", ...] }
        fetch(actionUrl + '?' + queryParams, {
            headers: { 'Accept': 'application/json' }
        })
        .then(function (response) {
            if (!response.ok) {
                throw new Error('Server returned ' + response.status);
            }
            return response.json();
        })
        .then(function (data) {
            // Loop through each returned PDF URL and trigger a browser download
            // by creating a temporary hidden <a> element with the download attribute.
            if (data.pdf_urls && data.pdf_urls.length > 0) {
                data.pdf_urls.forEach(function (url, index) {
                    // Stagger downloads slightly to prevent browser throttling
                    setTimeout(function () {
                        var a = document.createElement('a');
                        a.href = url;
                        a.download = '';
                        document.body.appendChild(a);
                        a.click();
                        document.body.removeChild(a);
                    }, index * 500);
                });
            }
        })
        .catch(function (err) {
            console.error('Label generation failed:', err);
            alert('Failed to generate labels. Please try again.');
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
    });
});

