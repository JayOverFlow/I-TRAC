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
    <link rel="stylesheet" href="{{ asset('plugins/src/flatpickr/flatpickr.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/css/light/flatpickr/custom-flatpickr.css') }}">

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
                            <img src="{{ asset('img/All.svg') }}" alt="ALL" width="70" height="70">
                        </div>
                        <div class="col-8 text-end">
                            <h6 class="card-title fw-bold">All</h6>
                            <h4 class="mb-0 fw-bold"><span>{{ $counts['all'] }}</span></h4>
                            <a href="javascript:void(0);" class="export-report-link" data-bs-toggle="modal" data-bs-target="#exportReportModal">Generate Report</a>
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
                    <th class="fw-bold text-nowrap text-center col-w-5"></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($mrItems as $item)
                    @if ($item->category === 'Semi-Expendable' || $item->category === 'Equipment')
                        @php
                            $images = $item->images
                                ->map(function ($img) {
                                    return [
                                        'url' => asset($img->image_path),
                                        'path' => $img->image_path,
                                    ];
                                })
                                ->toArray();
                        @endphp
                        <tr class="inventory-row" style="cursor: pointer;" data-item-name="{{ $item->item_name }}"
                            data-mr-id="{{ $item->mr_id }}"
                            data-assignee="{{ $item->assignedUser?->user_fullname ?? '—' }}"
                            data-office="{{ $item->assignedUser?->departments->first()?->dep_name ?? '—' }}"
                            data-date-scanned="{{ $item->date_scanned ? \Carbon\Carbon::parse($item->date_scanned)->format('Y-m-d') : '—' }}"
                            data-stock="{{ $item->stock ?? '—' }}" data-unit="{{ $item->unit ?? '—' }}"
                            data-specification="{{ $item->specification ?? '—' }}"
                            data-quantity="{{ $item->quantity ?? '—' }}" data-building="{{ $item->building ?? '—' }}"
                            data-room-no="{{ $item->room_no ?? '—' }}" data-item-images="{{ json_encode($images) }}"
                            data-mr-qr-code="{{ $item->mr_qr_code }}" data-category="{{ $item->category }}"
                            data-status="{{ $item->status }}">
                            <td class="text-center">{{ $item->mr_qr_code }}</td>
                            <td>
                                @if (in_array($item->status, ['Serviceable', 'Unserviceable', 'Missing']))
                                    <span class="d-inline-block rounded-circle me-2"
                                        style="width: 8px; height: 8px; vertical-align: middle; background-color: {{ ['Serviceable' => '#00ab55', 'Unserviceable' => '#e7515a', 'Missing' => '#888ea8'][$item->status] }};"
                                        title="{{ $item->status }}"></span>
                                @endif{{ $item->item_name }}
                            </td>
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
                            <td class="text-center action-dropdown-menu">
                                <div class="dropdown">
                                    <button
                                        class="btn btn-link text-secondary p-0 border-0 dropdown-toggle no-caret shadow-none"
                                        type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                            stroke-linecap="round" stroke-linejoin="round"
                                            class="feather feather-more-vertical">
                                            <circle cx="12" cy="12" r="1"></circle>
                                            <circle cx="12" cy="5" r="1"></circle>
                                            <circle cx="12" cy="19" r="1"></circle>
                                        </svg>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                        <li>
                                            <button class="dropdown-item btn-transfer-action" type="button"
                                                data-mr-id="{{ $item->mr_id }}"
                                                data-item-name="{{ $item->item_name }}">
                                                Transfer
                                            </button>
                                        </li>
                                        <li>
                                            <button class="dropdown-item btn-condemn-action text-danger" type="button"
                                                data-mr-id="{{ $item->mr_id }}"
                                                data-item-name="{{ $item->item_name }}">
                                                Condemn
                                            </button>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    @endif
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Item Details Modal -->
    <div class="modal fade" id="itemDetailsModal" tabindex="-1" aria-labelledby="itemDetailsModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-3">
                <div class="modal-header border-bottom-0 pb-0 px-3 pt-3 d-flex justify-content-between align-items-center">
                    <h4 class="modal-title fw-bold red-text-2" id="itemDetailsModalLabel">Item Details</h4>
                    <button type="button" class="btn-close shadow-none border-0 btn-close-custom"
                        data-bs-dismiss="modal" aria-label="Close"></button>
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
                                        <div class="tab-pane fade px-4 show active" id="details-tab-pane" role="tabpanel"
                                            aria-labelledby="details-tab" tabindex="0">
                                            <!-- Property Details Section -->
                                            <div class="mb-3">
                                                <div
                                                    class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-2">
                                                    <h6 class="fw-bold d-flex align-items-center red-text-2 mb-0">
                                                        <img src="{{ asset('img/property-details-icon.svg') }}">
                                                        <span class="ms-2">Property Details</span>
                                                    </h6>
                                                    <div>
                                                        <select class="form-select form-select-sm fw-bold border-1 ps-2"
                                                            id="detailItemStatus"
                                                            style="font-size: 0.8rem; border-radius: 6px; border-color: #dee2e6;">
                                                            <option class="text-success" value="Serviceable">● Serviceable
                                                            </option>
                                                            <option class="text-danger" value="Unserviceable">●
                                                                Unserviceable</option>
                                                            <option class="text-dark" value="Missing">● Missing</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="detail-rows">
                                                    <div class="row m-0 py-2 border-bottom align-items-center">
                                                        <div class="col-4 p-0 black-text">Item Name:</div>
                                                        <div class="col-8 p-0 fw-bold text-start black-text"
                                                            id="detailItemName"></div>
                                                    </div>
                                                    <div class="row m-0 py-2 border-bottom align-items-center">
                                                        <div class="col-4 p-0 black-text">Assignee:</div>
                                                        <div class="col-8 p-0 fw-bold text-start black-text"
                                                            id="detailAssignee"></div>
                                                    </div>
                                                    <div class="row m-0 py-2 border-bottom align-items-center">
                                                        <div class="col-4 p-0 black-text">Date Scanned:</div>
                                                        <div class="col-8 p-0 fw-bold text-start black-text"
                                                            id="detailDateScanned"></div>
                                                    </div>
                                                    <div class="row m-0 py-2 border-bottom align-items-center">
                                                        <div class="col-4 p-0 black-text">Stock:</div>
                                                        <div class="col-8 p-0 fw-bold text-start black-text"
                                                            id="detailStock"></div>
                                                    </div>
                                                    <div class="row m-0 py-2 border-bottom align-items-center">
                                                        <div class="col-4 p-0 black-text">Unit:</div>
                                                        <div class="col-8 p-0 fw-bold text-start black-text"
                                                            id="detailUnit"></div>
                                                    </div>
                                                    <div class="row m-0 py-2 border-bottom align-items-start">
                                                        <div class="col-4 p-0 black-text text-nowrap">Specifications:</div>
                                                        <div class="col-8 p-0 fw-bold text-start black-text text-break"
                                                            id="detailSpecifications"></div>
                                                    </div>
                                                    <div class="row m-0 py-2 border-bottom align-items-center">
                                                        <div class="col-4 p-0 black-text">Quantity:</div>
                                                        <div class="col-8 p-0 fw-bold text-start black-text"
                                                            id="detailQuantity"></div>
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
                                                        <div class="col-8 p-0 fw-bold text-start black-text"
                                                            id="detailBuilding"></div>
                                                    </div>
                                                    <div class="row m-0 py-2 border-bottom align-items-center">
                                                        <div class="col-4 p-0 black-text">Room:</div>
                                                        <div class="col-8 p-0 fw-bold text-start black-text"
                                                            id="detailRoom"></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Tab 2: Item Label (Interactive Form State) -->
                                        <div class="tab-pane fade px-2" id="item-label-tab-pane" role="tabpanel"
                                            aria-labelledby="item-label-tab" tabindex="0">
                                            <form id="createItemLabelForm"
                                                action="{{ route('inventory.generate-label') }}" method="GET">
                                                <input type="hidden" name="mr_id" id="mr_id" value="">
                                                <input type="hidden" name="mr_qr_code" id="mr_qr_code" value="">
                                                <div class="d-flex align-items-center mb-2 flex-wrap gap-2">
                                                    <h6 class="fw-bold d-flex align-items-center red-text-2 mb-0">
                                                        <img src="{{ asset('img/red-qr-code-icon.svg') }}">
                                                        <span class="ms-2">Create Item Label</span>
                                                    </h6>
                                                    <!-- Toggle: Individual vs Batch Export -->
                                                    <div id="exportModeToggleContainer"
                                                        class="d-flex toggle-export-mode-container align-items-center"
                                                        style="padding: 2px; border-radius: 6px; gap: 1px;">
                                                        <button type="button"
                                                            class="btn btn-xs py-1 px-2 fw-bold toggle-export-mode active"
                                                            data-mode="individual"
                                                            style="font-size: 0.7rem; border: none; border-radius: 4px; padding: 2px 8px !important;">
                                                            Individual
                                                        </button>
                                                        <button type="button"
                                                            class="btn btn-xs py-1 px-2 fw-bold toggle-export-mode"
                                                            data-mode="batch"
                                                            style="font-size: 0.7rem; border: none; border-radius: 4px; padding: 2px 8px !important;">
                                                            Batch
                                                        </button>
                                                    </div>
                                                    <input type="hidden" name="export_mode" id="export_mode"
                                                        value="individual">
                                                </div>

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
                                                            </div>
                                                        </div>
                                                        <div class="col-4">
                                                            <div class="size-card p-2 text-center rounded border"
                                                                data-size="Medium">
                                                                <div class="size-title small">Medium</div>
                                                            </div>
                                                        </div>
                                                        <div class="col-4">
                                                            <div class="size-card p-2 text-center rounded border"
                                                                data-size="Large">
                                                                <div class="size-title small">Large</div>
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
                                                            name="sticker_quantity">
                                                        <button class="btn btn-stepper-plus px-3 stepper-btn"
                                                            type="button">&plus;</button>
                                                    </div>
                                                </div>

                                                <!-- 4. Paper Size -->
                                                <div class="mb-3" id="paper-size-section">
                                                    <h6 class="fw-bold red-text-2 d-block small mb-2">4. Paper Size</h6>
                                                    <div class="row g-2 justify-content-center">
                                                        <input type="hidden" name="paper_size" id="paper_size"
                                                            value="A4">
                                                        <div class="col-5">
                                                            <div class="paper-size-card p-2 text-center rounded border selected"
                                                                data-paper-size="A4">
                                                                <div class="paper-title small">A4</div>
                                                                <div class="paper-dim text-muted small">(210 x 297mm)</div>
                                                            </div>
                                                        </div>

                                                        <div class="col-5">
                                                            <div class="paper-size-card p-2 text-center rounded border"
                                                                data-paper-size="A6">
                                                                <div class="paper-title small">A6</div>
                                                                <div class="paper-dim text-muted small">(105 x 148 mm)
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Footer buttons — side by side and centered with reduced width -->
                                                <div class="d-flex justify-content-center mt-4">
                                                    <button type="submit" id="btnCreateItemLabel"
                                                        class="btn btn-dark-red btn-md py-2 px-4 d-flex align-items-center justify-content-center fw-bold"
                                                        style="width: 220px; max-width: 100%;">
                                                        <img src="{{ asset('img/white-qr-code-icon.svg') }}">
                                                        <span class="ms-2">Create Label</span>
                                                    </button>
                                                    <!-- Add to Queue — hidden by default in Individual mode -->
                                                    <button type="button" id="btnAddToQueue"
                                                        class="btn btn-custom-outline-red btn-md py-2 px-4 d-flex align-items-center justify-content-center fw-bold d-none"
                                                        style="width: 220px; max-width: 100%;">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="15"
                                                            height="15" fill="currentColor" viewBox="0 0 16 16"
                                                            class="me-1 flex-shrink-0">
                                                            <path
                                                                d="M8 1a.5.5 0 0 1 .5.5v5h5a.5.5 0 0 1 0 1h-5v5a.5.5 0 0 1-1 0v-5h-5a.5.5 0 0 1 0-1h5v-5A.5.5 0 0 1 8 1" />
                                                        </svg>
                                                        <span>Add to Queue</span>
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

    <!-- Fullscreen Lightbox Overlay -->
    <div id="imageLightbox" class="lightbox-overlay d-none">
        <span class="lightbox-close">&times;</span>
        <img class="lightbox-content" id="lightboxImage" src="" alt="Fullscreen View">
    </div>

    <!-- Add Item Modal -->
    <div class="modal fade" id="addItemModal" tabindex="-1" aria-labelledby="addItemModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-3">
                <div class="modal-header border-bottom-0 pb-0 px-4 pt-3 d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="modal-title fw-bold red-text-2 mb-0" id="addItemModalLabel">Add Item</h4>
                        <p class="text-muted small mb-2 mt-n2">Enter the details and upload an image for the new property
                            assignment</p>
                    </div>
                    <button type="button" class="btn-close shadow-none border-0" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <hr class="mt-0 mb-2">
                <div class="modal-body px-4 pb-4 pt-0">
                    <form id="addItemForm" method="POST" enctype="multipart/form-data"
                        onsubmit="event.preventDefault();">
                        @csrf
                        <!-- Upload Image Area -->
                        <div class="mb-2">
                            <label class="form-label fw-bold text-muted small mb-1">Add your image down below</label>
                            <div class="add-image-dropzone text-center p-2 rounded-3 d-flex flex-column align-items-center justify-content-center"
                                id="addItemDropzone">
                                <input type="file" id="addItemImageFile" name="item_image"
                                    accept="image/jpeg,image/png,image/webp" class="d-none">
                                <div id="dropzonePrompt" class="d-flex flex-column align-items-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40"
                                        viewBox="0 0 24 24" fill="none" stroke="#C62742" stroke-width="1.8"
                                        stroke-linecap="round" stroke-linejoin="round" class="mb-2">
                                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                        <polyline points="14 2 14 8 20 8"></polyline>
                                        <line x1="12" y1="18" x2="12" y2="12"></line>
                                        <polyline points="9 15 12 12 15 15"></polyline>
                                    </svg>
                                    <span class="fw-bold text-dark small">Drag and drop your image here</span>
                                    <span class="text-muted small">or <a href="javascript:void(0);"
                                            class="text-danger fw-bold text-decoration-none" id="btnBrowseImage">click to
                                            browse</a></span>
                                </div>
                                <div id="dropzonePreview" class="d-none position-relative w-100 text-center">
                                    <img id="previewImage" src="" alt="Selected Preview"
                                        class="img-fluid rounded-2">
                                    <button type="button"
                                        class="btn btn-sm btn-danger position-absolute top-0 end-0 m-2 rounded-circle"
                                        id="btnRemovePreview">&times;</button>
                                </div>
                            </div>
                            <div class="d-flex justify-content-between mt-2 text-muted" style="font-size: 0.75rem;">
                                <span>Supported files: .jpg, .jpeg, .png, .webp</span>
                                <span>Maximum size: 10MB</span>
                            </div>
                        </div>

                        <!-- Main Grid: Left Column (Property Details) & Right Column (Location and Metadata) -->
                        <div class="row">
                            <!-- Left Column -->
                            <div class="col-md-6 border-end-md">
                                <h6 class="fw-bold red-text-2 mb-2 d-flex align-items-center">
                                    <img src="{{ asset('img/property-details-icon.svg') }}" class="me-2"
                                        style="width: 18px;">
                                    Property Details
                                </h6>
                                <!-- Item Name -->
                                <div class="mb-2">
                                    <label for="addItemName" class="form-label fw-bold small text-dark mb-1">Item Name
                                        <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control form-control-sm" id="addItemName"
                                        name="item_name">
                                </div>
                                <!-- Specifications -->
                                <div class="mb-2">
                                    <label for="addSpecifications"
                                        class="form-label fw-bold small text-dark mb-1">Specifications <span
                                            class="text-danger">*</span></label>
                                    <textarea class="form-control form-control-sm" id="addSpecifications" name="specification" rows="2" 
                                        style="resize: none;"></textarea>
                                </div>
                                <!-- Category -->
                                <div class="mb-2">
                                    <label for="addCategory" class="form-label fw-bold small text-dark mb-1">Category
                                        <span class="text-danger">*</span></label>
                                    <select class="form-select form-select-sm" id="addCategory" name="category" >
                                        <option value="" selected disabled>Select</option>
                                        <option value="Supply and Materials">Supply and Materials</option>
                                        <option value="Semi-Expendable">Semi-Expendable</option>
                                        <option value="Equipment">Equipment</option>
                                    </select>
                                </div>
                                <!-- Assignee -->
                                <div class="mb-2">
                                    <label for="addAssignee" class="form-label fw-bold small text-dark mb-1">Assignee
                                        <span class="text-danger">*</span></label>
                                    <select class="form-select form-select-sm" id="addAssignee" name="assigned_to"
                                        >
                                        <option value="" selected disabled>Select</option>
                                        @foreach ($allUsers->sortBy('user_fullname') as $userItem)
                                            <option value="{{ $userItem->user_id }}">
                                                {{ $userItem->user_fullname }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <!-- Right Column -->
                            <div class="col-md-6 ps-md-4 mt-1">
                                <div class="row">
                                    <!-- Date Received -->
                                    <div class="col-6 mb-2">
                                        <label for="addDateReceived" class="form-label fw-bold small text-dark mb-1">Date
                                            Received <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control form-control-sm flatpickr-date"
                                            id="addDateReceived" name="date_received" placeholder="Select Date">
                                    </div>
                                    <!-- Quantity -->
                                    <div class="col-6 mb-2">
                                        <label for="addQuantity" class="form-label fw-bold small text-dark mb-1">Quantity
                                            <span class="text-danger">*</span></label>
                                        <input type="number" class="form-control form-control-sm" id="addQuantity"
                                            name="quantity" >
                                    </div>
                                </div>
                                <div class="row">
                                    <!-- Unit -->
                                    <div class="col-6 mb-2">
                                        <label for="addUnit" class="form-label fw-bold small text-dark mb-1">Unit <span
                                                class="text-danger">*</span></label>
                                        <input type="text" class="form-control form-control-sm" id="addUnit"
                                            name="unit">
                                    </div>
                                    <!-- Stock -->
                                    <div class="col-6 mb-2">
                                        <label for="addStock"
                                            class="form-label fw-bold small text-dark mb-1">Stock</label>
                                        <input type="number" class="form-control form-control-sm" id="addStock"
                                            name="stock">
                                    </div>
                                </div>

                                <h6 class="fw-bold red-text-2 mt-3 mb-2 d-flex align-items-center">
                                    <img src="{{ asset('img/location-icon.svg') }}" class="me-2" style="width: 18px;">
                                    Location Information
                                </h6>
                                <!-- Building -->
                                <div class="mb-2">
                                    <label for="addBuilding"
                                        class="form-label fw-bold small text-dark mb-1">Building</label>
                                    <input type="text" class="form-control form-control-sm" id="addBuilding"
                                        name="building">
                                </div>
                                <!-- Room No. -->
                                <div class="mb-2">
                                    <label for="addRoomNo" class="form-label fw-bold small text-dark mb-1">Room
                                        No.</label>
                                    <input type="text" class="form-control form-control-sm" id="addRoomNo"
                                        name="room_no">
                                </div>
                            </div>
                        </div>

                        <!-- Footer Actions -->
                        <div
                            class="d-flex justify-content-between align-items-center pt-3 flex-wrap gap-2">
                            <div class="form-check form-check-danger">
                                <input class="form-check-input" type="checkbox" id="addConfirmCheckbox">
                                <label class="form-check-label small fw-bold text-muted" for="addConfirmCheckbox">
                                    I hereby confirm that all information is complete and accurate.
                                </label>
                            </div>
                            <div class="d-flex gap-2">
                                <button type="button"
                                    class="btn btn-md btn-outline-dark px-4 py-2 fw-bold rounded-3"
                                    data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-md btn-dark-red px-4 py-2 fw-bold rounded-3"
                                    id="btnSubmitAddItem" disabled>Add Item</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Print Queue Modal -->
    <div class="modal fade" id="exportQueueModal" tabindex="-1" aria-labelledby="exportQueueModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg rounded-3">
                <div class="modal-header border-bottom-0 pb-0">
                    <h4 class="modal-title fw-bold" id="exportQueueModalLabel">
                        Export Queue
                    </h4>
                    <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-0 mt-3">
                    <div id="exportQueueEmpty" class="text-center py-5 text-muted" style="display:none;">
                        <p class="mb-0">No items in queue. Select an item, choose A4, and click <em>Add to Export
                                Queue</em>.</p>
                    </div>
                    <div id="exportQueueTableWrap">
                        <table class="table align-middle mb-0" style="border-collapse: collapse; width: 100%;">
                            <thead>
                                <tr style="background-color: #EFEFEF; border-bottom: 2px solid #dee2e6;">
                                    <th>Property-ID</th>
                                    <th>Item Name</th>
                                    <th class="text-center">Size</th>
                                    <th class="text-center">Quantity</th>
                                    <th class="text-center">QR Layout</th>
                                    <th style="width: 60px;"></th>
                                </tr>
                            </thead>
                            <tbody id="exportQueueTableBody"></tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer border-top-0 d-flex justify-content-between align-items-center p-4">
                    <div class="text-start queue-stats-container">
                        <div>Total Items: <span id="queueTotalItems" class="fw-bold">0</span></div>
                        <div>Total Quantity: <span id="queueTotalQty" class="fw-bold">0</span></div>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="button" id="btnClearQueue" class="btn fw-bold">
                            Clear All
                        </button>
                        <button type="button" id="btnExportQueuePdf"
                            class="btn text-white fw-bold d-flex align-items-center justify-content-center">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                                class="bi bi-file-earmark-arrow-down me-2" viewBox="0 0 16 16">
                                <path
                                    d="M8.5 6.5a.5.5 0 0 0-1 0v3.793L6.354 9.146a.5.5 0 1 0-.708.708l2 2a.5.5 0 0 0 .708 0l2-2a.5.5 0 0 0-.708-.708L8.5 10.293z" />
                                <path
                                    d="M14 14V4.5L9.5 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2M9.5 3A1.5 1.5 0 0 0 11 4.5h2V14a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1h5.5z" />
                            </svg>
                            Export
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Paper Size Selection Modal (Export Time) -->
    <div class="modal fade" id="exportPaperSizeModal" tabindex="-1" aria-labelledby="exportPaperSizeModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-3">
                <div class="modal-header border-bottom-0 pb-0">
                    <h4 class="modal-title fw-bold red-text-2" id="exportPaperSizeModalLabel">Select Paper Size</h4>
                    <button type="button" class="btn-close shadow-none btn-close-custom-paper" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body px-4 py-1">
                    <p class="text-muted mb-3">Choose the paper size for exporting your batched QR stickers:</p>
                    <div class="row g-3">
                        <!-- A4 Card -->
                        <div class="col-6">
                            <div class="export-paper-card p-3 text-center rounded border selected" data-paper-size="A4"
                                style="cursor: pointer;">
                                <div class="fw-bold mb-1 fs-5 paper-title">A4</div>
                                <div class="text-muted small paper-dim mb-2">(210 x 297 mm)</div>
                            </div>
                        </div>
                        <!-- A6 Card -->
                        <div class="col-6">
                            <div class="export-paper-card p-3 text-center rounded border" data-paper-size="A6"
                                style="cursor: pointer;">
                                <div class="fw-bold mb-1 fs-5 paper-title">A6</div>
                                <div class="text-muted small paper-dim mb-2">(105 x 148 mm)</div>
                            </div>
                        </div>
                    </div>
                    <input type="hidden" id="export_paper_size_choice" value="A4">
                </div>
                <div class="modal-footer border-top-0 d-flex gap-2 justify-content-center px-4 py-2">
                    <button type="button" class="btn btn-dark-red text-white fw-bold px-4 d-flex align-items-center"
                        id="btnConfirmExport">
                        <span class="spinner-border spinner-border-sm me-2 d-none" id="exportSpinner" role="status"
                            aria-hidden="true"></span>
                        Export PDF
                    </button>
                </div>
            </div>
        </div>
    </div>
    <!-- Export Report Wizard Modal -->
    <div class="modal fade" id="exportReportModal" tabindex="-1" aria-labelledby="exportReportModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" style="max-width: 520px;">
            <div class="modal-content border-0 shadow-lg rounded-3">
                <div class="modal-header border-bottom-0 pb-0 px-4 pt-4 d-flex justify-content-between align-items-start">
                    <div>
                        <h4 class="modal-title fw-bold red-text-2 mb-1" id="exportReportModalLabel">Export Report</h4>
                        <p class="text-muted mb-0" style="font-size: 0.85rem;">Specify the details of your report down
                            below.</p>
                    </div>
                    <button type="button" class="btn-close shadow-none border-0" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body px-4 pt-3 pb-2">
                    <!-- Wizard Stepper Bar -->
                    <div class="wizard-stepper d-flex align-items-center justify-content-center mb-4">
                        <div class="wizard-step-item" data-step="1">
                            <div class="wizard-step-circle active">
                                <img src="{{ asset('img/steps/step1.svg') }}" alt="Step 1">
                            </div>
                            <span class="wizard-step-label">Step 1</span>
                        </div>
                        <div class="wizard-step-line"></div>
                        <div class="wizard-step-item" data-step="2">
                            <div class="wizard-step-circle">
                                <img src="{{ asset('img/steps/step2.svg') }}" alt="Step 2">
                            </div>
                            <span class="wizard-step-label">Step 2</span>
                        </div>
                        <div class="wizard-step-line"></div>
                        <div class="wizard-step-item" data-step="3">
                            <div class="wizard-step-circle">
                                <img src="{{ asset('img/steps/step3.svg') }}" alt="Step 3">
                            </div>
                            <span class="wizard-step-label">Step 3</span>
                        </div>
                    </div>

                    <!-- Step Content Panels -->
                    <form id="exportReportForm">
                        <input type="hidden" name="reporting_period" id="reporting_period" value="Annual">

                        <!-- Step 1 — Reporting Period -->
                        <div class="wizard-step-content" id="wizardStep1">
                            <div class="period-options d-flex flex-column gap-3">
                                <!-- Option Annual -->
                                <div class="period-option border rounded-3 p-3 position-relative cursor-pointer d-flex align-items-center selected"
                                    data-period="Annual">
                                    <div class="d-flex align-items-center gap-3 w-100">
                                        <div class="period-icon-wrapper rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; background-color: rgba(140, 4, 4, 0.08); flex-shrink: 0;">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-calendar3 red-text-2" viewBox="0 0 16 16">
                                                <path d="M14 0H2a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2zM1 3.857C1 3.384 1.448 3 2 3h12c.552 0 1 .384 1 .857v10.286c0 .473-.448.857-1 .857H2c-.552 0-1-.384-1-.857V3.857z"></path>
                                                <path d="M6.5 7a1 1 0 1 0 0-2 1 1 0 0 0 0 2zm3 0a1 1 0 1 0 0-2 1 1 0 0 0 0 2zm3 0a1 1 0 1 0 0-2 1 1 0 0 0 0 2zm-9 3a1 1 0 1 0 0-2 1 1 0 0 0 0 2zm3 0a1 1 0 1 0 0-2 1 1 0 0 0 0 2zm3 0a1 1 0 1 0 0-2 1 1 0 0 0 0 2zm3 0a1 1 0 1 0 0-2 1 1 0 0 0 0 2zm-9 3a1 1 0 1 0 0-2 1 1 0 0 0 0 2zm3 0a1 1 0 1 0 0-2 1 1 0 0 0 0 2zm3 0a1 1 0 1 0 0-2 1 1 0 0 0 0 2z"></path>
                                            </svg>
                                        </div>
                                        <div class="d-flex flex-column text-start">
                                            <span class="fw-bold period-title mb-0">Annual</span>
                                            <span class="text-muted small">Generate a report covering the entire fiscal year.</span>
                                        </div>
                                    </div>
                                    <div class="period-checkmark position-absolute top-0 end-0 mt-2 me-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="#8C0404" class="bi bi-check-circle-fill" viewBox="0 0 16 16">
                                            <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/>
                                        </svg>
                                    </div>
                                </div>
                                <!-- Option Monthly -->
                                <div class="period-option border rounded-3 p-3 position-relative cursor-pointer d-flex align-items-center"
                                    data-period="Monthly">
                                    <div class="d-flex align-items-center gap-3 w-100">
                                        <div class="period-icon-wrapper rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; background-color: rgba(140, 4, 4, 0.08); flex-shrink: 0;">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-calendar-month red-text-2" viewBox="0 0 16 16">
                                                <path d="M8 9.05q-.416 0-.708-.292t-.292-.708q0-.417.292-.709t.708-.291q.417 0 .709.291t.291.709q0 .416-.291.708T8 9.05m0 3q-.417 0-.709-.292t-.291-.708q0-.417.292-.708t.708-.292q.417 0 .709.292t.291.708q0 .416-.291.708t-.709.292m-3-3q-.417 0-.708-.292T4 8q0-.417.292-.709t.708-.291q.417 0 .708.291T6 8q0 .416-.292.708T5 9.05m0 3q-.417 0-.708-.292T4 11q0-.417.292-.708t.708-.292q.417 0 .708.292T6 11q0 .416-.292.708T5 12.05m6-3q-.417 0-.708-.292t-.292-.708q0-.417.292-.709t.708-.291q.417 0 .708.291t.292.709q0 .416-.292.708t-.708.292m0 3q-.417 0-.708-.292t-.292-.708q0-.417.292-.708t.708-.292q.417 0 .708.292t.292.708q0 .416-.292.708t-.708.292"></path>
                                                <path d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5M2 2a1 1 0 0 0-1 1v11a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V3a1 1 0 0 0-1-1z"></path>
                                                <path d="M2.5 4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5H3a.5.5 0 0 1-.5-.5z"></path>
                                            </svg>
                                        </div>
                                        <div class="d-flex flex-column text-start">
                                            <span class="fw-bold period-title mb-0">Monthly</span>
                                            <span class="text-muted small">Generate a report covering a specific calendar month.</span>
                                        </div>
                                    </div>
                                    <div class="period-checkmark position-absolute top-0 end-0 mt-2 me-2 d-none">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="#8C0404" class="bi bi-check-circle-fill" viewBox="0 0 16 16">
                                            <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/>
                                        </svg>
                                    </div>
                                </div>
                                <!-- Option Quarterly -->
                                <div class="period-option border rounded-3 p-3 position-relative cursor-pointer d-flex align-items-center"
                                    data-period="Quarterly">
                                    <div class="d-flex align-items-center gap-3 w-100">
                                        <div class="period-icon-wrapper rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; background-color: rgba(140, 4, 4, 0.08); flex-shrink: 0;">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-calendar3-range red-text-2" viewBox="0 0 16 16">
                                                <path d="M14 0H2a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2zM1 3.857C1 3.384 1.448 3 2 3h12c.552 0 1 .384 1 .857v10.286c0 .473-.448.857-1 .857H2c-.552 0-1-.384-1-.857V3.857z"></path>
                                                <path d="M2 10a1 1 0 0 0 0 2h12a1 1 0 0 0 0-2H2zm0-4a1 1 0 0 0 0 2h8a1 1 0 0 0 0-2H2z"></path>
                                            </svg>
                                        </div>
                                        <div class="d-flex flex-column text-start">
                                            <span class="fw-bold period-title mb-0">Quarterly</span>
                                            <span class="text-muted small">Generate a report covering a 3-month fiscal quarter.</span>
                                        </div>
                                    </div>
                                    <div class="period-checkmark position-absolute top-0 end-0 mt-2 me-2 d-none">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="#8C0404" class="bi bi-check-circle-fill" viewBox="0 0 16 16">
                                            <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/>
                                        </svg>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Step 2 — Filters -->
                        <div class="wizard-step-content d-none" id="wizardStep2">
                            <div class="filters-container d-flex flex-column gap-3">
                                <!-- Year Selection -->
                                <div class="form-group">
                                    <label for="filter_year" class="fw-bold mb-1 small text-muted">Select Year</label>
                                    <select class="form-select" id="filter_year" name="filter_year">
                                        @foreach ($availableYears as $year)
                                            <option value="{{ $year }}">{{ $year }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Month Selection (Conditional) -->
                                <div class="form-group filter-month-group d-none">
                                    <label for="filter_month" class="fw-bold mb-1 small text-muted">Select Month</label>
                                    <select class="form-select" id="filter_month" name="filter_month">
                                        @foreach (range(1, 12) as $m)
                                            @php $dateObj = DateTime::createFromFormat('!m', $m); @endphp
                                            <option value="{{ $m }}" {{ $m == date('n') ? 'selected' : '' }}>
                                                {{ $dateObj->format('F') }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Quarter Selection (Conditional) -->
                                <div class="form-group filter-quarter-group d-none">
                                    <label for="filter_quarter" class="fw-bold mb-1 small text-muted">Select
                                        Quarter</label>
                                    <select class="form-select" id="filter_quarter" name="filter_quarter">
                                        <option value="1">Q1 (January - March)</option>
                                        <option value="2">Q2 (April - June)</option>
                                        <option value="3">Q3 (July - September)</option>
                                        <option value="4">Q4 (October - December)</option>
                                    </select>
                                </div>

                                <!-- Grouped By Selection -->
                                <div class="form-group">
                                    <label for="filter_group_by" class="fw-bold mb-1 small text-muted">Grouped By</label>
                                    <select class="form-select" id="filter_group_by" name="filter_group_by">
                                        <option value="user">Per end-user</option>
                                        <option value="office">Per office</option>
                                    </select>
                                </div>

                                <!-- Select User -->
                                <div class="form-group filter-user-group">
                                    <label for="filter_user" class="fw-bold mb-1 small text-muted">Select User</label>
                                    <select class="form-select" id="filter_user" name="filter_user">
                                        <option value="">All Users</option>
                                        @foreach ($allUsers as $userItem)
                                            <option value="{{ $userItem->user_id }}">{{ $userItem->user_fullname }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Select Office -->
                                <div class="form-group filter-office-group d-none">
                                    <label for="filter_office" class="fw-bold mb-1 small text-muted">Select Office</label>
                                    <select class="form-select" id="filter_office" name="filter_office">
                                        <option value="">All Offices</option>
                                        @foreach($allOffices as $officeItem)
                                            <option value="{{ $officeItem->dep_id }}">{{ $officeItem->dep_name }}{{ $officeItem->dep_acronym ? ' (' . $officeItem->dep_acronym . ')' : '' }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Item Category Selection -->
                                <div class="form-group">
                                    <label for="filter_category" class="fw-bold mb-1 small text-muted">Item
                                        Category</label>
                                    <select class="form-select" id="filter_category" name="filter_category">
                                        <option value="All">All Categories</option>
                                        <option value="Equipment">Equipment</option>
                                        <option value="Semi-Expendable">Semi-Expendable</option>
                                        <option value="Supply and Materials">Supplies and Materials</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Step 3 — Review -->
                        <div class="wizard-step-content d-none" id="wizardStep3">
                            <div class="card border border-light-subtle rounded-3 mb-3 shadow-none">
                                <div class="card-body p-3">
                                    <h6 class="fw-bold text-dark border-bottom pb-2 mb-3">Review the details:</h6>

                                    <div class="d-flex flex-column gap-2" id="wizardReviewSummary">
                                        <!-- Will be populated dynamically via JS -->
                                    </div>
                                </div>
                            </div>

                            <button type="button" class="btn btn-wizard-export w-100 fw-bold py-3 mt-1 btn-dark-red" id="wizardBtnExport">
                                Export Report
                            </button>
                        </div>
                    </form>
                </div>
                <div class="modal-footer border-top-0 d-flex justify-content-center gap-3 px-4 pb-4 pt-0">
                    <button type="button" class="btn btn-wizard-prev fw-bold px-4 py-2" id="wizardBtnPrev"
                        disabled>Prev</button>
                    <button type="button" class="btn btn-wizard-next fw-bold px-4 py-2" id="wizardBtnNext">Next</button>
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

    <!-- Flatpickr JS -->
    <script src="{{ asset('plugins/src/flatpickr/flatpickr.js') }}"></script>

    <!-- CUSTOM js —- inject queue route URLs as global vars before the script -->
    <script>
        window.queueAddUrl = '{{ route('inventory.queue.add') }}';
        window.queueGetUrl = '{{ route('inventory.queue.get') }}';
        window.queueClearUrl = '{{ route('inventory.queue.clear') }}';
        window.queueExportUrl = '{{ route('inventory.queue.export') }}';
        window.exportReportUrl = '{{ route('inventory.export-report') }}';
    </script>
    <script src="{{ asset('js/supply/inventory/custom-inventory.js') }}"></script>
@endpush
