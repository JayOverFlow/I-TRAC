<meta name="assign-pr-url" content="{{ route('assign.pr') }}">

@push('css')
    <!-- Page SPECIFIC css -->
    <link rel="stylesheet" href="{{ asset('css/head/dashboard/page-specific/datatables.css') }}">
    <link rel="stylesheet" href="{{ asset('css/head/dashboard/page-specific/dt-global_style.css') }}">
    <link rel="stylesheet" href="{{ asset('css/general-pages/tasks/page-specific/animate.css') }}">
    <link rel="stylesheet" href="{{ asset('css/general-pages/tasks/page-specific/modal.css') }}">

    <!-- CUSTOM css -->
    <link rel="stylesheet" href="{{ asset('css/general-pages/tasks/custom-tasks.css') }}">

    <!-- DARK MODE SPECIFIC css -->
    <link rel="stylesheet" href="{{ asset('css/head/dashboard/page-specific/dark/datatables.css') }}">
    <link rel="stylesheet" href="{{ asset('css/head/dashboard/page-specific/dark/dt-global_style.css') }}">
    <link rel="stylesheet" href="{{ asset('css/general-pages/tasks/page-specific/dark/modal.css') }}">
@endpush

@section('content')
    <!-- Task Detail Modal -->
    <div class="modal fade" id="taskDetailModal" tabindex="-1" role="dialog" aria-labelledby="taskDetailModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document" style="max-width: 620px;">
            <div class="modal-content">

                <!-- Header -->
                <div class="modal-header border-bottom-0 pb-0">
                    <h5 class="modal-title fw-bold red-text-2" id="taskDetailModalLabel">Procurement Task</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                        <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="20" height="20"
                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round" class="feather feather-x">
                            <line x1="18" y1="6" x2="6" y2="18"></line>
                            <line x1="6" y1="6" x2="18" y2="18"></line>
                        </svg>
                    </button>
                </div>

                <!-- Sub-header: sender info -->
                <div class="modal-body pt-2 pb-0">
                    <div class="d-flex justify-content-between align-items-start py-2">
                        <div>
                            <p class="mb-0 fw-bold black-text" id="modal-sender-name"></p>
                            <p class="mb-0 text-muted small" id="modal-sender-email"></p>
                        </div>
                        <div class="text-end">
                            <p class="mb-0 small fw-bold black-text" id="modal-date"></p>
                            <p class="mb-0 small fw-bold black-text" id="modal-time"></p>
                        </div>
                    </div>
                    <hr class="my-2">

                    <!-- Task description -->
                    <div class="py-2">
                        <p class="mb-0 black-text" id="modal-description"></p>
                    </div>
                </div>

                <!-- Footer -->
                <div class="modal-footer border-top-0 pt-0 justify-content-center">
                    <a href="#" id="modal-view-pr-btn" class="btn btn-dark-red px-4">View Purchase Request</a>
                    <a href="#" id="modal-create-pr-btn" class="btn btn-red px-4">Create Purchase Request</a>
                </div>

            </div>
        </div>
    </div>

    @php
        $isHead = in_array($userRole ?? '', ['Head', 'Procurement', 'Supply']);
    @endphp

    {{-- ─── PANEL 1: PR List (default view) ─────────────────────────────── --}}
    <div id="pr-list-panel">
        <!-- Purchase Request Table Section -->
        <div class="widget-content widget-content-area br-8 mt-3 p-0 pt-1">
            <table id="zero-config" class="table dt-table-hover" style="width:100%">
                <thead>
                    @if($isHead)
                        <tr>
                            <th class="fw-bold black-text">PR-ID</th>
                            <th class="fw-bold black-text">Purpose</th>
                            <th class="fw-bold black-text text-center">Assigned</th>
                            <th class="fw-bold black-text">Assignee</th>
                            <th class="fw-bold black-text text-center">Status</th>
                            <th class="fw-bold black-text text-center dt-no-sorting" style="width: 8%;">Action</th>
                        </tr>
                    @else
                        <tr>
                            <th class="fw-bold black-text">PR-ID</th>
                            <th class="fw-bold black-text">Purpose</th>
                            <th class="fw-bold black-text text-end">Estimated Budget</th>
                            <th class="fw-bold black-text text-center">Status</th>
                            <th class="fw-bold black-text text-center dt-no-sorting" style="width: 8%;">Action</th>
                        </tr>
                    @endif
                </thead>
                <tbody>
                    @foreach($tasks as $task)
                        @php
                            $isAssigned = !empty($task->assigned_to) && $task->assigned_to !== $task->assigned_by;
                            $status = strtolower($task->task_status ?? 'pending');
                            $displayStatus = in_array($status, ['complete', 'completed', 'exported', 'approved']) ? 'Complete' : 'Pending';
                            $badgeClass = ($displayStatus === 'Complete') ? 'badge-status-completed' : 'badge-status-pending';
                            $prIdCode = $task->purchaseRequest->pr_unique_code ?? ('PR-' . date('Y') . '-01-' . str_pad($task->task_id, 3, '0', STR_PAD_LEFT));
                            $purposeText = $task->purchaseRequest?->pr_purpose ?: 'N/A';
                            $assigneeName = $task->assignedTo->user_fullname ?? '--';
                            $prLink = $task->pr_id_fk ? route('show.create.pr', ['task_id' => $task->task_id]) : null;
                        @endphp
                        <tr class="task-row" style="cursor: pointer;" data-task-id="{{ $task->task_id }}"
                            data-pr-link="{{ $prLink ?? '' }}"
                            data-fullname="{{ $task->assignedBy->user_fullname ?? 'N/A' }}"
                            data-email="{{ $task->assignedBy->user_email ?? '' }}"
                            data-date="{{ \Carbon\Carbon::parse($task->created_at)->format('m/d/Y') }}"
                            data-time="{{ \Carbon\Carbon::parse($task->created_at)->format('g:i A') }}"
                            data-description="{{ $task->task_description ?? '' }}"
                            data-task-type="{{ $task->task_type }}"
                            data-task-status="{{ $task->task_status }}">
                            <td>{{ $prIdCode }}</td>
                            <td>{{ $purposeText }}</td>
                            @if($isHead)
                                <td class="text-center">
                                    @if($isAssigned)
                                        <span class="fw-bold" style="color: #8B0000;">YES</span>
                                    @else
                                        <span>NO</span>
                                    @endif
                                </td>
                                <td>{{ $isAssigned ? $assigneeName : '--' }}</td>
                            @else
                                <td class="text-end">₱ {{ number_format($task->estimated_budget ?? 0, 2) }}</td>
                            @endif
                            <td class="text-center">
                                <span class="{{ $badgeClass }}">{{ $displayStatus }}</span>
                            </td>
                            <td class="text-center" onclick="event.stopPropagation();">
                                <button class="btn bg-transparent p-0 border-0 shadow-none btn-delete-task-single" data-task-id="{{ $task->task_id }}">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-trash-2 text-danger"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- ─── PANEL 2: APP Projects Checklist (shown on Create click) ──────── --}}
    <div id="app-checklist-panel" style="display: none;">

        {{-- Breadcrumb --}}
        <div class="mb-3 px-2 py-1" id="app-checklist-breadcrumb-bar">
            <div class="archive-breadcrumb">
                <a href="javascript:void(0);" id="btn-back-to-pr" class="text-decoration-none archive-back-link">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                        class="feather feather-arrow-left me-1">
                        <line x1="19" y1="12" x2="5" y2="12"></line>
                        <polyline points="12 19 5 12 12 5"></polyline>
                    </svg>
                    Purchase Request
                </a>
                <span class="breadcrumb-separator">&gt;</span>
                <span class="breadcrumb-current fw-bold red-text-2">Select Projects</span>
            </div>
        </div>

        {{-- APP Items Table Card --}}
        <div class="card px-0">
            <div class="card-body px-0">
                <h5 class="card-title red-text-2 fw-bold px-2 ms-4">ANNUAL PROCUREMENT PLAN</h5>
                <table id="app-items-config" class="table dt-table-hover border-top-0" style="width:100%; border-top: none !important;">
                    <thead>
                        <tr>
                            <th></th>
                            <th class="fw-bold">Project Title</th>
                            <th class="fw-bold">General Description</th>
                            <th class="fw-bold">Mode of Procurement</th>
                            <th class="fw-bold">Start of Procurement</th>
                            <th class="fw-bold">End of Procurement</th>
                            <th class="fw-bold">Estimated Budget</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($activeAppItems ?? [] as $appItem)
                            @php
                                $isUsed = in_array($appItem->app_item_id, $usedAppItemIds->toArray() ?? []);
                            @endphp
                            <tr class="{{ $isUsed ? 'opacity-50' : '' }}">
                                <td>
                                    <div class="form-check form-check-danger form-check-inline">
                                        <input class="form-check-input app-item-checkbox" type="checkbox"
                                            id="app-item-{{ $appItem->app_item_id }}"
                                            data-item-id="{{ $appItem->app_item_id }}"
                                            value="{{ $appItem->app_item_id }}"
                                            {{ $isUsed ? 'disabled' : '' }}>
                                    </div>
                                </td>
                                <td>
                                    {{ $appItem->app_item_proj_title ?: '---' }}
                                    @if($isUsed)
                                        <span class="badge ms-1" style="background-color: #6c757d; font-size: 10px;">Item Assigned</span>
                                    @endif
                                </td>
                                <td>{{ $appItem->app_items_gen_desc ?: '---' }}</td>
                                <td>{{ $appItem->app_items_mode ?: '---' }}</td>
                                <td>{{ $appItem->app_items_start ? \Carbon\Carbon::parse($appItem->app_items_start)->format('m-d-Y') : '---' }}</td>
                                <td>{{ $appItem->app_items_end ? \Carbon\Carbon::parse($appItem->app_items_end)->format('m-d-Y') : '---' }}</td>
                                <td>{{ $appItem->app_items_esti_budget ? number_format($appItem->app_items_esti_budget, 2) : '---' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center">No items available. Please set an active APP first.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                {{-- Action Buttons --}}
                <div id="app-action-buttons" class="d-flex justify-content-start ms-3 mt-3 mt-sm-0">
                    <button class="btn btn-red btn-nxt" id="btn-create-from-checklist" disabled>Create</button>
                </div>

                {{-- Total Amount --}}
                <h5 class="text-end fw-bold black-text me-3 mt-2">
                    Total Amount: <span class="fw-normal">₱ {{ number_format(collect($activeAppItems ?? [])->sum('app_items_esti_budget'), 2) }}</span>
                </h5>
            </div>
        </div>
    </div>{{-- End #app-checklist-panel --}}

    @if ($isHead)
        <!-- Assign Purchase Request Modal -->
        <div class="modal fade" id="exampleModalCenter" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle"
            aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document" style="max-width: 600px;">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold red-text-2" id="exampleModalCenterTitle">Assign Purchase Request</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" class="feather feather-x">
                                <line x1="18" y1="6" x2="6" y2="18"></line>
                                <line x1="6" y1="6" x2="18" y2="18"></line>
                            </svg>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group mb-3">
                            <input type="text" class="form-control" id="user-search-input"
                                placeholder="Search by Name/TUPT-ID">
                        </div>
                        <div class="table-responsive" style="height: 250px; overflow-y: auto;">
                            <table class="table table-hover user-list-table mb-0">
                                <thead>
                                    <tr>
                                        <th scope="col" class="fw-bold">Name</th>
                                        <th scope="col" class="text-center fw-bold" style="width: 120px;">TUPT-ID</th>
                                    </tr>
                                </thead>
                                <tbody id="user-list">
                                    @forelse($subordinates ?? [] as $subordinate)
                                        <tr class="user-list-item" style="cursor: pointer;"
                                            data-user-id="{{ $subordinate->user_id }}">
                                            <td class="align-middle user-name">
                                                {{ $subordinate->user_firstname }} {{ $subordinate->user_lastname }}
                                            </td>
                                            <td class="text-center align-middle">
                                                {{ $subordinate->user_tupid }}
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="2" class="text-center text-muted">No users found in your department.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer" style="border-top: none !important;">
                        <button type="button" class="btn btn-red" id="confirm-assign-btn" disabled>Assign</button>
                    </div>
                </div>
            </div>
        </div>
    @endif

@endsection

@push('js')
    <script src="{{ asset('js/head/dashboard/page-specific/datatables.js') }}"></script>
    <script>
        window.isHead = {{ $isHead ? 'true' : 'false' }};
        $(document).ready(function() {
            var isHead = window.isHead;
            var domConfig = isHead 
                ? "<'dt--top-section'<'row'<'col-12 col-sm-6 d-flex justify-content-sm-start align-items-center'<'custom-title'>><'col-12 col-sm-6 d-flex gap-2 justify-content-sm-end justify-content-center mt-sm-0 mt-3'<'custom-buttons'>>>>" +
                    "<'table-responsive'tr>" +
                    "<'dt--bottom-section d-sm-flex justify-content-sm-between text-center'<'dt--pages-count mb-sm-0 mb-3'i><'dt--pagination'p>>"
                : "<'dt--top-section'<'row'<'col-12 d-flex justify-content-start align-items-center'<'custom-title'>>>>" +
                    "<'table-responsive'tr>" +
                    "<'dt--bottom-section d-sm-flex justify-content-sm-between text-center'<'dt--pages-count mb-sm-0 mb-3'i><'dt--pagination'p>>";

            var columnDefsConfig = isHead
                ? [ { "orderable": false, "targets": [5] } ]
                : [ { "orderable": false, "targets": [4] } ];

            var table = $('#zero-config').DataTable({
                "dom": domConfig,
                "oLanguage": {
                    "oPaginate": {
                        "sPrevious": '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-arrow-left"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>',
                        "sNext": '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-arrow-right"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>'
                    },
                    "sInfo": "Showing page _PAGE_ of _PAGES_",
                    "sLengthMenu": "Results :  _MENU_",
                },
                "columnDefs": columnDefsConfig,
                "stripeClasses": [],
                "lengthMenu": [5, 10, 20, 50],
                "pageLength": 10,
                initComplete: function() {
                    if (isHead) {
                        // Left: title + budget + filters
                        $('.custom-title').html(`
                            <div>
                                <h4 class="fw-bold mb-1 red-text-2">Purchase Request</h4>
                                <p class="mb-2 text-muted" style="font-size: 13px;">
                                    Allocated Budget: <span class="fw-bold" style="color: #515365;">&#8369; {{ number_format($departmentBudget ?? 0, 2) }}</span>
                                </p>
                                <div class="d-flex gap-2 flex-wrap">
                                    <div class="search-input-container" style="position: relative;">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#888ea8" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="position:absolute;left:10px;top:50%;transform:translateY(-50%);pointer-events:none;"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                                        <input type="text" id="tasks-search-input" class="form-control" placeholder="Search by PR ID, Assignee..." style="padding-left: 34px; font-size: 13px; width: 240px; border: 1px solid #e0e6ed; border-radius: 6px;">
                                    </div>
                                    <select id="tasks-status-filter" class="form-select" style="font-size: 13px; width: 140px; border: 1px solid #e0e6ed; border-radius: 6px;">
                                        <option value="">All Status</option>
                                        <option value="Pending">Pending</option>
                                        <option value="Complete">Complete</option>
                                    </select>
                                </div>
                            </div>
                        `);

                        // Right: action buttons (Assign, Create)
                        $('.custom-buttons').html(`
                            <button id="btn-assign-pr" class="btn btn-dark-red d-flex align-items-center px-3 py-2 border-0" style="border-radius: 8px; font-size: 13px; font-weight: 700; gap: 6px;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="8.5" cy="7" r="4"></circle><line x1="20" y1="8" x2="20" y2="14"></line><line x1="23" y1="11" x2="17" y2="11"></line></svg>
                                Assign
                            </button>
                            <button id="btn-show-app-checklist" class="btn btn-dark-red d-flex align-items-center px-3 py-2 border-0" style="border-radius: 8px; font-size: 13px; font-weight: 700; gap: 6px;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                                Create
                            </button>
                        `);

                        // Wire search input to DataTable
                        $('#tasks-search-input').on('keyup', function() {
                            table.search(this.value).draw();
                        });

                        // Wire status filter to Status column (index 4)
                        $('#tasks-status-filter').on('change', function() {
                            table.column(4).search(this.value).draw();
                        });
                    } else {
                        // Subordinate title only
                        $('.custom-title').html(`
                            <div>
                                <h4 class="fw-bold mb-0 red-text-2">Purchase Request</h4>
                            </div>
                        `);
                    }
                }
            });
        });

        // ── APP Items DataTable (checklist panel) ─────────────────────────
        $('#app-items-config').DataTable({
            "columnDefs": [
                { "targets": [0, 3, 4, 5, 6], "className": "text-center align-middle" },
                { "targets": [1, 2],          "className": "text-start align-middle text-wrap" },
                { "targets": 1, "width": "35%" },
                { "targets": 2, "width": "15%" },
                { "orderable": false, "targets": [0] }
            ],
            "searching":    false,
            "lengthChange": false,
            "info":         false,
            "dom": "<'table-responsive border-top-0'<'row'<'col-12'>>tr>" +
                   "<'dt--bottom-section d-sm-flex justify-content-sm-between align-items-center text-center mt-3'<'#app-actions-container'><'dt--pagination'p>>",
            "initComplete": function() {
                $('#app-action-buttons').appendTo('#app-actions-container');
            },
            "oLanguage": {
                "oPaginate": {
                    "sPrevious": '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-arrow-left"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>',
                    "sNext":     '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-arrow-right"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>'
                }
            },
            "stripeClasses": [],
            "pageLength": 5
        });

        // APP item checkbox → enable Create button
        $(document).on('change', '.app-item-checkbox', function() {
            var anyChecked = $('.app-item-checkbox:checked').length > 0;
            $('#btn-create-from-checklist').prop('disabled', !anyChecked);
        });
    </script>

    <!-- CUSTOM js -->
    <script src="{{ asset('js/general-pages/tasks/custom-tasks.js') }}"></script>
@endpush

