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
        $roleName = $activeRole?->role_name ?? '';

        // Check if the viewer is a Head in ANY of their roles (not just the active one)
        $isHead = $user->roles->contains('gen_role', 'Head');

        $rowIndex = 0;
        $taskStatus = $task->task_status;

        // Is this an assigned task (assigner ≠ assignee)?
        $isAssigned = ($task->assigned_by !== $task->assigned_to);

        // Is the current viewer the one who assigned this task?
        $isViewer_Assigner = ($task->assigned_by === $user->user_id);

        // Head can edit if: viewer is the assigner, task is assigned (not self-created), and status is Complete
        $canHeadEdit = $isViewer_Assigner && $isAssigned && ($taskStatus === 'Complete') && (!$pr || $pr->pr_status === 'Complete');

        // Self-created: the Head assigned the task to themselves
        $isSelfCreatedHead = $isViewer_Assigner && !$isAssigned;

        // For self-created tasks, the Head can export directly from Pending
        $isReadOnly = ($taskStatus !== 'Pending') && !$canHeadEdit && !($isSelfCreatedHead && $taskStatus === 'Pending');

        // If the PR status is Draft, it is not read-only
        if ($pr && $pr->pr_status === 'Draft') {
            $isReadOnly = false;
        }

        // Only Head, Program Chair, Dean, Supply, and Procurement see the stepper
        $isAuthorized = in_array($userRole, ['Head', 'Supply', 'Procurement']);

        // Stepper variables setup
        $firstPo = null;
        $prReceivedDate = null;
        $poCreatedDate = null;
        $isDelivered = false;
        $deliveryDate = null;
        $isReceivedByEndUser = false;
        $receivedDate = null;
        $daSubsteps = [];
        $isAnyScanned = false;

        // PO Coverage: 'none' | 'partial' | 'full'
        $poCoverage = 'none';

        if ($pr) {
            $firstPo = $pr->purchaseOrders->first();

            // Step 2 Date (RECEIVED BY PROCUREMENT)
            if ($pr->retrieved_by) {
                $prReceivedDate = $pr->retrieved_at ? \Carbon\Carbon::parse($pr->retrieved_at)->format('d M, Y') : null;
            }

            // Step 3 Date (first PO created date)
            if ($firstPo) {
                $poCreatedDate = \Carbon\Carbon::parse($firstPo->created_at ?? $firstPo->po_date)->format('d M, Y');
            }

            // ── PO Coverage Calculation ──────────────────────────────────────
            // Total quantity requested across all PR items
            $totalPrQty = $pr->prItems->sum('pr_items_quantity');

            // Total quantity allocated across all PO items linked to this PR
            $totalPoQty = $pr->purchaseOrders->flatMap(fn($po) => $po->poItems)->sum('po_items_quantity');

            if ($pr->purchaseOrders->isNotEmpty()) {
                if ($totalPrQty > 0 && $totalPoQty >= $totalPrQty) {
                    $poCoverage = 'full';
                } else {
                    $poCoverage = 'partial';
                }
            }
            // ────────────────────────────────────────────────────────────────

            // Step 4 (DELIVERED) & Step 5 (RECEIVED BY END USER)
            $isDelivered = $pr->da_exported_at !== null;
            $deliveryDate = $pr->da_exported_at ? \Carbon\Carbon::parse($pr->da_exported_at)->format('d M, Y') : null;
            $isReceivedByEndUser = $pr->scanned_at !== null;
            $receivedDate = $pr->scanned_at ? \Carbon\Carbon::parse($pr->scanned_at)->format('d M, Y') : null;

            $daSubsteps = [];
            foreach ($pr->purchaseOrders as $po) {
                foreach ($po->risSlips as $ris) {
                    $risPoItemIds = $ris->risItems->pluck('ris_po_items_id_fk')->filter();
                    if ($risPoItemIds->isNotEmpty()) {
                        $mrItems = \App\Models\Mr::whereIn('po_item_id_fk', $risPoItemIds)->get();
                        if ($mrItems->isNotEmpty()) {
                            $isAllScanned = $mrItems->every(fn($item) => $item->is_assigned == 1);
                            $hasAnyScanned = $mrItems->contains(fn($item) => $item->is_assigned == 1);
                            
                            $latestScanDate = null;
                            if ($isAllScanned) {
                                $latestScan = $mrItems->whereNotNull('date_scanned')->max('date_scanned');
                                $latestScanDate = $latestScan ? \Carbon\Carbon::parse($latestScan)->format('d M, Y') : null;
                            }

                            $daSubsteps[] = [
                                'prefix'  => 'RIS No. ' . ($ris->ris_no ?? $ris->ris_id),
                                'label'   => $isAllScanned ? 'Completed' : 'Pending',
                                'active'  => (bool) ($isAllScanned || $hasAnyScanned),
                                'partial' => (bool) (!$isAllScanned && $hasAnyScanned),
                                'date'    => $latestScanDate,
                            ];
                        }
                    }
                }

                foreach ($po->parReceipts as $par) {
                    $parPoItemIds = $par->parItems->pluck('par_po_items_id_fk')->filter();
                    if ($parPoItemIds->isNotEmpty()) {
                        $mrItems = \App\Models\Mr::whereIn('po_item_id_fk', $parPoItemIds)->get();
                        if ($mrItems->isNotEmpty()) {
                            $isAllScanned = $mrItems->every(fn($item) => $item->is_assigned == 1);
                            $hasAnyScanned = $mrItems->contains(fn($item) => $item->is_assigned == 1);
                            
                            $latestScanDate = null;
                            if ($isAllScanned) {
                                $latestScan = $mrItems->whereNotNull('date_scanned')->max('date_scanned');
                                $latestScanDate = $latestScan ? \Carbon\Carbon::parse($latestScan)->format('d M, Y') : null;
                            }

                            $daSubsteps[] = [
                                'prefix'  => 'PAR No. ' . ($par->par_property_no ?? $par->par_id),
                                'label'   => $isAllScanned ? 'Completed' : 'Pending',
                                'active'  => (bool) ($isAllScanned || $hasAnyScanned),
                                'partial' => (bool) (!$isAllScanned && $hasAnyScanned),
                                'date'    => $latestScanDate,
                            ];
                        }
                    }
                }
            }

            $isAnyScanned = collect($daSubsteps)->contains('active', true);
        }

        $steps = [
            [
                'prefix' => 'Purchase Request:',
                'label' => 'CREATED',
                'active' => ($pr && ($pr->pr_status === 'Exported' || ($isDirectCreation && $pr->pr_status === 'Complete'))),
                'date' => ($pr && ($pr->submitted_at ?? $pr->updated_at ?? $pr->created_at)) ? \Carbon\Carbon::parse($pr->submitted_at ?? $pr->updated_at ?? $pr->created_at)->format('d M, Y') : null,
            ],
            [
                'prefix' => 'Purchase Request:',
                'label' => 'RECEIVED BY PROCUREMENT OFFICE',
                'active' => ($pr && $pr->retrieved_by) ? true : false,
                'date' => $prReceivedDate,
            ],
            [
                'prefix' => 'Purchase Order:',
                'label' => ($pr && $pr->is_po_done == 0 && $pr->purchaseOrders->isNotEmpty()) ? 'PARTIALLY CREATED' : 'CREATED',
                'active' => ($pr && ($pr->is_po_done == 1 || $pr->purchaseOrders->isNotEmpty())) ? true : false,
                'partial' => ($pr && $pr->is_po_done == 0 && $pr->purchaseOrders->isNotEmpty()) ? true : false,
                'date' => ($pr && $pr->po_done_at) ? \Carbon\Carbon::parse($pr->po_done_at)->format('d M, Y') : null,
                'sub_steps' => $pr ? $pr->purchaseOrders->map(fn($po) => [
                    'prefix'  => $po->po_title,
                    'label'   => $po->po_status === 'Done' ? 'Completed' : 'Draft',
                    'active'  => true,
                    'partial' => $po->po_status !== 'Done',
                    'date'    => $po->po_status === 'Done' && $po->updated_at ? \Carbon\Carbon::parse($po->updated_at)->format('d M, Y') : null,
                ])->toArray() : [],
            ],
            [
                'prefix' => 'Purchase Order:',
                'label' => ($pr && $pr->da_exported_at === null && $pr->purchaseOrders->contains(fn($po) => $po->hasAnyDaExported())) ? 'PARTIALLY DELIVERED' : 'DELIVERED',
                'active' => ($pr && ($pr->da_exported_at !== null || $pr->purchaseOrders->contains(fn($po) => $po->hasAnyDaExported()))) ? true : false,
                'partial' => ($pr && $pr->da_exported_at === null && $pr->purchaseOrders->contains(fn($po) => $po->hasAnyDaExported())) ? true : false,
                'date' => $deliveryDate,
                'sub_steps' => $pr ? $pr->purchaseOrders->map(fn($po) => [
                    'prefix'  => $po->po_title,
                    'label'   => $po->is_da_exported == 1 ? 'Completed' : 'Pending',
                    'active'  => ($po->is_da_exported == 1 || $po->hasAnyDaExported()) ? true : false,
                    'partial' => ($po->is_da_exported == 0 && $po->hasAnyDaExported()) ? true : false,
                    'date'    => $po->is_da_exported == 1 && $po->updated_at ? \Carbon\Carbon::parse($po->updated_at)->format('d M, Y') : null,
                ])->toArray() : [],
            ],
            [
                'prefix' => 'Purchase Order:',
                'label' => ($pr && $pr->scanned_at === null && $isAnyScanned) ? 'PARTIALLY RECEIVED BY END USER' : 'RECEIVED ITEM BY END USER',
                'active' => ($pr && ($pr->scanned_at !== null || $isAnyScanned)) ? true : false,
                'partial' => ($pr && $pr->scanned_at === null && $isAnyScanned) ? true : false,
                'date' => $receivedDate,
                'sub_steps' => $daSubsteps,
            ],
        ];

        $latestActiveIndex = -1;
        foreach ($steps as $index => $step) {
            if ($step['active']) {
                $latestActiveIndex = $index;
            }
        }
    @endphp

    @if ($isAuthorized)
        <div class="row w-100 mx-0 px-0">
            <!-- Left Column: Stepper -->
            <div class="col-xl-3 col-lg-4 col-md-5 col-12 mb-4 px-1">
                <div class="card stepper-card">
                    <div class="card-body">
                        <h5 class="stepper-title text-center text-md-start">Purchase Request Status</h5>
                        <ul class="stepper-container"
                            data-stepper-url="{{ route('pr.stepper.status', $task->task_id) }}"
                            id="stepper-list">
                            @foreach ($steps as $index => $step)
                                @php
                                    $isLatest  = ($index === $latestActiveIndex);
                                    $isActive  = $step['active'];
                                    $isPartial = $step['partial'] ?? false;

                                    $itemClass   = '';
                                    $circleClass = '';
                                    if ($isLatest && $isPartial) {
                                        $itemClass   = 'latest partial';
                                        $circleClass = 'active-partial';
                                    } elseif ($isLatest) {
                                        $itemClass   = 'latest';
                                        $circleClass = 'active-latest';
                                    } elseif ($isActive) {
                                        $itemClass   = 'completed';
                                        $circleClass = 'active-historic';
                                    } else {
                                        $itemClass   = 'pending';
                                        $circleClass = 'pending';
                                    }
                                @endphp
                                <li class="stepper-item {{ $itemClass }}" data-index="{{ $index }}">
                                    @if ($index < count($steps) - 1)
                                        <div class="stepper-line"></div>
                                    @elseif (!empty($step['sub_steps']))
                                        <div class="stepper-line stepper-line-short"></div>
                                    @endif
                                    <div class="stepper-circle {{ $circleClass }}">
                                        @if ($isPartial && $isLatest)
                                            {{-- Half-filled icon for "Partially Created" --}}
                                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" class="text-white">
                                                <circle cx="12" cy="12" r="9"></circle>
                                                <line x1="12" y1="3" x2="12" y2="21"></line>
                                            </svg>
                                        @elseif ($isActive)
                                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" class="text-white">
                                                <polyline points="20 6 9 17 4 12"></polyline>
                                            </svg>
                                        @endif
                                    </div>
                                    <div class="stepper-content">
                                        <span class="stepper-prefix">{{ $step['prefix'] }}</span>
                                        <span class="stepper-label">{{ $step['label'] }}</span>
                                        @if ($step['date'])
                                            <span class="stepper-date">{{ $step['date'] }}</span>
                                        @else
                                            <span class="stepper-date">Pending</span>
                                        @endif

                                        @if (!empty($step['sub_steps']))
                                            <div class="stepper-sub-container mt-2">
                                                @foreach ($step['sub_steps'] as $sub)
                                                    @php
                                                        $subCircleClass = 'pending';
                                                        $subStatusClass = 'status-pending';
                                                        if ($sub['active']) {
                                                            if ($sub['partial'] ?? false) {
                                                                $subCircleClass = 'active-partial';
                                                                $subStatusClass = 'status-partial';
                                                            } elseif ($isLatest) {
                                                                $subCircleClass = 'active-latest';
                                                                $subStatusClass = 'status-active';
                                                            } else {
                                                                $subCircleClass = 'active-historic';
                                                                $subStatusClass = 'status-active';
                                                            }
                                                        }
                                                    @endphp
                                                    <div class="stepper-sub-item">
                                                        <div class="stepper-sub-line"></div>
                                                        <div class="stepper-sub-circle {{ $subCircleClass }}">
                                                            @if ($sub['active'] && !($sub['partial'] ?? false))
                                                                <svg width="8" height="8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" class="text-white">
                                                                    <polyline points="20 6 9 17 4 12"></polyline>
                                                                </svg>
                                                            @elseif ($sub['active'] && ($sub['partial'] ?? false))
                                                                <svg width="8" height="8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" class="text-white">
                                                                    <circle cx="12" cy="12" r="9"></circle>
                                                                    <line x1="12" y1="3" x2="12" y2="21"></line>
                                                                </svg>
                                                            @endif
                                                        </div>
                                                        <span class="stepper-sub-title d-block fw-semibold" style="font-size: 0.8rem; margin-left: 0.25rem;">
                                                            {{ $sub['prefix'] }}
                                                        </span>
                                                        <span class="stepper-sub-status d-block text-uppercase {{ $subStatusClass }}" style="font-size: 0.7rem; font-weight: 600; margin-left: 0.25rem;">
                                                            {{ $sub['label'] }}
                                                        </span>
                                                        @if ($sub['date'])
                                                            <span class="stepper-sub-date d-block" style="font-size: 0.7rem; margin-left: 0.25rem;">{{ $sub['date'] }}</span>
                                                        @endif
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
            <!-- Right Column: PR Form -->
            <div class="col-xl-9 col-lg-8 col-md-7 col-12 px-1">
    @endif

    @if ($taskStatus === 'Complete' && $pr && $pr->pr_status === 'Complete' && $pr->submitted_at && !$isViewer_Assigner)
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
                                <img src="{{ asset('img/Export.svg') }}" width="18" height="18">
                                <span>Export as PDF</span>
                            </button>
                        </div>
                    @elseif ($isSelfCreatedHead && ($taskStatus === 'Pending' || ($pr && $pr->pr_status === 'Draft')))
                        {{-- Head self-created: skip Complete, export directly --}}
                        <button type="button" id="export-pr-btn" data-url="{{ route('export.pr.from_form', $task->task_id) }}"
                            class="btn border border-light-subtle btn-dark-red d-inline-flex align-items-center gap-1 px-3">
                            <img src="{{ asset('img/Export.svg') }}" width="18" height="18">
                            <span>Export as PDF</span>
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
                            <img src="{{ asset('img/Check.svg') }}" width="18" height="18">
                            <span>Complete</span>
                        </button>

                        <button type="submit"
                            class="btn border border-light-subtle btn-white d-inline-flex align-items-center gap-1 px-2">
                            <img src="{{ asset('img/Save.svg') }}" width="18" height="18">
                            <span class="fw-bold">Save as Draft</span>
                        </button>
                    @elseif ($taskStatus === 'Complete' && !$isSelfCreatedHead && ($pr && $pr->pr_status === 'Complete'))
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
                    @elseif (($taskStatus === 'Exported' && $pr && $pr->pr_status === 'Exported') || ($isSelfCreatedHead && $taskStatus === 'Complete' && $pr && $pr->pr_status === 'Complete'))
                        {{-- PR has been exported — fully locked for everyone, with re-download option for Head only --}}
                        <div class="d-flex align-items-center gap-2 justify-content-end">
                            <div class="badge bg-dark p-2 px-3">
                                <h6 class="mb-0 text-white">
                                    <i class="fas fa-file-export me-1"></i> Exported
                                </h6>
                            </div>
                            @if ($isHead)
                                <a href="{{ route('export.pr.download', $task->task_id) }}" id="export-again-btn"
                                   class="btn border border-light-subtle btn-dark-red d-inline-flex align-items-center gap-1 px-3">
                                    <img src="{{ asset('img/Export.svg') }}" width="18" height="18">
                                    <span>Export as PDF Again</span>
                                </a>
                            @endif
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
                                value="{{ $pr?->pr_no ?? old('pr_no') }}" {{ $isReadOnly ? 'disabled' : '' }} placeholder="Optional">
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
                        <button type="button" id="pr-create-collapse" class="collapse-toggle bg-transparent text-black btn p-0 border-0"
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
                                                        class="btn border-0 bg-transparent text-black fw-bold remove-row-btn p-0 ms-2">
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
                                                            name="items[{{ $rowIndex }}][specification]" data-field="specification" rows="2" placeholder="e.g., 16 GB RAM, AMD Ryzen 5, NVIDEA RTX 2050"
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

    @if ($isAuthorized)
            </div>
        </div>
    @endif

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
