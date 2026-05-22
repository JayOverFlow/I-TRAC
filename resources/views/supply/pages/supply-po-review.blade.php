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
    <div class="card shadow-sm border-0 mb-3 p-0">
        <div class="card-body px-0">
            <div class="d-flex justify-content-start ms-4">
                {{-- Back Button --}}
                <a href="{{ route('show.procure') }}" class="me-3">
                    <img src="{{ asset('img/Back.svg') }}" width="24" height="24">
                </a>
                <div class="mb-3">
                    <h5 class="fw-bold red-text-2 ms-1 mb-0">Purchase Order Review</h5>
                    <small><img src="{{ asset('img/Info.svg') }}" alt="info" width="16" height="16"> Please
                        verify the information, check the confirmation box, and click 'Continue to generate' below.</small>
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
                                <th class="text-center black-text fw-bold" style="width: 19%">Category</th>
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
                                <th class="text-center black-text fw-bold" style="width: 19%">Category</th>
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
                                <th class="text-center black-text fw-bold" style="width: 19%">Category</th>
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

        <button type="button" id="generate-btn"
            class="btn border border-light-subtle btn-dark-red d-inline-flex align-items-center gap-1 px-3" disabled>
            Generate Delivery Attachment/s
        </button>
    </div>
@endsection

@push('js')
    <!-- Page SPECIFIC css -->

    <!-- CUSTOM css -->
    <script src="{{ asset('js/supply/po-review/custom-po-review.js') }}"></script>
@endpush
