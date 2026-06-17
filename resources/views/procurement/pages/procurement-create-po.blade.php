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
    @php
        $isDone = $po->po_status == 'Done';
    @endphp

    <form method="POST" action="{{ route('update.po', ['po_id' => $po->po_id]) }}" id="po-form">
        @csrf
        <input type="hidden" name="po_status" id="po_status" value="{{ $po->po_status }}">
        <input type="hidden" name="po_total_amount" id="po_total_amount_input" value="{{ $po->po_total_amount }}">

        <div class="card allocated-budget-card mb-3">
            <div class="card-body d-flex justify-content-center justify-content-between align-items-center">
                <div class="d-flex flex-column">
                    <h5 class="fw-bold red-text-2">PURCHASE ORDER</h5>
                    @if ($isDone)
                        <h5 class="badge bg-success p-2 px-3 text-uppercase" style="font-size: 0.85rem;">Done</h5>
                    @endif
                </div>
                <div>
                    <div class="text-end">
                        @if ($isDone)
                            <a href="{{ route('export.po.pdf', ['po_id' => $po->po_id]) }}"
                                class="btn border border-light-subtle btn-dark-red d-inline-flex align-items-center gap-1 px-3">
                                <img src="{{ asset('img/Export.svg') }}" width="18" height="18">
                                <span>Export as PDF</span>
                            </a>
                        @else
                            <button type="button" id="submit-po-btn"
                                class="btn border border-light-subtle btn-dark-red d-inline-flex align-items-center gap-1 px-3">
                                <img src="{{ asset('img/Check.svg') }}" width="18" height="18">
                                <span>Done</span>
                            </button>

                            <button type="button" id="draft-po-btn"
                                class="btn border border-light-subtle btn-white d-inline-flex align-items-center gap-1 px-2">
                                <img src="{{ asset('img/Save.svg') }}" width="18" height="18">
                                <span class="fw-bold">Save as Draft</span>
                            </button>
                        @endif
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
                                <input type="text" name="po_supplier" data-field="po_supplier"
                                    class="form-control form-control-sm w-100"
                                    value="{{ $po->po_supplier }}" {{ $isDone ? 'disabled' : '' }}>
                                <span class="field-error d-none"></span>
                            </div>
                        </div>

                        <div class="row align-items-center mb-3">
                            <div class="col-4">
                                <h6 class="mb-0 black-text fw-bold">Address:</h6>
                            </div>
                            <div class="col-8">
                                <input type="text" name="po_address" data-field="po_address"
                                    class="form-control form-control-sm w-100"
                                    value="{{ $po->po_address }}" {{ $isDone ? 'disabled' : '' }}>
                                <span class="field-error d-none"></span>
                            </div>
                        </div>

                        <div class="row align-items-center mb-3">
                            <div class="col-4">
                                <h6 class="mb-0 black-text fw-bold">Tel No.:</h6>
                            </div>
                            <div class="col-8">
                                <input type="text" name="po_tele" data-field="po_tele"
                                    class="form-control form-control-sm w-100"
                                    value="{{ $po->po_tele }}" {{ $isDone ? 'disabled' : '' }}>
                                <span class="field-error d-none"></span>
                            </div>
                        </div>

                        <div class="row align-items-center mb-3">
                            <div class="col-4">
                                <h6 class="mb-0 black-text fw-bold">TIN:</h6>
                            </div>
                            <div class="col-8">
                                <input type="text" name="po_tin" data-field="po_tin"
                                    class="form-control form-control-sm w-100"
                                    placeholder="XXX-XXX-XXX-XXX"
                                    value="{{ $po->po_tin }}" {{ $isDone ? 'disabled' : '' }}>
                                <span class="field-error d-none"></span>
                            </div>
                        </div>

                        <div class="row align-items-center mb-3">
                            <div class="col-4">
                                <h6 class="mb-0 black-text fw-bold">Place of Delivery:</h6>
                            </div>
                            <div class="col-8">
                                <input type="text" name="po_place_delivery" data-field="po_place_delivery"
                                    class="form-control form-control-sm w-100"
                                    value="{{ $po->po_place_delivery }}" {{ $isDone ? 'disabled' : '' }}>
                                <span class="field-error d-none"></span>
                            </div>
                        </div>

                        <div class="row align-items-center">
                            <div class="col-4">
                                <h6 class="mb-0 black-text fw-bold">Date of Delivery:</h6>
                            </div>
                            <div class="col-8">
                                <input type="text" name="po_date_delivery" data-field="po_date_delivery"
                                    class="form-control form-control-sm w-100 flatpickr-date" placeholder="Select Date"
                                    value="{{ $po->po_date_delivery }}" {{ $isDone ? 'disabled' : '' }}>
                                <span class="field-error d-none"></span>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 border-start-md">
                        <div class="row align-items-center mb-3">
                            <div class="col-4">
                                <h6 class="mb-0 black-text fw-bold">P.O. No.:</h6>
                            </div>
                            <div class="col-8">
                                <input type="text" name="po_no" data-field="po_no"
                                    class="form-control form-control-sm w-100"
                                    value="{{ $po->po_no }}" {{ $isDone ? 'disabled' : '' }}>
                                <span class="field-error d-none"></span>
                            </div>
                        </div>

                        <div class="row align-items-center mb-3">
                            <div class="col-4">
                                <h6 class="mb-0 black-text fw-bold">Date:</h6>
                            </div>
                            <div class="col-8">
                                <input type="text" name="po_date" data-field="po_date"
                                    class="form-control form-control-sm w-100 flatpickr-date" placeholder="Select Date"
                                    value="{{ $po->po_date }}" {{ $isDone ? 'disabled' : '' }}>
                                <span class="field-error d-none"></span>
                            </div>
                        </div>

                        <div class="row align-items-center mb-3">
                            <div class="col-4">
                                <h6 class="mb-0 black-text fw-bold">Mode of Procurement:</h6>
                            </div>
                            <div class="col-8">
                                <input type="text" name="po_mode" data-field="po_mode"
                                    class="form-control form-control-sm w-100"
                                    value="{{ $po->po_mode }}" {{ $isDone ? 'disabled' : '' }}>
                                <span class="field-error d-none"></span>
                            </div>
                        </div>

                        <div class="row align-items-center mb-3">
                            <div class="col-4">
                                <h6 class="mb-0 black-text fw-bold">TUP-Taguig TIN:</h6>
                            </div>
                            <div class="col-8">
                                <input type="text" name="po_tuptin" data-field="po_tuptin"
                                    class="form-control form-control-sm w-100"
                                    placeholder="XXX-XXX-XXX-XXX"
                                    value="{{ $po->po_tuptin }}" {{ $isDone ? 'disabled' : '' }}>
                                <span class="field-error d-none"></span>
                            </div>
                        </div>

                        <div class="row align-items-center mb-3">
                            <div class="col-4">
                                <h6 class="mb-0 black-text fw-bold">Delivery Term:</h6>
                            </div>
                            <div class="col-8">
                                <input type="text" name="po_delivery_term" data-field="po_delivery_term"
                                    class="form-control form-control-sm w-100"
                                    value="{{ $po->po_delivery_term }}" {{ $isDone ? 'disabled' : '' }}>
                                <span class="field-error d-none"></span>
                            </div>
                        </div>

                        <div class="row align-items-center">
                            <div class="col-4">
                                <h6 class="mb-0 black-text fw-bold">Payment Term:</h6>
                            </div>
                            <div class="col-8">
                                <input type="text" name="po_payment_term" data-field="po_payment_term"
                                    class="form-control form-control-sm w-100"
                                    value="{{ $po->po_payment_term }}" {{ $isDone ? 'disabled' : '' }}>
                                <span class="field-error d-none"></span>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

        </div>

        <!-- Purchase Order Items -->
        <div class="card shadow-sm border-0 mb-3 px-0 po-card">
            <div class="card-body px-0 pb-0">
                <div class="d-flex justify-content-between ms-4 mb-1">
                    <div class="d-flex align-items-baseline gap-2">
                        <h5 class="red-text fw-bold">Purchase Order Items</h5>
                        <small class="black-text item-count">{{ $poItems->count() }} Item/s</small>
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
                <div class="po-collapse-area" id="collapseCardPoItems">
                    <hr class="m-0 p-0">
                    <div class="table-responsive mx-3 mt-3">
                        <table class="table table-sm table-borderless align-middle po-table">
                            <thead class="bg-transparent">
                                <tr>
                                    <th class="text-center black-text fw-bold" style="width: 8%">Stock</th>
                                    <th class="text-center black-text fw-bold" style="width: 12%">Unit</th>
                                    <th class="black-text fw-bold">Item Description</th>
                                    <th class="text-center black-text fw-bold" style="width: 8%">Qty.</th>
                                    <th class="text-center black-text fw-bold" style="width: 14%">Unit Cost</th>
                                    <th class="text-center black-text fw-bold" style="width: 14%">Amount</th>
                                    <th class="text-start px-0" style="width: 30px"></th>
                                </tr>
                            </thead>
                            <tbody id="tbody-po-items">
                                @foreach ($poItems as $index => $item)
                                    @include('procurement.partials.po-item-row', [
                                        'item' => $item,
                                        'index' => 'item_' . $index,
                                        'isDone' => $isDone,
                                    ])
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @if (!$isDone)
                        <hr class="m-0 p-0">
                        <div class="text-center my-2">
                            <button type="button" class="btn border-0 bg-transparent text-black fw-bold add-item-btn">+ Add Item</button>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-end align-items-center mb-3 mt-3">
            <h5 class="fw-bold black-text ps-2 pe-5">Total Amount</h5>
            <h5 class="ps-2 black-text pe-2" id="grand-total-amount">₱ {{ number_format($po->po_total_amount, 2) }}</h5>
        </div>

    </form>

    @include('partials.toast-feedback')


@endsection

@push('js')
    <!-- CUSTOM js -->
    <script src="{{ asset('plugins/src/flatpickr/flatpickr.js') }}"></script>
    <script src="{{ asset('js/procurement/create-po/custom-create-po.js') }}"></script>
@endpush
