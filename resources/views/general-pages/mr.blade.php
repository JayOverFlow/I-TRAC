@push('css')
    <!-- Page SPECIFIC css -->
    <link rel="stylesheet" href="{{ asset('css/head/dashboard/page-specific/apexcharts.css') }}">
    <link rel="stylesheet" href="{{ asset('css/head/dashboard/page-specific/dash_1.css') }}">
    <link rel="stylesheet" href="{{ asset('css/head/dashboard/page-specific/datatables.css') }}">
    <link rel="stylesheet" href="{{ asset('css/head/dashboard/page-specific/dt-global_style.css') }}">

    <!-- CUSTOM css -->
    <link rel="stylesheet" href="{{ asset('css/head/dashboard/custom-dashboard.css') }}">

    <!-- DARK MODE SPECIFIC css -->
    <link rel="stylesheet" href="{{ asset('css/head/dashboard/page-specific/dark/dash_1.css') }}">
    <link rel="stylesheet" href="{{ asset('css/head/dashboard/page-specific/dark/dt-global_style.css') }}">

    <!-- FilePond CSS -->
    <link rel="stylesheet" href="{{ asset('plugins/src/filepond/filepond.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/src/filepond/FilePondPluginImagePreview.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/css/light/filepond/custom-filepond.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/css/dark/filepond/custom-filepond.css') }}">

    <!-- Property Assignment Modal CSS -->
    <link rel="stylesheet" href="{{ asset('css/general-pages/mr-modal.css') }}">
@endpush

@section('content')
    <div class="p-0">
        <div class="row row-cols-3">
            <div class="col">
                <div class="card">
                    <div class="card-body row p-4">
                        <div class="col-4">
                            <img src="{{ asset('img/All.svg') }}" alt="ALL" width="70" height="70">
                        </div>
                        <div class="col-8 text-end">
                            <h6 class="card-title fw-bold">All</h6>
                            <h5 class="mb-0 fw-bold"><span>{{ $data->count() }}</span></h5>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col">
                <div class="card">
                    <div class="card-body row p-4">
                        <div class="col-4">
                            <img src="{{ asset('img/Equipment.svg') }}" alt="Equipment">
                        </div>
                        <div class="col-8 text-end">
                            <h6 class="card-title fw-bold">Equipment</h6>
                            <h5 class="mb-0 fw-bold"><span>{{ $data->where('category', 'Equipment')->count() }}</span></h5>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col">
                <div class="card">
                    <div class="card-body row p-4">
                        <div class="col-4">
                            <img src="{{ asset('img/SEMI-EXP.svg') }}" alt="Semi-Expandable">
                        </div>
                        <div class="col-8 text-end">
                            <h6 class="card-title fw-bold">Semi-Expandable</h6>
                            <h5 class="mb-0 fw-bold"><span>{{ $data->where('category', 'Semi-Expendable')->count() }}</span></h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="widget-content widget-content-area br-8 mt-3 p-0">
        <table id="zero-config" class="table dt-table-hover" style="width:100%">
            <thead>
                <tr>
                    <th class="fw-bold text-nowrap text-center" style="width: 1%">MR ID</th>
                    <th class="fw-bold">Item Name</th>
                    <th class="fw-bold">Location</th>
                    <th class="fw-bold text-nowrap text-center" style="width: 1%">Date Received</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($data as $item)
                    <tr style="cursor: pointer;" class="mr-row"
                        data-mr-id="{{ $item->mr_id }}"
                        data-item-name="{{ $item->item_name }}"
                        data-date-scanned="{{ $item->date_scanned ? \Carbon\Carbon::parse($item->date_scanned)->format('m/d/Y') : '—' }}"
                        data-stock="{{ $item->stock ?? '' }}"
                        data-unit="{{ $item->unit ?? '---' }}"
                        data-specification="{{ $item->specification ?? '—' }}"
                        data-quantity="{{ $item->quantity ?? '1' }}"
                        data-building="{{ $item->building ?? '' }}"
                        data-room-no="{{ $item->room_no ?? '' }}"
                        data-item-images="{{ json_encode($item->images->map(fn($img) => ['url' => asset($img->image_path), 'path' => $img->image_path])) }}">
                        <td class="text-center text-nowrap">{{ $item->mr_qr_code }}</td>
                        <td>{{ $item->item_name }}</td>
                        <td>
                            @if ($item->building && $item->room_no)
                                {{ $item->building }} - {{ $item->room_no }}
                            @else
                                {{ $item->building ?? $item->room_no ?? '—' }}
                            @endif
                        </td>
                        <td class="text-center text-nowrap">
                            {{ $item->date_scanned ? \Carbon\Carbon::parse($item->date_scanned)->format('Y-m-d') : '—' }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Property Assignment Details Modal -->
    <div class="modal fade" id="propertyAssignmentModal" tabindex="-1" aria-labelledby="propertyModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-bottom-0 pb-0 d-flex justify-content-between align-items-start">
                    <div>
                        <h4 class="modal-title" id="modalItemTitle"></h4>
                    </div>
                    <button type="button" class="btn-close shadow-none border-0 mt-1" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <!-- Left Column: Image Upload Area -->
                        <div class="col-md-6 d-flex align-items-stretch">
                            <div class="card w-100">
                                <div class="card-body p-4 d-flex flex-column justify-content-between">
                                    
                                    <!-- FilePond Container (Shown when 0 images) -->
                                    <div id="filepondUploadContainer" class="multiple-file-upload flex-grow-1 position-relative">
                                        <input type="file" 
                                            class="filepond file-upload-multiple"
                                            name="filepond" 
                                            multiple 
                                            data-allow-reorder="true"
                                            data-max-file-size="10MB"
                                            data-max-files="5"
                                            accept="image/jpeg, image/png, image/webp">
                                        
                                        <!-- Custom Uploading Overlay -->
                                        <div class="custom-upload-overlay d-none position-absolute top-0 start-0 w-100 h-100 d-flex flex-column align-items-center justify-content-center" style="z-index: 5; border-radius: 8px;">
                                            <div class="spinner-border text-danger mb-2" role="status" style="width: 2rem; height: 2rem;">
                                                <span class="visually-hidden">Loading...</span>
                                            </div>
                                            <span class="fw-bold">Uploading...</span>
                                        </div>
                                    </div>

                                    <!-- Gallery Grid Container (Shown when 1+ images) -->
                                    <div id="galleryContainer" class="flex-grow-1 d-flex flex-column d-none">
                                        <!-- Main Viewport Card -->
                                        <div class="card border-0 main-image-viewport-card mb-3 w-100 position-relative">
                                            <div class="card-body p-0 d-flex align-items-center justify-content-center position-relative overflow-hidden" style="height: 250px; min-height: 250px;">
                                                <!-- Active primary photo -->
                                                <img id="modalPrimaryImage" src="" alt="Primary Photo" class="img-fluid rounded-3">

                                                <!-- Delete Button Overlay (red outline trash icon at bottom-left) -->
                                                <button type="button" class="btn btn-delete-image position-absolute bottom-0 start-0 m-2 d-flex align-items-center justify-content-center" id="btnDeleteActiveImage" style="z-index: 10;" title="Delete this image">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-trash-2" viewBox="0 0 24 24">
                                                        <polyline points="3 6 5 6 21 6"></polyline>
                                                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                                        <line x1="10" y1="11" x2="10" y2="17"></line>
                                                        <line x1="14" y1="11" x2="14" y2="17"></line>
                                                    </svg>
                                                </button>
                                            </div>
                                        </div>

                                        <!-- Image Grid Slots -->
                                        <div id="modalImageGrid" class="image-grid-slots mt-2 w-100">
                                            <!-- Dynamic slots (max 4) -->  
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Right Column: Details & Location Stack -->
                        <div class="col-md-6">
                            <!-- Property Details Card -->
                            <div class="card mb-3">
                                <div class="card-body p-4">
                                    <h6 class="fw-bold red-text-2 mb-2 d-flex align-items-center">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="me-2">
                                            <path fill-rule="evenodd" clip-rule="evenodd" d="M21.75 4.5H2.25C2.05109 4.5 1.86032 4.57902 1.71967 4.71967C1.57902 4.86032 1.5 5.05109 1.5 5.25V18.75C1.5 18.9489 1.57902 19.1397 1.71967 19.2803C1.86032 19.421 2.05109 19.5 2.25 19.5H21.75C21.9489 19.5 22.1397 19.421 22.2803 19.2803C22.421 19.1397 22.5 18.9489 22.5 18.75V5.25C22.5 5.05109 22.421 4.86032 22.2803 4.71967C22.1397 4.57902 21.9489 4.5 21.75 4.5ZM2.25 3C1.65326 3 1.08097 3.23705 0.65901 3.65901C0.237053 4.08097 0 4.65326 0 5.25L0 18.75C0 19.3467 0.237053 19.919 0.65901 20.341C1.08097 20.7629 1.65326 21 2.25 21H21.75C22.3467 21 22.919 20.7629 23.341 20.341C23.7629 19.919 24 19.3467 24 18.75V5.25C24 4.65326 23.7629 4.08097 23.341 3.65901C22.919 3.23705 22.3467 3 21.75 3H2.25Z" fill="currentColor"/>
                                            <path fill-rule="evenodd" clip-rule="evenodd" d="M4.5 12.75C4.5 12.5511 4.57902 12.3603 4.71967 12.2197C4.86032 12.079 5.05109 12 5.25 12H18.75C18.9489 12 19.1397 12.079 19.2803 12.2197C19.421 12.3603 19.5 12.5511 19.5 12.75C19.5 12.9489 19.421 13.1397 19.2803 13.2803C19.1397 13.421 18.9489 13.5 18.75 13.5H5.25C5.05109 13.5 4.86032 13.421 4.71967 13.2803C4.57902 13.1397 4.5 12.9489 4.5 12.75ZM4.5 15.75C4.5 15.5511 4.57902 15.3603 4.71967 15.2197C4.86032 15.079 5.05109 15 5.25 15H14.25C14.4489 15 14.6397 15.079 14.7803 15.2197C14.921 15.3603 15 15.5511 15 15.75C15 15.9489 14.921 16.1397 14.7803 16.2803C14.6397 16.421 14.4489 16.5 14.25 16.5H5.25C5.05109 16.5 4.86032 16.421 4.71967 16.2803C4.57902 16.1397 4.5 15.9489 4.5 15.75Z" fill="currentColor"/>
                                            <path d="M4.5 8.25C4.5 8.05109 4.57902 7.86032 4.71967 7.71967C4.86032 7.57902 5.05109 7.5 5.25 7.5H18.75C18.9489 7.5 19.1397 7.57902 19.2803 7.71967C19.421 7.86032 19.5 8.05109 19.5 8.25V9.75C19.5 9.94891 19.421 10.1397 19.2803 10.2803C19.1397 10.421 18.9489 10.5 18.75 10.5H5.25C5.05109 10.5 4.86032 10.421 4.71967 10.2803C4.57902 10.1397 4.5 9.94891 4.5 9.75V8.25Z" fill="currentColor"/>
                                        </svg>
                                        Property Details
                                    </h6>
                                    <div class="property-details-scroll-container">
                                        <div class="detail-row"><span class="detail-label">Item Name:</span> <span class="detail-value" id="modalItemName"></span></div>
                                        <div class="detail-row"><span class="detail-label">Date Scanned:</span> <span class="detail-value" id="modalDateScanned"></span></div>
                                        <div class="detail-row"><span class="detail-label">Stock:</span> <span class="detail-value" id="modalStock"></span></div>
                                        <div class="detail-row"><span class="detail-label">Unit:</span> <span class="detail-value" id="modalUnit"></span></div>
                                        <div class="detail-row text-break"><span class="detail-label">Specifications:</span> <span class="detail-value" id="modalSpecifications"></span></div>
                                        <div class="detail-row mb-0"><span class="detail-label">Quantity:</span> <span class="detail-value" id="modalQuantity"></span></div>
                                    </div>
                                </div>
                            </div>

                            <!-- Location Information Card -->
                            <div class="card">
                                <div class="card-body p-4">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <h6 class="fw-bold red-text-2 mb-0 d-flex align-items-center">
                                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="me-2">
                                                <path d="M12 12C12.55 12 13.0208 11.8042 13.4125 11.4125C13.8042 11.0208 14 10.55 14 10C14 9.45 13.8042 8.97917 13.4125 8.5875C13.0208 8.19583 12.55 8 12 8C11.45 8 10.9792 8.19583 10.5875 8.5875C10.1958 8.97917 10 9.45 10 10C10 10.55 10.1958 11.0208 10.5875 11.4125C10.9792 11.8042 11.45 12 12 12ZM12 19.35C14.0333 17.4833 15.5417 15.7875 16.525 14.2625C17.5083 12.7375 18 11.3833 18 10.2C18 8.38333 17.4208 6.89583 16.2625 5.7375C15.1042 4.57917 13.6833 4 12 4C10.3167 4 8.89583 4.57917 7.7375 5.7375C6.57917 6.89583 6 8.38333 6 10.2C6 11.3833 6.49167 12.7375 7.475 14.2625C8.45833 15.7875 9.96667 17.4833 12 19.35ZM12 22C9.31667 19.7167 7.3125 17.5958 5.9875 15.6375C4.6625 13.6792 4 11.8667 4 10.2C4 7.7 4.80417 5.70833 6.4125 4.225C8.02083 2.74167 9.88333 2 12 2C14.1167 2 15.9792 2.74167 17.5875 4.225C19.1958 5.70833 20 7.7 20 10.2C20 11.8667 19.3375 13.6792 18.0125 15.6375C16.6875 17.5958 14.6833 19.7167 12 22Z" fill="currentColor"/>
                                            </svg>
                                            Location Information
                                        </h6>
                                        <!-- Edit Controls -->
                                        <div id="locationEditControlsContainer" class="d-flex align-items-center" style="height: 28px;">
                                            <!-- Pencil Icon Button -->
                                            <button type="button" class="btn p-0 border-0 bg-transparent location-edit-btn d-flex align-items-center justify-content-center" id="btnEditLocation" style="width: 28px; height: 28px;" title="Edit location">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="location-edit-icon text-muted"><path d="M12 20h9"></path><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path></svg>
                                            </button>
                                            <!-- Save / Cancel Buttons (Hidden by default) -->
                                            <div id="locationActionButtons" class="d-none d-flex gap-2">
                                                <!-- X Button: white bg, light border, black X -->
                                                <button type="button" class="btn btn-cancel-location d-flex align-items-center justify-content-center" id="btnCancelLocation" style="width: 28px; height: 28px; padding: 0; border-radius: 4px; background-color: white; border: 1px solid #cbd5e1; color: black;" title="Cancel">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" class="feather feather-x"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                                                </button>
                                                <!-- Check Button: red bg, white check -->
                                                <button type="button" class="btn btn-save-location d-flex align-items-center justify-content-center" id="btnSaveLocation" style="width: 28px; height: 28px; padding: 0; border-radius: 4px; background-color: #8C0404; border: none; color: white;" title="Save">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" class="feather feather-check"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Building</label>
                                        <input type="text" class="form-control shadow-none" id="modalBuildingInput" placeholder="College of Education and Sciences" disabled>
                                    </div>
                                    <div class="mb-0">
                                        <label class="form-label">Room No.</label>
                                        <input type="text" class="form-control shadow-none" id="modalRoomInput" placeholder="Faculty Room" disabled>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Fullscreen Lightbox Overlay -->
    <div id="imageLightbox" class="lightbox-overlay d-none">
        <span class="lightbox-close">&times;</span>
        <img class="lightbox-content" id="lightboxImage" src="" alt="Fullscreen View">
    </div>
@endsection

@push('js')
    <!-- Page SPECIFIC js -->
    <script src="{{ asset('js/head/dashboard/page-specific/apexcharts.min.js') }}"></script>
    <script src="{{ asset('js/head/dashboard/page-specific/dash_1.js') }}"></script>
    <script src="{{ asset('js/head/dashboard/page-specific/datatables.js') }}"></script>
    <script>
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
                "sLengthMenu": "<h4 class='fw-bold mb-0 red-text-2'>Property Assignment</h4>"
            },
            "stripeClasses": [],
            "lengthMenu": [5, 10, 20, 50],
            "pageLength": 5
        });
    </script>

    <!-- FilePond Scripts -->
    <script src="{{ asset('plugins/src/filepond/filepond.min.js') }}"></script>
    <script src="{{ asset('plugins/src/filepond/FilePondPluginImagePreview.min.js') }}"></script>
    <script src="{{ asset('plugins/src/filepond/FilePondPluginImageExifOrientation.min.js') }}"></script>
    <script src="{{ asset('plugins/src/filepond/FilePondPluginFileValidateType.min.js') }}"></script>
    <script src="{{ asset('plugins/src/filepond/filepondPluginFileValidateSize.min.js') }}"></script>

    <script>
        // State variables
        let activeImages = [];
        let activeImagePath = null;
        let activeMrId = null;
        let $activeRow = null;
        let uploadCount = 0;

        function updateUploadStatus() {
            const isUploading = uploadCount > 0;
            if (isUploading && activeImages.length === 0) {
                $('#filepondUploadContainer .custom-upload-overlay').removeClass('d-none');
            } else {
                $('#filepondUploadContainer .custom-upload-overlay').addClass('d-none');
            }
            renderItemImages();
        }

        // Register FilePond Plugins
        FilePond.registerPlugin(
            FilePondPluginImagePreview,
            FilePondPluginImageExifOrientation,
            FilePondPluginFileValidateSize,
            FilePondPluginFileValidateType
        );
        
        // Initialize FilePond
        const pond = FilePond.create(
            document.querySelector('.file-upload-multiple'),
            {
                allowMultiple: true,
                maxFiles: 5,
                maxFileSize: '10MB',
                acceptedFileTypes: ['image/jpeg', 'image/png', 'image/webp'],
                fileValidateTypeDetectType: (source, type) => new Promise((resolve, reject) => {
                    const ext = source.name ? '.' + source.name.split('.').pop().toLowerCase() : '';
                    if (['.jpeg', '.jpg', '.png', '.webp'].includes(ext)) {
                        resolve(type || ('image/' + (ext === '.jpg' ? 'jpeg' : ext.substring(1))));
                    } else {
                        resolve(type);
                    }
                }),
                labelIdle: `<div class="d-flex flex-column align-items-center justify-content-center py-2" style="cursor: pointer;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="40" height="48" viewBox="0 0 24 28" fill="none" stroke="#d9534f" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mb-2">
                                  <path d="M14 2H6a2 2 0 0 0-2 2v20a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                  <polyline points="14 2 14 8 20 8"></polyline>
                                  <line x1="12" y1="18" x2="12" y2="12"></line>
                                  <polyline points="9 15 12 12 15 15"></polyline>
                                </svg>
                                <span class="fw-bold" style="color: #3b3f5c;">Drag and drop your image here</span>
                                <span class="small text-muted">or <span class="text-danger text-decoration-underline" style="cursor: pointer;">click to browse</span></span>
                                <div class="mt-2 text-muted" style="font-size: 11px; line-height: 1.4;">
                                    Supported files: .jpeg, .png, .webp<br>
                                    Maximum size: 10MB
                                </div>
                            </div>`,
                server: {
                    process: (fieldName, file, metadata, load, error, progress, abort, transfer, options) => {
                        if (!activeMrId) {
                            error('No active item selected.');
                            return;
                        }

                        if (activeImages.length >= 5) {
                            error('Maximum limit of 5 images reached.');
                            return;
                        }

                        uploadCount++;
                        updateUploadStatus();

                        const formData = new FormData();
                        formData.append('mr_id', activeMrId);
                        formData.append('item_image', file);

                        const request = $.ajax({
                            url: '/inventory/upload-image',
                            method: 'POST',
                            data: formData,
                            processData: false,
                            contentType: false,
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            xhr: function () {
                                const xhr = new window.XMLHttpRequest();
                                xhr.upload.addEventListener('progress', function (evt) {
                                    if (evt.lengthComputable) {
                                        progress(evt.lengthComputable, evt.loaded, evt.total);
                                    }
                                }, false);
                                return xhr;
                            },
                            success: function (response) {
                                uploadCount = Math.max(0, uploadCount - 1);
                                updateUploadStatus();
                                if (response.status === 'success') {
                                    showToast(response.message || 'Image uploaded successfully!', 'success');
                                    activeImages = response.images;
                                    if (activeImages.length > 0) {
                                        activeImagePath = activeImages[activeImages.length - 1].path;
                                    }

                                    if ($activeRow) {
                                        $activeRow.attr('data-item-images', JSON.stringify(activeImages));
                                        $activeRow.data('item-images', activeImages);
                                    }

                                    renderItemImages();
                                    load(response);
                                    
                                    setTimeout(() => {
                                        pond.removeFile(file.id);
                                    }, 1000);
                                } else {
                                    error(response.message || 'Upload failed');
                                    showToast(response.message || 'Upload failed', 'danger');
                                }
                            },
                            error: function (xhr) {
                                uploadCount = Math.max(0, uploadCount - 1);
                                updateUploadStatus();
                                let errMsg = 'Upload failed';
                                if (xhr.responseJSON && xhr.responseJSON.message) {
                                    errMsg = xhr.responseJSON.message;
                                }
                                error(errMsg);
                                showToast(errMsg, 'danger');
                            }
                        });

                        return {
                            abort: () => {
                                request.abort();
                                uploadCount = Math.max(0, uploadCount - 1);
                                updateUploadStatus();
                                abort();
                            }
                        };
                    }
                }
            }
        );

        // Show FilePond upload container when files are added or rejected
        pond.on('addfile', (error, fileItem) => {
            if (error) {
                showToast(error.sub || error.main || 'Invalid file format or size.', 'danger');
                pond.removeFile(fileItem.id);
                return;
            }
            if (activeImages.length === 0) {
                $('#filepondUploadContainer').removeClass('d-none');
            }
        });

        // Hide FilePond upload container if files are removed and we still have gallery images
        pond.on('removefile', (error, fileItem) => {
            if (activeImages.length > 0) {
                $('#filepondUploadContainer').addClass('d-none');
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
            
            if ($('.toast-container').length === 0) {
                $('body').append('<div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 1060;"></div>');
            }
            
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
            const primaryImg = document.getElementById('modalPrimaryImage');
            const gridContainer = document.getElementById('modalImageGrid');
            const btnDelete = document.getElementById('btnDeleteActiveImage');
            const filepondContainer = document.getElementById('filepondUploadContainer');
            const galleryContainer = document.getElementById('galleryContainer');

            const totalImages = activeImages.length;

            if (totalImages > 0) {
                // Hide FilePond, show gallery
                $('#filepondUploadContainer').addClass('d-none');
                $('#galleryContainer').removeClass('d-none');

                // Find active image object, default to first if not found/null
                let activeObj = activeImages.find(function (img) { return img.path === activeImagePath; });
                if (!activeObj) {
                    activeObj = activeImages[0];
                    activeImagePath = activeObj.path;
                }

                // Show primary image with dynamic aspect ratio scaling
                primaryImg.onload = function() {
                    const isPortrait = primaryImg.naturalHeight > primaryImg.naturalWidth;
                    const card = document.querySelector('.main-image-viewport-card');
                    const cardBody = document.querySelector('.main-image-viewport-card .card-body');
                    if (card && cardBody && primaryImg.naturalHeight > 0) {
                        const aspectRatio = primaryImg.naturalWidth / primaryImg.naturalHeight;
                        if (isPortrait) {
                            cardBody.style.height = '400px';
                            cardBody.style.minHeight = '400px';
                            card.style.width = Math.round(330 * aspectRatio) + 'px';
                            card.style.margin = '0 auto';
                        } else {
                            cardBody.style.height = '250px';
                            cardBody.style.minHeight = '250px';
                            card.style.width = '100%';
                            card.style.margin = '';
                        }
                    }
                };

                primaryImg.src = activeObj.url;
                primaryImg.style.display = 'block';
                if (btnDelete) $(btnDelete).show(); // Enable delete button overlay

                if (primaryImg.complete) {
                    primaryImg.onload();
                }

                // Render 4 slots below
                gridContainer.innerHTML = '';

                // Filter non-active images
                const nonActiveImages = activeImages.filter(function (img) { return img.path !== activeImagePath; });
                const totalSlots = 4;
                let renderedSlots = 0;

                // 1. Render other images first
                for (let i = 0; i < nonActiveImages.length; i++) {
                    const imgObj = nonActiveImages[i];
                    const slotHtml = `
                        <div class="image-grid-slot slot-image" data-path="${imgObj.path}">
                            <img src="${imgObj.url}" alt="Thumbnail">
                        </div>
                    `;
                    gridContainer.insertAdjacentHTML('beforeend', slotHtml);
                    renderedSlots++;
                }

                // 2. Render "+" add slot if we have less than 5 images total
                if (activeImages.length < 5) {
                    if (uploadCount > 0) {
                        const addHtml = `
                            <div class="image-grid-slot slot-add uploading" title="Uploading Image...">
                                <div class="spinner-border spinner-border-sm text-danger" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                            </div>
                        `;
                        gridContainer.insertAdjacentHTML('beforeend', addHtml);
                    } else {
                        const addHtml = `
                            <div class="image-grid-slot slot-add" title="Add Image">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-plus"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                            </div>
                        `;
                        gridContainer.insertAdjacentHTML('beforeend', addHtml);
                    }
                    renderedSlots++;
                }

                // 3. Render empty slots to fill the rest of the 4 slots
                while (renderedSlots < totalSlots) {
                    const emptyHtml = '<div class="image-grid-slot slot-empty"></div>';
                    gridContainer.insertAdjacentHTML('beforeend', emptyHtml);
                    renderedSlots++;
                }
            } else {
                // No images available - show FilePond, hide gallery
                $('#filepondUploadContainer').removeClass('d-none');
                $('#galleryContainer').addClass('d-none');
                primaryImg.style.display = 'none';
                primaryImg.src = '';
                const card = document.querySelector('.main-image-viewport-card');
                const cardBody = document.querySelector('.main-image-viewport-card .card-body');
                if (card) {
                    card.style.width = '100%';
                    card.style.margin = '';
                }
                if (cardBody) {
                    cardBody.style.height = '250px';
                    cardBody.style.minHeight = '250px';
                }
                if (btnDelete) $(btnDelete).hide();
                activeImagePath = null;
            }
        }

        // Handle click on image slot to swap active image
        $(document).on('click', '#modalImageGrid .slot-image', function () {
            activeImagePath = $(this).data('path');
            renderItemImages();
        });

        // Trigger file selection when clicking add image slot
        $(document).on('click', '#modalImageGrid .slot-add', function () {
            if (uploadCount > 0) return;
            pond.browse();
        });

        // Handle Delete Image
        $(document).on('click', '#btnDeleteActiveImage', function () {
            if (!activeImagePath || !activeMrId) return;

            const $btn = $(this);

            window.confirmAction({
                title: 'Delete Image?',
                text: 'Are you sure you want to delete this image? This action cannot be undone.',
                icon: 'warning',
                confirmButtonText: 'Yes, Delete',
                cancelButtonText: 'Cancel',
                onConfirm: function () {
                    $btn.prop('disabled', true);
                    $.ajax({
                        url: '/inventory/delete-image',
                        type: 'POST',
                        data: {
                            mr_id: activeMrId,
                            image_path: activeImagePath
                        },
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function (response) {
                            if (response.status === 'success') {
                                showToast(response.message || 'Image deleted successfully!', 'success');
                                
                                // Update state
                                activeImages = response.images;
                                activeImagePath = activeImages.length > 0 ? activeImages[0].path : null;

                                // Update table row attribute and jQuery data cache so it persists
                                if ($activeRow) {
                                    $activeRow.attr('data-item-images', JSON.stringify(activeImages));
                                    $activeRow.data('item-images', activeImages);
                                }

                                // Re-render gallery
                                renderItemImages();
                            } else {
                                showToast(response.message || 'Failed to delete image.', 'danger');
                            }
                        },
                        error: function (xhr) {
                            let message = 'An error occurred during deletion.';
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                message = xhr.responseJSON.message;
                            }
                            showToast(message, 'danger');
                        },
                        complete: function () {
                            $btn.prop('disabled', false);
                        }
                    });
                }
            });
        });

        // Click handler for Property Assignment rows
        $(document).on('click', '.mr-row', function() {
            $activeRow = $(this);
            const mrId = $activeRow.data('mr-id');
            activeMrId = mrId;
            const itemName = $activeRow.data('item-name');
            const dateScanned = $activeRow.data('date-scanned');
            const stock = $activeRow.data('stock');
            const unit = $activeRow.data('unit');
            const specification = $activeRow.data('specification');
            const quantity = $activeRow.data('quantity');
            const building = $activeRow.data('building');
            const roomNo = $activeRow.data('room-no');

            // Parse images safely from jQuery data cache or attr
            let rawImages = $activeRow.data('item-images');
            if (typeof rawImages === 'string') {
                try {
                    activeImages = JSON.parse(rawImages);
                } catch (e) {
                    activeImages = [];
                }
            } else if (Array.isArray(rawImages)) {
                activeImages = rawImages;
            } else {
                activeImages = [];
            }
            activeImagePath = activeImages.length > 0 ? activeImages[0].path : null;

            // Populate modal fields
            $('#modalItemTitle').text(itemName);
            $('#modalItemName').text(itemName);
            $('#modalDateScanned').text(dateScanned);
            $('#modalStock').text(stock || '');
            $('#modalUnit').text(unit);
            $('#modalSpecifications').text(specification);
            $('#modalQuantity').text(quantity);

            // Populate form inputs
            $('#modalBuildingInput').val(building).attr('data-original-val', building || '').prop('disabled', true);
            $('#modalRoomInput').val(roomNo).attr('data-original-val', roomNo || '').prop('disabled', true);

            // Reset location edit controls
            $('#btnEditLocation').removeClass('d-none');
            $('#locationActionButtons').addClass('d-none');

            // Clear previous files in FilePond when opening a new item
            pond.removeFiles();

            // Render gallery layout state
            renderItemImages();

            // Open the modal
            $('#propertyAssignmentModal').modal('show');
        });

        $('#propertyAssignmentModal').on('hidden.bs.modal', function () {
            uploadCount = 0;
            updateUploadStatus();
            // Reset location controls
            $('#modalBuildingInput').prop('disabled', true);
            $('#modalRoomInput').prop('disabled', true);
            $('#btnEditLocation').removeClass('d-none');
            $('#locationActionButtons').addClass('d-none');
        });

        // Location Information Edit Toggle
        $(document).on('click', '#btnEditLocation', function () {
            $('#modalBuildingInput').prop('disabled', false).focus();
            $('#modalRoomInput').prop('disabled', false);
            $('#btnEditLocation').addClass('d-none');
            $('#locationActionButtons').removeClass('d-none');
        });

        // Cancel Location Edit
        $(document).on('click', '#btnCancelLocation', function () {
            const origBuilding = $('#modalBuildingInput').attr('data-original-val') || '';
            const origRoom = $('#modalRoomInput').attr('data-original-val') || '';
            $('#modalBuildingInput').val(origBuilding).prop('disabled', true);
            $('#modalRoomInput').val(origRoom).prop('disabled', true);
            $('#btnEditLocation').removeClass('d-none');
            $('#locationActionButtons').addClass('d-none');
        });

        // Save Location Edit
        $(document).on('click', '#btnSaveLocation', function () {
            const $btn = $(this);
            const newBuilding = $('#modalBuildingInput').val();
            const newRoom = $('#modalRoomInput').val();

            $btn.prop('disabled', true);

            $.ajax({
                url: '/mr/update-location',
                type: 'POST',
                data: {
                    mr_id: activeMrId,
                    building: newBuilding,
                    room_no: newRoom
                },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function (response) {
                    if (response.status === 'success') {
                        // Update original val attributes
                        $('#modalBuildingInput').attr('data-original-val', newBuilding).prop('disabled', true);
                        $('#modalRoomInput').attr('data-original-val', newRoom).prop('disabled', true);

                        // Update active row data cache & attributes so it persists locally
                        if ($activeRow) {
                            $activeRow.attr('data-building', newBuilding);
                            $activeRow.data('building', newBuilding);
                            $activeRow.attr('data-room-no', newRoom);
                            $activeRow.data('room-no', newRoom);

                            // Update the table cell text
                            let text = '—';
                            if (newBuilding && newRoom) {
                                text = newBuilding + ' - ' + newRoom;
                            } else if (newBuilding) {
                                text = newBuilding;
                            } else if (newRoom) {
                                text = newRoom;
                            }
                            $activeRow.find('td').eq(2).text(text);
                        }

                        $('#btnEditLocation').removeClass('d-none');
                        $('#locationActionButtons').addClass('d-none');

                        showToast('Location updated successfully!', 'success');
                    } else {
                        showToast(response.message || 'Failed to update location.', 'danger');
                    }
                },
                error: function (xhr) {
                    let message = 'An error occurred while updating location.';
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

        // Open fullscreen lightbox when clicking the primary image
        $(document).on('click', '#modalPrimaryImage', function () {
            const src = $(this).attr('src');
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
    </script>

@endpush
