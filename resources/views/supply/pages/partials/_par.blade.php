<div class="col-md-9 par-container">
    <div class="card shadow-sm border-0 mb-3">
        <div class="card-body px-0 pb-0">
            <div class="d-flex justify-content-between align-items-center mb-3 px-3">
                <h5 class="fw-bold red-text-2 ms-1 mb-0">Property Acknowledgement Receipt</h5>
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
                        <h6 class="mb-2 black-text fw-bold">Fund Cluster:</h6>
                        <input type="text" name="fund_cluster" class="form-control form-control-sm ms-2 mb-2 w-75">
                    </div>

                    <div class="row align-items-center mb-5">
                        <div class="col-4">
                            <h6 class="mb-0 black-text fw-bold">P.O. No.:</h6>
                        </div>
                        <div class="col-8">
                            <h6 class="mb-0">$po->po_no</h6>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 border-start-md">
                    <div class="row align-items-center mb-3">
                        <h6 class="mb-2 black-text fw-bold">PAR No.:</h6>
                        <input type="text" name="par_no" class="form-control form-control-sm ms-2 mb-2 w-75">
                    </div>
                    <div class="row align-items-center mb-3">
                        <h6 class="mb-2 black-text fw-bold">Code:</h6>
                        <input type="text" name="code" class="form-control form-control-sm ms-2 mb-2 w-75">
                    </div>
                </div>
            </div>
            <hr class="m-0 p-0">
            <div class="row g-4 ms-3 mt-1 mb-1">
                <div class="col-md-6">
                    <div class="row align-items-center mb-5">
                        <div class="col-4">
                            <h6 class="mb-0 black-text fw-bold">Position/Office:</h6>
                        </div>
                        <div class="col-8">
                            <h6 class="mb-0">$po->po_no</h6>
                        </div>
                    </div>
                    <div class="row align-items-center mb-3">
                        <h6 class="mb-2 black-text fw-bold">Received by:</h6>
                        <input type="text" class="form-control form-control-sm ms-2 w-75" name="received_by"
                            placeholder="Dropdown to select user?">
                    </div>
                    <div class="row align-items-center mb-3">
                        <h6 class="mb-2 black-text fw-bold">Date:</h6>
                        <input type="text"
                            class="form-control form-control-sm ms-2 w-75 flatpickr"
                            name="date" placeholder="Select Date..">
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="row align-items-center mb-5">
                        <div class="col-4">
                            <h6 class="mb-0 black-text fw-bold">Position/Office:</h6>
                        </div>
                        <div class="col-8">
                            <h6 class="mb-0">$po->po_no</h6>
                        </div>
                    </div>
                    <div class="row align-items-center mb-5">
                        <div class="col-4">
                            <h6 class="mb-0 black-text fw-bold">Issued by:</h6>
                        </div>
                        <div class="col-8">
                            <h6 class="mb-0">$po->po_no</h6>
                        </div>
                    </div>
                    <div class="row align-items-center mb-3">
                        <h6 class="mb-2 mt-2 pt-1 black-text fw-bold">Date:</h6>
                        <input type="text"
                            class="form-control form-control-sm ms-2 w-75 flatpickr"
                            name="date" placeholder="Select Date..">
                    </div>
                </div>
            </div>
            <hr class="m-0 p-0">
            <div class="table-responsive mx-3">
                <table class="table table-sm table-borderless align-middle">
                    <thead class="bg-transparent">
                        <tr>
                            <th class="text-center black-text fw-bold" style="width: 8%">Qty.</th>
                            <th class="text-center black-text fw-bold" style="width: 10%">Unit</th>
                            <th class="black-text fw-bold">Description</th>
                            <th class="text-center black-text fw-bold" style="width: 15%">Property No.</th>
                            <th class="text-center black-text fw-bold" style="width: 15%">Date Required</th>
                            <th class="text-center black-text fw-bold" style="width: 12%">Amount</th>
                            <th class="" style="width: 2%"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="px-1">
                                <input type="text" class="form-control form-control-sm text-center"
                                    name="items[0][qty]" oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                            </td>
                            <td class="px-1">
                                <input type="text" class="form-control form-control-sm text-center"
                                    name="items[0][unit]">
                            </td>
                            <td class="px-1">
                                <input type="text" class="form-control form-control-sm text-center"
                                    name="items[0][description]">
                            </td>
                            <td class="px-1">
                                <input type="text" class="form-control form-control-sm text-center"
                                    name="items[0][property_no]">
                            </td>
                            <td class="px-1">
                                <input type="text"
                                    class="form-control form-control-sm flatpickr"
                                    name="items[0][date_required]" placeholder="Select Date..">
                            </td>
                            <td class="px-1 text-center">
                                <span class="amount-display fw-bold" data-amount="0">₱
                                    0.00</span>
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
    <link rel="stylesheet" href="{{ asset('css/supply/delivery-attachment/partials/par.css') }}">
@endpush

@push('js')
    <script src="{{ asset('js/supply/delivery-attachment/partials/par.js') }}"></script>
@endpush
