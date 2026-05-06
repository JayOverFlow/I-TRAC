{{-- Extend the main layout that you want to use --}}
@extends('layouts.head-layout')

{{-- Define contents to show in the layout --}}
@section('title', 'Purchase Request Preview | I-TRAC')

@push('css')
    <!-- CUSTOM css -->
    <link rel="stylesheet" href="{{ asset('css/head/pr-review/custom-pr-review.css') }}">
@endpush

@section('content')

    <div class="card allocated-budget-card mb-3">
        <div class="card-body d-flex justify-content-between align-items-center">
            <div>
                <h5 class="fw-bold red-text-2 mb-1">PURCHASE REQUEST</h5>
                @if ($pr->pr_status !== 'Pending')
                    @php
                        $badgeClass = match (strtolower($pr->pr_status)) {
                            'approved' => 'bg-success',
                            'rejected' => 'bg-danger',
                            default => 'bg-secondary',
                        };
                    @endphp
                    <span class="badge {{ $badgeClass }} p-2 px-3 text-uppercase"
                        style="font-size: 0.85rem;">{{ $pr->pr_status }}</span>
                @endif
            </div>

            <div>
                <h5 class="card-title mb-3 black-text">ALLOCATED BUDGET: PHP
                    {{ number_format($pr->prItems->sum('pr_items_total_cost'), 2) }}</h5>

                <div class="text-end">
                    <button type="button"
                        class="btn border border-light-subtle btn-dark-red d-inline-flex align-items-center gap-1 px-3">
                        <img src="{{ asset('img/PO.svg') }}" width="18" height="18" class="img-white-icon"
                            style="filter: invert(1) brightness(100);">
                        <span>Create Purchase Order</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

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
                            <h6 class="mb-0">{{ $pr?->pr_purpose ?? 'N/A' }}</h6>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 border-start-md">
                    <div class="row align-items-center mb-3">
                        <div class="col-4 text-md-end">
                            <h6 class="mb-0 black-text fw-bold">Date:</h6>
                        </div>
                        <div class="col-8">
                            <h6 class="mb-0">
                                {{ $pr->pr_date ? \Carbon\Carbon::parse($pr->pr_date)->format('F d, Y') : 'N/A' }}
                            </h6>
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

    @foreach ($groupedItems as $projectTitle => $categoryGroups)
        <div class="card shadow-sm border-0 mb-3 px-0 pr-card">
            <div class="card-body px-0 pb-0">
                <div class="d-flex justify-content-between ms-4 mb-1">
                    <div class="d-flex align-items-baseline gap-2">
                        <h5 class="red-text fw-bold">Project Title: {{ $projectTitle }}</h5>
                        <small class="black-text item-count">{{ $categoryGroups->flatten()->count() }}
                            Item/s</small>
                    </div>
                    <div class="d-flex align-items-baseline gap-2 me-4">
                        @php
                            $projectTotal = $categoryGroups->flatten()->sum('pr_items_total_cost');
                        @endphp
                        <p class="project-total-amount mb-0 fw-bold">₱
                            {{ number_format($projectTotal, 2) }}</p>
                        <button type="button" class="collapse-toggle bg-transparent text-black btn p-0 border-0"
                            style="text-decoration: none; box-shadow: none;">
                            <svg class="arrow-icon" width="20" height="20" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="18 15 12 9 6 15"></polyline>
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="pr-collapse-area" id="collapseCard{{ $loop->iteration }}">
                    @foreach ($categoryGroups as $categoryKey => $items)
                        <hr class="m-0 p-0">
                        <h5 class="ms-4 mt-4 fw-bold black-text">{{ $categoryOrder[$categoryKey] ?? $categoryKey }}
                        </h5>
                        <div class="table-responsive mx-3">
                            <table class="table table-sm table-borderless align-middle pr-table">
                                <thead class="bg-transparent">
                                    <tr>
                                        <th class="text-center black-text fw-bold" style="width: 12%">Unit</th>
                                        <th class="black-text fw-bold" style="width: 33%">Item Description</th>
                                        <th class="text-center black-text fw-bold" style="width: 8%">Qty.</th>
                                        <th class="text-center black-text fw-bold" style="width: 14%">Unit Cost</th>
                                        <th class="text-center black-text fw-bold" style="width: 14%">Amount</th>
                                        <th class="text-center black-text fw-bold" style="width: 19%">Category</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($items as $item)
                                        @php
                                            $amount = $item->pr_items_quantity * $item->pr_items_cost;
                                            $catMap = [
                                                'consumable' => 'Consumable',
                                                'equipment' => 'Equipment',
                                                'equipment_50k' => 'Equipment (50k & ↑)',
                                            ];
                                        @endphp
                                        <tr class="pr-item-row">
                                            <td class="px-1 text-center">{{ $item->pr_items_unit }}</td>
                                            <td class="px-1">{{ $item->pr_items_descrip }}</td>
                                            <td class="px-1 text-center">{{ $item->pr_items_quantity }}</td>
                                            <td class="px-1 text-center">₱
                                                {{ number_format($item->pr_items_cost, 2) }}</td>
                                            <td class="px-1 text-center">₱ {{ number_format($amount, 2) }}</td>
                                            <td class="px-1 text-center">
                                                {{ $catMap[$item->pr_items_category] ?? '' }}</td>
                                        </tr>
                                        {{-- Specifications --}}
                                        @foreach ($item->prSpecs as $spec)
                                            <tr class="pr-specification-row">
                                                <td colspan="1"></td>
                                                <td class="px-1 text-muted" style="font-size: 0.85rem;">
                                                    {{ $spec->pr_spec_spec }}
                                                </td>
                                                <td colspan="4"></td>
                                            </tr>
                                        @endforeach
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endforeach

    <div class="d-flex justify-content-end align-items-center mb-3 mt-3">
        <h5 class="fw-bold ps-2 pe-5">Total Amount</h5>
        <h5 class="ps-2 pe-2" id="grand-total-amount">₱ {{ number_format($pr->prItems->sum('pr_items_total_cost'), 2) }}
        </h5>
    </div>
@endsection

@push('js')
    <!-- CUSTOM js -->
    <script src="{{ asset('js/head/pr-review/custom-pr-review.js') }}"></script>
@endpush
