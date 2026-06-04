<div class="col-md-9 iar-container document-view-container" id="doc-iar-{{ $iar->iar_id }}" style="display: none;">
    <div class="card shadow-sm border-0 mb-3">
        <div class="card-body px-0 pb-0">
            <div class="d-flex justify-content-between align-items-center mb-3 px-3">
                <h5 class="fw-bold red-text-2 ms-1 mb-0">Inspection and Acceptance Report</h5>
                <div class="">
                    <button type="button" class="btn border border-light-subtle btn-dark-red d-inline-flex align-items-center gap-1 px-3">
                        <img src="{{ asset('img/Export.svg') }}" width="18" height="18">
                        <span>Export as PDF</span>
                    </button>
                    <button type="submit" class="btn border border-light-subtle btn-white d-inline-flex align-items-center gap-1 px-2">
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
                            <h6>{{ $iar->iar_supplier }}</h6>
                        </div>
                    </div>

                    <div class="row align-items-center mb-3">
                        <div class="col-4">
                            <h6 class="mb-0 black-text fw-bold">P.O. No./ Date:</h6>
                        </div>
                        <div class="col-8">
                            <h6>{{ $iar->iar_po_no_date }}</h6>
                        </div>
                    </div>

                    <div class="row align-items-center mb-5">
                        <div class="col-4">
                            <h6 class="mb-0 black-text fw-bold">Requisition Office/ Dept:</h6>
                        </div>
                        <div class="col-8">
                            <h6>{{ $iar->iar_office }}</h6>
                        </div>
                    </div>

                    <div class="row align-items-center mb-3">
                        <h6 class="mb-2 black-text fw-bold">Responsibility Center Code:</h6>
                        <input type="text" name="responsibility_center_code" value="{{ $iar->iar_center_code }}"
                            class="form-control form-control-sm ms-2 mb-2 w-75">
                    </div>

                    <div class="row align-items-center mb-3">
                        <h6 class="mb-2 black-text fw-bold">Fund Cluster:</h6>
                        <input type="text" name="fund_cluster" value="{{ $iar->iar_fund_cluster }}" class="form-control form-control-sm ms-2 mb-2 w-75">
                    </div>
                </div>

                <div class="col-md-6 border-start-md">
                    <div class="row align-items-center mb-3">
                        <h6 class="mb-2 black-text fw-bold">IAR No.:</h6>
                        <input type="text" name="iar_no" value="{{ $iar->iar_no }}" class="form-control form-control-sm ms-2 mb-2 w-75">
                    </div>
                    <div class="row align-items-center mb-3">
                        <h6 class="mb-2 black-text fw-bold">Date:</h6>
                        <input type="text" name="iar_date" value="{{ $iar->iar_date }}" class="form-control form-control-sm ms-2 mb-2 w-75 flatpickr" placeholder="Select Date..">
                    </div>
                    <div class="row align-items-center mb-3">
                        <h6 class="mb-2 black-text fw-bold">Invoice No.:</h6>
                        <input type="text" name="invoice_no" value="{{ $iar->iar_invoice_no }}" class="form-control form-control-sm ms-2 mb-2 w-75">
                    </div>
                    <div class="row align-items-center mb-3">
                        <h6 class="mb-2 black-text fw-bold">Date:</h6>
                        <input type="text" name="invoice_date" value="{{ $iar->iar_invoice_date }}" class="form-control form-control-sm ms-2 mb-2 w-75 flatpickr" placeholder="Select Date..">
                    </div>
                </div>
            </div>
            <hr class="m-0 p-0">
            <div class="row g-4 ms-3 mt-1 mb-1">
                <div class="col-md-6">
                    <p class="black-text fw-bold text-center">Inspection</p>
                    <div class="row align-items-center mb-3">
                        <h6 class="mb-2 black-text fw-bold">Inspection Officer:</h6>
                        <input type="text" class="form-control form-control-sm ms-2 w-75" name="inspection_officer" value="{{ $iar->iar_inspected_by }}"
                            placeholder="Officer Name">
                    </div>
                </div>

                <div class="col-md-6">
                    <p class="black-text fw-bold text-center">Acceptance</p>
                    <div class="row align-items-center mb-3">
                        <h6 class="mb-2 black-text fw-bold">Date Received:</h6>
                        <input type="text" class="form-control form-control-sm ms-2 w-75 flatpickr" name="date_received" value="{{ $iar->iar_date_accepted }}"
                            placeholder="Select Date..">
                    </div>

                    <div class="row align-items-center mb-3">
                        <div class="col-4">
                            <h6 class="mb-0 black-text fw-bold">Position/Office:</h6>
                        </div>
                        <div class="col-8">
                            <h6 class="mb-0">Supply Officer</h6>
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
                        @foreach($iar->iarItems as $index => $item)
                        <tr>
                            <td class="px-1">
                                <input type="text" class="form-control form-control-sm text-center"
                                    name="items[{{ $index }}][stock_no]"
                                    value="{{ $item->iar_stock_no }}">
                            </td>
                            <td class="px-1">
                                <input type="text" class="form-control form-control-sm"
                                    name="items[{{ $index }}][description]"
                                    value="{{ implode(', ', array_filter(array_merge([$item->iar_items_descrip], $item->iarSpecs->pluck('iar_spec_description')->toArray()))) }}">
                            </td>
                            <td class="px-1">
                                <input type="text" class="form-control form-control-sm text-center"
                                    name="items[{{ $index }}][unit]"
                                    value="{{ $item->iar_unit }}">
                            </td>
                            <td class="px-1">
                                <input type="text" class="form-control form-control-sm text-center"
                                    name="items[{{ $index }}][quantity]"
                                    value="{{ $item->iar_quantity }}">
                            </td>
                            <td class="p-0">
                                <button type="button"
                                    class="btn border-0 bg-transparent text-black fw-bold remove-row-btn p-0 ms-2">
                                    <img src="{{ asset('img/remove.svg') }}" alt="Remove">
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <hr class="m-0 p-0">
            <div class="text-center my-2">
                <button type="button" class="btn border-0 bg-transparent text-black fw-bold add-item-btn">+ Add Item</button>
            </div>
        </div>
    </div>
</div>
