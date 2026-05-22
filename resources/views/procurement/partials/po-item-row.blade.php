@php
    $amount = ($item->po_items_quantity ?? 0) * ($item->po_items_cost ?? 0);
    $hasSpec = $item->poSpecs->count() > 0;
@endphp

<tr class="po-item-row" data-id="{{ $item->po_items_id ?? 'new' }}">
    <td class="px-1">
        <input type="text" class="form-control form-control-sm text-center stock-input" 
            name="items[{{ $index }}][stock]" data-field="stock"
            value="{{ $item->po_items_stockno }}" 
            oninput="this.value = this.value.replace(/[^0-9]/g, '')"
            {{ $isDone ? 'disabled' : '' }}>
        <span class="field-error d-none text-center"></span>
    </td>
    <td class="px-1">
        <input type="text" class="form-control form-control-sm text-center unit-input" 
            name="items[{{ $index }}][unit]" data-field="unit"
            value="{{ $item->po_items_unit }}" 
            {{ $isDone ? 'disabled' : '' }}>
        <span class="field-error d-none text-center"></span>
    </td>
    <td class="px-1">
        <div class="input-group input-group-sm">
            <input type="text" class="form-control description-input"
                name="items[{{ $index }}][description]" data-field="description"
                value="{{ $item->po_items_descrip }}"
                {{ $isDone ? 'disabled' : '' }}>
            @if (!$isDone)
                <span class="input-group-text bg-white border-start-0 add-specification-btn"
                    title="Add Specifications" style="cursor: pointer;">
                    <img src="{{ asset('img/add-description-btn.png') }}" alt="Add"
                        style="width: 14px; height: 14px;">
                </span>
            @endif
        </div>
        <span class="field-error d-none"></span>
    </td>
    <td class="px-1">
        <input type="text" class="form-control form-control-sm text-center qty-input" 
            name="items[{{ $index }}][quantity]" data-field="quantity"
            value="{{ $item->po_items_quantity }}" 
            oninput="this.value = this.value.replace(/[^0-9]/g, '')"
            {{ $isDone ? 'disabled' : '' }}>
        <span class="field-error d-none text-center"></span>
    </td>
    <td class="px-1">
        <input type="text" class="form-control form-control-sm text-center cost-input" 
            name="items[{{ $index }}][cost]" data-field="cost"
            value="{{ $item->po_items_cost }}" 
            oninput="this.value = this.value.replace(/[^0-9.]/g, '')"
            {{ $isDone ? 'disabled' : '' }}>
        <span class="field-error d-none text-center"></span>
    </td>
    <td class="px-1 text-center">
        <span class="amount-display fw-bold" data-amount="{{ $amount }}">₱ {{ number_format($amount, 2) }}</span>
    </td>

    <td class="text-start px-0">
        @if (!$isDone)
            <button type="button" class="btn border-0 bg-transparent text-black fw-bold remove-row-btn p-0 ms-2"
                style="{{ str_contains($index, 'other') && $loop->first ? 'visibility: hidden;' : 'visibility: visible;' }}">
                <img src="{{ asset('img/remove.svg') }}" alt="Remove">
            </button>
        @endif
    </td>
</tr>

<tr class="po-specification-row {{ $hasSpec ? '' : 'd-none' }}">
    <td colspan="2"></td>
    <td class="px-1">
        <div class="custom-specification-container">
            <div class="d-flex justify-content-between align-items-center bg-white border rounded-top custom-specification-header toggle-specification-action"
                style="cursor: pointer; border-color: #ced4da !important;">
                <div class="p-1 px-2 black-text flex-grow-1" style="font-size: 0.8rem;">
                    Specification</div>
                <div class="d-flex align-items-center pe-3">
                    @if (!$isDone)
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
            <div class="specification-body border border-top-0 rounded-bottom bg-white"
                style="border-color: #ced4da !important; {{ $hasSpec ? '' : 'display: none;' }}">
                <textarea class="form-control form-control-sm border-0 shadow-none px-2" 
                    name="items[{{ $index }}][specification]" data-field="specification"
                    rows="2" placeholder="Enter specification details."
                    {{ $isDone ? 'disabled' : '' }}>{{ $item->poSpecs->first()->po_spec_description ?? '' }}</textarea>
                <span class="field-error d-none"></span>
            </div>
        </div>
    </td>
    <td colspan="4"></td>
</tr>
