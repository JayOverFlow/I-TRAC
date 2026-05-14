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
                <a href="" class="me-3">
                    <img src="{{ asset('img/Back.svg') }}" width="24" height="24">
                </a>
                <div class="mb-3">
                    <h5 class="fw-bold red-text-2 ms-1 mb-0">Purchase Order Review</h5>
                    <small><img src="{{ asset('img/Info.svg') }}" alt="info" width="16" height="16"> Please
                        verify the information, check the confirmation box, and click 'Continue to generate' below.</small>
                </div>
            </div>

            <hr class="m-0 p-0">
            <h6 class="fw-bold red-text-2 ms-4 mt-4 mb-3">Title: po_title</h6>
            <div class="row g-4 ms-3">
                <div class="col-md-6">
                    <div class="row align-items-center mb-3">
                        <div class="col-4">
                            <h6 class="mb-0 black-text fw-bold">Supplier:</h6>
                        </div>
                        <div class="col-8">
                            <h6>po_supplier</h6>
                        </div>
                    </div>

                    <div class="row align-items-center mb-3">
                        <div class="col-4">
                            <h6 class="mb-0 black-text fw-bold">Address:</h6>
                        </div>
                        <div class="col-8">
                            <h6>po_address</h6>
                        </div>
                    </div>

                    <div class="row align-items-center mb-3">
                        <div class="col-4">
                            <h6 class="mb-0 black-text fw-bold">Tel No.:</h6>
                        </div>
                        <div class="col-8">
                            <h6>po_tele</h6>
                        </div>
                    </div>

                    <div class="row align-items-center mb-3">
                        <div class="col-4">
                            <h6 class="mb-0 black-text fw-bold">TIN:</h6>
                        </div>
                        <div class="col-8">
                            <h6>po_tin</h6>
                        </div>
                    </div>

                    <div class="row align-items-center mb-3">
                        <div class="col-4">
                            <h6 class="mb-0 black-text fw-bold">Place of Delivery:</h6>
                        </div>
                        <div class="col-8">
                            <h6>po_place_delivery</h6>
                        </div>
                    </div>

                    <div class="row align-items-center">
                        <div class="col-4">
                            <h6 class="mb-0 black-text fw-bold">Date of Delivery:</h6>
                        </div>
                        <div class="col-8">
                            <h6>po_date_delivery</h6>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 border-start-md">
                    <div class="row align-items-center mb-3">
                        <div class="col-4">
                            <h6 class="mb-0 black-text fw-bold">P.O. No.:</h6>
                        </div>
                        <div class="col-8">
                            <h6>po_no</h6>
                        </div>
                    </div>

                    <div class="row align-items-center mb-3">
                        <div class="col-4">
                            <h6 class="mb-0 black-text fw-bold">Date:</h6>
                        </div>
                        <div class="col-8">
                            <h6>po_date</h6>
                        </div>
                    </div>

                    <div class="row align-items-center mb-3">
                        <div class="col-4">
                            <h6 class="mb-0 black-text fw-bold">Mode of Procurement:</h6>
                        </div>
                        <div class="col-8">
                            <h6>po_mode</h6>
                        </div>
                    </div>

                    <div class="row align-items-center mb-3">
                        <div class="col-4">
                            <h6 class="mb-0 black-text fw-bold">TUP-Taguig TIN:</h6>
                        </div>
                        <div class="col-8">
                            <h6>po_tuptin</h6>
                        </div>
                    </div>

                    <div class="row align-items-center mb-3">
                        <div class="col-4">
                            <h6 class="mb-0 black-text fw-bold">Delivery Term:</h6>
                        </div>
                        <div class="col-8">
                            <h6>po_delivery_term</h6>
                        </div>
                    </div>

                    <div class="row align-items-center">
                        <div class="col-4">
                            <h6 class="mb-0 black-text fw-bold">Payment Term:</h6>
                        </div>
                        <div class="col-8">
                            <h6>po_payment_term</h6>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    {{-- Supply and Materials --}}
    <div class="card shadow-sm border-0 mb-3 px-0 po-card">
        <div class="card-body px-0 pb-0">
            <div class="d-flex justify-content-between ms-4 mb-1">
                <div class="d-flex align-items-baseline gap-2">
                    <h5 class="red-text fw-bold">Supply and Materials</h5>
                    <small class="black-text item-count">0 Item/s</small>
                </div>
                <div class="d-flex align-items-baseline gap-2 me-4">
                    <p class="project-total-amount mb-0 fw-bold">₱ 0.00</p>
                </div>
            </div>
            <div class="po-collapse-area" id="collapseCardSupply">
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
                            </tr>
                        </thead>
                        <tbody id="tbody-supply">
                            <tr class="po-item-row" data-id="">
                                <td class="px-1 text-center">po_items_stockno</td>
                                <td class="px-1 text-center">po_items_unit</td>
                                <td class="px-1">po_items_descrip</td>
                                <td class="px-1 text-center">po_items_quantity</td>
                                <td class="px-1 text-center">po_items_cost</td>
                                <td class="px-1 text-center">po_items_amount</td>
                                <td class="px-1 text-center">
                                    <select class="form-select form-control-sm category-select" name="items[]">
                                        <option value="" selected>Supply and Materials</option>
                                        <option value="">Semi-Expendable</option>
                                        <option value="">Equipment</option>
                                    </select>
                                </td>
                            </tr>

                            <tr class="po-specification-row">
                                <td colspan="2"></td>
                                <td class="px-1 text-muted" style="font-size: 0.85rem;">
                                    >po_spec_description
                                </td>
                                <td colspan="4"></td>
                            </tr>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <hr>
            <div class="d-flex justify-content-between ms-4 mb-1">
                <div class="d-flex align-items-baseline gap-2">
                    <h5 class="red-text fw-bold">Semi-Expendables</h5>
                    <small class="black-text item-count">0 Item/s</small>
                </div>
                <div class="d-flex align-items-baseline gap-2 me-4">
                    <p class="project-total-amount mb-0 fw-bold">₱ 0.00</p>
                </div>
            </div>
            <div class="po-collapse-area" id="collapseCardSupply">
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
                            </tr>
                        </thead>
                        <tbody id="tbody-supply">
                            <tr class="po-item-row" data-id="">
                                <td class="px-1 text-center">po_items_stockno</td>
                                <td class="px-1 text-center">po_items_unit</td>
                                <td class="px-1">po_items_descrip</td>
                                <td class="px-1 text-center">po_items_quantity</td>
                                <td class="px-1 text-center">po_items_cost</td>
                                <td class="px-1 text-center">po_items_amount</td>
                                <td class="px-1 text-center">
                                    <select class="form-select form-control-sm category-select" name="items[]">
                                        <option value="">Supply and Materials</option>
                                        <option value="" selected>Semi-Expendable</option>
                                        <option value="">Equipment</option>
                                    </select>
                                </td>
                            </tr>

                            <tr class="po-specification-row">
                                <td colspan="2"></td>
                                <td class="px-1 text-muted" style="font-size: 0.85rem;">
                                    >po_spec_description
                                </td>
                                <td colspan="4"></td>
                            </tr>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <hr>
            <div class="d-flex justify-content-between ms-4 mb-1">
                <div class="d-flex align-items-baseline gap-2">
                    <h5 class="red-text fw-bold">Equipment</h5>
                    <small class="black-text item-count">0 Item/s</small>
                </div>
                <div class="d-flex align-items-baseline gap-2 me-4">
                    <p class="project-total-amount mb-0 fw-bold">₱ 0.00</p>
                </div>
            </div>
            <div class="po-collapse-area" id="collapseCardSupply">
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
                            </tr>
                        </thead>
                        <tbody id="tbody-supply">
                            <tr class="po-item-row" data-id="">
                                <td class="px-1 text-center">po_items_stockno</td>
                                <td class="px-1 text-center">po_items_unit</td>
                                <td class="px-1">po_items_descrip</td>
                                <td class="px-1 text-center">po_items_quantity</td>
                                <td class="px-1 text-center">po_items_cost</td>
                                <td class="px-1 text-center">po_items_amount</td>
                                <td class="px-1 text-center">
                                    <select class="form-select form-control-sm category-select" name="items[]">
                                        <option value="">Supply and Materials</option>
                                        <option value="">Semi-Expendable</option>
                                        <option value="" selected>Equipment</option>
                                    </select>
                                </td>
                            </tr>

                            <tr class="po-specification-row">
                                <td colspan="2"></td>
                                <td class="px-1 text-muted" style="font-size: 0.85rem;">
                                    >po_spec_description
                                </td>
                                <td colspan="4"></td>
                            </tr>
                            </tr>
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
                I’ve checked all the information and ensures its accuracy
            </label>
        </div>

        <button type="button" id="generate-btn"
            class="btn border border-light-subtle btn-dark-red d-inline-flex align-items-center gap-1 px-3" disabled>
            Continue to generate
        </button>
    </div>
@endsection

@push('js')
    <!-- Page SPECIFIC css -->

    <!-- CUSTOM css -->
    <script src="{{ asset('js/supply/po-review/custom-po-review.js') }}"></script>
@endpush
