{{-- Extend the main layout that you want to use --}}
@extends('layouts.procurement-layout')

{{-- Define contents to show in the layout --}}
@section('title', 'Create Purchase Order | I-TRAC')

@push('css')
    <!-- Page SPECIFIC css -->
    <!-- CUSTOM css -->
    <link rel="stylesheet" href="{{ asset('css/procurement/create-po/custom-create-po.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/src/flatpickr/flatpickr.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/css/light/flatpickr/custom-flatpickr.css') }}">
@endpush

@section('content')
    <form method="POST" action="" id="po-form">
        @csrf

        <div class="card allocated-budget-card mb-3">
            <div class="card-body d-flex justify-content-center justify-content-between align-items-center">
                <div class="d-flex flex-column">
                    <h5 class="fw-bold red-text-2">PURCHASE ORDER</h5>
                </div>
                <div>
                    <h5 class="card-title mb-3 black-text">ALLOCATED BUDGET: PHP 12,345.00</h5>

                    <div class="text-end">
                        <button type="button" id="submit-po-btn" data-url=""
                            class="btn border border-light-subtle btn-dark-red d-inline-flex align-items-center gap-1 px-3">
                            <img src="{{ asset('img/Check.svg') }}" width="18" height="18">
                            <span>Done</span>
                        </button>

                        <button type="submit"
                            class="btn border border-light-subtle btn-white d-inline-flex align-items-center gap-1 px-2">
                            <img src="{{ asset('img/Save.svg') }}" width="18" height="18">
                            <span class="fw-bold">Save as Draft</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>


        <div class="card shadow-sm border-0 mb-3">
            <div class="card-body">
                <h5 class="fw-bold red-text-2 mb-4">Title: {{ $po->po_title }}</h5>
                <div class="row g-4">
                    <div class="col-md-6">
                        <div class="row align-items-center mb-3">
                            <div class="col-4">
                                <h6 class="mb-0 black-text fw-bold">Supplier:</h6>
                            </div>
                            <div class="col-8">
                                <input type="text" name="" class="form-control form-control-sm w-100">
                            </div>
                        </div>

                        <div class="row align-items-center mb-3">
                            <div class="col-4">
                                <h6 class="mb-0 black-text fw-bold">Address:</h6>
                            </div>
                            <div class="col-8">
                                <input type="text" name="" class="form-control form-control-sm w-100">
                            </div>
                        </div>

                        <div class="row align-items-center mb-3">
                            <div class="col-4">
                                <h6 class="mb-0 black-text fw-bold">Tel No.:</h6>
                            </div>
                            <div class="col-8">
                                <input type="text" name="" class="form-control form-control-sm w-100">
                            </div>
                        </div>

                        <div class="row align-items-center mb-3">
                            <div class="col-4">
                                <h6 class="mb-0 black-text fw-bold">TIN:</h6>
                            </div>
                            <div class="col-8">
                                <input type="text" name="" class="form-control form-control-sm w-100">
                            </div>
                        </div>

                        <div class="row align-items-center mb-3">
                            <div class="col-4">
                                <h6 class="mb-0 black-text fw-bold">Place of Delivery:</h6>
                            </div>
                            <div class="col-8">
                                <input type="text" name="" class="form-control form-control-sm w-100">
                            </div>
                        </div>

                        <div class="row align-items-center">
                            <div class="col-4">
                                <h6 class="mb-0 black-text fw-bold">Date of Delivery:</h6>
                            </div>
                            <div class="col-8">
                                <input type="text" name=""
                                    class="form-control form-control-sm w-100 flatpickr-date" placeholder="Select Date">
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 border-start-md">
                        <div class="row align-items-center mb-3">
                            <div class="col-4">
                                <h6 class="mb-0 black-text fw-bold">P.O. No.:</h6>
                            </div>
                            <div class="col-8">
                                <input type="text" name="" class="form-control form-control-sm w-100">
                            </div>
                        </div>

                        <div class="row align-items-center mb-3">
                            <div class="col-4">
                                <h6 class="mb-0 black-text fw-bold">Date:</h6>
                            </div>
                            <div class="col-8">
                                <input type="text" name=""
                                    class="form-control form-control-sm w-100 flatpickr-date" placeholder="Select Date">
                            </div>
                        </div>

                        <div class="row align-items-center mb-3">
                            <div class="col-4">
                                <h6 class="mb-0 black-text fw-bold">Mode of Procurement:</h6>
                            </div>
                            <div class="col-8">
                                <input type="text" name="" class="form-control form-control-sm w-100">
                            </div>
                        </div>

                        <div class="row align-items-center mb-3">
                            <div class="col-4">
                                <h6 class="mb-0 black-text fw-bold">TUP-Taguig TIN:</h6>
                            </div>
                            <div class="col-8">
                                <input type="text" name="" class="form-control form-control-sm w-100">
                            </div>
                        </div>

                        <div class="row align-items-center mb-3">
                            <div class="col-4">
                                <h6 class="mb-0 black-text fw-bold">Delivery Term:</h6>
                            </div>
                            <div class="col-8">
                                <input type="text" name="" class="form-control form-control-sm w-100">
                            </div>
                        </div>

                        <div class="row align-items-center">
                            <div class="col-4">
                                <h6 class="mb-0 black-text fw-bold">Payment Term:</h6>
                            </div>
                            <div class="col-8">
                                <input type="text" name="" class="form-control form-control-sm w-100">
                            </div>
                        </div>
                    </div>

                </div>
            </div>
            <hr>
            <h5 class="black-text fw-bold ms-4 ps-1">Add Items</h5>
            <div class="table-responsive mx-3">
                <table class="table table-sm table-borderless align-middle po-table">
                    <thead class="bg-transparent">
                        <tr>
                            <th class="text-center black-text fw-bold" style="width: 8%">Stock</th>
                            <th class="text-center black-text fw-bold" style="width: 12%">Unit</th>
                            <th class="black-text fw-bold">Item Description</th>
                            <th class="text-center black-text fw-bold" style="width: 8%">Qty.</th>
                            <th class="text-center black-text fw-bold" style="width: 10%">Unit Cost</th>
                            <th class="text-center black-text fw-bold" style="width: 10%">Amount</th>
                            <th class="text-center black-text fw-bold" style="width: 19%">Category</th>
                            <th class="text-start px-0" style="width: 30px"></th>
                        </tr>
                    </thead>
                    <tbody id="tbody-add-items">
                        <!-- Item Row 1 -->
                        <tr class="po-item-row" data-id="1">
                            <input type="hidden" name="items[0][app_item_id]" value="">
                            <td class="px-1"><input type="text"
                                    class="form-control form-control-sm text-center stock-input" name=""
                                    value="" oninput="this.value = this.value.replace(/[^0-9]/g, '')"></td>
                            <td class="px-1">
                                <select class="form-select form-control-sm unit-select" name="items[0][unit]">
                                    <option value="" selected disabled>Select</option>
                                    <option value="Piece">Piece</option>
                                    <option value="Lot">Lot</option>
                                    <option value="Set">Set</option>
                                    <option value="Box">Box</option>
                                    <option value="Pack">Pack</option>
                                    <option value="Ream">Ream</option>
                                    <option value="Dozen">Dozen</option>
                                    <option value="Carton">Carton</option>
                                    <option value="Liter">Liter</option>
                                    <option value="Milliliter">Milliliter</option>
                                    <option value="Kilogram">Kilogram</option>
                                    <option value="Gram">Gram</option>
                                    <option value="Meter">Meter</option>
                                    <option value="Roll">Roll</option>
                                    <option value="Square meter">Square meter</option>
                                </select>
                            </td>
                            <td class="px-1">
                                <div class="input-group input-group-sm">
                                    <input type="text" class="form-control description-input"
                                        name="items[0][description]" value="">
                                    <span class="input-group-text bg-white border-start-0 add-specification-btn"
                                        title="Add Specifications" style="cursor: pointer;">
                                        <img src="{{ asset('img/add-description-btn.png') }}" alt="Add"
                                            style="width: 14px; height: 14px;">
                                    </span>
                                </div>
                            </td>
                            <td class="px-1"><input type="text"
                                    class="form-control form-control-sm text-center qty-input" name="items[0][quantity]"
                                    value="" oninput="this.value = this.value.replace(/[^0-9]/g, '')"></td>
                            <td class="px-1"><input type="text"
                                    class="form-control form-control-sm text-center cost-input" name="items[0][cost]"
                                    value="" oninput="this.value = this.value.replace(/[^0-9.]/g, '')"></td>
                            <td class="px-1 text-center">
                                <span class="amount-display fw-bold" data-amount="0">₱ 0.00</span>
                            </td>
                            <td class="px-1">
                                <select class="form-select form-control-sm category-select" name="items[0][category]">
                                    <option value="" selected disabled>Select</option>
                                    <option value="Supply and Materials">Supply and Materials</option>
                                    <option value="Semi-Expendable">Semi-Expendable</option>
                                    <option value="Equipment">Equipment</option>
                                </select>
                            </td>
                            <td class="text-start px-0">
                                <button type="button"
                                    class="btn border-0 bg-transparent text-black fw-bold remove-row-btn p-0 ms-2"
                                    style="visibility: hidden;">
                                    <img src="{{ asset('img/remove.svg') }}" alt="Remove">
                                </button>
                            </td>
                        </tr>
                        <tr class="po-specification-row d-none">
                            <td colspan="2"></td>
                            <td class="px-1">
                                <div class="custom-specification-container">
                                    <div class="d-flex justify-content-between align-items-center bg-white border rounded-top custom-specification-header toggle-specification-action"
                                        style="cursor: pointer; border-color: #ced4da !important;">
                                        <div class="p-1 px-2 black-text flex-grow-1" style="font-size: 0.8rem;">
                                            Specification</div>
                                        <div class="d-flex align-items-center pe-3">
                                            <button type="button" class="btn-close btn-sm remove-specification-btn me-2"
                                                aria-label="Close" style="width: 0.5em; height: 0.5em;"></button>
                                            <svg class="specification-arrow" width="12" height="12"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <polyline points="6 9 12 15 18 9"></polyline>
                                            </svg>
                                        </div>
                                    </div>
                                    <div class="specification-body border border-top-0 rounded-bottom bg-white"
                                        style="border-color: #ced4da !important;">
                                        <textarea class="form-control form-control-sm border-0 shadow-none px-2" name="items[0][specification]"
                                            rows="2" placeholder="Enter specification details."></textarea>
                                    </div>
                                </div>
                            </td>
                            <td colspan="5"></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <hr class="m-0 p-0">
            <div class="text-center my-2">
                <button type="button" class="btn border-0 bg-transparent text-black fw-bold add-item-btn">+ Add
                    Item</button>
            </div>
        </div>

        <!-- Supply and Materials -->
        <div class="card shadow-sm border-0 mb-3 px-0 po-card">
            <div class="card-body px-0 pb-0">
                <div class="d-flex justify-content-between ms-4 mb-1">
                    <div class="d-flex align-items-baseline gap-2">
                        <h5 class="red-text fw-bold">Supply and Materials</h5>
                        <small class="black-text item-count">0 Item/s</small>
                    </div>
                    <div class="d-flex align-items-baseline gap-2 me-4">
                        <p class="project-total-amount mb-0 fw-bold">₱ 0.00</p>
                        <button type="button" class="collapse-toggle bg-transparent text-black btn p-0 border-0"
                            style="text-decoration: none; box-shadow: none;">
                            <svg class="arrow-icon" width="20" height="20" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="18 15 12 9 6 15"></polyline>
                            </svg>
                        </button>
                    </div>
                </div>
                <div class="po-collapse-area" id="collapseCardSupply">
                    <hr class="m-0 p-0">
                    <div class="table-responsive mx-3">
                        <table class="table table-sm table-borderless align-middle po-table">
                            <thead class="bg-transparent">
                                <tr>
                                    <th class="text-center black-text fw-bold" style="width: 8%">Stock</th>
                                    <th class="text-center black-text fw-bold" style="width: 12%">Unit</th>
                                    <th class="black-text fw-bold">Item Description</th>
                                    <th class="text-center black-text fw-bold" style="width: 8%">Qty.</th>
                                    <th class="text-center black-text fw-bold" style="width: 10%">Unit Cost</th>
                                    <th class="text-center black-text fw-bold" style="width: 10%">Amount</th>
                                    <th class="text-center black-text fw-bold" style="width: 19%">Category</th>
                                    <th class="text-start px-0" style="width: 30px"></th>
                                </tr>
                            </thead>
                            <tbody id="tbody-supply">
                                <!-- Items will be moved here -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Semi-Expendable -->
        <div class="card shadow-sm border-0 mb-3 px-0 po-card">
            <div class="card-body px-0 pb-0">
                <div class="d-flex justify-content-between ms-4 mb-1">
                    <div class="d-flex align-items-baseline gap-2">
                        <h5 class="red-text fw-bold">Semi-Expendable</h5>
                        <small class="black-text item-count">0 Item/s</small>
                    </div>
                    <div class="d-flex align-items-baseline gap-2 me-4">
                        <p class="project-total-amount mb-0 fw-bold">₱ 0.00</p>
                        <button type="button" class="collapse-toggle bg-transparent text-black btn p-0 border-0"
                            style="text-decoration: none; box-shadow: none;">
                            <svg class="arrow-icon" width="20" height="20" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="18 15 12 9 6 15"></polyline>
                            </svg>
                        </button>
                    </div>
                </div>
                <div class="po-collapse-area" id="collapseCardSemi">
                    <hr class="m-0 p-0">
                    <div class="table-responsive mx-3">
                        <table class="table table-sm table-borderless align-middle po-table">
                            <thead class="bg-transparent">
                                <tr>
                                    <th class="text-center black-text fw-bold" style="width: 8%">Stock</th>
                                    <th class="text-center black-text fw-bold" style="width: 12%">Unit</th>
                                    <th class="black-text fw-bold">Item Description</th>
                                    <th class="text-center black-text fw-bold" style="width: 8%">Qty.</th>
                                    <th class="text-center black-text fw-bold" style="width: 10%">Unit Cost</th>
                                    <th class="text-center black-text fw-bold" style="width: 10%">Amount</th>
                                    <th class="text-center black-text fw-bold" style="width: 19%">Category</th>
                                    <th class="text-start px-0" style="width: 30px"></th>
                                </tr>
                            </thead>
                            <tbody id="tbody-semi-expendable">
                                <!-- Items will be moved here -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Equipment -->
        <div class="card shadow-sm border-0 mb-3 px-0 po-card">
            <div class="card-body px-0 pb-0">
                <div class="d-flex justify-content-between ms-4 mb-1">
                    <div class="d-flex align-items-baseline gap-2">
                        <h5 class="red-text fw-bold">Equipment</h5>
                        <small class="black-text item-count">0 Item/s</small>
                    </div>
                    <div class="d-flex align-items-baseline gap-2 me-4">
                        <p class="project-total-amount mb-0 fw-bold">₱ 0.00</p>
                        <button type="button" class="collapse-toggle bg-transparent text-black btn p-0 border-0"
                            style="text-decoration: none; box-shadow: none;">
                            <svg class="arrow-icon" width="20" height="20" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="18 15 12 9 6 15"></polyline>
                            </svg>
                        </button>
                    </div>
                </div>
                <div class="po-collapse-area" id="collapseCardEquip">
                    <hr class="m-0 p-0">
                    <div class="table-responsive mx-3">
                        <table class="table table-sm table-borderless align-middle po-table">
                            <thead class="bg-transparent">
                                <tr>
                                    <th class="text-center black-text fw-bold" style="width: 8%">Stock</th>
                                    <th class="text-center black-text fw-bold" style="width: 12%">Unit</th>
                                    <th class="black-text fw-bold">Item Description</th>
                                    <th class="text-center black-text fw-bold" style="width: 8%">Qty.</th>
                                    <th class="text-center black-text fw-bold" style="width: 10%">Unit Cost</th>
                                    <th class="text-center black-text fw-bold" style="width: 10%">Amount</th>
                                    <th class="text-center black-text fw-bold" style="width: 19%">Category</th>
                                    <th class="text-start px-0" style="width: 30px"></th>
                                </tr>
                            </thead>
                            <tbody id="tbody-equipment">
                                <!-- Items will be moved here -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-end align-items-center mb-3 mt-3">
            <h5 class="fw-bold ps-2 pe-5">Total Amount</h5>
            <h5 class="ps-2 pe-2" id="grand-total-amount">₱ 0.00</h5>
        </div>

    </form>

    @include('partials.toast-feedback')

    <!-- Dynamic JS Toast (Hidden) -->
    <div id="jsValidationToast"
        class="toast align-items-center text-white bg-danger border-0 shadow-lg position-fixed bottom-0 end-0 m-3"
        role="alert" aria-live="assertive" aria-atomic="true" style="z-index: 10000;">
        <div class="d-flex">
            <div class="toast-body d-flex align-items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                    stroke-linejoin="round" class="feather feather-alert-circle">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="12" y1="8" x2="12" y2="12"></line>
                    <line x1="12" y1="16" x2="12.01" y2="16"></line>
                </svg>
                <div id="jsValidationToastMessage"></div>
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"
                aria-label="Close"></button>
        </div>
    </div>
@endsection

@push('js')
    <!-- CUSTOM js -->
    <script src="{{ asset('plugins/src/flatpickr/flatpickr.js') }}"></script>
    <script src="{{ asset('js/procurement/create-po/custom-create-po.js') }}"></script>
@endpush
