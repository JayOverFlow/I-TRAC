<div class="col-md-9 ndr-container document-view-container" id="doc-ndr-{{ $ndr->ndr_id }}" style="display: none;">
    <div class="card shadow-sm border-0 mb-3">
        <div class="card-body px-0 pb-0">
            <div class="d-flex justify-content-between align-items-center mb-3 px-3">
                <h5 class="fw-bold red-text-2 ms-1 mb-0">Non-Delivery Report</h5>
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
                            <h6 class="mb-0 black-text fw-bold">NDR No.:</h6>
                        </div>
                        <div class="col-8">
                            <h6>{{ $ndr->ndr_no }}</h6>
                        </div>
                    </div>
                    <div class="row align-items-center mb-3">
                        <div class="col-4">
                            <h6 class="mb-0 black-text fw-bold">Date Reported:</h6>
                        </div>
                        <div class="col-8">
                            <h6>{{ $ndr->ndr_date }}</h6>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 border-start-md">
                    <div class="row align-items-center mb-3">
                        <div class="col-4">
                            <h6 class="mb-0 black-text fw-bold">Reported by:</h6>
                        </div>
                        <div class="col-8">
                            <h6>{{ $ndr->reporter ? $ndr->reporter->user_fullname : 'Supply Officer' }}</h6>
                        </div>
                    </div>
                </div>
            </div>
            <hr class="m-0 p-0">
            <div class="table-responsive mx-3 mt-3">
                <table class="table table-sm table-borderless align-middle">
                    <thead class="bg-transparent">
                        <tr>
                            <th class="text-center black-text fw-bold" style="width: 20%">Stock/Property No.</th>
                            <th class="black-text fw-bold">Description</th>
                            <th class="text-center black-text fw-bold" style="width: 15%">Unit</th>
                            <th class="text-center black-text fw-bold" style="width: 10%">Quantity</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($ndr->ndrItems as $index => $item)
                        <tr>
                            <td class="px-1 text-center">
                                <input type="text" class="form-control form-control-sm text-center"
                                    name="items[{{ $index }}][stock_no]" value="{{ $item->ndr_stock_no }}">
                            </td>
                            <td class="px-1">
                                <input type="text" class="form-control form-control-sm"
                                    name="items[{{ $index }}][description]" value="{{ $item->ndr_items_descrip }}">
                                @if($item->ndrSpecs->isNotEmpty())
                                    <div class="text-muted small ps-3 mt-1">
                                        <strong>Specifications:</strong>
                                        <ul class="mb-0">
                                            @foreach($item->ndrSpecs as $spec)
                                                <li>{{ $spec->ndr_spec_description }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif
                            </td>
                            <td class="px-1 text-center">
                                <input type="text" class="form-control form-control-sm text-center"
                                    name="items[{{ $index }}][unit]" value="{{ $item->ndr_unit }}">
                            </td>
                            <td class="px-1 text-center">
                                <input type="text" class="form-control form-control-sm text-center"
                                    name="items[{{ $index }}][qty]" value="{{ $item->ndr_quantity }}">
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
