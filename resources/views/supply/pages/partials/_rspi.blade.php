<div class="col-md-9 rspi-container document-view-container" id="doc-rspi-{{ $rspi->rspi_id }}" style="display: none;">
    <form action="{{ route('save.rspi', $rspi->rspi_id) }}" method="POST">
        @csrf
        <input type="hidden" name="export_pdf" class="export-pdf-flag" value="0">
        <div class="card shadow-sm border-0 mb-3">
            <div class="card-body px-0 pb-0">
            <div class="d-flex justify-content-between align-items-center mb-3 px-3">
                <h5 class="fw-bold red-text-2 ms-1 mb-0">Report of Semi-Expendable Property Issued</h5>
                <div class="">
                    <a href="{{ route('export.rspi.pdf', $rspi->rspi_id) }}" class="btn border border-light-subtle btn-dark-red d-inline-flex align-items-center gap-1 px-3">
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
                        <h6 class="mb-2 black-text fw-bold">Fund Cluster:</h6>
                        <input type="text" name="rspi_fund_cluster" value="{{ $rspi->rspi_fund_cluster }}" class="form-control form-control-sm ms-2 mb-2 w-75">
                        <span class="invalid-feedback field-error d-none ms-2" data-valmsg-for="rspi_fund_cluster"></span>
                    </div>

                    <div class="row align-items-center mb-5">
                        <div class="col-4">
                            <h6 class="mb-0 black-text fw-bold">P.O. No.:</h6>
                        </div>
                        <div class="col-8">
                            <h6 class="mb-0">{{ $rspi->rspi_po_no }}</h6>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 border-start-md">
                    <div class="row align-items-center mb-3">
                        <h6 class="mb-2 black-text fw-bold">Serial No.:</h6>
                        <input type="text" name="rspi_serial_no" value="{{ $rspi->rspi_serial_no }}" class="form-control form-control-sm ms-2 mb-2 w-75">
                        <span class="invalid-feedback field-error d-none ms-2" data-valmsg-for="rspi_serial_no"></span>
                    </div>
                    <div class="row align-items-center mb-3">
                        <h6 class="mb-2 black-text fw-bold">Date:</h6>
                        <input type="text" class="form-control form-control-sm ms-2 w-75 flatpickr" name="rspi_date" value="{{ $rspi->rspi_date }}"
                            placeholder="Select Date..">
                        <span class="invalid-feedback field-error d-none ms-2" data-valmsg-for="rspi_date"></span>
                    </div>
                </div>
            </div>
            <hr class="m-0 p-0">
            <div class="table-responsive mx-3">
                <table class="table table-sm table-borderless align-middle">
                    <thead class="bg-transparent">
                        <tr>
                            <th class="text-center black-text fw-bold" style="width: 10%">ICS No.</th>
                            <th class="text-center black-text fw-bold" style="width: 10%">Responsibility Center Code</th>
                            <th class="text-center black-text fw-bold" style="width: 10%">Semi-expendable Property No.</th>
                            <th class="black-text fw-bold">Item Description</th>
                            <th class="text-center black-text fw-bold" style="width: 10%">Unit</th>
                            <th class="text-center black-text fw-bold" style="width: 2%">Qty. Issued</th>
                            <th class="text-center black-text fw-bold" style="width: 10%">Unit Cost</th>
                            <th class="text-center black-text fw-bold" style="width: 8%">Amount</th>
                            <th class="" style="width: 2%"></th>
                        </tr>
                    </thead>
                    <tbody id="rspiItemsTbody">
                        @foreach($rspi->rspiItems as $index => $item)
                        <tr>
                            <td class="px-1">
                                <input type="hidden" name="items[{{ $index }}][rspi_items_id]" value="{{ $item->rspi_items_id }}">
                                <input type="text" class="form-control form-control-sm text-center"
                                    name="items[{{ $index }}][rspi_ics_no]" value="{{ $item->rspi_ics_no }}">
                                <span class="invalid-feedback field-error d-none text-center" data-valmsg-for="items[{{ $index }}][rspi_ics_no]"></span>
                            </td>
                            <td class="px-1">
                                <input type="text" class="form-control form-control-sm text-center"
                                    name="items[{{ $index }}][rspi_center_code]" value="{{ $item->rspi_center_code }}">
                                <span class="invalid-feedback field-error d-none text-center" data-valmsg-for="items[{{ $index }}][rspi_center_code]"></span>
                            </td>
                            <td class="px-1">
                                <input type="text" class="form-control form-control-sm text-center"
                                    name="items[{{ $index }}][rspi_property_no]" value="{{ $item->rspi_property_no }}">
                                <span class="invalid-feedback field-error d-none text-center" data-valmsg-for="items[{{ $index }}][rspi_property_no]"></span>
                            </td>
                            <td class="px-1">
                                <input type="text" class="form-control form-control-sm"
                                    name="items[{{ $index }}][rspi_items_descrip]" value="{{ implode(', ', array_filter(array_merge([$item->rspi_items_descrip], $item->rspiSpecs->pluck('rspi_spec_description')->toArray()))) }}">
                                <span class="invalid-feedback field-error d-none" data-valmsg-for="items[{{ $index }}][rspi_items_descrip]"></span>
                            </td>
                            <td class="px-1">
                                <input type="text" class="form-control form-control-sm text-center"
                                    name="items[{{ $index }}][rspi_unit]" value="{{ $item->rspi_unit }}">
                                <span class="invalid-feedback field-error d-none text-center" data-valmsg-for="items[{{ $index }}][rspi_unit]"></span>
                            </td>
                            <td class="px-1">
                                <input type="text" class="form-control form-control-sm text-center qty-input"
                                    name="items[{{ $index }}][rspi_quantity]" value="{{ $item->rspi_quantity }}">
                                <span class="invalid-feedback field-error d-none text-center" data-valmsg-for="items[{{ $index }}][rspi_quantity]"></span>
                            </td>
                            <td class="px-1">
                                <input type="text" class="form-control form-control-sm text-center unit-cost-input"
                                    name="items[{{ $index }}][rspi_unit_cost]" value="{{ $item->rspi_unit_cost }}" data-field="unit_cost">
                                <span class="invalid-feedback field-error d-none text-center" data-valmsg-for="items[{{ $index }}][rspi_unit_cost]"></span>
                            </td>
                            <td class="px-1 text-center">
                                <span class="total-cost-display fw-bold" data-amount="{{ $item->rspi_amount }}">₱{{ number_format($item->rspi_amount, 2) }}</span>
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
                <button type="button" class="btn border-0 bg-transparent text-black fw-bold" id="rspiAddItemBtn">+ Add Item</button>
            </div>
        </div>
    </div>
    </form>
</div>
