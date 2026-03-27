{{-- Extend the main layout that you want to use --}}
@extends('main-layout')

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
    <form method="POST" action="{{ route('create.app') }}" id="create-app-form">
        @csrf

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="card allocated-budget-card mb-3">
            <div class="card-body d-flex justify-content-center justify-content-between align-items-center">
                <h5 class="card-title mb-0 fw-bold red-text-2">ANNUAL PROCUREMENT PROJECT</h5>
                <div>
                    <h5 class="card-title mb-3 black-text">ALLOCATED BUDGET: PHP 12,345.00</h5>

                    <div class="text-end">
                        <button type="submit"
                            class="btn border border-light-subtle btn-dark-red d-inline-flex align-items-center gap-1 px-3">
                            <img src="{{ asset('img/Check.svg') }}" width="18" height="18">
                            <span>Done</span>
                        </button>

                        <button type="button"
                            class="btn border border-light-subtle btn-white d-inline-flex align-items-center gap-1 px-2">
                            <img src="{{ asset('img/Save.svg') }}" width="18" height="18">
                            <span class="fw-bold">Save as Draft</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div id="project-items-container">
            <div class="card project-item-card mb-3">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title fw-bold"><span class="red-text-2 item-number-span">Item 1</span> | Procurement
                            Project
                            Details</h5>

                        <button type="button" class="btn btn-dark-red remove-project-btn d-none">
                            <img src="{{ asset('img/Trash.svg') }}" width="20" height="20">
                        </button>
                    </div>

                    <div class="row mb-3">
                        <div class="form-group col-4">
                            <label>Project Title</label>
                            <input type="text" class="form-control" name="items[0][proj_title]">
                        </div>

                        <div class="form-group col-4">
                            <label>End-User or Implementing Unit</label>
                            <input type="text" class="form-control" name="items[0][end_user]">
                        </div>

                        <div class="form-group col-4">
                            <label>General Description</label>
                            <input type="text" class="form-control" name="items[0][gen_desc]">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="form-group col-4">
                            <label>Mode of Procurement</label>
                            <input type="text" class="form-control" name="items[0][mode]">
                        </div>

                        <div class="form-group col-4">
                            <label>Criteria for Bid Evaluation</label>
                            <input type="text" class="form-control" name="items[0][criteria]"
                                placeholder="Including Sustainability and Domestic
                            Preference">
                        </div>

                        <div class="form-group col-4">
                            <label>To be covered by an Early Procurement Activity</label>
                            <div class="mt-4 d-flex justify-content-center">
                                <input class="form-check-input" type="radio" name="items[0][covered]" id="covered-yes"
                                    value="Yes">
                                <label class="form-check-label ms-2 me-4" for="covered-yes">
                                    Yes
                                </label>

                                <input class="form-check-input" type="radio" name="items[0][covered]" id="covered-no"
                                    value="No">
                                <label class="form-check-label ms-2" for="covered-no">
                                    No
                                </label>
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
                                placeholder="Select Date..">
                        </div>

                        <div class="form-group col-3">
                            <label>End of Procurement Activity</label>
                            <input type="text" class="form-control flatpickr-date" name="items[0][end]"
                                placeholder="Select Date..">
                        </div>

                        <div class="form-group col-3">
                            <label>Source of Fund</label>
                            <input type="text" class="form-control" name="items[0][source]">
                        </div>

                        <div class="form-group col-3">
                            <label>Estimated Budget/Approved Budget</label>
                            <input type="number" class="form-control estimated-budget-input"
                                name="items[0][esti_budget]" placeholder="In Peso" step="any" min="0">
                        </div>
                    </div>

                    <div class="row">
                        <div class="form-group col-6">
                            <label>Procurement Strategy or Tools</label>
                            <input type="text" class="form-control" name="items[0][tools]">
                        </div>

                        <div class="form-group col-6">
                            <label>REMARKS</label>
                            <input type="text" class="form-control" name="items[0][remarks]"
                                placeholder="Other relevant descriptions of the procurement project, if applicable">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-center mb-3">
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
    {{-- <script src="{{ asset('js/head/import-app/page-specific/filepond.min.js') }}"></script> --}}

    <script src="{{ asset('plugins/src/flatpickr/flatpickr.js') }}"></script>

    <!-- CUSTOM js -->
    <script src="{{ asset('js/head/create-app/head-create-app.js') }}"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            if (typeof flatpickr !== "undefined") {
                flatpickr('.flatpickr-date');
            }
        });
    </script>
@endpush
