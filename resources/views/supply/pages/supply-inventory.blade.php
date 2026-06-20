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
                            <img src="{{ asset('img/ALL.svg') }}" alt="ALL">
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
                            <img src="{{ asset('img/EQUIPMENT.svg') }}" alt="Equipment">
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
                            <img src="{{ asset('img/SEMI-EXP.svg') }}" alt="Semi-Expendable">
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
                    @php
                        $images = $item->images->map(function ($img) {
                            return [
                                'url' => asset($img->image_path),
                                'path' => $img->image_path
                            ];
                        })->toArray();
                    @endphp
                    <tr class="inventory-row" style="cursor: pointer;" data-item-name="{{ $item->item_name }}"
                        data-mr-id="{{ $item->mr_id }}"
                        data-assignee="{{ $item->assignedUser?->user_fullname ?? '—' }}"
                        data-office="{{ $item->assignedUser?->departments->first()?->dep_name ?? '—' }}"
                        data-date-scanned="{{ $item->date_scanned ? \Carbon\Carbon::parse($item->date_scanned)->format('Y-m-d') : '—' }}"
                        data-stock="{{ $item->stock ?? '—' }}" data-unit="{{ $item->unit ?? '—' }}"
                        data-specification="{{ $item->specification ?? '—' }}"
                        data-quantity="{{ $item->quantity ?? '—' }}" data-building="{{ $item->building ?? '—' }}"
                        data-room-no="{{ $item->room_no ?? '—' }}"
                        data-item-images="{{ json_encode($images) }}"
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
            <div class="modal-content border-0 shadow-lg rounded-3">
                <div class="modal-header border-bottom-0 pb-0 px-3 pt-3 d-flex justify-content-between align-items-center">
                    <h4 class="modal-title fw-bold red-text-2" id="itemDetailsModalLabel">Item Details</h4>
                    <button type="button" class="btn-close shadow-none border-0 btn-close-custom" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body px-3 pt-0">
                    <div class="row">
                        <!-- Left Column: Media Canvas & Splide Sync -->
                        <div class="col-md-5 mb-4 mb-md-0 d-flex flex-column align-items-center">
                            <!-- Main Viewport Card -->
                            <div class="card border-0 main-image-viewport-card mb-3 w-100 position-relative">
                                <div
                                    class="card-body p-0 d-flex align-items-center justify-content-center position-relative overflow-hidden">
                                    <!-- Active primary photo -->
                                    <img id="modalPrimaryImage" src="" alt="Primary Photo"
                                        class="img-fluid rounded-3" style="display: none;">

                                    <!-- Delete Button Overlay (Commented out - managed on other page) -->
                                    <!--
                                    <button type="button" class="btn btn-danger btn-delete-image position-absolute top-0 end-0 m-2 shadow-sm rounded-circle d-flex align-items-center justify-content-center" id="btnDeleteActiveImage" style="display: none; width: 32px; height: 32px; z-index: 10;" title="Delete this image">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="bi bi-trash3" viewBox="0 0 16 16">
                                            <path d="M6.5 1h3a.5.5 0 0 1 .5.5v1H6v-1a.5.5 0 0 1 .5-.5ZM11 2.5v-1A1.5 1.5 0 0 0 9.5 0h-3A1.5 1.5 0 0 0 5 1.5v1H2.506a.58.58 0 0 0-.01 0H1.5a.5.5 0 0 0 0 1h.538l.853 10.66A2 2 0 0 0 4.885 16h6.23a2 2 0 0 0 1.994-1.84l.853-10.66h.538a.5.5 0 0 0 0-1h-.995a.59.59 0 0 0-.01 0H11Zm1.958 1-.846 10.58a1 1 0 0 1-.997.92h-6.23a1 1 0 0 1-.997-.92L3.042 3.5h9.916Zm-7.487 1a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0v-6a.5.5 0 0 1 .5-.5Zm3 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0v-6a.5.5 0 0 1 .5-.5Zm3 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0v-6a.5.5 0 0 1 .5-.5Z"/>
                                        </svg>
                                    </button>
                                    -->

                                    <!-- No image placeholder text -->
                                    <div id="modalNoImagePlaceholder" class="text-center py-5 gray-text"
                                        style="display: none;">
                                        <i class="icon-placeholder d-block mx-auto mb-2 opacity-50 icon-no-image"></i>
                                        <span class="fw-bold no-image-text">No Image Available</span>
                                    </div>
                                </div>
                            </div>
                                       <!-- Image Grid Slots -->
                            <div id="modalImageGrid" class="image-grid-slots mt-2 w-100" style="display: none;">
                                <!-- Dynamic slots (max 4) -->
                            </div>

                            <!-- Bottom Action & Counter Bar -->
                            <!--
                            <div class="w-100 mt-2 px-3 d-flex justify-content-center align-items-center" id="modalImageActionsContainer">
                                <span class="small text-muted fw-bold" id="modalImageCountText">0/5 Images</span>
                            </div>
                            -->

                            <!-- Hidden Upload Input -->
                            <!--
                            <input type="file" id="modalImageFileInput" accept="image/*" style="display: none;">
                            -->
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
                                        <div class="tab-pane fade px-2 show active" id="details-tab-pane" role="tabpanel"
                                            aria-labelledby="details-tab" tabindex="0">
                                            <!-- Property Details Section -->
                                            <div class="mb-3">
                                                <h6 class="fw-bold d-flex align-items-center red-text-2">
                                                    <img src="{{ asset('img/property-details-icon.svg') }}">
                                                    <span class="ms-2">Property Details</span>
                                                </h6>
                                                <div class="detail-rows">
                                                    <div class="row m-0 py-2 border-bottom align-items-center">
                                                        <div class="col-4 p-0 black-text">Item Name:</div>
                                                        <div class="col-8 p-0 fw-bold text-start black-text" id="detailItemName"></div>
                                                    </div>
                                                    <div class="row m-0 py-2 border-bottom align-items-center">
                                                        <div class="col-4 p-0 black-text">Assignee:</div>
                                                        <div class="col-8 p-0 fw-bold text-start black-text" id="detailAssignee"></div>
                                                    </div>
                                                    <div class="row m-0 py-2 border-bottom align-items-center">
                                                        <div class="col-4 p-0 black-text">Date Scanned:</div>
                                                        <div class="col-8 p-0 fw-bold text-start black-text" id="detailDateScanned"></div>
                                                    </div>
                                                    <div class="row m-0 py-2 border-bottom align-items-center">
                                                        <div class="col-4 p-0 black-text">Stock:</div>
                                                        <div class="col-8 p-0 fw-bold text-start black-text" id="detailStock"></div>
                                                    </div>
                                                    <div class="row m-0 py-2 border-bottom align-items-center">
                                                        <div class="col-4 p-0 black-text">Unit:</div>
                                                        <div class="col-8 p-0 fw-bold text-start black-text" id="detailUnit"></div>
                                                    </div>
                                                    <div class="row m-0 py-2 border-bottom align-items-start">
                                                        <div class="col-4 p-0 black-text text-nowrap">Specifications:</div>
                                                        <div class="col-8 p-0 fw-bold text-start black-text text-break" id="detailSpecifications"></div>
                                                    </div>
                                                    <div class="row m-0 py-2 border-bottom align-items-center">
                                                        <div class="col-4 p-0 black-text">Quantity:</div>
                                                        <div class="col-8 p-0 fw-bold text-start black-text" id="detailQuantity"></div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Location Information Section -->
                                            <div>
                                                <h6 class="fw-bold d-flex align-items-center red-text-2">
                                                    <img src="{{ asset('img/location-icon.svg') }}">
                                                    <span class="ms-2">Location Information</span>
                                                </h6>
                                                <div class="detail-rows">
                                                    <div class="row m-0 py-2 border-bottom align-items-center">
                                                        <div class="col-4 p-0 black-text">Building:</div>
                                                        <div class="col-8 p-0 fw-bold text-start black-text" id="detailBuilding"></div>
                                                    </div>
                                                    <div class="row m-0 py-2 border-bottom align-items-center">
                                                        <div class="col-4 p-0 black-text">Room:</div>
                                                        <div class="col-8 p-0 fw-bold text-start black-text" id="detailRoom"></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Tab 2: Item Label (Interactive Form State) -->
                                        <div class="tab-pane fade px-2" id="item-label-tab-pane" role="tabpanel"
                                            aria-labelledby="item-label-tab" tabindex="0">
                                            <form id="createItemLabelForm" action="{{ route('inventory.generate-label') }}" method="GET">
                                                <input type="hidden" name="mr_qr_code" id="mr_qr_code" value="">
                                                <h6 class="fw-bold d-flex align-items-center red-text-2">
                                                    <img src="{{ asset('img/red-qr-code-icon.svg') }}">
                                                    <span class="ms-2">Create Item Label
                                                </h6>

                                                <!-- 1. Size Selection -->
                                                <div class="mb-3">
                                                    <h6 class="fw-bold red-text-2 small d-block mb-2">1. Size</h6>
                                                    <div class="row g-2">
                                                        <input type="hidden" name="label_size" id="label_size"
                                                            value="Small">
                                                        <div class="col-4">
                                                            <div class="size-card p-2 text-center rounded border selected"
                                                                data-size="Small">
                                                                <div class="size-title small">Small</div>
                                                                <div class="size-dim text-muted">(Sample Items)</div>
                                                            </div>
                                                        </div>
                                                        <div class="col-4">
                                                            <div class="size-card p-2 text-center rounded border"
                                                                data-size="Medium">
                                                                <div class="size-title small">Medium</div>
                                                                <div class="size-dim text-muted">(Sample Items)</div>
                                                            </div>
                                                        </div>
                                                        <div class="col-4">
                                                            <div class="size-card p-2 text-center rounded border"
                                                                data-size="Large">
                                                                <div class="size-title small">Large</div>
                                                                <div class="size-dim text-muted">(Sample Items)</div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- 2. QR Label Layout -->
                                                <div class="mb-3">
                                                    <h6 class="fw-bold red-text-2 d-block small mb-2">2. QR Label Layout
                                                    </h6>
                                                    <div class="row g-3 justify-content-center">
                                                        <input type="hidden" name="qr_layout" id="qr_layout"
                                                            value="layout_1">
                                                        <!-- Layout 1 Card -->
                                                        <div class="col-6" id="layout-1-col">
                                                            <div class="layout-card p-2 rounded border text-center selected"
                                                                data-layout="layout_1">
                                                                <img src="{{ asset('img/qr-label-layout-1.svg') }}"
                                                                    alt="Layout 1"
                                                                    class="mx-auto my-2 img-fluid shadow-sm border rounded"
                                                                    style="height: 80px; object-fit: contain;">
                                                            </div>
                                                        </div>
                                                        <!-- Layout 2 Card -->
                                                        <div class="col-6" id="layout-2-col">
                                                            <div class="layout-card p-2 rounded border text-center"
                                                                data-layout="layout_2">
                                                                <img src="{{ asset('img/qr-label-layout-2.svg') }}"
                                                                    alt="Layout 2"
                                                                    class="mx-auto my-2 img-fluid shadow-sm border rounded"
                                                                    style="height: 80px; object-fit: contain;">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- 3. Quantity Stepper -->
                                                <div class="mb-3">

                                                    <h6 class="fw-bold red-text-2 d-block small mb-2">3. Quantity <span
                                                            class="text-muted fw-normal">(Number of
                                                            Stickers)</span></h6>
                                                    <div class="input-group input-group-sm w-50 shadow-sm mx-auto">
                                                        <button class="btn btn-stepper-minus px-3 stepper-btn"
                                                            type="button">&minus;</button>
                                                        <input type="text"
                                                            class="form-control text-center stepper-quantity stepper-input"
                                                            name="sticker_quantity" value="15">
                                                        <button class="btn btn-stepper-plus px-3 stepper-btn"
                                                            type="button">&plus;</button>
                                                    </div>
                                                </div>

                                                <!-- 4. Paper Size -->
                                                <div class="mb-3">
                                                    <h6 class="fw-bold red-text-2 d-block small mb-2">4. Paper Size</h6>
                                                    <div class="row g-2 justify-content-center">
                                                        <input type="hidden" name="paper_size" id="paper_size"
                                                            value="A6">
                                                        <div class="col-5">
                                                            <div class="paper-size-card p-2 text-center rounded border selected"
                                                                data-paper-size="A6">
                                                                <div class="paper-title small">A6</div>
                                                                <div class="paper-dim text-muted small">(105 x 148 mm)</div>
                                                            </div>
                                                        </div>
                                                        
                                                        <div class="col-5">
                                                            <div class="paper-size-card p-2 text-center rounded border"
                                                                data-paper-size="A4">
                                                                <div class="paper-title small">A4</div>
                                                                <div class="paper-dim text-muted small">(210 x 297mm)</div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Footer button inside the card -->
                                                <div class="text-center">
                                                    <button type="submit"
                                                        class="btn btn-red btn-md w-100 py-2 d-flex align-items-center justify-content-center fw-bold">
                                                        <img src="{{ asset('img/white-qr-code-icon.svg') }}">
                                                        <span class="ms-2">Create Item Label</span>
                                                    </button>
                                                </div>
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
