@push('css')
    <!-- Page SPECIFIC css -->
    <link rel="stylesheet" href="{{ asset('css/general-pages/tasks/page-specific/datatables.css') }}">
    <link rel="stylesheet" href="{{ asset('css/general-pages/tasks/page-specific/dt-global_style.css') }}">

    <!-- FilePond CSS -->
    <link href="https://unpkg.com/filepond/dist/filepond.css" rel="stylesheet">

    <!-- CUSTOM css -->
    <link rel="stylesheet" href="{{ asset('css/general-pages/create-pr/page-specific/accordions.css') }}">
    <link rel="stylesheet" href="{{ asset('css/general-pages/create-pr/custom-create-pr.css') }}">
@endpush

<form method="POST" action="{{ route('draft.pr', $task->task_id) }}" id="pr-form">
    @csrf
    @php
        $rowIndex = 0;
        $isReadOnly = !in_array($task->task_status, ['Pending', 'Rejected']);
    @endphp

    @if ($task->task_status === 'Submitted' && $pr && $pr->submitted_at)
        @php
            $deadline = \Carbon\Carbon::parse($pr->submitted_at)->addDays(3);
            $now = now();
            $diff = $now->diff($deadline);
            $isPast = $now->greaterThanOrEqualTo($deadline);
        @endphp
        @if (!$isPast)
            <div class="alert alert-info py-2" role="alert">
                You have
                <strong>{{ $diff->d }}</strong> {{ Str::plural('day', $diff->d) }},
                <strong>{{ $diff->h }}</strong> {{ Str::plural('hour', $diff->h) }} and
                <strong>{{ $diff->i }}</strong> {{ Str::plural('minute', $diff->i) }}
                left to cancel your submission.
            </div>
        @endif
    @endif

    <div class="card allocated-budget-card mb-3">
        <div class="card-body d-flex justify-content-center justify-content-between align-items-center">
            <div class="d-flex flex-column">
                <h5 class="fw-bold red-text-2">PURCHASE REQUEST</h5>
                <div class="d-flex align-items-center mt-1">
                    <img src="{{ asset('img/user-profile.jpeg') }}"
                        class="avatar-img rounded-circle border border-2 border-white">
                    <img src="{{ asset('img/user-profile.jpeg') }}"
                        class="avatar-img rounded-circle border border-2 border-white ms-n2">
                    <div class="avatar-add rounded-circle border bg-white d-flex align-items-center justify-content-center ms-n2"
                        style="width: 35px; height: 35px; color: #ccc;">
                        <span>+</span>
                    </div>
                </div>
            </div>
            <div>
                <h5 class="card-title mb-3 black-text">ALLOCATED BUDGET: PHP 12,345.00</h5>

                <div class="text-end">
                    @if (!$isReadOnly)
                        <button type="button" id="submit-pr-btn" data-url="{{ route('submit.pr', $task->task_id) }}"
                            class="btn border border-light-subtle btn-dark-red d-inline-flex align-items-center gap-1 px-3">
                            <img src="{{ asset('img/Submit.svg') }}" width="18" height="18">
                            <span>Submit</span>
                        </button>

                        <button type="submit"
                            class="btn border border-light-subtle btn-white d-inline-flex align-items-center gap-1 px-2">
                            <img src="{{ asset('img/Save.svg') }}" width="18" height="18">
                            <span class="fw-bold">Save as Draft</span>
                        </button>
                    @else
                        <div class="d-flex align-items-center gap-2 justify-content-end">
                            <div
                                class="badge {{ $task->task_status == 'Submitted' ? 'bg-info' : 'bg-success' }} p-2 px-3">
                                <h6 class="mb-0 text-white">
                                    <i class="fas fa-check-circle me-1"></i> Purchase Request {{ $task->task_status }}
                                </h6>
                            </div>

                            @if ($task->task_status === 'Submitted')
                                @php
                                    $deadline =
                                        $pr && $pr->submitted_at
                                            ? \Carbon\Carbon::parse($pr->submitted_at)->addDays(3)
                                            : null;
                                    $canCancel = $deadline && now()->lessThan($deadline);
                                @endphp
                                    <button type="button" id="cancel-pr-btn"
                                        data-url="{{ route('cancel.pr', $task->task_id) }}"
                                        class="btn border border-light-subtle btn-white d-inline-flex align-items-center gap-1 px-3"
                                        {{ !$canCancel ? 'disabled' : '' }}>
                                        <img src="{{ asset('img/Cancel.svg') }}" width="18" height="18">
                                        <span class="fw-bold black-text"> Cancel Submission</span>
                                    </button>
                            @endif
                        </div>
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
                            <h6 class="mb-0">{{ auth()->user()->departments->first()?->dep_name ?? 'N/A' }}</h6>
                        </div>
                    </div>

                    <div class="row align-items-center mb-3">
                        <div class="col-4">
                            <h6 class="mb-0 black-text fw-bold">Section:</h6>
                        </div>
                        <div class="col-8">
                            <input type="text" name="pr_section" class="form-control form-control-sm w-100"
                                value="{{ $pr?->pr_section ?? old('pr_section') }}"
                                {{ $isReadOnly ? 'disabled' : '' }}>
                        </div>
                    </div>

                    <div class="row align-items-center">
                        <div class="col-4">
                            <h6 class="mb-0 black-text fw-bold">Purpose:</h6>
                        </div>
                        <div class="col-8">
                            <input type="text" name="pr_purpose" class="form-control form-control-sm w-100"
                                value="{{ $pr?->pr_purpose ?? old('pr_purpose') }}"
                                {{ $isReadOnly ? 'disabled' : '' }}>
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
                                {{ $pr?->pr_date ? \Carbon\Carbon::parse($pr->pr_date)->format('F d, Y') : now()->format('F d, Y') }}
                            </h6>
                        </div>
                    </div>
                    <div class="row align-items-center">
                        <div class="col-4 text-md-end">
                            <h6 class="mb-0 black-text fw-bold">P.R No.:</h6>
                        </div>
                        <div class="col-8">
                            <input type="text" name="pr_no" class="form-control form-control-sm w-100"
                                value="{{ $pr?->pr_no ?? old('pr_no') }}" {{ $isReadOnly ? 'disabled' : '' }}>
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
                        <small class="black-text item-count">{{ $items->count() }} Item/s</small>
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
                                        // Get all saved rows for this APP item, default to one empty row if none saved
                                        $currentSavedItems = $savedItemsGrouped->get($rowKey, [null]);
                                    @endphp

                                    @foreach ($currentSavedItems as $saved)
                                        <tr class="pr-item-row" data-id="{{ $item->app_item_id }}">
                                            {{-- Identity for the row --}}
                                            <input type="hidden" name="items[{{ $rowIndex }}][app_item_id]"
                                                value="{{ $rowKey }}">

                                            {{-- Unit --}}
                                            <td class="px-1">
                                                <select class="form-select form-control-sm"
                                                    name="items[{{ $rowIndex }}][unit]"
                                                    {{ $isReadOnly ? 'disabled' : '' }}>
                                                    <option value="" {{ !$saved ? 'selected' : '' }} disabled>
                                                        Select</option>
                                                    <option value="Piece"
                                                        {{ ($saved?->pr_items_unit ?? '') === 'Piece' ? 'selected' : '' }}>
                                                        Piece</option>
                                                    <option value="Lot"
                                                        {{ ($saved?->pr_items_unit ?? '') === 'Lot' ? 'selected' : '' }}>
                                                        Lot</option>
                                                    <option value="Set"
                                                        {{ ($saved?->pr_items_unit ?? '') === 'Set' ? 'selected' : '' }}>
                                                        Set</option>
                                                    <option value="More options"
                                                        {{ ($saved?->pr_items_unit ?? '') === 'More options' ? 'selected' : '' }}>
                                                        More options</option>
                                                </select>
                                            </td>
                                            {{-- Item Description --}}
                                            <td class="px-1">
                                                <div class="input-group input-group-sm">
                                                    <input type="text" class="form-control"
                                                        name="items[{{ $rowIndex }}][description]"
                                                        value="{{ $saved?->pr_items_descrip ?? '' }}"
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
                                                    value="{{ $saved?->pr_items_quantity ?? '' }}"
                                                    oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                                                    {{ $isReadOnly ? 'disabled' : '' }}>
                                            </td>
                                            {{-- Unit Cost --}}
                                            <td class="px-1"><input type="text"
                                                    class="form-control form-control-sm text-center cost-input"
                                                    name="items[{{ $rowIndex }}][cost]"
                                                    value="{{ $saved?->pr_items_cost ? number_format($saved?->pr_items_cost, 2, '.', '') : '' }}"
                                                    oninput="this.value = this.value.replace(/[^0-9.]/g, '')"
                                                    {{ $isReadOnly ? 'disabled' : '' }}>
                                            </td>
                                            {{-- Amount --}}
                                            @php
                                                $amount = $saved
                                                    ? $saved->pr_items_quantity * $saved->pr_items_cost
                                                    : 0;
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
                                                $savedCat = $saved ? $catMap[$saved->pr_items_category] ?? '' : '';
                                            @endphp
                                            <td class="px-1">
                                                <select class="form-select form-control-sm"
                                                    name="items[{{ $rowIndex }}][category]"
                                                    {{ $isReadOnly ? 'disabled' : '' }}>
                                                    <option value=""
                                                        {{ !$saved || !$saved?->pr_items_category ? 'selected' : '' }}
                                                        disabled>Select</option>
                                                    <option value="Consumable"
                                                        {{ $savedCat === 'Consumable' ? 'selected' : '' }}>Consumable
                                                    </option>
                                                    <option value="Equipment"
                                                        {{ $savedCat === 'Equipment' ? 'selected' : '' }}>Equipment
                                                    </option>
                                                    <option value="Equipment (50k & ↑)"
                                                        {{ $savedCat === 'Equipment (50k & ↑)' ? 'selected' : '' }}>
                                                        Equipment (50k & ↑)</option>
                                                </select>
                                            </td>
                                            <td class="text-start px-0">
                                                @if (!$isReadOnly)
                                                    <button type="button"
                                                        class="btn border-0 bg-transparent text-black fw-bold remove-row-btn p-0 ms-2"
                                                        style="{{ $loop->first && $loop->parent->first && !$saved ? 'visibility: hidden;' : 'visibility: visible;' }}">
                                                        <img src="{{ asset('img/remove.svg') }}" alt="Remove">
                                                    </button>
                                                @endif
                                            </td>
                                        </tr>
                                        {{-- Specification --}}
                                        @php
                                            $specText = $saved?->prSpecs->first()?->pr_spec_spec ?? '';
                                        @endphp
                                        <tr class="pr-specification-row {{ !$specText ? 'd-none' : '' }}">
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
                                                            {{ $isReadOnly ? 'disabled' : '' }}>{{ $specText }}</textarea>
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

<form id="cancel-pr-form" method="POST" style="display: none;">
    @csrf
</form>

@push('js')
    <!-- FilePond JavaScript -->
    <script src="https://unpkg.com/filepond/dist/filepond.js"></script>
    <script src="https://unpkg.com/filepond-plugin-file-validate-size/dist/filepond-plugin-file-validate-size.js"></script>

    <!-- CUSTOM js -->
    <script src="{{ asset('js/general-pages/create-pr/custom-create-pr.js') }}"></script>
@endpush
