<div class="col-md-9 ris-container document-view-container" id="doc-ris-{{ $ris->ris_id }}" style="display: none;">
    <form action="{{ route('save.ris', $ris->ris_id) }}" method="POST">
        @csrf
        <div class="card shadow-sm border-0 mb-3">
            <div class="card-body px-0 pb-0">
                <div class="d-flex justify-content-between align-items-center mb-3 px-3">
                    <h5 class="fw-bold red-text-2 ms-1 mb-0">Requisition and Issue Slip</h5>
                    <div class="">
                        <a href="{{ route('export.ris.pdf', $ris->ris_id) }}" class="btn border border-light-subtle btn-dark-red d-inline-flex align-items-center gap-1 px-3">
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
                                <h6 class="mb-0 black-text fw-bold">Division:</h6>
                            </div>
                            <div class="col-8">
                                <h6>Taguig Campus</h6>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 border-start-md">
                        <div class="row align-items-center mb-3">
                            <div class="col-4">
                                <h6 class="mb-0 black-text fw-bold">Office:</h6>
                            </div>
                            <div class="col-8">
                                <h6>{{ $ris->ris_office }}</h6>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row justify-content-center align-items-center ms-4 ps-1 mb-0 text-start">
                    <div class="col-1 ps-0">
                        <h6 class="black-text fw-bold">Purpose:</h6>
                    </div>
                    <div class="col-11">
                        <h6>{{ $ris->ris_purpose }}</h6>
                    </div>
                </div>

                <div class="row g-4 ms-3 mt-0 mb-1">
                    <div class="col-md-6">
                        <div class="row align-items-center mb-3">
                            <h6 class="mb-2 black-text fw-bold">Fund Cluster:</h6>
                            <input type="text" name="ris_fund_cluster" value="{{ $ris->ris_fund_cluster }}" class="form-control form-control-sm ms-2 w-75">
                        </div>
                        <div class="row align-items-center mb-3">
                            <h6 class="mb-2 black-text fw-bold">RIS Number:</h6>
                            <input type="text" name="ris_no" value="{{ $ris->ris_no }}" class="form-control form-control-sm ms-2 w-75">
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="row align-items-center mb-3">
                            <h6 class="mb-2 black-text fw-bold">Responsibility Center Code:</h6>
                            <input type="text" name="ris_center_code" value="{{ $ris->ris_center_code }}"
                                class="form-control form-control-sm ms-2 mb-2 w-75">
                        </div>
                    </div>
                </div>
                <hr class="m-0 p-0">
                @php
                    $firstItem = $ris->risItems->first();
                    $isSemiExpendable = $firstItem && $firstItem->poItem && $firstItem->poItem->po_items_category === 'Semi-Expendable';
                @endphp
                <div class="row g-4 ms-3 mt-0 mb-1">
                    <div class="col-md-6">
                        <div class="row align-items-center mb-3">
                            @if($isSemiExpendable)
                                <h6 class="mb-3 black-text fw-bold">Received by:</h6>
                                <h6 class="ms-2 mb-2">{{ $ris->receiver ? $ris->receiver->user_fullname : 'No user assigned' }}</h6>
                                <input type="hidden" name="ris_received_by" value="{{ $ris->receiver ? $ris->receiver->user_id : '' }}">
                            @else
                                <h6 class="mb-2 black-text fw-bold">Received by:</h6>
                                <select class="form-select form-select-sm ms-2 w-75" name="ris_received_by">
                                    <option value="">Select User</option>
                                    @if($ris->department)
                                        @foreach($ris->department->users as $user)
                                            <option value="{{ $user->user_id }}" {{ ($ris->receiver && $ris->receiver->user_id == $user->user_id) ? 'selected' : '' }}>
                                                {{ $user->user_fullname }}
                                            </option>
                                        @endforeach
                                    @else
                                        <option value="">No department assigned</option>
                                    @endif
                                </select>
                            @endif
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="row align-items-center mb-3">
                            <h6 class="mb-2 black-text fw-bold">Date:</h6>
                            <input type="text" name="ris_received_date" value="{{ $ris->ris_received_date }}" class="form-control form-control-sm ms-2 mb-2 w-75 flatpickr" placeholder="Select Date..">
                        </div>
                    </div>
                </div>
                <hr class="m-0 p-0">
                <div class="row mt-3">
                    <p class="black-text text-center col-7">Requisition</p>
                    <p class="black-text text-start ps-5 ms-1 col-2">Stock</p>
                    <p class="black-text text-center col-2">Issue</p>
                </div>
                <div class="table-responsive mx-3">
                    <table class="table table-sm table-borderless align-middle">
                        <thead class="bg-transparent">
                            <tr>
                                <th class="text-center black-text fw-bold" style="width: 12%">Stock No.</th>
                                <th class="text-center black-text fw-bold" style="width: 12%">Unit</th>
                                <th class="black-text fw-bold" style="width: 38%">Description</th>
                                <th class="text-center black-text fw-bold" style="width: 8%">Qty.</th>
                                <th class="text-center black-text fw-bold" style="width: 2%">Yes</th>
                                <th class="text-center black-text fw-bold" style="width: 2%">No</th>
                                <th class="text-center black-text fw-bold" style="width: 8%">Qty.</th>
                                <th class="text-center black-text fw-bold" style="width: 18%">Remarks</th>
                                <th class="" style="width: 2%"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($ris->risItems as $index => $item)
                            <tr>
                                <td class="px-1">
                                    <input type="hidden" name="items[{{ $index }}][ris_items_id]" value="{{ $item->ris_items_id }}">
                                    <input type="text" class="form-control form-control-sm text-center"
                                        name="items[{{ $index }}][ris_stock_no]"
                                        value="{{ $item->ris_stock_no }}">
                                </td>
                                <td class="px-1">
                                    <input type="text" class="form-control form-control-sm text-center"
                                        name="items[{{ $index }}][ris_unit]"
                                        value="{{ $item->ris_unit }}">
                                </td>
                                <td class="px-1">
                                    <input type="text" class="form-control form-control-sm"
                                        name="items[{{ $index }}][ris_items_descrip]"
                                        value="{{ implode(', ', array_filter(array_merge([$item->ris_items_descrip], $item->risSpecs->pluck('ris_spec_description')->toArray()))) }}">
                                </td>
                                <td class="px-1">
                                    <input type="text" class="form-control form-control-sm text-center"
                                        name="items[{{ $index }}][ris_quantity]"
                                        value="{{ $item->ris_quantity }}">
                                </td>
                                <td class="px-1 text-center">
                                    <div class="form-check form-check-danger form-check-inline m-0">
                                        <input class="form-check-input" type="radio" name="items[{{ $index }}][ris_stock_available]"
                                            id="ris-available-yes-{{ $index }}" value="Yes" {{ $item->ris_stock_available === 'Yes' ? 'checked' : '' }}>
                                    </div>
                                </td>
                                <td class="px-1 text-center">
                                    <div class="form-check form-check-danger form-check-inline m-0">
                                        <input class="form-check-input" type="radio" name="items[{{ $index }}][ris_stock_available]"
                                            id="ris-available-no-{{ $index }}" value="No" {{ $item->ris_stock_available === 'No' ? 'checked' : '' }}>
                                    </div>
                                </td>
                                <td class="px-1">
                                    <input type="text" class="form-control form-control-sm text-center"
                                        name="items[{{ $index }}][ris_issued_quantity]"
                                        value="{{ $item->ris_issued_quantity }}">
                                </td>
                                <td class="px-1">
                                    <input type="text" class="form-control form-control-sm text-center"
                                        name="items[{{ $index }}][ris_issued_remarks]"
                                        value="{{ $item->ris_issued_remarks }}">
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
    </form>
</div>
