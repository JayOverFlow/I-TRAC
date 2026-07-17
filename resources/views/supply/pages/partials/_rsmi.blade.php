<div class="col-md-9 rsmi-container document-view-container" id="doc-rsmi-{{ $rsmi->rsmi_id }}" style="display: none;">
    <form action="{{ route('save.rsmi', $rsmi->rsmi_id) }}" method="POST">
        @csrf
        <input type="hidden" name="export_pdf" class="export-pdf-flag" value="0">
        <div class="card shadow-sm border-0 mb-3">
            <div class="card-body px-0 pb-0">
                <div class="d-flex justify-content-between align-items-center mb-3 px-3">
                    <h5 class="fw-bold red-text-2 ms-1 mb-0">Report of Supplies and Materials Issued</h5>
                    <div class="">
                        <a href="{{ route('export.rsmi.pdf', $rsmi->rsmi_id) }}" class="btn border border-light-subtle btn-dark-red d-inline-flex align-items-center gap-1 px-3">
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
                            <h6 class="mb-2 black-text fw-bold">Fund Cluster: <span class="text-danger">*</span></h6>
                            <input type="text" name="rsmi_fund_cluster" value="{{ $rsmi->rsmi_fund_cluster }}" class="form-control form-control-sm ms-2 mb-2 w-75">
                            <span class="invalid-feedback field-error d-none ms-2" data-valmsg-for="rsmi_fund_cluster"></span>
                        </div>

                        <div class="row align-items-center mb-5">
                            <div class="col-4">
                                <h6 class="mb-0 black-text fw-bold">P.O. No.:</h6>
                            </div>
                            <div class="col-8">
                                <h6 class="mb-0">{{ $rsmi->rsmi_po_no }}</h6>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 border-start-md">
                        <div class="row align-items-center mb-3">
                            <h6 class="mb-2 black-text fw-bold">Serial No.: <span class="text-danger">*</span></h6>
                            <input type="text" name="rsmi_serial_no" value="{{ $rsmi->rsmi_serial_no }}" class="form-control form-control-sm ms-2 mb-2 w-75">
                            <span class="invalid-feedback field-error d-none ms-2" data-valmsg-for="rsmi_serial_no"></span>
                        </div>
                        <div class="row align-items-center mb-3">
                            <h6 class="mb-2 black-text fw-bold">Date: <span class="text-danger">*</span></h6>
                            <input type="text" class="form-control form-control-sm ms-2 w-75 flatpickr" name="rsmi_date" value="{{ $rsmi->rsmi_date }}"
                                placeholder="Select Date..">
                            <span class="invalid-feedback field-error d-none ms-2" data-valmsg-for="rsmi_date"></span>
                        </div>
                    </div>
                </div>
                <hr class="m-0 p-0">
                <div class="row g-4 ms-3 mt-1 mb-1">
                    <div class="col-md-6">
                        <div class="row align-items-center mb-3">
                            <div class="col-4">
                                <h6 class="mb-0 black-text fw-bold">Position/Office:</h6>
                            </div>
                            <div class="col-8">
                                <h6 class="mb-0">Supply Officer</h6>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="row align-items-center mb-3">
                            <div class="col-4">
                                <h6 class="mb-0 black-text fw-bold">Issued by:</h6>
                            </div>
                            <div class="col-8">
                                <h6 class="mb-0">{{ $rsmi->user ? $rsmi->user->user_fullname : 'Supply Officer' }}</h6>
                            </div>
                        </div>
                    </div>
                </div>
                <hr class="m-0 p-0">
                <div class="table-responsive mx-3">
                    <table class="table table-sm table-borderless align-middle">
                        <thead class="bg-transparent">
                            <tr>
                                <th class="text-center black-text fw-bold" style="width: 10%">RIS No.<span class="text-danger">*</span></th>
                                <th class="text-center black-text fw-bold" style="width: 10%">Responsibility Center Code <span class="text-danger">*</span></th>
                                <th class="text-center black-text fw-bold" style="width: 10%">Stock No. <span class="text-danger">*</span></th>
                                <th class="black-text fw-bold">Item Description <span class="text-danger">*</span></th>
                                <th class="text-center black-text fw-bold" style="width: 10%">Unit <span class="text-danger">*</span></th>
                                <th class="text-center black-text fw-bold" style="width: 2%">Qty. Issued<span class="text-danger">*</span></th>
                                <th class="text-center black-text fw-bold" style="width: 10%">Unit Cost <span class="text-danger">*</span></th>
                                <th class="text-center black-text fw-bold" style="width: 8%">Amount</th>
                                <th class="" style="width: 2%"></th>
                            </tr>
                        </thead>
                        <tbody id="rsmiItemsTbody">
                            @foreach($rsmi->rsmiItems as $index => $item)
                            @php
                                $specDescription = $item->rsmiSpecs->first()->rsmi_spec_description ?? '';
                                $hasSpec = !empty($specDescription);
                            @endphp
                            <tr class="rsmi-item-row">
                                <td class="px-1">
                                    <input type="hidden" name="items[{{ $index }}][rsmi_items_id]" value="{{ $item->rsmi_items_id }}">
                                    <input type="text" class="form-control form-control-sm text-center"
                                        name="items[{{ $index }}][rsmi_ris_no]" value="{{ $item->rsmi_ris_no }}">
                                    <span class="invalid-feedback field-error d-none" data-valmsg-for="items[{{ $index }}][rsmi_ris_no]"></span>
                                </td>
                                <td class="px-1">
                                    <input type="text" class="form-control form-control-sm text-center"
                                        name="items[{{ $index }}][rsmi_center_code]" value="{{ $item->rsmi_center_code }}">
                                    <span class="invalid-feedback field-error d-none" data-valmsg-for="items[{{ $index }}][rsmi_center_code]"></span>
                                </td>
                                <td class="px-1">
                                    <input type="text" class="form-control form-control-sm text-center"
                                        name="items[{{ $index }}][rsmi_stock_no]" value="{{ $item->rsmi_stock_no }}">
                                    <span class="invalid-feedback field-error d-none" data-valmsg-for="items[{{ $index }}][rsmi_stock_no]"></span>
                                </td>
                                <td class="px-1">
                                    <div class="input-group input-group-sm">
                                        <input type="text" class="form-control form-control-sm"
                                            name="items[{{ $index }}][rsmi_items_descrip]" value="{{ $item->rsmi_items_descrip }}">
                                        <span class="input-group-text bg-white border-start-0 add-specification-btn"
                                            title="Add Specifications" style="cursor: pointer;">
                                            <img src="{{ asset('img/add-description-btn.png') }}" alt="Add"
                                                style="width: 14px; height: 14px;">
                                        </span>
                                    </div>
                                    <span class="invalid-feedback field-error d-none" data-valmsg-for="items[{{ $index }}][rsmi_items_descrip]"></span>
                                </td>
                                <td class="px-1">
                                    <input type="text" class="form-control form-control-sm text-center"
                                        name="items[{{ $index }}][rsmi_unit]" value="{{ $item->rsmi_unit }}">
                                    <span class="invalid-feedback field-error d-none" data-valmsg-for="items[{{ $index }}][rsmi_unit]"></span>
                                </td>
                                <td class="px-1">
                                    <input type="text" class="form-control form-control-sm text-center qty-input"
                                        name="items[{{ $index }}][rsmi_quantity]" value="{{ $item->rsmi_quantity }}">
                                    <span class="invalid-feedback field-error d-none text-center" data-valmsg-for="items[{{ $index }}][rsmi_quantity]"></span>
                                </td>
                                <td class="px-1">
                                    <input type="text" class="form-control form-control-sm text-center unit-cost-input"
                                        name="items[{{ $index }}][rsmi_unit_cost]" value="{{ $item->rsmi_unit_cost }}">
                                    <span class="invalid-feedback field-error d-none text-center" data-valmsg-for="items[{{ $index }}][rsmi_unit_cost]"></span>
                                </td>
                                <td class="px-1 text-center">
                                    <span class="total-cost-display fw-bold" data-amount="{{ $item->rsmi_amount }}">₱{{ number_format($item->rsmi_amount, 2) }}</span>
                                </td>
                                <td class="p-0">
                                    <button type="button"
                                        class="btn border-0 bg-transparent text-black fw-bold remove-row-btn p-0 ms-2">
                                        <img src="{{ asset('img/remove.svg') }}" alt="Remove">
                                    </button>
                                </td>
                            </tr>
                            <tr class="specification-row {{ $hasSpec ? '' : 'd-none' }}">
                                <td colspan="3"></td>
                                <td class="px-1">
                                    <div class="custom-specification-container">
                                        <div class="d-flex justify-content-between align-items-center rounded-top custom-specification-header toggle-specification-action"
                                            style="cursor: pointer;">
                                            <div class="p-1 px-2 black-text flex-grow-1" style="font-size: 0.8rem;">
                                                Specification <span class="text-danger">*</span></div>
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
                                <td colspan="5"></td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <hr class="m-0 p-0">
                <div class="text-center my-2">
                    <button type="button" class="btn border-0 bg-transparent text-black fw-bold" id="rsmiAddItemBtn">+ Add Item</button>
                </div>
            </div>
        </div>
    </form>
</div>
