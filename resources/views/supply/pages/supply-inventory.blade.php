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
                    <tr class="inventory-row" style="cursor: pointer;"
                        data-item-name="{{ $item->item_name }}"
                        data-assignee="{{ $item->assignedUser?->user_fullname ?? '—' }}"
                        data-office="{{ $item->assignedUser?->departments->first()?->dep_name ?? '—' }}"
                        data-date-scanned="{{ $item->date_scanned ? \Carbon\Carbon::parse($item->date_scanned)->format('Y-m-d') : '—' }}"
                        data-stock="{{ $item->stock ?? '—' }}"
                        data-unit="{{ $item->unit ?? '—' }}"
                        data-specification="{{ $item->specification ?? '—' }}"
                        data-quantity="{{ $item->quantity ?? '—' }}"
                        data-building="{{ $item->building ?? '—' }}"
                        data-room-no="{{ $item->room_no ?? '—' }}"
                        data-item-image="{{ $item->item_image ? asset($item->item_image) : '' }}"
                        data-mr-qr-code="{{ $item->mr_qr_code }}"
                        data-category="{{ $item->category }}">
                        <td class="text-center">{{ $item->mr_qr_code }}</td>
                        <td>{{ $item->item_name }}</td>
                        <td>{{ $item->assignedUser?->user_fullname ?? '—' }}</td>
                        <td class="text-center">{{ $item->assignedUser?->departments->first()?->dep_name ?? '—' }}</td>
                        <td class="text-center">{{ $item->date_scanned ? \Carbon\Carbon::parse($item->date_scanned)->format('Y-m-d') : '—' }}</td>
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
            <div class="modal-content border-0 shadow-lg" style="border-radius: 16px; background: #ffffff;">
                <div class="modal-header border-bottom-0 pb-0 px-4 pt-4 d-flex justify-content-between align-items-center">
                    <h4 class="modal-title fw-bold" id="itemDetailsModalLabel" style="color: var(--red-1); font-family: 'Nunito', sans-serif;">Item Details</h4>
                    <button type="button" class="btn-close shadow-none border-0" data-bs-dismiss="modal" aria-label="Close" style="background-size: 14px; opacity: 0.6;"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row">
                        <!-- Left Column: Media Canvas & Splide Sync -->
                        <div class="col-md-5 mb-4 mb-md-0 d-flex flex-column align-items-center">
                            <!-- Main Viewport Card -->
                            <div class="card border-0 main-image-viewport-card mb-3 w-100" style="background: #ffffff; border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.08); border: 1px solid #f3f4f6 !important;">
                                <div class="card-body p-0 d-flex align-items-center justify-content-center position-relative" style="height: 300px; min-height: 300px; overflow: hidden; border-radius: 12px;">
                                    <!-- Active primary photo -->
                                    <img id="modalPrimaryImage" src="" alt="Primary Photo" class="img-fluid rounded-3" style="max-height: 90%; max-width: 90%; object-fit: contain; display: none;">
                                    <!-- No image placeholder text -->
                                    <div id="modalNoImagePlaceholder" class="text-center py-5" style="display: none; color: var(--gray-text);">
                                        <i class="icon-placeholder d-block bg-secondary mx-auto mb-2" style="width: 48px; height: 48px; opacity: 0.5; mask: url('data:image/svg+xml;utf8,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 24 24%22 fill=%22none%22 stroke=%22currentColor%22 stroke-width=%222%22><rect x=%223%22 y=%223%22 width=%2218%22 height=%2218%22 rx=%222%22/><circle cx=%228.5%22 cy=%228.5%22 r=%221.5%22/><polyline points=%2221 15 16 10 5 21%22/></svg>') no-repeat center; -webkit-mask: url('data:image/svg+xml;utf8,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 24 24%22 fill=%22none%22 stroke=%22currentColor%22 stroke-width=%222%22><rect x=%223%22 y=%223%22 width=%2218%22 height=%2218%22 rx=%222%22/><circle cx=%228.5%22 cy=%228.5%22 r=%221.5%22/><polyline points=%2221 15 16 10 5 21%22/></svg>') no-repeat center; background-color: var(--gray-text) !important;"></i>
                                        <span class="fw-bold" style="font-size: 0.95rem;">No Image Available</span>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Thumbnail Carousel -->
                            <div id="modalThumbnailSlider" class="splide w-100" style="padding: 0 20px;">
                                <div class="splide__track">
                                    <ul class="splide__list" id="modalThumbnailList">
                                        <!-- Dynamic thumbnails -->
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <!-- Right Column: Navigation Tabs System -->
                        <div class="col-md-7">
                            <div class="card border-0 h-100 rounded-3" style="background: transparent;">
                                <div class="card-body p-0">
                                    <!-- Navigation Tabs -->
                                    <ul class="nav nav-tabs custom-detail-tabs border-bottom mb-3" id="itemModalTab" role="tablist" style="border-bottom: 2px solid #e2e8f0 !important;">
                                        <li class="nav-item w-50" role="presentation">
                                            <button class="nav-link w-100 active text-center fw-bold py-2 border-0" id="details-tab" data-bs-toggle="tab" data-bs-target="#details-tab-pane" type="button" role="tab" aria-controls="details-tab-pane" aria-selected="true" style="background: transparent; font-size: 0.95rem;">Details</button>
                                        </li>
                                        <li class="nav-item w-50" role="presentation">
                                            <button class="nav-link w-100 text-center fw-bold py-2 border-0" id="item-label-tab" data-bs-toggle="tab" data-bs-target="#item-label-tab-pane" type="button" role="tab" aria-controls="item-label-tab-pane" aria-selected="false" style="background: transparent; font-size: 0.95rem;">Item Label</button>
                                        </li>
                                    </ul>

                                    <div class="tab-content" id="itemModalTabContent">
                                        <!-- Tab 1: Details (Read-Only State) -->
                                        <div class="tab-pane fade show active" id="details-tab-pane" role="tabpanel" aria-labelledby="details-tab" tabindex="0">
                                            <!-- Property Details Section -->
                                            <div class="mb-4">
                                                <h6 class="fw-bold d-flex align-items-center mb-3" style="color: var(--red-1); font-size: 0.95rem;">
                                                    <i class="icon-placeholder d-inline-block bg-secondary me-2" style="width:18px; height:18px; mask: url('data:image/svg+xml;utf8,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 24 24%22 fill=%22none%22 stroke=%22currentColor%22 stroke-width=%222%22><rect x=%223%22 y=%223%22 width=%2218%22 height=%2218%22 rx=%222%22/><line x1=%229%22 y1=%229%22 x2=%2215%22 y2=%229%22/><line x1=%229%22 y1=%2213%22 x2=%2215%22 y2=%2213%22/><line x1=%229%22 y1=%2217%22 x2=%2215%22 y2=%2217%22/></svg>') no-repeat center; -webkit-mask: url('data:image/svg+xml;utf8,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 24 24%22 fill=%22none%22 stroke=%22currentColor%22 stroke-width=%222%22><rect x=%223%22 y=%223%22 width=%2218%22 height=%2218%22 rx=%222%22/><line x1=%229%22 y1=%229%22 x2=%2215%22 y2=%229%22/><line x1=%229%22 y1=%2213%22 x2=%2215%22 y2=%2213%22/><line x1=%229%22 y1=%2217%22 x2=%2215%22 y2=%2217%22/></svg>') no-repeat center; background-color: var(--red-1);"></i>
                                                    Property Details
                                                </h6>
                                                <div class="detail-rows">
                                                    <div class="d-flex justify-content-between py-2 border-bottom">
                                                        <span class="text-muted small">Item Name:</span>
                                                        <span id="detailItemName" class="fw-bold text-end text-dark"></span>
                                                    </div>
                                                    <div class="d-flex justify-content-between py-2 border-bottom">
                                                        <span class="text-muted small">Assignee:</span>
                                                        <span id="detailAssignee" class="fw-bold text-end text-dark"></span>
                                                    </div>
                                                    <div class="d-flex justify-content-between py-2 border-bottom">
                                                        <span class="text-muted small">Date Scanned:</span>
                                                        <span id="detailDateScanned" class="fw-bold text-end text-dark"></span>
                                                    </div>
                                                    <div class="d-flex justify-content-between py-2 border-bottom">
                                                        <span class="text-muted small">Stock:</span>
                                                        <span id="detailStock" class="fw-bold text-end text-dark"></span>
                                                    </div>
                                                    <div class="d-flex justify-content-between py-2 border-bottom">
                                                        <span class="text-muted small">Unit:</span>
                                                        <span id="detailUnit" class="fw-bold text-end text-dark"></span>
                                                    </div>
                                                    <div class="d-flex justify-content-between py-2 border-bottom align-items-start">
                                                        <span class="text-muted small me-2" style="white-space: nowrap;">Specifications:</span>
                                                        <span id="detailSpecifications" class="fw-bold text-end text-dark" style="max-width: 65%; word-break: break-word;"></span>
                                                    </div>
                                                    <div class="d-flex justify-content-between py-2 border-bottom">
                                                        <span class="text-muted small">Quantity:</span>
                                                        <span id="detailQuantity" class="fw-bold text-end text-dark"></span>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Location Information Section -->
                                            <div>
                                                <h6 class="fw-bold d-flex align-items-center mb-3" style="color: var(--red-1); font-size: 0.95rem;">
                                                    <i class="icon-placeholder d-inline-block bg-secondary me-2" style="width:18px; height:18px; mask: url('data:image/svg+xml;utf8,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 24 24%22 fill=%22none%22 stroke=%22currentColor%22 stroke-width=%222%22><path d=%22M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z%22/><circle cx=%2212%22 cy=%2210%22 r=%223%22/></svg>') no-repeat center; -webkit-mask: url('data:image/svg+xml;utf8,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 24 24%22 fill=%22none%22 stroke=%22currentColor%22 stroke-width=%222%22><path d=%22M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z%22/><circle cx=%2212%22 cy=%2210%22 r=%223%22/></svg>') no-repeat center; background-color: var(--red-1);"></i>
                                                    Location Information
                                                </h6>
                                                <div class="detail-rows">
                                                    <div class="d-flex justify-content-between py-2 border-bottom">
                                                        <span class="text-muted small">Building:</span>
                                                        <span id="detailBuilding" class="fw-bold text-end text-dark"></span>
                                                    </div>
                                                    <div class="d-flex justify-content-between py-2 border-bottom">
                                                        <span class="text-muted small">Room:</span>
                                                        <span id="detailRoom" class="fw-bold text-end text-dark"></span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Tab 2: Item Label (Interactive Form State) -->
                                        <div class="tab-pane fade" id="item-label-tab-pane" role="tabpanel" aria-labelledby="item-label-tab" tabindex="0">
                                            <form id="createItemLabelForm">
                                                <h6 class="fw-bold d-flex align-items-center mb-3" style="color: var(--red-1); font-size: 0.95rem;">
                                                    <i class="icon-placeholder d-inline-block bg-secondary me-2" style="width:18px; height:18px; mask: url('data:image/svg+xml;utf8,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 24 24%22 fill=%22none%22 stroke=%22currentColor%22 stroke-width=%222%22><rect x=%223%22 y=%223%22 width=%227%22 height=%227%22/><rect x=%2214%22 y=%223%22 width=%227%22 height=%227%22/><rect x=%2214%22 y=%2214%22 width=%227%22 height=%227%22/><rect x=%223%22 y=%2214%22 width=%227%22 height=%227%22/></svg>') no-repeat center; -webkit-mask: url('data:image/svg+xml;utf8,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 24 24%22 fill=%22none%22 stroke=%22currentColor%22 stroke-width=%222%22><rect x=%223%22 y=%223%22 width=%227%22 height=%227%22/><rect x=%2214%22 y=%223%22 width=%227%22 height=%227%22/><rect x=%2214%22 y=%2214%22 width=%227%22 height=%227%22/><rect x=%223%22 y=%2214%22 width=%227%22 height=%227%22/></svg>') no-repeat center; background-color: var(--red-1);"></i>
                                                    Create Item Label
                                                </h6>

                                                <!-- 1. Size Selection -->
                                                <div class="mb-3">
                                                    <label class="form-label fw-bold text-muted small d-block mb-2">1. Size</label>
                                                    <div class="row g-2">
                                                        <input type="hidden" name="label_size" id="label_size" value="Medium">
                                                        <div class="col-4">
                                                            <div class="size-card p-2 text-center rounded border" data-size="Small" style="cursor: pointer;">
                                                                <div class="size-title small">Small</div>
                                                                <div class="size-dim text-muted" style="font-size: 0.65rem;">3x3 cm</div>
                                                            </div>
                                                        </div>
                                                        <div class="col-4">
                                                            <div class="size-card p-2 text-center rounded border selected" data-size="Medium" style="cursor: pointer;">
                                                                <div class="size-title small">Medium</div>
                                                                <div class="size-dim text-muted" style="font-size: 0.65rem;">5x5 cm</div>
                                                            </div>
                                                        </div>
                                                        <div class="col-4">
                                                            <div class="size-card p-2 text-center rounded border" data-size="Large" style="cursor: pointer;">
                                                                <div class="size-title small">Large</div>
                                                                <div class="size-dim text-muted" style="font-size: 0.65rem;">10x10 cm</div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- 2. QR Label Layout -->
                                                <div class="mb-3">
                                                    <label class="form-label fw-bold text-muted small d-block mb-2">2. QR Label Layout</label>
                                                    <div class="row g-3">
                                                        <input type="hidden" name="qr_layout" id="qr_layout" value="layout_1">
                                                        <!-- Layout 1 Card -->
                                                        <div class="col-6">
                                                            <div class="layout-card p-2 rounded border text-center selected" data-layout="layout_1" style="cursor: pointer; background: var(--white-3);">
                                                                <!-- Mock layout design -->
                                                                <div class="mx-auto my-2 p-2 bg-white rounded shadow-sm border position-relative" style="width: 80px; height: 80px; font-size: 8px; border-color: #e2e8f0 !important;">
                                                                    <div class="qr-mock-code mx-auto" style="width: 45px; height: 45px; background: url('data:image/svg+xml;utf8,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 24 24%22 fill=%22%23630418%22><path d=%22M3 3h8v8H3zm2 2v4h4V5zm14-2h8v8h-8zm2 2v4h4V5zM3 19h8v8H3zm2 2v4h4v-4zm11-13h2v2h-2zm4 4h2v2h-2zm-4 4h2v2h-2zm4 4h2v2h-2zm-4-4h2v2h-2zm8 4h2v2h-2zm-4-8h2v2h-2zm4 4h2v2h-2z%22/></svg>') no-repeat center; background-size: cover;"></div>
                                                                    <div class="position-absolute bottom-0 start-0 end-0 py-1 text-white fw-bold text-center" style="background: #EAB308; font-size: 5px; border-bottom-left-radius: 4px; border-bottom-right-radius: 4px; line-height: 1.2;">DO NOT REMOVE</div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <!-- Layout 2 Card -->
                                                        <div class="col-6">
                                                            <div class="layout-card p-2 rounded border text-center" data-layout="layout_2" style="cursor: pointer; background: var(--white-3);">
                                                                <!-- Mock layout design -->
                                                                <div class="mx-auto my-2 p-2 bg-white rounded shadow-sm border position-relative d-flex flex-column align-items-center justify-content-between" style="width: 80px; height: 80px; font-size: 5px; border-color: #e2e8f0 !important;">
                                                                    <div class="qr-mock-code" style="width: 30px; height: 30px; background: url('data:image/svg+xml;utf8,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 24 24%22 fill=%22%23630418%22><path d=%22M3 3h8v8H3zm2 2v4h4V5zm14-2h8v8h-8zm2 2v4h4V5zM3 19h8v8H3zm2 2v4h4v-4zm11-13h2v2h-2zm4 4h2v2h-2zm-4 4h2v2h-2zm4 4h2v2h-2zm-4-4h2v2h-2zm8 4h2v2h-2zm-4-8h2v2h-2zm4 4h2v2h-2z%22/></svg>') no-repeat center; background-size: cover;"></div>
                                                                    <div class="w-100 text-start px-1" style="font-size: 4px; transform: scale(0.95); line-height: 1.1; color: #475569;">
                                                                        <div class="border-bottom pb-0.5" style="border-color: #f1f5f9 !important;">Code: ________________</div>
                                                                        <div class="border-bottom pb-0.5" style="border-color: #f1f5f9 !important;">Prop No: ______________</div>
                                                                        <div class="border-bottom pb-0.5" style="border-color: #f1f5f9 !important;">Date: ________________</div>
                                                                    </div>
                                                                    <div class="position-absolute bottom-0 start-0 end-0 py-1 text-white fw-bold text-center" style="background: #EF4444; font-size: 5px; border-bottom-left-radius: 4px; border-bottom-right-radius: 4px; line-height: 1.2;">DO NOT REMOVE</div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- 3. Quantity Stepper -->
                                                <div class="mb-4">
                                                    <label class="form-label fw-bold text-muted small d-block mb-2">3. Quantity <span class="text-muted fw-normal">(Number of Stickers)</span></label>
                                                    <div class="input-group" style="width: 150px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                                                        <button class="btn btn-outline-secondary btn-stepper-minus px-3" type="button" style="border-color: #cbd5e1; background: #f8fafc; font-size: 1.1rem; color: #475569;">&minus;</button>
                                                        <input type="text" class="form-control text-center stepper-quantity" name="sticker_quantity" value="15" style="border-color: #cbd5e1; font-weight: bold; font-size: 0.95rem; color: #1e293b;">
                                                        <button class="btn btn-outline-secondary btn-stepper-plus px-3" type="button" style="border-color: #cbd5e1; background: #f8fafc; font-size: 1.1rem; color: #475569;">&plus;</button>
                                                    </div>
                                                </div>

                                                <!-- Footer button inside the card -->
                                                <button type="submit" class="btn btn-dark-red w-100 py-2.5 d-flex align-items-center justify-content-center fw-bold" style="background-color: var(--red-1) !important; border-radius: 8px; border: none; font-size: 0.95rem; transition: background-color 0.2s;">
                                                    <i class="icon-placeholder d-inline-block bg-white me-2" style="width:16px; height:16px; mask: url('data:image/svg+xml;utf8,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 24 24%22 fill=%22none%22 stroke=%22currentColor%22 stroke-width=%222%22><rect x=%223%22 y=%223%22 width=%227%22 height=%227%22/><rect x=%2214%22 y=%223%22 width=%227%22 height=%227%22/><rect x=%2214%22 y=%2214%22 width=%227%22 height=%227%22/><rect x=%223%22 y=%2214%22 width=%227%22 height=%227%22/></svg>') no-repeat center; -webkit-mask: url('data:image/svg+xml;utf8,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 24 24%22 fill=%22none%22 stroke=%22currentColor%22 stroke-width=%222%22><rect x=%223%22 y=%223%22 width=%227%22 height=%227%22/><rect x=%2214%22 y=%223%22 width=%227%22 height=%227%22/><rect x=%2214%22 y=%2214%22 width=%227%22 height=%227%22/><rect x=%223%22 y=%2214%22 width=%227%22 height=%227%22/></svg>') no-repeat center; background-color: var(--white-text) !important;"></i>
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