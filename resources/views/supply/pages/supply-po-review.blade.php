{{-- Extend the main layout that you want to use --}}
@extends('layouts.supply-layout')

{{-- Define contents to show in the layout --}}
@section('title', 'Purchase Order Review | I-TRAC')

@push('css')
    <!-- Page SPECIFIC css -->

    <!-- CUSTOM css -->
    <link rel="stylesheet" href="{{ asset('css/supply/po-review/custom-po-review.css') }}">
@endpush

@section('content')
    <div class="card shadow-sm border-0 mb-3 p-0" data-po-id="{{ $po->po_id }}">
        <div class="card-body px-0">
            <div class="d-flex justify-content-start ms-4">
                {{-- Back Button --}}
                <a href="{{ route('show.procure') }}" class="me-3">
                    <img src="{{ asset('img/Back.svg') }}" width="24" height="24">
                </a>
                <div class="mb-3">
                    <h5 class="fw-bold red-text-2 ms-1 mb-0">Purchase Order Review</h5>
                    <small><img src="{{ asset('img/Info.svg') }}" alt="info" width="16" height="16"> Please
                        verify the information, sort the items by category, and check the confirmation box to proceed by
                        clicking the 'Generate Delivery Attachment/s' button below.</small>
                </div>
            </div>

            <hr class="m-0 p-0">
            <h6 class="fw-bold red-text-2 ms-4 mt-4 mb-3">Title: {{ $po->po_title }}</h6>
            <div class="row g-4 ms-3">
                <div class="col-md-6">
                    <div class="row align-items-center mb-3">
                        <div class="col-4">
                            <h6 class="mb-0 black-text fw-bold">Supplier:</h6>
                        </div>
                        <div class="col-8">
                            <h6>{{ $po->po_supplier }}</h6>
                        </div>
                    </div>

                    <div class="row align-items-center mb-3">
                        <div class="col-4">
                            <h6 class="mb-0 black-text fw-bold">Address:</h6>
                        </div>
                        <div class="col-8">
                            <h6>{{ $po->po_address }}</h6>
                        </div>
                    </div>

                    <div class="row align-items-center mb-3">
                        <div class="col-4">
                            <h6 class="mb-0 black-text fw-bold">Tel No.:</h6>
                        </div>
                        <div class="col-8">
                            <h6>{{ $po->po_tele }}</h6>
                        </div>
                    </div>

                    <div class="row align-items-center mb-3">
                        <div class="col-4">
                            <h6 class="mb-0 black-text fw-bold">TIN:</h6>
                        </div>
                        <div class="col-8">
                            <h6>{{ $po->po_tin }}</h6>
                        </div>
                    </div>

                    <div class="row align-items-center mb-3">
                        <div class="col-4">
                            <h6 class="mb-0 black-text fw-bold">Place of Delivery:</h6>
                        </div>
                        <div class="col-8">
                            <h6>{{ $po->po_place_delivery }}</h6>
                        </div>
                    </div>

                    <div class="row align-items-center">
                        <div class="col-4">
                            <h6 class="mb-0 black-text fw-bold">Date of Delivery:</h6>
                        </div>
                        <div class="col-8">
                            <h6>{{ $po->po_date_delivery }}</h6>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 border-start-md">
                    <div class="row align-items-center mb-3">
                        <div class="col-4">
                            <h6 class="mb-0 black-text fw-bold">P.O. No.:</h6>
                        </div>
                        <div class="col-8">
                            <h6>{{ $po->po_no }}</h6>
                        </div>
                    </div>

                    <div class="row align-items-center mb-3">
                        <div class="col-4">
                            <h6 class="mb-0 black-text fw-bold">Date:</h6>
                        </div>
                        <div class="col-8">
                            <h6>{{ $po->po_date }}</h6>
                        </div>
                    </div>

                    <div class="row align-items-center mb-3">
                        <div class="col-4">
                            <h6 class="mb-0 black-text fw-bold">Mode of Procurement:</h6>
                        </div>
                        <div class="col-8">
                            <h6>{{ $po->po_mode }}</h6>
                        </div>
                    </div>

                    <div class="row align-items-center mb-3">
                        <div class="col-4">
                            <h6 class="mb-0 black-text fw-bold">TUP-Taguig TIN:</h6>
                        </div>
                        <div class="col-8">
                            <h6>{{ $po->po_tuptin }}</h6>
                        </div>
                    </div>

                    <div class="row align-items-center mb-3">
                        <div class="col-4">
                            <h6 class="mb-0 black-text fw-bold">Delivery Term:</h6>
                        </div>
                        <div class="col-8">
                            <h6>{{ $po->po_delivery_term }}</h6>
                        </div>
                    </div>

                    <div class="row align-items-center">
                        <div class="col-4">
                            <h6 class="mb-0 black-text fw-bold">Payment Term:</h6>
                        </div>
                        <div class="col-8">
                            <h6>{{ $po->po_payment_term }}</h6>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0 mb-3 px-0 po-card">
        <div class="card-body px-0 pb-0">
            <div class="d-flex align-items-center justify-content-between mx-3 mb-3">

                <div class="d-flex align-items-baseline gap-2">
                    <h6 class="black-text fw-bold mb-0">0 items selected</h6>
                    <a href="#" class="red-text fw-bold link-underline-danger small">Clear</a>
                </div>

                <div class="d-flex align-items-center gap-2">
                    <p class="project-total-amount mb-0 fw-bold flex-shrink-0">Categorize </p>

                    <select class="form-select form-select-sm w-auto" id="assign-category-select">
                        <option value="" selected disabled>Select</option>
                        <option value="Supply and Materials">Supply and Materials</option>
                        <option value="Semi-Expendable">Semi-Expendable</option>
                        <option value="Equipment">Equipment</option>
                        <option value="Not Delivered">Not Delivered</option>
                    </select>

                    <button type="button" id="apply-btn" class="btn btn-red text-white" disabled>
                        Apply
                    </button>
                </div>
            </div>
            <div class="po-collapse-area" id="collapseCardSupply">
                <div class="table-responsive mx-3">
                    <table class="table table-sm table-borderless align-middle po-table">
                        <thead class="bg-transparent">
                            <tr>
                                <th class="text-center black-text fw-bold" style="width: 5%"></th>
                                <th class="text-center black-text fw-bold" style="width: 8%">Stock</th>
                                <th class="text-center black-text fw-bold" style="width: 12%">Unit</th>
                                <th class="black-text fw-bold ps-0">Item Description</th>
                                <th class="text-center black-text fw-bold" style="width: 8%">Qty.</th>
                                <th class="text-center black-text fw-bold" style="width: 10%">Unit Cost</th>
                                <th class="text-center black-text fw-bold" style="width: 10%">Amount</th>
                                <th class="text-center black-text fw-bold" style="width: 15%">Category</th>
                            </tr>
                        </thead>
                        <tbody id="tbody-uncategorized">
                            @foreach ($po->poItems->whereNull('po_items_category') as $item)
                                <tr class="po-item-row" data-id="{{ $item->po_items_id }}">
                                    <td class="px-1 text-center">
                                        <div class="form-check form-check-danger form-check-inline">
                                            <input class="form-check-input" type="checkbox"
                                                value="{{ $item->po_items_id }}" id="check-{{ $item->po_items_id }}">
                                        </div>
                                    </td>
                                    <td class="px-1 text-center py-0">{{ $item->po_items_stockno }}</td>
                                    <td class="px-1 text-center py-0">{{ $item->po_items_unit }}</td>
                                    <td class="px-1 py-0">{{ $item->po_items_descrip }}</td>
                                    <td class="px-1 text-center py-0">{{ $item->po_items_quantity }}</td>
                                    <td class="px-1 text-center py-0">{{ number_format($item->po_items_cost, 2) }}</td>
                                    <td class="px-1 text-center py-0">
                                        {{ number_format($item->po_items_quantity * $item->po_items_cost, 2) }}</td>
                                    <td class="px-1 text-center py-0">
                                        <select class="form-select form-control-sm category-select"
                                            name="items[{{ $item->po_items_id }}]">
                                            <option value="" selected disabled>Select</option>
                                            <option value="Supply and Materials">Supply and Materials</option>
                                            <option value="Semi-Expendable">Semi-Expendable</option>
                                            <option value="Equipment">Equipment</option>
                                            <option value="Not Delivered">Not Delivered</option>
                                        </select>
                                    </td>
                                </tr>

                                @foreach ($item->poSpecs as $spec)
                                    <tr class="po-specification-row" data-item-id="{{ $item->po_items_id }}">
                                        <td colspan="3"></td>
                                        <td class="px-1 py-0" style="font-size: 0.85rem;">
                                            >{{ $spec->po_spec_description }}
                                        </td>
                                        <td colspan="4"></td>
                                    </tr>
                                @endforeach
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Categories Card --}}
    <div class="card shadow-sm border-0 mb-3 px-0 po-card">
        <div class="card-body px-0 pb-0">
            <div class="d-flex justify-content-between ms-4 mb-1">
                <div class="d-flex align-items-baseline gap-2">
                    <h5 class="red-text fw-bold">Supply and Materials</h5>
                    <small class="black-text item-count"
                        id="count-supply-materials">{{ $po->poItems->where('po_items_category', 'Supply and Materials')->count() }}
                        Item/s</small>
                </div>
                <div class="d-flex align-items-baseline gap-2 me-4">
                    <p class="project-total-amount mb-0 fw-bold" id="total-supply-materials">₱
                        {{ number_format($po->poItems->where('po_items_category', 'Supply and Materials')->sum(function ($item) {return $item->po_items_quantity * $item->po_items_cost;}),2) }}
                    </p>
                </div>
            </div>
            <div class="po-collapse-area" id="collapseCardSupplyMaterials">
                <div class="table-responsive mx-3">
                    <table class="table table-sm table-borderless align-middle po-table">
                        <thead class="bg-transparent">
                            <tr>
                                <th class="text-center black-text fw-bold" style="width: 8%">Stock</th>
                                <th class="text-center black-text fw-bold" style="width: 12%">Unit</th>
                                <th class="black-text fw-bold ps-0">Item Description</th>
                                <th class="text-center black-text fw-bold" style="width: 8%">Qty.</th>
                                <th class="text-center black-text fw-bold" style="width: 10%">Unit Cost</th>
                                <th class="text-center black-text fw-bold" style="width: 10%">Amount</th>
                                <th class="text-center black-text fw-bold" style="width: 14%">Category</th>
                                <th class="text-center black-text fw-bold" style="width: 4%"></th>
                            </tr>
                        </thead>
                        <tbody id="tbody-supply-materials">
                            @foreach ($po->poItems->where('po_items_category', 'Supply and Materials') as $item)
                                <tr class="po-item-row" data-id="{{ $item->po_items_id }}">
                                    <td class="px-1 text-center py-0">{{ $item->po_items_stockno }}</td>
                                    <td class="px-1 text-center py-0">{{ $item->po_items_unit }}</td>
                                    <td class="px-1 py-0">{{ $item->po_items_descrip }}</td>
                                    <td class="px-1 text-center py-0">{{ $item->po_items_quantity }}</td>
                                    <td class="px-1 text-center py-0">{{ number_format($item->po_items_cost, 2) }}</td>
                                    <td class="px-1 text-center py-0">
                                        {{ number_format($item->po_items_quantity * $item->po_items_cost, 2) }}</td>
                                    <td class="px-1 text-center">
                                        <select class="form-select form-control-sm category-select"
                                            name="items[{{ $item->po_items_id }}]">
                                            <option value="Supply and Materials" selected>Supply and Materials</option>
                                            <option value="Semi-Expendable">Semi-Expendable</option>
                                            <option value="Equipment">Equipment</option>
                                            <option value="Not Delivered">Not Delivered</option>
                                        </select>
                                    </td>
                                    <td class="px-1 text-center">
                                        <button type="button" class="btn border btn-white assign-item-btn"
                                            title="Assign Item" data-item-id="{{ $item->po_items_id }}"
                                            data-item-desc="{{ $item->po_items_descrip }}"
                                            data-item-qty="{{ $item->po_items_quantity }}">
                                            <img src="{{ asset('img/Assign.svg') }}" width="16" height="16"
                                                alt="Assign">
                                        </button>
                                    </td>
                                </tr>

                                @foreach ($item->poSpecs as $spec)
                                    <tr class="po-specification-row" data-item-id="{{ $item->po_items_id }}">
                                        <td colspan="2"></td>
                                        <td class="px-1 py-0" style="font-size: 0.85rem;">
                                            > {{ $spec->po_spec_description }}
                                        </td>
                                        <td colspan="5"></td>
                                    </tr>
                                @endforeach
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <hr>
            <div class="d-flex justify-content-between ms-4 mb-1">
                <div class="d-flex align-items-baseline gap-2">
                    <h5 class="red-text fw-bold">Semi-Expendables</h5>
                    <small class="black-text item-count"
                        id="count-semi-expendable">{{ $po->poItems->where('po_items_category', 'Semi-Expendable')->count() }}
                        Item/s</small>
                </div>
                <div class="d-flex align-items-baseline gap-2 me-4">
                    <p class="project-total-amount mb-0 fw-bold" id="total-semi-expendable">₱
                        {{ number_format($po->poItems->where('po_items_category', 'Semi-Expendable')->sum(function ($item) {return $item->po_items_quantity * $item->po_items_cost;}),2) }}
                    </p>
                </div>
            </div>
            <div class="po-collapse-area" id="collapseCardSemiExpendable">
                <div class="table-responsive mx-3">
                    <table class="table table-sm table-borderless align-middle po-table">
                        <thead class="bg-transparent">
                            <tr>
                                <th class="text-center black-text fw-bold" style="width: 8%">Stock</th>
                                <th class="text-center black-text fw-bold" style="width: 12%">Unit</th>
                                <th class="black-text fw-bold ps-0">Item Description</th>
                                <th class="text-center black-text fw-bold" style="width: 8%">Qty.</th>
                                <th class="text-center black-text fw-bold" style="width: 10%">Unit Cost</th>
                                <th class="text-center black-text fw-bold" style="width: 10%">Amount</th>
                                <th class="text-center black-text fw-bold" style="width: 14%">Category</th>
                                <th class="text-center black-text fw-bold" style="width: 4%"></th>
                            </tr>
                        </thead>
                        <tbody id="tbody-semi-expendable">
                            @foreach ($po->poItems->where('po_items_category', 'Semi-Expendable') as $item)
                                <tr class="po-item-row" data-id="{{ $item->po_items_id }}">
                                    <td class="px-1 text-center py-0">{{ $item->po_items_stockno }}</td>
                                    <td class="px-1 text-center py-0">{{ $item->po_items_unit }}</td>
                                    <td class="px-1 py-0">{{ $item->po_items_descrip }}</td>
                                    <td class="px-1 text-center py-0">{{ $item->po_items_quantity }}</td>
                                    <td class="px-1 text-center py-0">{{ number_format($item->po_items_cost, 2) }}</td>
                                    <td class="px-1 text-center py-0">
                                        {{ number_format($item->po_items_quantity * $item->po_items_cost, 2) }}</td>
                                    <td class="px-1 text-center">
                                        <select class="form-select form-control-sm category-select"
                                            name="items[{{ $item->po_items_id }}]">
                                            <option value="Supply and Materials">Supply and Materials</option>
                                            <option value="Semi-Expendable" selected>Semi-Expendable</option>
                                            <option value="Equipment">Equipment</option>
                                            <option value="Not Delivered">Not Delivered</option>
                                        </select>
                                    </td>
                                    <td class="px-1 text-center">
                                        <button type="button" class="btn border btn-white assign-item-btn"
                                            title="Assign Item" data-item-id="{{ $item->po_items_id }}"
                                            data-item-desc="{{ $item->po_items_descrip }}"
                                            data-item-qty="{{ $item->po_items_quantity }}">
                                            <img src="{{ asset('img/Assign.svg') }}" width="16" height="16"
                                                alt="Assign">
                                        </button>
                                    </td>
                                </tr>

                                @foreach ($item->poSpecs as $spec)
                                    <tr class="po-specification-row" data-item-id="{{ $item->po_items_id }}">
                                        <td colspan="2"></td>
                                        <td class="px-1 py-0" style="font-size: 0.85rem;">
                                            > {{ $spec->po_spec_description }}
                                        </td>
                                        <td colspan="5"></td>
                                    </tr>
                                @endforeach
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <hr>
            <div class="d-flex justify-content-between ms-4 mb-1">
                <div class="d-flex align-items-baseline gap-2">
                    <h5 class="red-text fw-bold">Equipment</h5>
                    <small class="black-text item-count"
                        id="count-equipment">{{ $po->poItems->where('po_items_category', 'Equipment')->count() }}
                        Item/s</small>
                </div>
                <div class="d-flex align-items-baseline gap-2 me-4">
                    <p class="project-total-amount mb-0 fw-bold" id="total-equipment">₱
                        {{ number_format($po->poItems->where('po_items_category', 'Equipment')->sum(function ($item) {return $item->po_items_quantity * $item->po_items_cost;}),2) }}
                    </p>
                </div>
            </div>
            <div class="po-collapse-area" id="collapseCardEquipment">
                <div class="table-responsive mx-3">
                    <table class="table table-sm table-borderless align-middle po-table">
                        <thead class="bg-transparent">
                            <tr>
                                <th class="text-center black-text fw-bold" style="width: 8%">Stock</th>
                                <th class="text-center black-text fw-bold" style="width: 12%">Unit</th>
                                <th class="black-text fw-bold ps-0">Item Description</th>
                                <th class="text-center black-text fw-bold" style="width: 8%">Qty.</th>
                                <th class="text-center black-text fw-bold" style="width: 10%">Unit Cost</th>
                                <th class="text-center black-text fw-bold" style="width: 10%">Amount</th>
                                <th class="text-center black-text fw-bold" style="width: 14%">Category</th>
                                <th class="text-center black-text fw-bold" style="width: 4%"></th>
                            </tr>
                        </thead>
                        <tbody id="tbody-equipment">
                            @foreach ($po->poItems->where('po_items_category', 'Equipment') as $item)
                                <tr class="po-item-row" data-id="{{ $item->po_items_id }}">
                                    <td class="px-1 text-center py-0">{{ $item->po_items_stockno }}</td>
                                    <td class="px-1 text-center py-0">{{ $item->po_items_unit }}</td>
                                    <td class="px-1 py-0">{{ $item->po_items_descrip }}</td>
                                    <td class="px-1 text-center py-0">{{ $item->po_items_quantity }}</td>
                                    <td class="px-1 text-center py-0">{{ number_format($item->po_items_cost, 2) }}</td>
                                    <td class="px-1 text-center py-0">
                                        {{ number_format($item->po_items_quantity * $item->po_items_cost, 2) }}</td>
                                    <td class="px-1 text-center">
                                        <select class="form-select form-control-sm category-select"
                                            name="items[{{ $item->po_items_id }}]">
                                            <option value="Supply and Materials">Supply and Materials</option>
                                            <option value="Semi-Expendable">Semi-Expendable</option>
                                            <option value="Equipment" selected>Equipment</option>
                                        </select>
                                    </td>
                                    <td class="px-1 text-center">
                                        <button type="button" class="btn border btn-white assign-item-btn"
                                            title="Assign Item" data-item-id="{{ $item->po_items_id }}"
                                            data-item-desc="{{ $item->po_items_descrip }}"
                                            data-item-qty="{{ $item->po_items_quantity }}">
                                            <img src="{{ asset('img/Assign.svg') }}" width="16" height="16"
                                                alt="Assign">
                                        </button>
                                    </td>
                                </tr>

                                @foreach ($item->poSpecs as $spec)
                                    <tr class="po-specification-row" data-item-id="{{ $item->po_items_id }}">
                                        <td colspan="2"></td>
                                        <td class="px-1 py-0" style="font-size: 0.85rem;">
                                            > {{ $spec->po_spec_description }}
                                        </td>
                                        <td colspan="5"></td>
                                    </tr>
                                @endforeach
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <hr>
            <div class="d-flex justify-content-between ms-4 mb-1">
                <div class="d-flex align-items-baseline gap-2">
                    <h5 class="red-text fw-bold">Not Delivered</h5>
                    <small class="black-text item-count"
                        id="count-not-delivered">{{ $po->poItems->where('po_items_category', 'Not Delivered')->count() }}
                        Item/s</small>
                </div>
                <div class="d-flex align-items-baseline gap-2 me-4">
                    <p class="project-total-amount mb-0 fw-bold" id="total-not-delivered">₱
                        {{ number_format($po->poItems->where('po_items_category', 'Not Delivered')->sum(function ($item) {return $item->po_items_quantity * $item->po_items_cost;}),2) }}
                    </p>
                </div>
            </div>
            <div class="po-collapse-area" id="collapseCardNotDelivered">
                <div class="table-responsive mx-3">
                    <table class="table table-sm table-borderless align-middle po-table">
                        <thead class="bg-transparent">
                            <tr>
                                <th class="text-center black-text fw-bold" style="width: 8%">Stock</th>
                                <th class="text-center black-text fw-bold" style="width: 12%">Unit</th>
                                <th class="black-text fw-bold ps-0">Item Description</th>
                                <th class="text-center black-text fw-bold" style="width: 8%">Qty.</th>
                                <th class="text-center black-text fw-bold" style="width: 10%">Unit Cost</th>
                                <th class="text-center black-text fw-bold" style="width: 10%">Amount</th>
                                <th class="text-center black-text fw-bold" style="width: 19%">Category</th>
                            </tr>
                        </thead>
                        <tbody id="tbody-not-delivered">
                            @foreach ($po->poItems->where('po_items_category', 'Not Delivered') as $item)
                                <tr class="po-item-row" data-id="{{ $item->po_items_id }}">
                                    <td class="px-1 text-center py-0">{{ $item->po_items_stockno }}</td>
                                    <td class="px-1 text-center py-0">{{ $item->po_items_unit }}</td>
                                    <td class="px-1 py-0">{{ $item->po_items_descrip }}</td>
                                    <td class="px-1 text-center py-0">{{ $item->po_items_quantity }}</td>
                                    <td class="px-1 text-center py-0">{{ number_format($item->po_items_cost, 2) }}</td>
                                    <td class="px-1 text-center py-0">
                                        {{ number_format($item->po_items_quantity * $item->po_items_cost, 2) }}</td>
                                    <td class="px-1 text-center">
                                        <select class="form-select form-control-sm category-select"
                                            name="items[{{ $item->po_items_id }}]">
                                            <option value="Supply and Materials">Supply and Materials</option>
                                            <option value="Semi-Expendable">Semi-Expendable</option>
                                            <option value="Equipment">Equipment</option>
                                            <option value="Not Delivered" selected>Not Delivered</option>
                                        </select>
                                    </td>
                                </tr>

                                @foreach ($item->poSpecs as $spec)
                                    <tr class="po-specification-row" data-item-id="{{ $item->po_items_id }}">
                                        <td colspan="2"></td>
                                        <td class="px-1 py-0" style="font-size: 0.85rem;">
                                            > {{ $spec->po_spec_description }}
                                        </td>
                                        <td colspan="4"></td>
                                    </tr>
                                @endforeach
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-between">
        <div class="form-check form-check-danger form-check-inline">
            <input class="form-check-input" type="checkbox" value="" id="form-check-danger">
            <label class="form-check-label" for="form-check-danger">
                I hereby declare that the information provided is reviewed and correct.
            </label>
        </div>

        <div class="generate-btn-container">
            <button type="button" id="generate-btn"
                class="btn border border-light-subtle btn-dark-red d-inline-flex align-items-center gap-1 px-3" disabled>
                Generate Delivery Attachment/s
            </button>
        </div>
    </div>

    {{-- Assign Quantity Modal (Supply and Materials only) --}}
    <div class="modal fade" id="assignDeptModal" tabindex="-1" aria-labelledby="assignDeptModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-sm">
                <div class="modal-header border-bottom pb-3">
                    <h5 class="modal-title fw-bold red-text-2" id="assignDeptModalLabel">Assign Quantity</h5>
                </div>
                <div class="modal-body">
                    {{-- Item Info --}}
                    <div class="">
                        <p class="mb-1 black-text fw-bold" id="modal-item-desc"></p>
                        <p class="mb-2 black-text">Quantity: <span id="modal-item-qty"
                                class="fw-semibold text-dark"></span></p>
                        <p class="mb-0 black-text d-flex align-items-center gap-1 small">
                            <img src="{{ asset('img/Info.svg') }}" alt="info" width="16" height="16">
                            Distribute the quantity to one or more departments
                        </p>
                    </div>

                    {{-- Column header bar --}}
                    <table class="table table-sm table-borderless align-middle mb-2" id="assign-dept-table">
                        <thead>
                            <tr class="assign-dept-header">
                                <th class="black-text fw-bold rounded-start px-1">Department</th>
                                <th class="black-text fw-bold text-center px-1" style="width: 20%;">Quantity</th>
                                <th class="rounded-end px-1" style="width: 2%;"></th>
                            </tr>
                        </thead>
                        <tbody id="assign-dept-tbody">
                            <tr class="dept-row align-middle">
                                <td class="px-2">
                                    <select class="form-select form-select-sm dept-name">
                                        <option value="" selected disabled>Select Department</option>
                                        @foreach($departments as $dept)
                                            <option value="{{ $dept->dep_id }}">{{ $dept->dep_name }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td class="px-2">
                                    <input type="text" class="form-control form-control-sm dept-qty text-center">
                                </td>
                                <td class="text-center px-0">
                                    <button type="button" class="btn border-0 bg-transparent text-black fw-bold remove-dept-row-btn p-0">
                                        <img src="{{ asset('img/remove.svg') }}" alt="Remove">
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    {{-- Add Department button --}}
                    <div class="text-center mt-3">
                        <button type="button" class="btn border btn-white btn-sm px-4 fw-bold" id="add-dept-row-btn">
                            + Add Department
                        </button>
                    </div>

                    {{-- Total assigned tracker (right-aligned, always visible) --}}
                    <div class="d-flex justify-content-end align-items-center gap-2 mt-4">
                        <img src="{{ asset('img/gray-check.svg') }}" id="assign-check-icon" alt="Check Icon" width="20" height="20">
                        <span class="black-text">Total assigned:</span>
                        <span class="black-text fw-bold">
                            <span id="total-assigned-display">0</span>/<span id="total-qty-cap"></span>
                        </span>
                    </div>
                </div>
                <div class="modal-footer pt-0" style="border-top: none !important;">
                    <button type="button" class="btn btn-gray px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-red text-white px-4" id="confirm-assign-btn"
                        disabled>Assign</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Assign Quantity Modal (Semi-Expendables and Equipment) --}}
    <div class="modal fade" id="assignUserModal" tabindex="-1" aria-labelledby="assignUserModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-sm">
                <div class="modal-header border-bottom pb-3">
                    <h5 class="modal-title fw-bold red-text-2" id="assignUserModalLabel">Assign Quantity</h5>
                </div>
                <div class="modal-body">
                    {{-- Item Info --}}
                    <div class="">
                        <p class="mb-1 black-text fw-bold" id="modal-user-item-desc"></p>
                        <p class="mb-2 black-text">Quantity: <span id="modal-user-item-qty"
                                class="fw-semibold text-dark"></span></p>
                        <p class="mb-0 black-text d-flex align-items-center gap-1 small">
                            <img src="{{ asset('img/Info.svg') }}" alt="info" width="16" height="16">
                            Distribute the quantity to one or more departments
                        </p>
                    </div>

                    {{-- Column header bar --}}
                    <table class="table table-sm table-borderless align-middle mb-2" id="assign-user-table">
                        <thead>
                            <tr class="assign-user-header">
                                <th class="black-text fw-bold rounded-start px-2 py-2">Name</th>
                                <th class="black-text fw-bold text-center px-2 py-2" style="width: 20%;">Quantity</th>
                                <th class="rounded-end px-2 py-2" style="width: 2%;"></th>
                            </tr>
                        </thead>
                        <tbody id="assign-user-tbody">
                            <tr class="user-row align-middle">
                                <td class="px-2">
                                    <select class="form-select form-select-sm user-select">
                                        <option value="" selected disabled>Select User</option>
                                        @foreach($users->sortBy('user_fullname') as $user)
                                            <option value="{{ $user->user_id }}">{{ $user->user_fullname }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td class="px-2">
                                    <input type="text" class="form-control form-control-sm user-qty text-center">
                                </td>
                                <td class="text-center px-0">
                                    <button type="button" class="btn border-0 bg-transparent text-black fw-bold remove-user-row-btn p-0">
                                        <img src="{{ asset('img/remove.svg') }}" alt="Remove">
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    {{-- Add User button --}}
                    <div class="text-center mt-3">
                        <button type="button" class="btn border btn-white btn-sm px-4 fw-bold" id="add-user-row-btn">
                            + Add User
                        </button>
                    </div>

                    {{-- Total assigned tracker (right-aligned, always visible) --}}
                    <div class="d-flex justify-content-end align-items-center gap-2 mt-4">
                        <img src="{{ asset('img/gray-check.svg') }}" id="user-assign-check-icon" alt="Check Icon" width="20" height="20">
                        <span class="black-text">Total assigned:</span>
                        <span class="black-text fw-bold">
                            <span id="total-user-assigned-display">0</span>/<span id="total-user-qty-cap"></span>
                        </span>
                    </div>
                </div>
                <div class="modal-footer pt-0" style="border-top: none !important;">
                    <button type="button" class="btn btn-gray px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-red text-white px-4" id="confirm-user-assign-btn"
                        disabled>Assign</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Quantity Distribution HTML Templates for Dynamic Rendering --}}
    <template id="qty-distribution-header-template">
        <tr class="qty-distribution-header-row" data-item-id="">
            <td colspan="2"></td>
            <td class="px-1 py-0">
                <div class="qty-distribution-header-text">
                    Quantity Distribution
                </div>
            </td>
            <td colspan="5"></td>
        </tr>
    </template>

    <template id="qty-distribution-row-template">
        <tr class="qty-distribution-row" data-item-id="">
            <td colspan="2"></td>
            <td class="px-1 py-0">
                <div class="qty-dept-name-container">
                    <span class="qty-dept-name"></span>
                </div>
            </td>
            <td class="px-1 text-center py-0 qty-dept-qty">
                {{-- Quantity --}}
            </td>
            <td colspan="4"></td>
        </tr>
    </template>
@endsection

@push('js')
    <!-- CUSTOM css -->
    <script src="{{ asset('js/supply/po-review/custom-po-review.js') }}"></script>
@endpush
