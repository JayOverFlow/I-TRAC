<div class="col-md-9 iar-container document-view-container" id="doc-iar-{{ $iar->iar_id }}" style="display: none;">
    <form action="{{ route('save.iar', $iar->iar_id) }}" method="POST">
        @csrf
        <input type="hidden" name="export_pdf" class="export-pdf-flag" value="0">
        <div class="card shadow-sm border-0 mb-3">
            <div class="card-body px-0 pb-0">
            <div class="d-flex justify-content-between align-items-center mb-3 px-3">
                <h5 class="fw-bold red-text-2 ms-1 mb-0">Inspection and Acceptance Report</h5>
                <div class="">
                    <a href="{{ route('export.iar.pdf', $iar->iar_id) }}" class="btn border border-light-subtle btn-dark-red d-inline-flex align-items-center gap-1 px-3">
                        <img src="{{ asset('img/Export.svg') }}" width="18" height="18">
                        <span>Export as PDF</span>
                    </a>
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
                        <input type="text" name="iar_center_code" value="{{ $iar->iar_center_code }}"
                            class="form-control form-control-sm ms-2 mb-2 w-75">
                        <span class="invalid-feedback field-error d-none ms-2" data-valmsg-for="iar_center_code"></span>
                    </div>

                    <div class="row align-items-center mb-3">
                        <h6 class="mb-2 black-text fw-bold">Fund Cluster:</h6>
                        <input type="text" name="iar_fund_cluster" value="{{ $iar->iar_fund_cluster }}" class="form-control form-control-sm ms-2 mb-2 w-75">
                        <span class="invalid-feedback field-error d-none ms-2" data-valmsg-for="iar_fund_cluster"></span>
                    </div>
                </div>

                <div class="col-md-6 border-start-md">
                    <div class="row align-items-center mb-3">
                        <h6 class="mb-2 black-text fw-bold">IAR No.:</h6>
                        <input type="text" name="iar_no" value="{{ $iar->iar_no }}" class="form-control form-control-sm ms-2 mb-2 w-75">
                        <span class="invalid-feedback field-error d-none ms-2" data-valmsg-for="iar_no"></span>
                    </div>
                    <div class="row align-items-center mb-3">
                        <h6 class="mb-2 black-text fw-bold">Date:</h6>
                        <input type="text" name="iar_date" value="{{ $iar->iar_date }}" class="form-control form-control-sm ms-2 mb-2 w-75 flatpickr" placeholder="Select Date..">
                        <span class="invalid-feedback field-error d-none ms-2" data-valmsg-for="iar_date"></span>
                    </div>
                    <div class="row align-items-center mb-3">
                        <h6 class="mb-2 black-text fw-bold">Invoice No.:</h6>
                        <input type="text" name="iar_invoice_no" value="{{ $iar->iar_invoice_no }}" class="form-control form-control-sm ms-2 mb-2 w-75">
                        <span class="invalid-feedback field-error d-none ms-2" data-valmsg-for="iar_invoice_no"></span>
                    </div>
                    <div class="row align-items-center mb-3">
                        <h6 class="mb-2 black-text fw-bold">Date:</h6>
                        <input type="text" name="iar_invoice_date" value="{{ $iar->iar_invoice_date }}" class="form-control form-control-sm ms-2 mb-2 w-75 flatpickr" placeholder="Select Date..">
                        <span class="invalid-feedback field-error d-none ms-2" data-valmsg-for="iar_invoice_date"></span>
                    </div>
                </div>
            </div>
            <hr class="m-0 p-0">
            <div class="row g-4 ms-3 mt-1 mb-1">
                <div class="col-md-6">
                    <p class="black-text fw-bold text-center">Inspection</p>
                    <div class="row align-items-center mb-3">
                        <h6 class="mb-2 black-text fw-bold">Inspection Officer:</h6>
                        <input type="text" class="form-control form-control-sm ms-2 w-75" name="iar_inspected_by" value="{{ $iar->iar_inspected_by }}"
                            placeholder="Officer Name">
                        <span class="invalid-feedback field-error d-none ms-2" data-valmsg-for="iar_inspected_by"></span>
                    </div>
                </div>

                <div class="col-md-6">
                    <p class="black-text fw-bold text-center">Acceptance</p>
                    <div class="row align-items-center mb-3">
                        <h6 class="mb-2 black-text fw-bold">Date Received:</h6>
                        <input type="text" class="form-control form-control-sm ms-2 w-75 flatpickr" name="iar_date_accepted" value="{{ $iar->iar_date_accepted }}"
                            placeholder="Select Date..">
                        <span class="invalid-feedback field-error d-none ms-2" data-valmsg-for="iar_date_accepted"></span>
                    </div>

                    <div class="row align-items-center mb-3">
                        <div class="col-4">
                            <h6 class="mb-0 black-text fw-bold">Property and Supply Office:</h6>
                        </div>
                        <div class="col-8">
                            <h6 class="mb-0">{{ $headPropertySupply ? $headPropertySupply->user_fullname : '' }}</h6>
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
                        @php
                            $specDescription = $item->iarSpecs->first()->iar_spec_description ?? '';
                            $hasSpec = !empty($specDescription);
                        @endphp
                        <tr class="iar-item-row">
                            <td class="px-1">
                                <input type="hidden" name="items[{{ $index }}][iar_items_id]" value="{{ $item->iar_items_id }}">
                                <input type="text" class="form-control form-control-sm text-center"
                                    name="items[{{ $index }}][iar_stock_no]"
                                    value="{{ $item->iar_stock_no }}">
                                <span class="invalid-feedback field-error d-none" data-valmsg-for="items[{{ $index }}][iar_stock_no]"></span>
                            </td>
                            <td class="px-1">
                                <div class="input-group input-group-sm">
                                    <input type="text" class="form-control form-control-sm"
                                        name="items[{{ $index }}][iar_items_descrip]"
                                        value="{{ $item->iar_items_descrip }}">
                                    <span class="input-group-text bg-white border-start-0 add-specification-btn"
                                        title="Add Specifications" style="cursor: pointer;">
                                        <img src="{{ asset('img/add-description-btn.png') }}" alt="Add"
                                            style="width: 14px; height: 14px;">
                                    </span>
                                </div>
                                <span class="invalid-feedback field-error d-none" data-valmsg-for="items[{{ $index }}][iar_items_descrip]"></span>
                            </td>
                            <td class="px-1">
                                <input type="text" class="form-control form-control-sm text-center"
                                    name="items[{{ $index }}][iar_unit]"
                                    value="{{ $item->iar_unit }}">
                                <span class="invalid-feedback field-error d-none text-center" data-valmsg-for="items[{{ $index }}][iar_unit]"></span>
                            </td>
                            <td class="px-1">
                                <input type="text" class="form-control form-control-sm text-center"
                                    name="items[{{ $index }}][iar_quantity]"
                                    value="{{ $item->iar_quantity }}">
                                <span class="invalid-feedback field-error d-none text-center" data-valmsg-for="items[{{ $index }}][iar_quantity]"></span>
                            </td>
                            <td class="p-0">
                                <button type="button"
                                    class="btn border-0 bg-transparent text-black fw-bold remove-row-btn p-0 ms-2">
                                    <img src="{{ asset('img/remove.svg') }}" alt="Remove">
                                </button>
                            </td>
                        </tr>
                        <tr class="specification-row {{ $hasSpec ? '' : 'd-none' }}">
                            <td colspan="1"></td>
                            <td class="px-1">
                                <div class="custom-specification-container">
                                    <div class="d-flex justify-content-between align-items-center rounded-top custom-specification-header toggle-specification-action"
                                        style="cursor: pointer;">
                                        <div class="p-1 px-2 black-text flex-grow-1" style="font-size: 0.8rem;">
                                            Specification</div>
                                        <div class="d-flex align-items-center pe-3">
                                            <button type="button" class="btn-close btn-sm remove-specification-btn me-2"
                                                aria-label="Close" style="width: 0.5em; height: 0.5em;"></button>
                                            <svg class="specification-arrow" width="12" height="12"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                                style="{{ $hasSpec ? 'transform: rotate(180deg);' : '' }}">
                                                <polyline points="6 9 12 15 18 9"></polyline>
                                            </svg>
                                        </div>
                                    </div>
                                    <div class="specification-body rounded-bottom"
                                        style="{{ $hasSpec ? '' : 'display: none;' }}">
                                        <textarea class="form-control form-control-sm border-0 shadow-none px-2 specification-textarea" 
                                            name="items[{{ $index }}][specification]" data-field="specification"
                                            rows="2" placeholder="Enter specification details.">{{ $specDescription }}</textarea>
                                        <span class="invalid-feedback field-error d-none" data-valmsg-for="items[{{ $index }}][specification]"></span>
                                    </div>
                                </div>
                            </td>
                            <td colspan="3"></td>
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
    </form>
</div>
