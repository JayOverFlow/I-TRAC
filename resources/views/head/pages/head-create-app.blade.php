{{-- Extend the main layout that you want to use --}}
@extends('layouts.head-layout')

{{-- Define contents to show in the layout --}}
@section('title', 'Create Annual Procurement Plan | I-TRAC')

@push('css')
    {{-- Page SPECIFIC css --}}
    <link rel="stylesheet" href="{{ asset('plugins/src/flatpickr/flatpickr.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/css/light/flatpickr/custom-flatpickr.css') }}">

    <!-- CUSTOM css -->
    <link rel="stylesheet" href="{{ asset('css/head/create-app/head-create-app.css') }}">
@endpush

@section('content')
    @php
        $isReadOnly = isset($isReadOnly) ? $isReadOnly : (isset($app_data) && $app_data->app_status === 'Done');
    @endphp
    <form method="POST" action="{{ route('create.app') }}" id="create-app-form">
        @csrf
        <input type="hidden" name="_intent" id="form-intent" value="done">
        @if(isset($app_data))
            <input type="hidden" name="app_id" value="{{ $app_data->app_id }}">
        @endif

        <div class="card allocated-budget-card mb-3">
            <div class="card-body d-flex justify-content-center justify-content-between align-items-center">
                <h5 class="card-title mb-0 fw-bold red-text-2">{{ isset($app_data) && $app_data->app_title ? strtoupper($app_data->app_title) : 'ANNUAL PROCUREMENT PLAN' }}</h5>
                <div>
                    <h5 class="card-title mb-3 black-text">ALLOCATED BUDGET: PHP {{ number_format($app_data?->app_total ?? 0, 2) }}</h5>

                    <div class="text-end d-flex align-items-center gap-2 justify-content-end">
                        @if($isReadOnly)
                            <div class="badge bg-success p-2 px-3" id="completed-badge">
                                <h6 class="mb-0 text-white">
                                    <i class="fas fa-check-circle me-1"></i> Completed
                                </h6>
                            </div>
                            <button type="button" id="btn-edit-app"
                                class="btn border border-light-subtle btn-white d-inline-flex align-items-center justify-content-center px-3" style="min-height: 38px;">
                                <span>Edit</span>
                            </button>
                        @endif

                        <button type="button" id="btn-done"
                            class="btn border border-light-subtle btn-dark-red align-items-center justify-content-center gap-1 px-3 {{ $isReadOnly ? 'd-none' : 'd-inline-flex' }}" style="min-height: 38px;">
                            <img src="{{ asset('img/Check.svg') }}" width="18" height="18">
                            <span>Done</span>
                        </button>

                        <button type="button" id="btn-draft"
                            class="btn border border-light-subtle btn-white align-items-center justify-content-center gap-1 px-2 {{ $isReadOnly ? 'd-none' : 'd-inline-flex' }}" style="min-height: 38px;">
                            <img src="{{ asset('img/Save.svg') }}" width="18" height="18">
                            <span class="fw-bold">Save as Draft</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div id="project-items-container">
            @if(isset($app_data) && $app_data->appItems->count() > 0)
                @foreach($app_data->appItems as $index => $item)
                    @php
                        $isItemAssigned = $item->app_items_assigned_to !== null;
                        $isItemLocked = $isReadOnly || $isItemAssigned;
                    @endphp
                    <div class="card project-item-card mb-3 {{ $isItemAssigned ? 'is-assigned' : '' }}">
                        <div class="card-body">
                            <input type="hidden" name="items[{{ $index }}][app_item_id]" value="{{ $item->app_item_id }}">
                            @if($isItemAssigned)
                                <input type="hidden" class="assigned-hidden-input" name="items[{{ $index }}][proj_title]" value="{{ $item->app_item_proj_title }}">
                                <input type="hidden" class="assigned-hidden-input" name="items[{{ $index }}][end_user]" value="{{ $item->app_items_end_user }}">
                                <input type="hidden" class="assigned-hidden-input" name="items[{{ $index }}][gen_desc]" value="{{ $item->app_items_gen_desc }}">
                                <input type="hidden" class="assigned-hidden-input" name="items[{{ $index }}][mode]" value="{{ $item->app_items_mode }}">
                                <input type="hidden" class="assigned-hidden-input" name="items[{{ $index }}][criteria]" value="{{ $item->app_items_criteria }}">
                                <input type="hidden" class="assigned-hidden-input" name="items[{{ $index }}][covered]" value="{{ $item->app_items_covered }}">
                                <input type="hidden" class="assigned-hidden-input" name="items[{{ $index }}][start]" value="{{ $item->app_items_start }}">
                                <input type="hidden" class="assigned-hidden-input" name="items[{{ $index }}][end]" value="{{ $item->app_items_end }}">
                                <input type="hidden" class="assigned-hidden-input" name="items[{{ $index }}][source]" value="{{ $item->app_items_source }}">
                                <input type="hidden" class="assigned-hidden-input" name="items[{{ $index }}][esti_budget]" value="{{ $item->app_items_esti_budget }}">
                                <input type="hidden" class="assigned-hidden-input" name="items[{{ $index }}][tools]" value="{{ $item->app_items_tools }}">
                                <input type="hidden" class="assigned-hidden-input" name="items[{{ $index }}][remarks]" value="{{ $item->app_items_remarks }}">
                            @endif

                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="card-title fw-bold">
                                    <span class="red-text-2 item-number-span">Item {{ $index + 1 }}</span> | Procurement Project Details
                                    @if($isItemAssigned)
                                        <span class="badge bg-secondary ms-2 text-white" style="font-size: 0.75rem;">Assigned</span>
                                    @endif
                                </h5>

                                <button type="button" class="btn btn-dark-red remove-project-btn {{ ($isItemLocked || $app_data->appItems->count() === 1) ? 'd-none' : '' }}" title="Remove project/item">
                                    <img src="{{ asset('img/Trash.svg') }}" width="20" height="20">
                                </button>
                            </div>

                            <div class="row mb-3">
                                <div class="form-group col-4">
                                    <label>Project Title</label>
                                    <input type="text" class="form-control" name="items[{{ $index }}][proj_title]"
                                        data-field="proj_title" value="{{ $item->app_item_proj_title }}" {{ $isItemLocked ? 'disabled' : '' }}>
                                    <span class="field-error d-none"></span>
                                </div>

                                <div class="form-group col-4">
                                    <label>End-User or Implementing Unit</label>
                                    <input type="text" class="form-control" name="items[{{ $index }}][end_user]"
                                        data-field="end_user" value="{{ $item->app_items_end_user }}" {{ $isItemLocked ? 'disabled' : '' }}>
                                    <span class="field-error d-none"></span>
                                </div>

                                <div class="form-group col-4">
                                    <label>General Description</label>
                                    <input type="text" class="form-control" name="items[{{ $index }}][gen_desc]"
                                        data-field="gen_desc" value="{{ $item->app_items_gen_desc }}" {{ $isItemLocked ? 'disabled' : '' }}>
                                    <span class="field-error d-none"></span>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="form-group col-4">
                                    <label>Mode of Procurement</label>
                                    <input type="text" class="form-control" name="items[{{ $index }}][mode]"
                                        data-field="mode" value="{{ $item->app_items_mode }}" {{ $isItemLocked ? 'disabled' : '' }}>
                                    <span class="field-error d-none"></span>
                                </div>

                                <div class="form-group col-4">
                                    <label>Criteria for Bid Evaluation</label>
                                    <input type="text" class="form-control" name="items[{{ $index }}][criteria]"
                                        data-field="criteria"
                                        placeholder="Including Sustainability and Domestic Preference" value="{{ $item->app_items_criteria }}" {{ $isItemLocked ? 'disabled' : '' }}>
                                    <span class="field-error d-none"></span>
                                </div>

                                <div class="form-group col-4">
                                    <label>To be covered by an Early Procurement Activity</label>
                                    <span class="field-error d-none"></span>
                                    <div class="mt-4 d-flex justify-content-center" data-field="covered">
                                        <input class="form-check-input" type="radio" name="items[{{ $index }}][covered]"
                                            id="covered-yes-{{ $index }}" value="Yes" {{ $item->app_items_covered === 'Yes' ? 'checked' : '' }} {{ $isItemLocked ? 'disabled' : '' }}>
                                        <label class="form-check-label ms-2 me-4" for="covered-yes-{{ $index }}">Yes</label>

                                        <input class="form-check-input" type="radio" name="items[{{ $index }}][covered]"
                                            id="covered-no-{{ $index }}" value="No" {{ $item->app_items_covered === 'No' ? 'checked' : '' }} {{ $isItemLocked ? 'disabled' : '' }}>
                                        <label class="form-check-label ms-2" for="covered-no-{{ $index }}">No</label>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <h5 class="card-title fw-bold col-6">Projected Timeline</h5>
                                <h5 class="card-title fw-bold col-6">Funding Details</h5>
                            </div>

                            <div class="row mb-3">
                                <div class="form-group col-3">
                                    <label>Start of Procurement Activity</label>
                                    <input type="text" class="form-control flatpickr-date" name="items[{{ $index }}][start]"
                                        data-field="start" placeholder="Select Date" value="{{ $item->app_items_start ? \Carbon\Carbon::parse($item->app_items_start)->format('Y-m-d') : '' }}" {{ $isItemLocked ? 'disabled' : '' }}>
                                    <span class="field-error d-none"></span>
                                </div>

                                <div class="form-group col-3">
                                    <label>End of Procurement Activity</label>
                                    <input type="text" class="form-control flatpickr-date" name="items[{{ $index }}][end]"
                                        data-field="end" placeholder="Select Date" value="{{ $item->app_items_end }}" {{ $isItemLocked ? 'disabled' : '' }}>
                                    <span class="field-error d-none"></span>
                                </div>

                                <div class="form-group col-3">
                                    <label>Source of Fund</label>
                                    <input type="text" class="form-control" name="items[{{ $index }}][source]"
                                        data-field="source" value="{{ $item->app_items_source }}" {{ $isItemLocked ? 'disabled' : '' }}>
                                    <span class="field-error d-none"></span>
                                </div>

                                <div class="form-group col-3">
                                    <label>Estimated Budget/Approved Budget</label>
                                    <input type="number" class="form-control estimated-budget-input"
                                        name="items[{{ $index }}][esti_budget]" data-field="esti_budget"
                                        placeholder="In Peso" step="0.01" min="0" value="{{ $item->app_items_esti_budget }}" {{ $isItemLocked ? 'disabled' : '' }}>
                                    <span class="field-error d-none"></span>
                                </div>
                            </div>

                            <div class="row">
                                <div class="form-group col-6">
                                    <label>Procurement Strategy or Tools</label>
                                    <input type="text" class="form-control" name="items[{{ $index }}][tools]"
                                        data-field="tools" value="{{ $item->app_items_tools }}" {{ $isItemLocked ? 'disabled' : '' }}>
                                    <span class="field-error d-none"></span>
                                </div>

                                <div class="form-group col-6">
                                    <label>REMARKS</label>
                                    <input type="text" class="form-control" name="items[{{ $index }}][remarks]"
                                        data-field="remarks"
                                        placeholder="Other relevant descriptions of the procurement project, if applicable" value="{{ $item->app_items_remarks }}" {{ $isItemLocked ? 'disabled' : '' }}>
                                    <span class="field-error d-none"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            @else
                <div class="card project-item-card mb-3">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="card-title fw-bold"><span class="red-text-2 item-number-span">Item 1</span> | Procurement Project Details</h5>

                            <button type="button" class="btn btn-dark-red remove-project-btn d-none" title="Remove project/item">
                                <img src="{{ asset('img/Trash.svg') }}" width="20" height="20">
                            </button>
                        </div>

                        <div class="row mb-3">
                            <div class="form-group col-4">
                                <label>Project Title</label>
                                <input type="text" class="form-control" name="items[0][proj_title]"
                                    data-field="proj_title">
                                <span class="field-error d-none"></span>
                            </div>

                            <div class="form-group col-4">
                                <label>End-User or Implementing Unit</label>
                                <input type="text" class="form-control" name="items[0][end_user]"
                                    data-field="end_user">
                                <span class="field-error d-none"></span>
                            </div>

                            <div class="form-group col-4">
                                <label>General Description</label>
                                <input type="text" class="form-control" name="items[0][gen_desc]"
                                    data-field="gen_desc">
                                <span class="field-error d-none"></span>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="form-group col-4">
                                <label>Mode of Procurement</label>
                                <input type="text" class="form-control" name="items[0][mode]"
                                    data-field="mode">
                                <span class="field-error d-none"></span>
                            </div>

                            <div class="form-group col-4">
                                <label>Criteria for Bid Evaluation</label>
                                <input type="text" class="form-control" name="items[0][criteria]"
                                    data-field="criteria"
                                    placeholder="Including Sustainability and Domestic Preference">
                                <span class="field-error d-none"></span>
                            </div>

                            <div class="form-group col-4">
                                <label>To be covered by an Early Procurement Activity</label>
                                <span class="field-error d-none"></span>
                                <div class="mt-4 d-flex justify-content-center" data-field="covered">
                                    <input class="form-check-input" type="radio" name="items[0][covered]"
                                        id="covered-yes-0" value="Yes">
                                    <label class="form-check-label ms-2 me-4" for="covered-yes-0">Yes</label>

                                    <input class="form-check-input" type="radio" name="items[0][covered]"
                                        id="covered-no-0" value="No">
                                    <label class="form-check-label ms-2" for="covered-no-0">No</label>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <h5 class="card-title fw-bold col-6">Projected Timeline</h5>
                            <h5 class="card-title fw-bold col-6">Funding Details</h5>
                        </div>

                        <div class="row mb-3">
                            <div class="form-group col-3">
                                <label>Start of Procurement Activity</label>
                                <input type="text" class="form-control flatpickr-date" name="items[0][start]"
                                    data-field="start" placeholder="Select Date">
                                <span class="field-error d-none"></span>
                            </div>

                            <div class="form-group col-3">
                                <label>End of Procurement Activity</label>
                                <input type="text" class="form-control flatpickr-date" name="items[0][end]"
                                    data-field="end" placeholder="Select Date">
                                <span class="field-error d-none"></span>
                            </div>

                            <div class="form-group col-3">
                                <label>Source of Fund</label>
                                <input type="text" class="form-control" name="items[0][source]"
                                    data-field="source">
                                <span class="field-error d-none"></span>
                            </div>

                            <div class="form-group col-3">
                                <label>Estimated Budget/Approved Budget</label>
                                <input type="number" class="form-control estimated-budget-input"
                                    name="items[0][esti_budget]" data-field="esti_budget"
                                    placeholder="In Peso" step="0.01" min="0">
                                <span class="field-error d-none"></span>
                            </div>
                        </div>

                        <div class="row">
                            <div class="form-group col-6">
                                <label>Procurement Strategy or Tools</label>
                                <input type="text" class="form-control" name="items[0][tools]"
                                    data-field="tools">
                                <span class="field-error d-none"></span>
                            </div>

                            <div class="form-group col-6">
                                <label>REMARKS</label>
                                <input type="text" class="form-control" name="items[0][remarks]"
                                    data-field="remarks"
                                    placeholder="Other relevant descriptions of the procurement project, if applicable">
                                <span class="field-error d-none"></span>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <div class="d-flex justify-content-center mb-3 {{ $isReadOnly ? 'd-none' : '' }}" id="add-project-btn-container">
            <button type="button"
                class="btn border border-light-subtle btn-white d-inline-flex align-items-center justify-content-center w-50 gap-1 py-2"
                id="add-project-btn">
                <img src="{{ asset('img/Add.svg') }}" width="14" height="14" alt="">
                <span class="fw-bold">Add Project</span>
            </button>
        </div>

        <div class="d-flex justify-content-end">
            <h4><span class="fw-bold">Total Amount: </span><span id="total-amount-display">0.00</span></h4>
        </div>
    </form>
@endsection

@push('js')
    {{-- Page SPECIFIC js --}}
    <script src="{{ asset('plugins/src/flatpickr/flatpickr.js') }}"></script>

    <!-- CUSTOM js -->
    <script src="{{ asset('js/head/create-app/head-create-app.js') }}"></script>
@endpush
