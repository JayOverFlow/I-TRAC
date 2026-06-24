{{-- Extend the main layout that you want to use --}}
@extends('layouts.procurement-layout')

{{-- Define contents to show in the layout --}}
@section('title', 'Purchase Request Preview | I-TRAC')

@push('css')
    <!-- CUSTOM css -->
    <link rel="stylesheet" href="{{ asset('css/procurement/pr-preview/custom-pr-preview.css') }}">
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
                    {{ number_format($pr->app ? $pr->app->app_total : 0, 2) }}</h5>

                <div class="text-end d-flex align-items-center justify-content-end gap-3">
                    {{-- switch/toggle --}}
                    <div class="switch form-switch-custom switch-inline form-switch-primary inner-label-toggle mb-0">
                        <div class="input-checkbox">
                            <span class="switch-chk-label label-left">View PR</span>
                            <input class="switch-input" type="checkbox" role="switch" id="form-custom-switch-inner-label">
                            <span class="switch-chk-label label-right">View PO</span>
                        </div>
                    </div>

                    <button type="button" data-bs-toggle="modal" data-bs-target="#createPoModal"
                        class="btn border border-light-subtle btn-dark-red d-inline-flex align-items-center gap-1 px-3"
                        {{ $pr->is_po_done == 1 ? 'disabled' : '' }}>
                        <img src="{{ asset('img/PO.svg') }}" width="18" height="18" class="img-white-icon"
                            style="filter: invert(1) brightness(100);">
                        <span>Create Purchase Order</span>
                    </button>

                    @if ($pr->is_po_done == 0)
                        <form action="{{ route('pr.po.done', $pr->pr_id) }}" method="POST" class="d-inline mb-0">
                            @csrf
                            <button type="submit" class="btn border btn-brand-red-outline d-inline-flex align-items-center gap-1 px-3 fw-bold"
                                {{ $pr->purchaseOrders->isEmpty() ? 'disabled' : '' }}>
                                <span>✓ Done</span>
                            </button>
                        </form>
                    @else
                        <span class="badge bg-success p-2 px-3 text-uppercase d-inline-flex align-items-center fw-bold" style="font-size: 0.85rem; line-height: 1.5;">
                            ✓ Done
                        </span>
                    @endif
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

    {{-- Container for PR Items --}}
    <div id="pr-items-container" class="px-0">

        @forelse ($groupedItems as $projectTitle => $items)
            <div class="card shadow-sm border-0 mb-3 px-0 pr-card">
                <div class="card-body px-0 pb-0">
                    <div class="d-flex justify-content-between ms-4 mb-1">
                        <div class="d-flex align-items-baseline gap-2">
                            <h5 class="red-text fw-bold">Project Title: {{ $projectTitle }}</h5>
                            <small class="black-text item-count">{{ $items->count() }}
                                Item/s</small>
                        </div>
                        <div class="d-flex align-items-baseline gap-2 me-4">
                            @php
                                $projectTotal = $items->sum('pr_items_total_cost');
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
                        <hr class="m-0 p-0">
                        <div class="table-responsive mx-3 mt-3">
                            <table class="table table-sm table-borderless align-middle pr-table">
                                <thead class="bg-transparent">
                                    <tr>
                                        <th class="text-center black-text fw-bold" style="width: 12%">Unit</th>
                                        <th class="black-text fw-bold" style="width: 33%">Item Description</th>
                                        <th class="text-center black-text fw-bold" style="width: 8%">Qty.</th>
                                        <th class="text-center black-text fw-bold" style="width: 14%">Unit Cost</th>
                                        <th class="text-center black-text fw-bold" style="width: 14%">Amount</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($items as $item)
                                        @php
                                            $amount = $item->pr_items_quantity * $item->pr_items_cost;
                                        @endphp
                                        <tr class="pr-item-row">
                                            <td class="px-1 text-center">{{ $item->pr_items_unit }}</td>
                                            <td class="px-1">{{ $item->pr_items_descrip }}</td>
                                            <td class="px-1 text-center">{{ $item->pr_items_quantity }}</td>
                                            <td class="px-1 text-center">₱
                                                {{ number_format($item->pr_items_cost, 2) }}</td>
                                            <td class="px-1 text-center">₱ {{ number_format($amount, 2) }}</td>
                                        </tr>
                                        {{-- Specifications --}}
                                        @foreach ($item->prSpecs as $spec)
                                            <tr class="pr-specification-row">
                                                <td colspan="1"></td>
                                                <td class="px-1 text-muted" style="font-size: 0.85rem;">
                                                    {{ $spec->pr_spec_spec }}
                                                </td>
                                                <td colspan="3"></td>
                                            </tr>
                                        @endforeach
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="card shadow-sm border-0 mb-3">
                <div class="card-body text-center py-5 text-muted">
                    <img src="{{ asset('img/no-data.svg') }}" width="60" class="mb-2 opacity-50" onerror="this.style.display='none'">
                    <p class="mb-0">No items found for this Purchase Request.</p>
                </div>
            </div>
        @endforelse

        <div class="d-flex justify-content-end align-items-center mb-3 mt-3">
            <h5 class="fw-bold ps-2 pe-5">Total Amount</h5>
            <h5 class="ps-2 pe-2" id="grand-total-amount">₱
                {{ number_format($pr->prItems->sum('pr_items_total_cost'), 2) }}
            </h5>
        </div>
    </div>

    {{-- Container for PO Table --}}
    <div id="po-items-container" class="px-0" style="display: none;">
        <div class="card shadow-sm border-0 mb-3">
            <div class="card-body px-0 pt-1">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-transparent">
                            <tr>
                                <th class="ps-4 black-text fw-bold">PO-ID</th>
                                <th class="black-text fw-bold">Title</th>
                                <th class="black-text fw-bold">Date Created</th>
                                <th class="black-text fw-bold">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($pr->purchaseOrders as $po)
                                <tr onclick="window.location='{{ route('show.create.po', $po->po_id) }}'"
                                    style="cursor: pointer;">
                                    <td class="ps-4">
                                        <span class="fw-bold">{{ $po->po_unique_code }}</span>
                                    </td>
                                    <td>{{ $po->po_title }}</td>
                                    <td>{{ $po->created_at ? $po->created_at->format('M d, Y') : 'N/A' }}</td>
                                    <td>
                                        <span
                                            class="badge {{ $po->po_status == 'Draft' ? 'bg-warning' : 'bg-info' }} p-2 px-3">
                                            {{ $po->po_status }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-5 text-muted">
                                        <div class="d-flex flex-column align-items-center">
                                            <img src="{{ asset('img/no-data.svg') }}" width="60"
                                                class="mb-2 opacity-50" onerror="this.style.display='none'">
                                            <span>No Purchase Orders created for this PR yet.</span>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

{{-- Create PO Modal --}}
<div class="modal fade" id="createPoModal" tabindex="-1" aria-labelledby="createPoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow bg-white">
            <div class="modal-header bg-dark-red text-white mb-3">
                <h5 class="modal-title fw-bold red-text-2" id="createPoModalLabel">Create Purchase Order</h5>
            </div>
            <form action="{{ route('create.po', $pr->pr_id) }}" method="POST">
                @csrf
                <div class="modal-body py-0">
                    <div class="mb-3">
                        <label for="po_title" class="form-label fw-bold black-text">Purchase Order Title</label>
                        <input type="text" class="form-control form-control-md" id="po_title" name="po_title"
                            placeholder="Enter a descriptive title" required>
                        <div class="form-text black-text mt-2">Example: PO_Armchairs_Ariado</div>
                    </div>
                </div>
                <div class="modal-footer border-0 py-0">
                    <button type="button" class="btn btn-light fw-bold black-text px-4"
                        data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" id="confirmCreatePoBtn" class="btn btn-dark-red px-4" disabled>Create
                        PO</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('js')
    <!-- Page SPECIFIC js -->
    <script>
        $(document).ready(function() {
            // Handle PR/PO Toggle
            $('#form-custom-switch-inner-label').on('change', function() {
                if ($(this).is(':checked')) {
                    $('#pr-items-container').hide();
                    $('#po-items-container').show();
                    $('.label-left').removeClass('fw-bold');
                    $('.label-right').addClass('fw-bold');
                } else {
                    $('#pr-items-container').show();
                    $('#po-items-container').hide();
                    $('.label-left').addClass('fw-bold');
                    $('.label-right').removeClass('fw-bold');
                }
            });

            // Modal validation
            $('#po_title').on('input', function() {
                const title = $(this).val().trim();
                $('#confirmCreatePoBtn').prop('disabled', title.length === 0);
            });
        });
    </script>
    <!-- CUSTOM js -->
    <script src="{{ asset('js/procurement/pr-preview/custom-pr-preview.js') }}"></script>
@endpush
