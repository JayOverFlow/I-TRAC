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
    <input type="hidden" name="_intent" id="pr-intent" value="draft">
    @php
        $user = auth()->user();
        $activeRoleId = session('active_role_id');
        $activeRole = $user->roles->where('role_id', $activeRoleId)->first() ?? $user->roles->first();
        $userRole = $activeRole?->gen_role;

        // Check if the viewer is a Head in ANY of their roles (not just the active one)
        $isHead = $user->roles->contains('gen_role', 'Head');

        $rowIndex = 0;
        $taskStatus = $task->task_status;

        // Is this an assigned task (assigner ≠ assignee)?
        $isAssigned = ($task->assigned_by !== $task->assigned_to);

        // Is the current viewer the one who assigned this task?
        $isViewer_Assigner = ($task->assigned_by === $user->user_id);

        // Head can edit if: viewer is the assigner, task is assigned (not self-created), and status is Complete
        $canHeadEdit = $isViewer_Assigner && $isAssigned && ($taskStatus === 'Complete');

        // Self-created: the Head assigned the task to themselves
        $isSelfCreatedHead = $isViewer_Assigner && !$isAssigned;

        // For self-created tasks, the Head can export directly from Pending
        $isReadOnly = ($taskStatus !== 'Pending') && !$canHeadEdit && !($isSelfCreatedHead && $taskStatus === 'Pending');
    @endphp

    @if ($taskStatus === 'Complete' && $pr && $pr->submitted_at && !$isViewer_Assigner)
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
            </div>
            <div>
                @php
                    // Calculate allocated budget as the sum of assigned APP items' budgets
                    $allocated_budget = $task->appItems->sum('app_items_esti_budget') ?? 0;
                @endphp
                <h5 class="card-title mb-3 black-text" id="allocated-budget-title" data-budget="{{ $allocated_budget }}">ALLOCATED BUDGET: PHP {{ number_format($allocated_budget, 2) }}</h5>

                <div class="text-end">
                    @if ($canHeadEdit)
                        {{-- Head is viewing completed assigned task: editable, shows "Marked as Complete" and "Export" --}}
                        <div class="d-flex align-items-center gap-2 justify-content-end">
                            <div class="badge bg-success p-2 px-3">
                                <h6 class="mb-0 text-white">
                                    <i class="fas fa-check-circle me-1"></i> Marked as Complete
                                </h6>
                            </div>
                            <button type="button" id="export-pr-btn" data-url="{{ route('export.pr.from_form', $task->task_id) }}"
                                class="btn border border-light-subtle btn-dark-red d-inline-flex align-items-center gap-1 px-3">
                                <img src="{{ asset('img/Submit.svg') }}" width="18" height="18">
                                <span>Export</span>
                            </button>
                        </div>
                    @elseif ($isSelfCreatedHead && $taskStatus === 'Pending')
                        {{-- Head self-created: skip Complete, export directly --}}
                        <button type="button" id="export-pr-btn" data-url="{{ route('export.pr.from_form', $task->task_id) }}"
                            class="btn border border-light-subtle btn-dark-red d-inline-flex align-items-center gap-1 px-3">
                            <img src="{{ asset('img/Submit.svg') }}" width="18" height="18">
                            <span>Export</span>
                        </button>

                        <button type="submit"
                            class="btn border border-light-subtle btn-white d-inline-flex align-items-center gap-1 px-2">
                            <img src="{{ asset('img/Save.svg') }}" width="18" height="18">
                            <span class="fw-bold">Save as Draft</span>
                        </button>
                    @elseif (!$isReadOnly)
                        {{-- Subordinate or non-self-created: show Complete + Save as Draft --}}
                        <button type="button" id="submit-pr-btn" data-url="{{ route('submit.pr', $task->task_id) }}"
                            class="btn border border-light-subtle btn-dark-red d-inline-flex align-items-center gap-1 px-3">
                            <img src="{{ asset('img/Submit.svg') }}" width="18" height="18">
                            <span>Complete</span>
                        </button>

                        <button type="submit"
                            class="btn border border-light-subtle btn-white d-inline-flex align-items-center gap-1 px-2">
                            <img src="{{ asset('img/Save.svg') }}" width="18" height="18">
                            <span class="fw-bold">Save as Draft</span>
                        </button>
                    @elseif ($taskStatus === 'Complete' && !$isSelfCreatedHead)
                        {{-- Subordinate completed; Head has not yet exported --}}
                        @php
                            $deadline =
                                $pr && $pr->submitted_at
                                    ? \Carbon\Carbon::parse($pr->submitted_at)->addDays(3)
                                    : null;
                            $canCancel = $deadline && now()->lessThan($deadline);
                        @endphp
                        <div class="d-flex align-items-center gap-2 justify-content-end">
                            <div class="badge bg-success p-2 px-3">
                                <h6 class="mb-0 text-white">
                                    <i class="fas fa-check-circle me-1"></i> Marked as Complete
                                </h6>
                            </div>
                            <button type="button" id="cancel-pr-btn"
                                data-url="{{ route('cancel.pr', $task->task_id) }}"
                                class="btn border border-light-subtle btn-white d-inline-flex align-items-center gap-1 px-3"
                                {{ !$canCancel ? 'disabled' : '' }}>
                                <img src="{{ asset('img/Cancel.svg') }}" width="18" height="18">
                                <span class="fw-bold black-text"> Cancel</span>
                            </button>
                        </div>
                    @elseif ($taskStatus === 'Exported' || ($isSelfCreatedHead && $taskStatus === 'Complete'))
                        {{-- PR has been exported — fully locked for everyone, with re-download option --}}
                        <div class="d-flex align-items-center gap-2 justify-content-end">
                            <div class="badge bg-dark p-2 px-3">
                                <h6 class="mb-0 text-white">
                                    <i class="fas fa-file-export me-1"></i> Exported
                                </h6>
                            </div>
                            <a href="{{ route('export.pr.download', $task->task_id) }}"
                               class="btn border border-light-subtle btn-dark-red d-inline-flex align-items-center gap-1 px-3">
                                <img src="{{ asset('img/Submit.svg') }}" width="18" height="18">
                                <span>Export Again</span>
                            </a>
                        </div>
                    @else
                        {{-- Fallback for any other read-only status --}}
                        <div class="d-flex align-items-center gap-2 justify-content-end">
                            <div class="badge bg-info p-2 px-3">
                                <h6 class="mb-0 text-white">
                                    <i class="fas fa-info-circle me-1"></i> {{ $taskStatus }}
                                </h6>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>



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
                            <input type="text" name="pr_section" data-field="pr_section"
                                class="form-control form-control-sm w-100"
                                value="{{ $pr?->pr_section ?? old('pr_section') }}"
                                {{ $isReadOnly ? 'disabled' : '' }}>
                            <span class="field-error d-none"></span>
                        </div>
                    </div>

                    <div class="row align-items-center">
                        <div class="col-4">
                            <h6 class="mb-0 black-text fw-bold">Purpose:</h6>
                        </div>
                        <div class="col-8">
                            <input type="text" name="pr_purpose" data-field="pr_purpose"
                                class="form-control form-control-sm w-100"
                                value="{{ $pr?->pr_purpose ?? old('pr_purpose') }}"
                                {{ $isReadOnly ? 'disabled' : '' }}>
                            <span class="field-error d-none"></span>
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
                            <input type="text" name="pr_no" data-field="pr_no"
                                class="form-control form-control-sm w-100"
                                value="{{ $pr?->pr_no ?? old('pr_no') }}" {{ $isReadOnly ? 'disabled' : '' }}>
                            <span class="field-error d-none"></span>
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
                                                <input type="text" class="form-control form-control-sm text-center"
                                                    name="items[{{ $rowIndex }}][unit]" data-field="unit"
                                                    value="{{ $saved?->pr_items_unit ?? '' }}"
                                                    {{ $isReadOnly ? 'disabled' : '' }}>
                                                <span class="field-error d-none text-center"></span>
                                            </td>
                                            {{-- Item Description --}}
                                            <td class="px-1">
                                                <div class="input-group input-group-sm">
                                                    <input type="text" class="form-control"
                                                        name="items[{{ $rowIndex }}][description]" data-field="description"
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
                                                <span class="field-error d-none"></span>
                                            </td>
                                            {{-- Qty. --}}
                                            <td class="px-1"><input type="text"
                                                    class="form-control form-control-sm text-center qty-input"
                                                    name="items[{{ $rowIndex }}][quantity]" data-field="quantity"
                                                    value="{{ $saved?->pr_items_quantity ?? '' }}"
                                                    oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                                                    {{ $isReadOnly ? 'disabled' : '' }}>
                                                <span class="field-error d-none text-center"></span>
                                            </td>
                                            {{-- Unit Cost --}}
                                            <td class="px-1"><input type="text"
                                                    class="form-control form-control-sm text-center cost-input"
                                                    name="items[{{ $rowIndex }}][cost]" data-field="cost"
                                                    value="{{ $saved?->pr_items_cost ? number_format($saved?->pr_items_cost, 2, '.', '') : '' }}"
                                                    oninput="this.value = this.value.replace(/[^0-9.]/g, '')"
                                                    {{ $isReadOnly ? 'disabled' : '' }}>
                                                <span class="field-error d-none text-center"></span>
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
                                                    <div class="d-flex justify-content-between align-items-center rounded-top custom-specification-header toggle-specification-action"
                                                        style="cursor: pointer;">
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
                                                    <div class="specification-body rounded-bottom"
                                                        style="{{ !$specText ? 'display: none;' : '' }}">
                                                        <textarea class="form-control form-control-sm border-0 shadow-none px-2"
                                                            name="items[{{ $rowIndex }}][specification]" data-field="specification" rows="2" placeholder="Enter specification details."
                                                            {{ $isReadOnly ? 'disabled' : '' }}>{{ $specText }}</textarea>
                                                        <span class="field-error d-none"></span>
                                                    </div>
                                                </div>
                                            </td>
                                            <td colspan="4"></td>
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
