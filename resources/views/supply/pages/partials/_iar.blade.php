<div class="col-md-9 iar-container">
    <div class="card shadow-sm border-0 mb-3">
        <div class="card-body px-0 pb-0">
            <div class="d-flex justify-content-between align-items-center mb-3 px-3">
                <h5 class="fw-bold red-text-2 ms-1 mb-0">Inspection and Acceptance Report</h5>
                <div class="">
                    <button type="button" id=""
                        class="btn border border-light-subtle btn-dark-red d-inline-flex align-items-center gap-1 px-3">
                        <img src="{{ asset('img/Export.svg') }}" width="18" height="18">
                        <span>Export as PDF</span>
                    </button>

                    <button type="submit" id=""
                        class="btn border border-light-subtle btn-white d-inline-flex align-items-center gap-1 px-2">
                        <img src="{{ asset('img/Save.svg') }}" width="18" height="18">
                        <span class="fw-bold">Save as Draft</span>
                    </button>
                </div>
            </div>
            <hr class="m-0 p-0">
            <div class="row g-4 ms-3 mt-1 mb-1">
                <div class="col-md-6">
                    <div class="row align-items-center mb-3">
                        <div class="col-4">
                            <h6 class="mb-0 black-text fw-bold">Supplier:</h6>
                        </div>
                        <div class="col-8">
                            <h6>$po->po_supplier</h6>
                        </div>
                    </div>

                    <div class="row align-items-center mb-3">
                        <div class="col-4">
                            <h6 class="mb-0 black-text fw-bold">P.O. No./ Date:</h6>
                        </div>
                        <div class="col-8">
                            <h6>$po->po_supplier $po->date</h6>
                        </div>
                    </div>

                    <div class="row align-items-center mb-5">
                        <div class="col-4">
                            <h6 class="mb-0 black-text fw-bold">Requisition Office/ Dept:</h6>
                        </div>
                        <div class="col-8">
                            <h6>$po->department</h6>
                        </div>
                    </div>

                    <div class="row align-items-center mb-3">
                        <h6 class="mb-2 black-text fw-bold">Responsibility Center Code:</h6>
                        <input type="text" name="responsibility_center_code"
                            class="form-control form-control-sm ms-2 mb-2 w-75">
                    </div>

                    <div class="row align-items-center mb-3">
                        <h6 class="mb-2 black-text fw-bold">Fund Cluster:</h6>
                        <input type="text" name="fund_cluster" class="form-control form-control-sm ms-2 mb-2 w-75">
                    </div>
                </div>

                <div class="col-md-6 border-start-md">
                    <div class="row align-items-center mb-3">
                        <h6 class="mb-2 black-text fw-bold">IAR No.:</h6>
                        <input type="text" name="iar_no" class="form-control form-control-sm ms-2 mb-2 w-75">
                    </div>
                    <div class="row align-items-center mb-3">
                        <h6 class="mb-2 black-text fw-bold">Date:</h6>
                        <input type="text" name="iar_date" class="form-control form-control-sm ms-2 mb-2 w-75 flatpickr flatpickr-input active" placeholder="Select Date..">
                    </div>
                    <div class="row align-items-center mb-3">
                        <h6 class="mb-2 black-text fw-bold">Invoice No.:</h6>
                        <input type="text" name="invoice_no" class="form-control form-control-sm ms-2 mb-2 w-75">
                    </div>
                    <div class="row align-items-center mb-3">
                        <h6 class="mb-2 black-text fw-bold">Date:</h6>
                        <input type="text" name="invoice_date" class="form-control form-control-sm ms-2 mb-2 w-75 flatpickr flatpickr-input active" placeholder="Select Date..">
                    </div>
                </div>
            </div>
            <hr class="m-0 p-0">
            <div class="row g-4 ms-3 mt-1 mb-1">
                <div class="col-md-6">
                    <p class="black-text fw-bold text-center">Inspection</p>
                    <div class="row align-items-center mb-3">
                        <h6 class="mb-2 black-text fw-bold">Date Inspected:</h6>
                        <input type="text" class="form-control form-control-sm ms-2 w-75 flatpickr flatpickr-input active" name="date_inspected"
                            placeholder="Select Date..">
                    </div>
                    <div class="row align-items-center mb-3">
                        <h6 class="mb-2 black-text fw-bold">Inspection Officer:</h6>
                        <input type="text" class="form-control form-control-sm ms-2 w-75" name="inspection_officer"
                            placeholder="Dropdown to select user?">
                    </div>
                </div>

                <div class="col-md-6">
                    <p class="black-text fw-bold text-center">Acceptance</p>
                    <div class="row align-items-center mb-3">
                        <h6 class="mb-2 black-text fw-bold">Date Received:</h6>
                        <input type="text" class="form-control form-control-sm ms-2 w-75 flatpickr flatpickr-input active" name="date_received"
                            placeholder="Select Date..">
                    </div>

                    <div class="row align-items-center mb-3">
                        <div class="col-auto">
                            <div class="form-check form-check-danger d-flex align-items-center gap-2 mb-0 ps-0">
                                <input class="form-check-input m-0 position-static" type="radio"
                                    name="flexRadioDefault" id="iar-status-complete" value="Complete">
                                <label class="form-check-label mb-0" for="iar-status-complete" style="margin-top: 2px; cursor: pointer;">
                                    Complete
                                </label>
                            </div>
                        </div>

                        <div class="col-auto d-flex align-items-center gap-2">
                            <div class="form-check form-check-danger d-flex align-items-center gap-2 mb-0 ps-0">
                                <input class="form-check-input m-0 position-static" type="radio"
                                    name="flexRadioDefault" id="iar-status-partial" value="Partial">
                                <label class="form-check-label mb-0" for="iar-status-partial" style="margin-top: 2px; cursor: pointer;">
                                    Partial
                                </label>
                            </div>

                            <input type="text" class="form-control form-control-sm ms-2" name="partial"
                                id="iar-partial-qty" placeholder="Please specify quantity" disabled>
                        </div>
                    </div>


                    <div class="row align-items-center mb-3">
                        <div class="col-4">
                            <h6 class="mb-0 black-text fw-bold">Position/Office:</h6>
                        </div>
                        <div class="col-8">
                            <h6 class="mb-0">$po->office</h6>
                        </div>
                    </div>
                </div>
            </div>
            <hr class="m-0 p-0">
            <div class="table-responsive mx-3">
                <table class="table table-sm table-borderless align-middle">
                    <thead class="bg-transparent">
                        <tr>
                            <th class="text-center black-text fw-bold" style="width: 20%">Stock/Property No.</th>
                            <th class="black-text fw-bold">Description</th>
                            <th class="text-center black-text fw-bold" style="width: 15%">Unit</th>
                            <th class="text-center black-text fw-bold" style="width: 10%">Quantity</th>
                            <th class="" style="width: 2%"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="px-1">
                                <input type="text" class="form-control form-control-sm text-center"
                                    name="items[0][stock_no]"
                                    oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                            </td>
                            <td class="px-1">
                                <input type="text" class="form-control form-control-sm text-center"
                                    name="items[0][description]">
                            </td>
                            <td class="px-1">
                                <input type="text" class="form-control form-control-sm text-center"
                                    name="items[0][unit]">
                            </td>
                            <td class="px-1">
                                <input type="text" class="form-control form-control-sm text-center"
                                    name="items[0][quantity]"
                                    oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                            </td>
                            <td class="p-0">
                                <button type="button"
                                    class="btn border-0 bg-transparent text-black fw-bold remove-row-btn p-0 ms-2">
                                    <img src="{{ asset('img/remove.svg') }}" alt="Remove">
                                </button>
                            </td>
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
    </div>
</div>

@push('css')
    <link rel="stylesheet" href="{{ asset('css/supply/delivery-attachment/partials/iar.css') }}">
@endpush

@push('js')
    <script src="{{ asset('js/supply/delivery-attachment/partials/iar.js') }}"></script>
@endpush
