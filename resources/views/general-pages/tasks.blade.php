{{-- Extend the main layout that you want to use --}}
@extends('main-layout')

{{-- Define contents to show in the layout --}}
@section('title', 'Tasks | I-TRAC')

@push('css')
    <!-- Page SPECIFIC css -->
    <link rel="stylesheet" href="{{ asset('css/general-pages/tasks/page-specific/datatables.css') }}">
    <link rel="stylesheet" href="{{ asset('css/general-pages/tasks/page-specific/dt-global_style.css') }}">
    <link rel="stylesheet" href="{{ asset('css/general-pages/tasks/page-specific/animate.css') }}">
    <link rel="stylesheet" href="{{ asset('css/general-pages/tasks/page-specific/modal.css') }}">

    <!-- CUSTOM css -->
    <link rel="stylesheet" href="{{ asset('css/general-pages/tasks/custom-tasks.css') }}">
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
                    <button type="button" class="btn btn-red px-4">Create Purchase Request</button>
                </div>

            </div>
        </div>
    </div>

    <!-- Tasks Table -->
    <div class="widget-content widget-content-area br-8">
        <table id="zero-config" class="table table-striped dt-table-hover" style="width:100%">
            <thead>
                <tr>
                    <th class="fw-bold">From</th>
                    <th class="fw-bold">Document</th>
                    <th class="fw-bold">Date Received</th>
                </tr>
            </thead>
            <tbody>
                @forelse($tasks as $task)
                    <tr class="task-row" style="cursor: pointer;"
                        data-fullname="{{ $task->assignedBy->user_fullname ?? 'N/A' }}"
                        data-email="{{ $task->assignedBy->user_email ?? '' }}"
                        data-date="{{ \Carbon\Carbon::parse($task->created_at)->format('m/d/Y') }}"
                        data-time="{{ \Carbon\Carbon::parse($task->created_at)->format('g:i A') }}"
                        data-description="{{ $task->task_description ?? '' }}">
                        <td>{{ $task->assignedBy->user_fullname ?? 'N/A' }}</td>
                        <td>{{ $task->task_type }}</td>
                        <td>{{ \Carbon\Carbon::parse($task->created_at)->format('m/d/Y') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="text-center text-muted">No tasks for you.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection

@push('js')
    <!-- Page SPECIFIC js -->
    <script src="{{ asset('js/general-pages/tasks/page-specific/datatables.js') }}"></script>

    <script>
        $('#zero-config').DataTable({
            "dom": "<'dt--top-section'<'row'<'col-12 col-sm-6 d-flex justify-content-sm-start justify-content-center'l><'col-12 col-sm-6 d-flex justify-content-sm-end justify-content-center mt-sm-0 mt-3'>>>" +
                "<'table-responsive'tr>" +
                "<'dt--bottom-section d-sm-flex justify-content-sm-between text-center'<'dt--pages-count  mb-sm-0 mb-3'i><'dt--pagination'p>>",
            "oLanguage": {
                "oPaginate": {
                    "sPrevious": '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-arrow-left"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>',
                    "sNext": '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-arrow-right"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>'
                },
                "sInfo": "Showing page _PAGE_ of _PAGES_",
                "sLengthMenu": "Filter :  _MENU_",
            },
            "stripeClasses": [],
            "lengthMenu": [5, 10, 20, 50],
            "pageLength": 5,
        });
    </script>

    <!-- CUSTOM js -->
    <script src="{{ asset('js/general-pages/tasks/custom-tasks.js') }}"></script>
@endpush
