{{-- Extend the main layout that you want to use --}}
@extends('layouts.supply-layout')

{{-- Define contents to show in the layout --}}
@section('title', 'Inventory | I-TRAC')

@push('css')
    <!-- Page SPECIFIC css -->
    <link rel="stylesheet" href="{{ asset('css/supply/inventory/page-specific/apexcharts.css') }}">
    <link rel="stylesheet" href="{{ asset('css/supply/inventory/page-specific/dash_1.css') }}">
    <link rel="stylesheet" href="{{ asset('css/supply/inventory/page-specific/datatables.css') }}">
    <link rel="stylesheet" href="{{ asset('css/supply/inventory/page-specific/dt-global_style.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/src/splide/splide.min.css') }}">

    <!-- CUSTOM css -->
    <link rel="stylesheet" href="{{ asset('css/supply/inventory/custom-inventory.css') }}">

    <!-- DARK MODE SPECIFIC css -->
    <link rel="stylesheet" href="{{ asset('css/supply/inventory/page-specific/dark/dash_1.css') }}">
    <link rel="stylesheet" href="{{ asset('css/supply/inventory/page-specific/dark/dt-global_style.css') }}">
@endpush

@section('content')
    <div class="p-0">
        <div class="row row-cols-4">
            <div class="col">
                <div class="card">
                    <div class="card-body row p-4">
                        <div class="col-4">
                            <img src="{{ asset('img/mr-all.svg') }}" alt="ALL">
                        </div>
                        <div class="col-8 text-end">
                            <h6 class="card-title fw-bold">ALL</h6>
                            <h5 class="mb-0 fw-bold"><span>{{ $counts['all'] }}</span></h5>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col">
                <div class="card">
                    <div class="card-body row p-4">
                        <div class="col-4">
                            <img src="{{ asset('img/mr-equipment.svg') }}" alt="Equipment">
                        </div>
                        <div class="col-8 text-end">
                            <h6 class="card-title fw-bold">Equipment</h6>
                            <h5 class="mb-0 fw-bold"><span>{{ $counts['equipment'] }}</span></h5>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col">
                <div class="card">
                    <div class="card-body row p-4">
                        <div class="col-4">
                            <img src="{{ asset('img/mr-semi-expandable.svg') }}" alt="Semi-Expendable">
                        </div>
                        <div class="col-8 text-end">
                            <h6 class="card-title fw-bold">Semi-Expendable</h6>
                            <h5 class="mb-0 fw-bold"><span>{{ $counts['semi_expendable'] }}</span></h5>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col">
                <div class="card">
                    <div class="card-body row p-4">
                        <div class="col-4">
                            <img src="{{ asset('img/mr-supplies.svg') }}" alt="Supplies & Materials">
                        </div>
                        <div class="col-8 text-end">
                            <h6 class="card-title fw-bold">Supplies & Materials</h6>
                            <h5 class="mb-0 fw-bold"><span>{{ $counts['supplies'] }}</span></h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="widget-content widget-content-area br-8 mt-3 p-0">
        <table id="zero-config" class="table dt-table-hover table-w-100">
            <thead>
                <tr>
                    <th class="fw-bold text-nowrap text-center col-w-10">MR-ID</th>
                    <th class="fw-bold">Item Name</th>
                    <th class="fw-bold">Assigned to</th>
                    <th class="fw-bold text-nowrap text-center col-w-15">Office</th>
                    <th class="fw-bold text-nowrap text-center col-w-10">Date Received</th>
                    <th class="fw-bold text-nowrap text-center col-w-10">Category</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($mrItems as $item)
                    <tr class="inventory-row" style="cursor: pointer;" data-item-name="{{ $item->item_name }}"
                        data-assignee="{{ $item->assignedUser?->user_fullname ?? '—' }}"
                        data-office="{{ $item->assignedUser?->departments->first()?->dep_name ?? '—' }}"
                        data-date-scanned="{{ $item->date_scanned ? \Carbon\Carbon::parse($item->date_scanned)->format('Y-m-d') : '—' }}"
                        data-stock="{{ $item->stock ?? '—' }}" data-unit="{{ $item->unit ?? '—' }}"
                        data-specification="{{ $item->specification ?? '—' }}"
                        data-quantity="{{ $item->quantity ?? '—' }}" data-building="{{ $item->building ?? '—' }}"
                        data-room-no="{{ $item->room_no ?? '—' }}"
                        data-item-image="{{ $item->item_image ? asset($item->item_image) : '' }}"
                        data-mr-qr-code="{{ $item->mr_qr_code }}" data-category="{{ $item->category }}">
                        <td class="text-center">{{ $item->mr_qr_code }}</td>
                        <td>{{ $item->item_name }}</td>
                        <td>{{ $item->assignedUser?->user_fullname ?? '—' }}</td>
                        <td class="text-center">{{ $item->assignedUser?->departments->first()?->dep_name ?? '—' }}</td>
                        <td class="text-center">
                            {{ $item->date_scanned ? \Carbon\Carbon::parse($item->date_scanned)->format('Y-m-d') : '—' }}
                        </td>
                        <td class="text-center">
                            @if ($item->category === 'Supply and Materials')
                                <span class="badge badge-light-info">Supplies and Materials</span>
                            @elseif ($item->category === 'Semi-Expendable')
                                <span class="badge badge-light-success">Semi-Expendable</span>
                            @elseif ($item->category === 'Equipment')
                                <span class="badge badge-light-danger">Equipment</span>
                            @else
                                <span>—</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Item Details Modal -->
    <div class="modal fade" id="itemDetailsModal" tabindex="-1" aria-labelledby="itemDetailsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-5">
                <div class="modal-header border-bottom-0 pb-0 px-4 pt-4 d-flex justify-content-between align-items-center">
                    <h4 class="modal-title fw-bold red-text-2" id="itemDetailsModalLabel">Item Details</h4>
                    <button type="button" class="btn-close shadow-none border-0 btn-close-custom" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row">
                        <!-- Left Column: Media Canvas & Splide Sync -->
                        <div class="col-md-5 mb-4 mb-md-0 d-flex flex-column align-items-center">
                            <!-- Main Viewport Card -->
                            <div class="card border-0 main-image-viewport-card mb-3 w-100">
                                <div
                                    class="card-body p-0 d-flex align-items-center justify-content-center position-relative overflow-hidden">
                                    <!-- Active primary photo -->
                                    <img id="modalPrimaryImage" src="" alt="Primary Photo"
                                        class="img-fluid rounded-3" style="display: none;">
                                    <!-- No image placeholder text -->
                                    <div id="modalNoImagePlaceholder" class="text-center py-5 gray-text"
                                        style="display: none;">
                                        <i class="icon-placeholder d-block mx-auto mb-2 opacity-50 icon-no-image"></i>
                                        <span class="fw-bold no-image-text">No Image Available</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Thumbnail Carousel -->
                            <div id="modalThumbnailSlider" class="splide w-100">
                                <div class="splide__track">
                                    <ul class="splide__list" id="modalThumbnailList">
                                        <!-- Dynamic thumbnails -->
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <!-- Right Column: Navigation Tabs System -->
                        <div class="col-md-7">
                            <div class="card border-0 h-100 rounded-3 bg-transparent">
                                <div class="card-body p-0">
                                    <!-- Navigation Tabs -->
                                    <ul class="nav nav-tabs custom-detail-tabs border-bottom mb-3" id="itemModalTab"
                                        role="tablist">
                                        <li class="nav-item w-50" role="presentation">
                                            <button class="nav-link w-100 active text-center fw-bold py-2 border-0"
                                                id="details-tab" data-bs-toggle="tab" data-bs-target="#details-tab-pane"
                                                type="button" role="tab" aria-controls="details-tab-pane"
                                                aria-selected="true">Details</button>
                                        </li>
                                        <li class="nav-item w-50" role="presentation">
                                            <button class="nav-link w-100 text-center fw-bold py-2 border-0"
                                                id="item-label-tab" data-bs-toggle="tab"
                                                data-bs-target="#item-label-tab-pane" type="button" role="tab"
                                                aria-controls="item-label-tab-pane" aria-selected="false">Item
                                                Label</button>
                                        </li>
                                    </ul>

                                    <div class="tab-content" id="itemModalTabContent">
                                        <!-- Tab 1: Details (Read-Only State) -->
                                        <div class="tab-pane fade show active" id="details-tab-pane" role="tabpanel"
                                            aria-labelledby="details-tab" tabindex="0">
                                            <!-- Property Details Section -->
                                            <div class="mb-4">
                                                <h6 class="fw-bold d-flex align-items-center red-text-2">
                                                    <i
                                                        class="icon-placeholder d-inline-block me-2 icon-property-details"></i>
                                                    Property Details
                                                </h6>
                                                <div class="detail-rows">
                                                    <div class="d-flex justify-content-between py-2 border-bottom">
                                                        <span class="black-text">Item Name:</span>
                                                        <span id="detailItemName"
                                                            class="fw-bold text-end black-text"></span>
                                                    </div>
                                                    <div class="d-flex justify-content-between py-2 border-bottom">
                                                        <span class="black-text">Assignee:</span>
                                                        <span id="detailAssignee"
                                                            class="fw-bold text-end black-text"></span>
                                                    </div>
                                                    <div class="d-flex justify-content-between py-2 border-bottom">
                                                        <span class="black-text">Date Scanned:</span>
                                                        <span id="detailDateScanned"
                                                            class="fw-bold text-end black-text"></span>
                                                    </div>
                                                    <div class="d-flex justify-content-between py-2 border-bottom">
                                                        <span class="black-text">Stock:</span>
                                                        <span id="detailStock" class="fw-bold text-end black-text"></span>
                                                    </div>
                                                    <div class="d-flex justify-content-between py-2 border-bottom">
                                                        <span class="black-text">Unit:</span>
                                                        <span id="detailUnit" class="fw-bold text-end black-text"></span>
                                                    </div>
                                                    <div
                                                        class="d-flex justify-content-between py-2 border-bottom align-items-start">
                                                        <span class="black-text text-nowrap">Specifications:</span>
                                                        <span id="detailSpecifications"
                                                            class="fw-bold text-end black-text text-break detail-specifications"></span>
                                                    </div>
                                                    <div class="d-flex justify-content-between py-2 border-bottom">
                                                        <span class="black-text">Quantity:</span>
                                                        <span id="detailQuantity"
                                                            class="fw-bold text-end black-text"></span>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Location Information Section -->
                                            <div>
                                                <h6 class="fw-bold d-flex align-items-center red-text-2">
                                                    <i class="icon-placeholder d-inline-block me-2 icon-location-info"></i>
                                                    Location Information
                                                </h6>
                                                <div class="detail-rows">
                                                    <div class="d-flex justify-content-between py-2 border-bottom">
                                                        <span class="black-text">Building:</span>
                                                        <span id="detailBuilding"
                                                            class="fw-bold text-end black-text"></span>
                                                    </div>
                                                    <div class="d-flex justify-content-between py-2 border-bottom">
                                                        <span class="black-text">Room:</span>
                                                        <span id="detailRoom" class="fw-bold text-end black-text"></span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Tab 2: Item Label (Interactive Form State) -->
                                        <div class="tab-pane fade" id="item-label-tab-pane" role="tabpanel"
                                            aria-labelledby="item-label-tab" tabindex="0">
                                            <form id="createItemLabelForm">
                                                <h6 class="fw-bold d-flex align-items-center red-text-2">
                                                    <i class="icon-placeholder d-inline-block me-2 icon-qr-label"></i>
                                                    Create Item Label
                                                </h6>

                                                <!-- 1. Size Selection -->
                                                <div class="mb-3">
                                                    <h6 class="fw-bold red-text-2 d-block mb-2">1. Size</h6>
                                                    <div class="row g-2">
                                                        <input type="hidden" name="label_size" id="label_size"
                                                            value="Small">
                                                        <div class="col-4">
                                                            <div class="size-card p-2 text-center rounded border selected"
                                                                data-size="Small">
                                                                <div class="size-title small">Small</div>
                                                                <div class="size-dim text-muted black-text">3x3 cm</div>
                                                            </div>
                                                        </div>
                                                        <div class="col-4">
                                                            <div class="size-card p-2 text-center rounded border"
                                                                data-size="Medium">
                                                                <div class="size-title small">Medium</div>
                                                                <div class="size-dim text-muted">5x5 cm</div>
                                                            </div>
                                                        </div>
                                                        <div class="col-4">
                                                            <div class="size-card p-2 text-center rounded border"
                                                                data-size="Large">
                                                                <div class="size-title small">Large</div>
                                                                <div class="size-dim text-muted">10x10 cm</div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- 2. QR Label Layout -->
                                                <div class="mb-3">
                                                    <h6 class="fw-bold red-text-2 d-block mb-2">2. QR Label Layout</h6>
                                                    <div class="row g-3 justify-content-center">
                                                        <input type="hidden" name="qr_layout" id="qr_layout"
                                                            value="layout_1">
                                                        <!-- Layout 1 Card -->
                                                        <div class="col-6" id="layout-1-col">
                                                            <div class="layout-card p-2 rounded border text-center selected"
                                                                data-layout="layout_1">
                                                                <!-- Mock layout design -->
                                                                <div
                                                                    class="mx-auto my-2 p-2 bg-white rounded shadow-sm border position-relative d-flex align-items-center justify-content-center mock-layout-container mock-layout-layout1">
                                                                    <div class="qr-mock-code qr-mock-code-lg">
                                                                    </div>
                                                                    <div
                                                                        class="position-absolute bottom-0 start-0 end-0 py-1 text-white fw-bold text-center do-not-remove-banner banner-black">
                                                                        DO NOT REMOVE</div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <!-- Layout 2 Card -->
                                                        <div class="col-6" id="layout-2-col">
                                                            <div class="layout-card p-2 rounded border text-center"
                                                                data-layout="layout_2">
                                                                <!-- Mock layout design -->
                                                                <div
                                                                    class="mx-auto my-2 p-2 bg-white rounded shadow-sm border position-relative d-flex flex-column align-items-center justify-content-between mock-layout-container mock-layout-layout2">
                                                                    <div class="qr-mock-code qr-mock-code-sm"></div>
                                                                    <div class="w-100 text-start px-1 layout-details-text">
                                                                        <div class="border-bottom layout-details-line">
                                                                            Code: ________________</div>
                                                                        <div class="border-bottom layout-details-line">Prop
                                                                            No: ______________</div>
                                                                        <div class="border-bottom layout-details-line">
                                                                            Date: ________________</div>
                                                                    </div>
                                                                    <div
                                                                        class="position-absolute bottom-0 start-0 end-0 py-1 text-white fw-bold text-center do-not-remove-banner banner-black">
                                                                        DO NOT REMOVE</div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- 3. Quantity Stepper -->
                                                <div class="mb-4">

                                                    <h6 class="fw-bold red-text-2 d-block mb-2">3. Quantity <span
                                                            class="text-muted fw-normal">(Number of
                                                            Stickers)</span></h6>
                                                    <div class="input-group input-group-sm w-75 shadow-sm">
                                                        <button
                                                            class="btn btn-outline-secondary btn-stepper-minus px-3 stepper-btn"
                                                            type="button">&minus;</button>
                                                        <input type="text"
                                                            class="form-control text-center stepper-quantity stepper-input"
                                                            name="sticker_quantity" value="15">
                                                        <button
                                                            class="btn btn-outline-secondary btn-stepper-plus px-3 stepper-btn"
                                                            type="button">&plus;</button>
                                                    </div>
                                                </div>

                                                <!-- Footer button inside the card -->
                                                <button type="submit"
                                                    class="btn btn-red w-100 py-2.5 d-flex align-items-center justify-content-center fw-bold">
                                                    <i
                                                        class="icon-placeholder d-inline-block me-2 icon-btn-create-label"></i>
                                                    Create Item Label
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('js')
    <!-- Page SPECIFIC js -->
    <script src="{{ asset('js/supply/inventory/page-specific/apexcharts.min.js') }}"></script>
    <script src="{{ asset('js/supply/inventory/page-specific/dash_1.js') }}"></script>
    <script src="{{ asset('js/supply/inventory/page-specific/datatables.js') }}"></script>

    <!-- Splide.js js -->
    <script src="{{ asset('plugins/src/splide/splide.min.js') }}"></script>

    <!-- CUSTOM js -->
    <script src="{{ asset('js/supply/inventory/custom-inventory.js') }}"></script>
@endpush
