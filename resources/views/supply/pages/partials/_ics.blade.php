<div class="col-md-9 ics-container document-view-container" id="doc-ics-{{ $ics->ics_id }}" style="display: none;">
    <form action="{{ $ics->is_transfer ? route('transfer.ics.submit', $ics->ics_id) : route('save.ics', $ics->ics_id) }}" method="POST">
        @csrf
        <input type="hidden" name="export_pdf" class="export-pdf-flag" value="0">
        <div class="card shadow-sm border-0 mb-3">
            <div class="card-body px-0 pb-0">
                <div class="d-flex justify-content-between align-items-center mb-3 px-3">
                    <h5 class="fw-bold red-text-2 ms-1 mb-0">{{ $ics->is_transfer ? 'ICS - Transfer Form' : 'Inventory Custodian Slip' }}</h5>
                    <div class="">
                        @if($ics->is_transfer)
                            @if(is_null($ics->ics_received_by))
                                <button type="submit" class="btn btn-dark-red btn-transfer-submit px-3" data-current-owner="{{ $ics->mr && $ics->mr->assignedUser ? $ics->mr->assignedUser->user_fullname : 'Supply Officer' }}">
                                    <span class="fw-bold">Transfer</span>
                                </button>
                            @else
                                <a href="javascript:void(0)" class="btn border border-light-subtle btn-dark-red d-inline-flex align-items-center gap-1 px-3 disabled" title="Export as PDF is non-functional for now">
                                    <img src="{{ asset('img/Export.svg') }}" width="18" height="18">
                                    <span>Export as PDF</span>
                                </a>
                            @endif
                        @else
                            <a href="{{ route('export.ics.pdf', $ics->ics_id) }}" class="btn border border-light-subtle btn-dark-red d-inline-flex align-items-center gap-1 px-3">
                                <img src="{{ asset('img/Export.svg') }}" width="18" height="18">
                                <span>Export as PDF</span>
                            </a>
                            <button type="submit" class="btn border border-light-subtle btn-white d-inline-flex align-items-center gap-1 px-2">
                                <img src="{{ asset('img/Save.svg') }}" width="18" height="18">
                                <span class="fw-bold">Save as Draft</span>
                            </button>
                        @endif
                    </div>
                </div>
                <hr class="m-0 p-0">
                <div class="row g-4 ms-3 mt-1 mb-1">
                    <div class="col-md-6">
                        <fieldset @if($ics->is_transfer) disabled @endif>
                            <div class="row align-items-center mb-3">
                                <div class="col-4">
                                    <h6 class="mb-0 black-text fw-bold">Fund Cluster:</h6>
                                </div>
                                <div class="col-8">
                                    @if($ics->is_transfer)
                                        <p class="mb-0">{{ $ics->ics_fund_cluster }}</p>
                                    @else
                                        <input type="text" name="ics_fund_cluster" value="{{ $ics->ics_fund_cluster }}" class="form-control form-control-sm w-75">
                                        <span class="invalid-feedback field-error d-none" data-valmsg-for="ics_fund_cluster"></span>
                                    @endif
                                </div>
                            </div>

                            <div class="row align-items-center mb-3">
                                <div class="col-4">
                                    <h6 class="mb-0 black-text fw-bold">P.O. No.:</h6>
                                </div>
                                <div class="col-8">
                                    <h6 class="mb-0">{{ $ics->ics_po_no }}</h6>
                                </div>
                            </div>
                        </fieldset>
                    </div>

                    <div class="col-md-6 border-start-md">
                        <fieldset @if($ics->is_transfer) disabled @endif>
                            <div class="row align-items-center mb-3">
                                <div class="col-4">
                                    <h6 class="mb-0 black-text fw-bold">ICS No.:</h6>
                                </div>
                                <div class="col-8">
                                    @if($ics->is_transfer)
                                        <p class="mb-0">{{ $ics->ics_no }}</p>
                                    @else
                                        <input type="text" name="ics_no" value="{{ $ics->ics_no }}" class="form-control form-control-sm w-75">
                                        <span class="invalid-feedback field-error d-none" data-valmsg-for="ics_no"></span>
                                    @endif
                                </div>
                            </div>
                            <div class="row align-items-center mb-3">
                                <div class="col-4">
                                    <h6 class="mb-0 black-text fw-bold">Code No:</h6>
                                </div>
                                <div class="col-8">
                                    @if($ics->is_transfer)
                                        <p class="mb-0">{{ $ics->ics_code_no }}</p>
                                    @else
                                        <input type="text" name="ics_code_no" value="{{ $ics->ics_code_no }}" class="form-control form-control-sm w-75">
                                        <span class="invalid-feedback field-error d-none" data-valmsg-for="ics_code_no"></span>
                                    @endif
                                </div>
                            </div>
                        </fieldset>
                    </div>
                </div>
                <hr class="m-0 p-0">
                <div class="row g-4 ms-3 mt-1 mb-1">
                    <div class="col-md-6">
                        <fieldset @if($ics->is_transfer) disabled @endif>
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
                                <div class="col-4">
                                    <h6 class="mb-0 black-text fw-bold">Date:</h6>
                                </div>
                                <div class="col-8">
                                    @if($ics->is_transfer)
                                        <p class="mb-0">{{ $ics->ics_received_from_date }}</p>
                                    @else
                                        <input type="text" class="form-control form-control-sm w-75 flatpickr" name="ics_received_from_date" value="{{ $ics->ics_received_from_date }}"
                                            placeholder="Select Date..">
                                        <span class="invalid-feedback field-error d-none" data-valmsg-for="ics_received_from_date"></span>
                                    @endif
                                </div>
                            </div>
                        </fieldset>
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
                                @if($ics->is_transfer)
                                    @if(is_null($ics->ics_received_by))
                                        <select name="ics_received_by" class="form-select form-select-sm ms-2 w-75">
                                            <option value="">Select User...</option>
                                            @foreach($users as $user)
                                                <option value="{{ $user->user_id }}" {{ $ics->ics_received_by == $user->user_id ? 'selected' : '' }}>
                                                    {{ $user->user_fullname }}
                                                </option>
                                            @endforeach
                                        </select>
                                    @else
                                        <h6 class="mb-0">{{ $ics->receiver ? $ics->receiver->user_fullname : '—' }}</h6>
                                        <input type="hidden" name="ics_received_by" value="{{ $ics->ics_received_by }}">
                                    @endif
                                @else
                                    <h6 class="mb-0">{{ $ics->receiver ? $ics->receiver->user_fullname : '' }}</h6>
                                @endif
                            </div>
                        </div>
                        <div class="row align-items-center mb-3">
                            <div class="col-4">
                                <h6 class="mb-0 black-text fw-bold">Date:</h6>
                            </div>
                            <div class="col-8">
                                @if($ics->is_transfer && !is_null($ics->ics_received_by))
                                    <p class="mb-0">{{ $ics->ics_received_by_date }}</p>
                                @else
                                    <input type="text" class="form-control form-control-sm w-75 flatpickr" name="ics_received_by_date" value="{{ $ics->ics_received_by_date }}"
                                        placeholder="Select Date..">
                                    <span class="invalid-feedback field-error d-none" data-valmsg-for="ics_received_by_date"></span>
                                @endif
                            </div>
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
                            @php
                                $specDescription = $item->icsSpecs->first()->ics_spec_description ?? '';
                                $hasSpec = !empty($specDescription);
                            @endphp
                            <tr class="ics-item-row">
                                <td class="px-1">
                                    <input type="hidden" name="items[{{ $index }}][ics_items_id]" value="{{ $item->ics_items_id }}">
                                    @if($ics->is_transfer)
                                        <p class="mb-0 text-center">{{ $item->ics_quantity }}</p>
                                    @else
                                        <input type="text" class="form-control form-control-sm text-center qty-input"
                                            name="items[{{ $index }}][ics_quantity]" value="{{ $item->ics_quantity }}" oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                                        <span class="invalid-feedback field-error d-none text-center" data-valmsg-for="items[{{ $index }}][ics_quantity]"></span>
                                    @endif
                                </td>
                                <td class="px-1">
                                    @if($ics->is_transfer)
                                        <p class="mb-0 text-center">{{ $item->ics_unit }}</p>
                                    @else
                                        <input type="text" class="form-control form-control-sm text-center"
                                            name="items[{{ $index }}][ics_unit]" value="{{ $item->ics_unit }}">
                                        <span class="invalid-feedback field-error d-none text-center" data-valmsg-for="items[{{ $index }}][ics_unit]"></span>
                                    @endif
                                </td>
                                <td class="px-1">
                                    @if($ics->is_transfer)
                                        <p class="mb-0 text-center">₱{{ number_format($item->ics_unit_cost, 2) }}</p>
                                    @else
                                        <input type="text" class="form-control form-control-sm text-center unit-cost-input"
                                            name="items[{{ $index }}][ics_unit_cost]" value="{{ $item->ics_unit_cost }}" data-field="unit_cost"
                                            oninput="this.value = this.value.replace(/[^0-9.]/g, '')">
                                        <span class="invalid-feedback field-error d-none text-center" data-valmsg-for="items[{{ $index }}][ics_unit_cost]"></span>
                                    @endif
                                </td>
                                <td class="px-1 text-center">
                                    <span class="total-cost-display fw-bold" data-amount="{{ $item->ics_total_cost }}">₱{{ number_format($item->ics_total_cost, 2) }}</span>
                                </td>
                                <td class="px-1">
                                    @if($ics->is_transfer)
                                        <p class="mb-0 fw-bold">{{ $item->ics_items_descrip }}</p>
                                        @if($hasSpec)
                                            <p class="mb-0 text-muted small mt-1" style="white-space: pre-line;">{{ $specDescription }}</p>
                                        @endif
                                    @else
                                        <div class="input-group input-group-sm">
                                            <input type="text" class="form-control form-control-sm"
                                                name="items[{{ $index }}][ics_items_descrip]" value="{{ $item->ics_items_descrip }}">
                                            <span class="input-group-text bg-white border-start-0 add-specification-btn"
                                                title="Add Specifications" style="cursor: pointer;">
                                                <img src="{{ asset('img/add-description-btn.png') }}" alt="Add"
                                                    style="width: 14px; height: 14px;">
                                            </span>
                                        </div>
                                        <span class="invalid-feedback field-error d-none" data-valmsg-for="items[{{ $index }}][ics_items_descrip]"></span>
                                    @endif
                                </td>
                                <td class="px-1">
                                    @if($ics->is_transfer)
                                        <p class="mb-0 text-center">{{ $item->ics_inventory_item_no }}</p>
                                    @else
                                        <input type="text" class="form-control form-control-sm text-center"
                                            name="items[{{ $index }}][ics_inventory_item_no]" value="{{ $item->ics_inventory_item_no }}">
                                        <span class="invalid-feedback field-error d-none text-center" data-valmsg-for="items[{{ $index }}][ics_inventory_item_no]"></span>
                                    @endif
                                </td>
                                <td class="px-1">
                                    @if($ics->is_transfer)
                                        <p class="mb-0 text-center">{{ $item->ics_estimated_useful_life }}</p>
                                    @else
                                        <input type="text" class="form-control form-control-sm text-center"
                                            name="items[{{ $index }}][ics_estimated_useful_life]" value="{{ $item->ics_estimated_useful_life }}">
                                        <span class="invalid-feedback field-error d-none text-center" data-valmsg-for="items[{{ $index }}][ics_estimated_useful_life]"></span>
                                    @endif
                                </td>
                                @if(!$ics->is_transfer)
                                <td class="p-0">
                                    <button type="button"
                                        class="btn border-0 bg-transparent text-black fw-bold remove-row-btn p-0 ms-2">
                                        <img src="{{ asset('img/remove.svg') }}" alt="Remove">
                                    </button>
                                </td>
                                @endif
                            </tr>
                            @if(!$ics->is_transfer)
                            <tr class="specification-row {{ $hasSpec ? '' : 'd-none' }}">
                                <td colspan="4"></td>
                                <td class="px-1">
                                    <div class="custom-specification-container">
                                        <div class="d-flex justify-content-between align-items-center rounded-top custom-specification-header toggle-specification-action"
                                            style="cursor: pointer;">
                                            <div class="p-1 px-2 black-text flex-grow-1" style="font-size: 0.8rem;">
                                                Specification</div>
                                            <div class="d-flex align-items-center pe-3">
                                                @if(!$ics->is_transfer)
                                                <button type="button" class="btn-close btn-sm remove-specification-btn me-2"
                                                    aria-label="Close" style="width: 0.5em; height: 0.5em;"></button>
                                                @endif
                                                <svg class="specification-arrow" width="12" height="12"
                                                    viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                                    style="{{ $hasSpec ? 'transform: rotate(180deg);' : '' }}">
                                                    <polyline points="6 9 12 15 18 9"></polyline>
                                                </svg>
                                            </div>
                                        </div>
                                        <div class="specification-body rounded-bottom px-2 py-1"
                                            style="{{ $hasSpec ? '' : 'display: none;' }}">
                                            @if($ics->is_transfer)
                                                <p class="mb-0 text-muted" style="white-space: pre-line;">{{ $specDescription }}</p>
                                            @else
                                                <textarea class="form-control form-control-sm border-0 shadow-none px-2 specification-textarea" 
                                                    name="items[{{ $index }}][specification]" data-field="specification"
                                                    rows="2" placeholder="Enter specification details.">{{ $specDescription }}</textarea>
                                                <span class="invalid-feedback field-error d-none" data-valmsg-for="items[{{ $index }}][specification]"></span>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td colspan="3"></td>
                            </tr>
                            @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <hr class="m-0 p-0">
                @if(!$ics->is_transfer)
                <div class="text-center my-2">
                    <button type="button" class="btn border-0 bg-transparent text-black fw-bold" id="icsAddItemBtn">+ Add Item</button>
                </div>
                @endif
            </div>
        </div>
    </form>
</div>
