{{-- Extend the main layout that you want to use --}}
@extends('layouts.procurement-layout')

{{-- Define contents to show in the layout --}}
@section('title', 'Create Purchase Order | I-TRAC')

@push('css')
    <!-- Page SPECIFIC css -->
    <link rel="stylesheet" href="{{ asset('css/general-pages/tasks/page-specific/datatables.css') }}">
    <link rel="stylesheet" href="{{ asset('css/general-pages/tasks/page-specific/dt-global_style.css') }}">

    <!-- FilePond CSS -->
    <link href="https://unpkg.com/filepond/dist/filepond.css" rel="stylesheet">

    <!-- CUSTOM css -->
    <link rel="stylesheet" href="{{ asset('css/general-pages/create-pr/page-specific/accordions.css') }}">
    <link rel="stylesheet" href="{{ asset('css/procurement/create-po/custom-create-po.css') }}">
@endpush

@section('content')
<form method="POST" action="{{ route('save.po', $pr->pr_id) }}" id="pr-form">
    @csrf
    @php
        $rowIndex = 0;
        $isReadOnly = false;
    @endphp

    <div class="card allocated-budget-card mb-3">
        <div class="card-body d-flex justify-content-center justify-content-between align-items-center">
            <div class="d-flex flex-column">
                <h5 class="fw-bold red-text-2">PURCHASE ORDER</h5>
            </div>
            <div>
                <h5 class="card-title mb-3 black-text">ALLOCATED BUDGET: PHP 12,345.00</h5>

                <div class="text-end">
                    <input type="hidden" name="status" id="form-status" value="Draft">
                    <button type="button" id="submit-pr-btn" data-url="{{ route('save.po', $pr->pr_id) }}" onclick="document.getElementById('form-status').value='Submitted'"
                        class="btn border border-light-subtle btn-dark-red d-inline-flex align-items-center gap-1 px-3">
                        <img src="{{ asset('img/Check.svg') }}" width="18" height="18">
                        <span>Done</span>
                    </button>

                    <button type="submit" onclick="document.getElementById('form-status').value='Draft'"
                        class="btn border border-light-subtle btn-white d-inline-flex align-items-center gap-1 px-2">
                        <img src="{{ asset('img/Save.svg') }}" width="18" height="18">
                        <span class="fw-bold">Save as Draft</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    @include('partials.toast-feedback')

    <div class="card shadow-sm border-0 mb-3">
        <div class="card-body">
            <div class="row g-4">
                <div class="col-md-6">
                    <div class="row align-items-center mb-3">
                        <div class="col-4">
                            <h6 class="mb-0 black-text fw-bold">Department:</h6>
                        </div>
                        <div class="col-8">
                            <h6 class="mb-0">{{ $pr->department->dep_name ?? 'N/A' }}</h6>
                        </div>
                    </div>

                    <div class="row align-items-center mb-3">
                        <div class="col-4">
                            <h6 class="mb-0 black-text fw-bold">Section:</h6>
                        </div>
                        <div class="col-8">
                            <h6 class="mb-0">{{ $pr->pr_section ?? 'N/A' }}</h6>
                        </div>
                    </div>

                    <div class="row align-items-center">
                        <div class="col-4">
                            <h6 class="mb-0 black-text fw-bold">Purpose:</h6>
                        </div>
                        <div class="col-8">
                            <h6 class="mb-0">{{ $pr->pr_purpose ?? 'N/A' }}</h6>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 border-start-md">
                    <div class="row align-items-center mb-3">
                        <div class="col-4 text-md-end">
                            <h6 class="mb-0 black-text fw-bold">Date:</h6>
                        </div>
                        <div class="col-8">
                            <h6 class="mb-0">{{ $po?->po_date ?? now()->format('F d, Y') }}</h6>
                        </div>
                    </div>
                    <div class="row align-items-center">
                        <div class="col-4 text-md-end">
                            <h6 class="mb-0 black-text fw-bold">P.R No.:</h6>
                        </div>
                        <div class="col-8">
                            <h6 class="mb-0">{{ $pr->pr_no ?? 'N/A' }}</h6>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    @foreach ($groupedItems as $projectTitle => $items)
        <div class="card shadow-sm border-0 mb-3 px-0 pr-card">
            <div class="card-body px-0 pb-0">
                <div class="d-flex justify-content-between ms-4 mb-1">
                    <div class="d-flex align-items-baseline gap-2">
                        <h5 class="red-text fw-bold">Project Title: {{ $projectTitle }}</h5>
                        @php
                            $projectItems = $savedItemsGrouped->get($items->first()->app_item_id, collect());
                        @endphp
                        <small class="black-text item-count">{{ $projectItems->count() ?: $items->count() }} Item/s</small>
                    </div>
                    <div class="d-flex align-items-baseline gap-2 me-4">
                        <p class="project-total-amount mb-0 fw-bold">₱ 0.00</p>
                        <button type="button" class="collapse-toggle bg-transparent text-black btn p-0 border-0"
                            style="text-decoration: none; box-shadow: none;">
                            <svg class="arrow-icon" width="20" height="20" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round">
                                <polyline points="18 15 12 9 6 15"></polyline>
                            </svg>
                        </button>
                    </div>
                </div>
                <div class="pr-collapse-area" id="collapseCard{{ $loop->iteration }}">
                    <hr class="m-0 p-0">
                    <div class="table-responsive mx-3">
                        <table class="table table-sm table-borderless align-middle pr-table">
                            <thead class="bg-transparent">
                                <tr>
                                    <th class="text-center black-text fw-bold" style="width: 12%">Unit</th>
                                    <th class="black-text fw-bold">Item Description</th>
                                    <!-- Auto width takes remaining space -->
                                    <th class="text-center black-text fw-bold" style="width: 8%">Qty.</th>
                                    <th class="text-center black-text fw-bold" style="width: 14%">Unit Cost</th>
                                    <th class="text-center black-text fw-bold" style="width: 14%">Amount</th>
                                    <th class="text-center black-text fw-bold" style="width: 19%">Category</th>
                                    <th class="text-start px-0" style="width: 30px"></th>
                                    <!-- Fixed strict pixel width -->
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($items as $item)
                                    @php
                                        $rowKey = $item->app_item_id;
                                        // Get all saved rows for this APP item, default to the PR item if none saved in PO yet
                                        $currentSavedItems = $savedItemsGrouped->get($rowKey, collect([$item]));
                                    @endphp

                                    @foreach ($currentSavedItems as $saved)
                                        <tr class="pr-item-row" data-id="{{ $rowKey }}">
                                            {{-- Identity for the row --}}
                                            <input type="hidden" name="items[{{ $rowIndex }}][app_item_id]"
                                                value="{{ $rowKey }}">

                                            {{-- Unit --}}
                                            <td class="px-1">
                                                <select class="form-select form-control-sm"
                                                    name="items[{{ $rowIndex }}][unit]"
                                                    {{ $isReadOnly ? 'disabled' : '' }}>
                                                    <option value="" {{ !$saved->unit ? 'selected' : '' }} disabled>
                                                        Select</option>
                                                    @foreach(['Piece', 'Lot', 'Set', 'Box', 'Pack', 'Ream', 'Dozen', 'Carton', 'Liter', 'Milliliter', 'Kilogram', 'Gram', 'Meter', 'Roll', 'Square meter'] as $u)
                                                        <option value="{{ $u }}" {{ $saved->unit === $u ? 'selected' : '' }}>{{ $u }}</option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            {{-- Item Description --}}
                                            <td class="px-1">
                                                <div class="input-group input-group-sm">
                                                    <input type="text" class="form-control"
                                                        name="items[{{ $rowIndex }}][description]"
                                                        value="{{ $saved->description }}"
                                                        {{ $isReadOnly ? 'disabled' : '' }}>
                                                    @if (!$isReadOnly)
                                                        <span
                                                            class="input-group-text bg-white border-start-0 add-specification-btn"
                                                            title="Add Specifications" style="cursor: pointer;">
                                                            <img src="{{ asset('img/add-description-btn.png') }}"
                                                                alt="Add" style="width: 14px; height: 14px;">
                                                        </span>
                                                    @endif
                                                </div>
                                            </td>
                                            {{-- Qty. --}}
                                            <td class="px-1"><input type="text"
                                                    class="form-control form-control-sm text-center qty-input"
                                                    name="items[{{ $rowIndex }}][quantity]"
                                                    value="{{ $saved->quantity }}"
                                                    oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                                                    {{ $isReadOnly ? 'disabled' : '' }}>
                                            </td>
                                            {{-- Unit Cost --}}
                                            <td class="px-1"><input type="text"
                                                    class="form-control form-control-sm text-center cost-input"
                                                    name="items[{{ $rowIndex }}][cost]"
                                                    value="{{ number_format($saved->cost, 2, '.', '') }}"
                                                    oninput="this.value = this.value.replace(/[^0-9.]/g, '')"
                                                    {{ $isReadOnly ? 'disabled' : '' }}>
                                            </td>
                                            {{-- Amount --}}
                                            @php
                                                $amount = $saved->quantity * $saved->cost;
                                            @endphp
                                            <td class="px-1 text-center">
                                                <span class="amount-display fw-bold"
                                                    data-amount="{{ $amount }}">₱
                                                    {{ number_format($amount, 2) }}</span>
                                            </td>
                                            {{-- Category --}}
                                            @php
                                                $catMap = [
                                                    'consumable' => 'Consumable',
                                                    'equipment' => 'Equipment',
                                                    'equipment_50k' => 'Equipment (50k & ↑)',
                                                ];
                                                $catLabel = $catMap[$saved->category] ?? 'Select';
                                            @endphp
                                            <td class="px-1">
                                                <select class="form-select form-control-sm"
                                                    name="items[{{ $rowIndex }}][category]"
                                                    {{ $isReadOnly ? 'disabled' : '' }}>
                                                    <option value="" {{ $catLabel === 'Select' ? 'selected' : '' }} disabled>Select</option>
                                                    <option value="Consumable" {{ $catLabel === 'Consumable' ? 'selected' : '' }}>Consumable</option>
                                                    <option value="Equipment" {{ $catLabel === 'Equipment' ? 'selected' : '' }}>Equipment</option>
                                                    <option value="Equipment (50k & ↑)" {{ $catLabel === 'Equipment (50k & ↑)' ? 'selected' : '' }}>Equipment (50k & ↑)</option>
                                                </select>
                                            </td>
                                            <td class="text-start px-0">
                                                @if (!$isReadOnly)
                                                    <button type="button"
                                                        class="btn border-0 bg-transparent text-black fw-bold remove-row-btn p-0 ms-2"
                                                        style="{{ $loop->first && $loop->parent->first ? 'visibility: hidden;' : 'visibility: visible;' }}">
                                                        <img src="{{ asset('img/remove.svg') }}" alt="Remove">
                                                    </button>
                                                @endif
                                            </td>
                                        </tr>
                                        {{-- Specification --}}
                                        <tr class="pr-specification-row {{ !$saved->specification ? 'd-none' : '' }}">
                                            <td colspan="1"></td>
                                            <td class="px-1">
                                                <div class="custom-specification-container">
                                                    <div class="d-flex justify-content-between align-items-center bg-white border rounded-top custom-specification-header toggle-specification-action"
                                                        style="cursor: pointer; border-color: #ced4da !important;">
                                                        <div class="p-1 px-2 black-text flex-grow-1"
                                                            style="font-size: 0.8rem;">
                                                            Specification
                                                        </div>
                                                        <div class="d-flex align-items-center pe-3">
                                                            @if (!$isReadOnly)
                                                                <button type="button"
                                                                    class="btn-close btn-sm remove-specification-btn me-2"
                                                                    aria-label="Close"
                                                                    style="width: 0.5em; height: 0.5em;"></button>
                                                            @endif
                                                            <svg class="specification-arrow" width="12"
                                                                height="12" viewBox="0 0 24 24" fill="none"
                                                                stroke="currentColor" stroke-width="2"
                                                                stroke-linecap="round" stroke-linejoin="round">
                                                                <polyline points="6 9 12 15 18 9"></polyline>
                                                            </svg>
                                                        </div>
                                                    </div>
                                                    <div class="specification-body border border-top-0 rounded-bottom bg-white"
                                                        style="border-color: #ced4da !important;">
                                                        <textarea class="form-control form-control-sm border-0 shadow-none px-2"
                                                            name="items[{{ $rowIndex }}][specification]" rows="2" placeholder="Enter specification details."
                                                            {{ $isReadOnly ? 'disabled' : '' }}>{{ $saved->specification }}</textarea>
                                                    </div>
                                                </div>
                                            </td>
                                            <td colspan="5"></td>
                                        </tr>
                                        @php $rowIndex++; @endphp
                                    @endforeach
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <hr class="m-0 p-0">
                    @if (!$isReadOnly)
                        <div class="text-center my-2">
                            <button class="btn border-0 bg-transparent text-black fw-bold add-item-btn">+ Add
                                Item</button>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endforeach

    <div class="d-flex justify-content-end align-items-center mb-3 mt-3">
        <h5 class="fw-bold ps-2 pe-5">Total Amount</h5>
        <h5 class="ps-2 pe-2" id="grand-total-amount">₱0.00</h5>
    </div>

</form>
@endsection

@push('js')
    <!-- FilePond JavaScript -->
    <script src="https://unpkg.com/filepond/dist/filepond.js"></script>
    <script src="https://unpkg.com/filepond-plugin-file-validate-size/dist/filepond-plugin-file-validate-size.js"></script>

    <!-- CUSTOM js -->
    <script src="{{ asset('js/procurement/create-po/custom-create-po.js') }}"></script>
@endpush
