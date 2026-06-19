$(document).ready(function() {
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
    $('#zero-config tbody').on('click', 'tr.inventory-row', function(e) {
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
        var itemImage = $row.data('item-image');

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

        $('.layout-card').removeClass('selected');
        $('.layout-card[data-layout="layout_1"]').addClass('selected');
        $('#qr_layout').val('layout_1');
        
        // Hide Layout 2 since Small is selected by default
        $('#layout-2-col').hide();

        $('.stepper-quantity').val('15');

        // Handle Media/Image Gallery
        var primaryImg = document.getElementById('modalPrimaryImage');
        var noImgPlaceholder = document.getElementById('modalNoImagePlaceholder');
        var thumbnailSliderContainer = document.getElementById('modalThumbnailSlider');
        var thumbnailList = document.getElementById('modalThumbnailList');

        // Clear dynamic slides
        thumbnailList.innerHTML = '';

        // Destroy old splide slider instance if exists
        if (window.modalSplide) {
            window.modalSplide.destroy();
            window.modalSplide = null;
        }

        if (itemImage) {
            // Render primary photo
            primaryImg.src = itemImage;
            primaryImg.style.display = 'block';
            noImgPlaceholder.style.display = 'none';
            thumbnailSliderContainer.style.display = 'block';

            // Create three thumbnail slides using the item's main image
            for (var i = 0; i < 3; i++) {
                var li = document.createElement('li');
                li.className = 'splide__slide';
                li.innerHTML = '<img src="' + itemImage + '" alt="Thumbnail ' + (i + 1) + '">';
                thumbnailList.appendChild(li);
            }

            // Initialize Splide for thumbnails
            setTimeout(function() {
                window.modalSplide = new Splide('#modalThumbnailSlider', {
                    fixedWidth  : 60,
                    fixedHeight : 60,
                    gap         : 10,
                    rewind      : true,
                    pagination  : false,
                    isNavigation: true,
                    arrows      : true,
                    focus       : 'center'
                });

                window.modalSplide.mount();

                // Listen to Splide's active slide change to update primary image
                window.modalSplide.on('active', function (slide) {
                    var img = slide.slide.querySelector('img');
                    if (img) {
                        primaryImg.src = img.src;
                    }
                });

                // Workaround for direct click syncing on images
                $('#modalThumbnailList').on('click', '.splide__slide img', function() {
                    primaryImg.src = this.src;
                });
            }, 150);

        } else {
            // "No image" text in container instead of image fallback
            primaryImg.style.display = 'none';
            noImgPlaceholder.style.display = 'block';
            thumbnailSliderContainer.style.display = 'none';
        }

        // Show Bootstrap Modal
        var myModal = bootstrap.Modal.getOrCreateInstance(document.getElementById('itemDetailsModal'));
        myModal.show();
    });

    // Sizing selectors toggles
    $(document).on('click', '.size-card', function() {
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
    $(document).on('click', '.layout-card', function() {
        $('.layout-card').removeClass('selected');
        $(this).addClass('selected');
        $('#qr_layout').val($(this).data('layout'));
    });

    // Stepper Quantity buttons logic
    $(document).on('click', '.btn-stepper-plus', function() {
        var $input = $(this).siblings('.stepper-quantity');
        var val = parseInt($input.val()) || 0;
        $input.val(val + 1);
    });

    $(document).on('click', '.btn-stepper-minus', function() {
        var $input = $(this).siblings('.stepper-quantity');
        var val = parseInt($input.val()) || 1;
        if (val > 1) {
            $input.val(val - 1);
        }
    });

    $(document).on('change', '.stepper-quantity', function() {
        var val = parseInt($(this).val()) || 1;
        if (val < 1) {
            val = 1;
        }
        $(this).val(val);
    });

    // Form Submission Alert handler
    $(document).on('submit', '#createItemLabelForm', function(e) {
        e.preventDefault();
        var size = $('#label_size').val();
        var layout = $('#qr_layout').val();
        var quantity = $('.stepper-quantity').val();
        var itemName = $('#detailItemName').text();

        // Confirm label generation action
        if (window.confirmAction) {
            window.confirmAction({
                title: 'Generate Item Label?',
                text: 'Create ' + quantity + ' labels (' + size + ', ' + layout + ') for ' + itemName,
                icon: 'question',
                confirmButtonText: 'Yes, Create',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Success!',
                        text: 'Labels successfully generated.',
                        icon: 'success',
                        confirmButtonColor: '#630418'
                    });
                    var myModal = bootstrap.Modal.getInstance(document.getElementById('itemDetailsModal'));
                    if (myModal) myModal.hide();
                }
            });
        } else {
            Swal.fire({
                title: 'Generate Item Label?',
                text: 'Create ' + quantity + ' labels (' + size + ', ' + layout + ') for ' + itemName,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Yes, Create',
                cancelButtonText: 'Cancel',
                confirmButtonColor: '#630418'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Success!',
                        text: 'Labels successfully generated.',
                        icon: 'success',
                        confirmButtonColor: '#630418'
                    });
                    var myModal = bootstrap.Modal.getInstance(document.getElementById('itemDetailsModal'));
                    if (myModal) myModal.hide();
                }
            });
        }
    });
});

