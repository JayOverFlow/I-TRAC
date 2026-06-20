<div class="col-md-9 ics-container document-view-container" id="doc-ics-{{ $ics->ics_id }}" style="display: none;">
    <form action="{{ route('save.ics', $ics->ics_id) }}" method="POST">
        @csrf
        <input type="hidden" name="export_pdf" class="export-pdf-flag" value="0">
        <div class="card shadow-sm border-0 mb-3">
            <div class="card-body px-0 pb-0">
                <div class="d-flex justify-content-between align-items-center mb-3 px-3">
                    <h5 class="fw-bold red-text-2 ms-1 mb-0">Inventory Custodian Slip</h5>
                    <div class="">
                        <a href="{{ route('export.ics.pdf', $ics->ics_id) }}" class="btn border border-light-subtle btn-dark-red d-inline-flex align-items-center gap-1 px-3">
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
                            <input type="text" name="ics_fund_cluster" value="{{ $ics->ics_fund_cluster }}" class="form-control form-control-sm ms-2 mb-2 w-75">
                        </div>

                        <div class="row align-items-center mb-5">
                            <div class="col-4">
                                <h6 class="mb-0 black-text fw-bold">P.O. No.:</h6>
                            </div>
                            <div class="col-8">
                                <h6 class="mb-0">{{ $ics->ics_po_no }}</h6>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 border-start-md">
                        <div class="row align-items-center mb-3">
                            <h6 class="mb-2 black-text fw-bold">ICS No.:</h6>
                            <input type="text" name="ics_no" value="{{ $ics->ics_no }}" class="form-control form-control-sm ms-2 mb-2 w-75">
                        </div>
                        <div class="row align-items-center mb-3">
                            <h6 class="mb-2 black-text fw-bold">Code No:</h6>
                            <input type="text" name="ics_code_no" value="{{ $ics->ics_code_no }}" class="form-control form-control-sm ms-2 mb-2 w-75">
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
                        <div class="row align-items-center mb-3">
                            <div class="col-4">
                                <h6 class="mb-0 black-text fw-bold">Received from:</h6>
                            </div>
                            <div class="col-8">
                                <h6 class="mb-0">{{ $ics->giver ? $ics->giver->user_fullname : 'Supply Officer' }}</h6>
                            </div>
                        </div>
                        <div class="row align-items-center mb-3">
                            <h6 class="mb-2 black-text fw-bold">Date:</h6>
                            <input type="text" class="form-control form-control-sm ms-2 w-75 flatpickr" name="ics_received_from_date" value="{{ $ics->ics_received_from_date }}"
                                placeholder="Select Date..">
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="row align-items-center mb-3">
                            <div class="col-4">
                                <h6 class="mb-0 black-text fw-bold">Position/Office:</h6>
                            </div>
                            <div class="col-8">
                                <h6 class="mb-0">{{ $ics->receiver && $ics->receiver->departments->isNotEmpty() ? $ics->receiver->departments->first()->dep_name : '' }}</h6>
                            </div>
                        </div>
                        <div class="row align-items-center mb-3">
                            <div class="col-4">
                                <h6 class="mb-0 black-text fw-bold">Received by:</h6>
                            </div>
                            <div class="col-8">
                                <h6 class="mb-0">{{ $ics->receiver ? $ics->receiver->user_fullname : '' }}</h6>
                            </div>
                        </div>
                        <div class="row align-items-center mb-3">
                            <h6 class="mb-2 black-text fw-bold">Date:</h6>
                            <input type="text" class="form-control form-control-sm ms-2 w-75 flatpickr" name="ics_received_by_date" value="{{ $ics->ics_received_by_date }}"
                                placeholder="Select Date..">
                        </div>
                    </div>
                </div>
                <hr class="m-0 p-0">
                <div class="table-responsive mx-3">
                    <table class="table table-sm table-borderless align-middle">
                        <thead class="bg-transparent">
                            <tr>
                                <th class="text-center black-text fw-bold" style="width: 5%">Qty.</th>
                                <th class="text-center black-text fw-bold" style="width: 10%">Unit</th>
                                <th class="text-center black-text fw-bold" style="width: 10%">Unit Cost</th>
                                <th class="text-center black-text fw-bold" style="width: 10%">Total Cost</th>
                                <th class="black-text fw-bold">Description</th>
                                <th class="text-center black-text fw-bold" style="width: 15%">Inventory Item No.</th>
                                <th class="text-center black-text fw-bold" style="width: 15%">Estimated Useful Time</th>
                                <th class="" style="width: 2%"></th>
                            </tr>
                        </thead>
                        <tbody id="icsItemsTbody">
                            @foreach($ics->icsItems as $index => $item)
                            <tr>
                                <td class="px-1">
                                    <input type="hidden" name="items[{{ $index }}][ics_items_id]" value="{{ $item->ics_items_id }}">
                                    <input type="text" class="form-control form-control-sm text-center qty-input"
                                        name="items[{{ $index }}][ics_quantity]" value="{{ $item->ics_quantity }}" oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                                </td>
                                <td class="px-1">
                                    <input type="text" class="form-control form-control-sm text-center"
                                        name="items[{{ $index }}][ics_unit]" value="{{ $item->ics_unit }}">
                                </td>
                                <td class="px-1">
                                    <input type="text" class="form-control form-control-sm text-center unit-cost-input"
                                        name="items[{{ $index }}][ics_unit_cost]" value="{{ $item->ics_unit_cost }}" data-field="unit_cost"
                                        oninput="this.value = this.value.replace(/[^0-9.]/g, '')">
                                </td>
                                <td class="px-1 text-center">
                                    <span class="total-cost-display fw-bold" data-amount="{{ $item->ics_total_cost }}">₱{{ number_format($item->ics_total_cost, 2) }}</span>
                                </td>
                                <td class="px-1">
                                    <input type="text" class="form-control form-control-sm"
                                        name="items[{{ $index }}][ics_items_descrip]" value="{{ implode(', ', array_filter(array_merge([$item->ics_items_descrip], $item->icsSpecs->pluck('ics_spec_description')->toArray()))) }}">
                                </td>
                                <td class="px-1">
                                    <input type="text" class="form-control form-control-sm text-center"
                                        name="items[{{ $index }}][ics_inventory_item_no]" value="{{ $item->ics_inventory_item_no }}">
                                </td>
                                <td class="px-1">
                                    <input type="text" class="form-control form-control-sm text-center"
                                        name="items[{{ $index }}][ics_estimated_useful_life]" value="{{ $item->ics_estimated_useful_life }}">
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
                    <button type="button" class="btn border-0 bg-transparent text-black fw-bold" id="icsAddItemBtn">+ Add Item</button>
                </div>
            </div>
        </div>
    </form>
</div>
