{{-- Extend the main layout that you want to use --}}
@extends('main-layout')

{{-- Define contents to show in the layout --}}
@section('title', 'Create Annual Procurement Plan | I-TRAC')

@push('css')
    {{-- Page SPECIFIC css --}}
    <link rel="stylesheet" href="{{ asset('css/head/import-app/page-specific/filepond.min.css') }}">

    <!-- CUSTOM css -->
    <link rel="stylesheet" href="{{ asset('css/head/create-app/head-create-app.css') }}">
@endpush

@section('content')
    <div class="card allocated-budget-card mb-3">
        <div class="card-body d-flex justify-content-center justify-content-between align-items-center">
            <h5 class="card-title mb-0 white-text">ALLOCATED BUDGET</h5>
            <h5 class="card-title mb-0 white-text">ALLOCATED BUDGET: PHP 12,345.00</h5>
        </div>
    </div>

    <div id="project-items-container">
        <div class="card project-item-card mb-3">
            <div class="card-body">
                <h5 class="card-title fw-bold"><span class="red-text-2 item-number-span">Item 1</span> | Procurement Project
                    Details</h5>

                <div class="row mb-3">
                    <div class="form-group col-4">
                        <label for="">Project Title</label>
                        <input type="text" class="form-control" id="">
                    </div>

                    <div class="form-group col-4">
                        <label for="">End-User or Implementing Unit</label>
                        <input type="text" class="form-control" id="">
                    </div>

                    <div class="form-group col-4">
                        <label for="">General Description</label>
                        <input type="text" class="form-control" id="">
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="form-group col-4">
                        <label for="">Mode of Procurement</label>
                        <input type="text" class="form-control" id="">
                    </div>

                    <div class="form-group col-4">
                        <label for="">Criteria for Bid Evaluation</label>
                        <input type="text" class="form-control" id=""
                            placeholder="Including Sustainability and Domestic
                        Preference">
                    </div>

                    <div class="form-group col-4">
                        <label for="">To be covered by an Early Procurement Activity</label>
                        <div class="mt-4 d-flex justify-content-center">
                            <input class="form-check-input" type="radio" name="" id="covered-yes" value="Yes">
                            <label class="form-check-label ms-2 me-4" for="covered-yes">
                                Yes
                            </label>

                            <input class="form-check-input" type="radio" name="" id="covered-no" value="No">
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
                        <label for="">Start of Procurement Activity</label>
                        <input type="text" class="form-control" id="">
                    </div>

                    <div class="form-group col-3">
                        <label for="">End of Procurement Activity</label>
                        <input type="text" class="form-control" id="">
                    </div>

                    <div class="form-group col-3">
                        <label for="">Source of Fund</label>
                        <input type="text" class="form-control" id="">
                    </div>

                    <div class="form-group col-3">
                        <label for="">Estimated Budget/Approved Budget</label>
                        <input type="text" class="form-control estimated-budget-input" id=""
                            placeholder="In Peso">
                    </div>
                </div>

                <div class="row">
                    <div class="form-group col-6">
                        <label for="">Procurement Strategy or Tools</label>
                        <input type="text" class="form-control" id="">
                    </div>

                    <div class="form-group col-6">
                        <label for="">REMARKS</label>
                        <input type="text" class="form-control" id=""
                            placeholder="Other relevant descriptions of the procurement project, if applicable">
                    </div>
                </div>
            </div>
        </div>

        <div class="card project-item-card mb-3">
            <div class="card-body">
                <h5 class="card-title fw-bold"><span class="red-text-2 item-number-span">Item 2</span> | Procurement Project
                    Details</h5>

                <div class="row mb-3">
                    <div class="form-group col-4">
                        <label for="">Project Title</label>
                        <input type="text" class="form-control" id="">
                    </div>

                    <div class="form-group col-4">
                        <label for="">End-User or Implementing Unit</label>
                        <input type="text" class="form-control" id="">
                    </div>

                    <div class="form-group col-4">
                        <label for="">General Description</label>
                        <input type="text" class="form-control" id="">
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="form-group col-4">
                        <label for="">Mode of Procurement</label>
                        <input type="text" class="form-control" id="">
                    </div>

                    <div class="form-group col-4">
                        <label for="">Criteria for Bid Evaluation</label>
                        <input type="text" class="form-control" id=""
                            placeholder="Including Sustainability and Domestic
                        Preference">
                    </div>

                    <div class="form-group col-4">
                        <label for="">To be covered by an Early Procurement Activity</label>
                        <div class="mt-4 d-flex justify-content-center">
                            <input class="form-check-input" type="radio" name="" id="covered-yes"
                                value="Yes">
                            <label class="form-check-label ms-2 me-4" for="covered-yes">
                                Yes
                            </label>

                            <input class="form-check-input" type="radio" name="" id="covered-no"
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
                        <label for="">Start of Procurement Activity</label>
                        <input type="text" class="form-control" id="">
                    </div>

                    <div class="form-group col-3">
                        <label for="">End of Procurement Activity</label>
                        <input type="text" class="form-control" id="">
                    </div>

                    <div class="form-group col-3">
                        <label for="">Source of Fund</label>
                        <input type="text" class="form-control" id="">
                    </div>

                    <div class="form-group col-3">
                        <label for="">Estimated Budget/Approved Budget</label>
                        <input type="text" class="form-control estimated-budget-input" id=""
                            placeholder="In Peso">
                    </div>
                </div>

                <div class="row">
                    <div class="form-group col-6">
                        <label for="">Procurement Strategy or Tools</label>
                        <input type="text" class="form-control" id="">
                    </div>

                    <div class="form-group col-6">
                        <label for="">REMARKS</label>
                        <input type="text" class="form-control" id=""
                            placeholder="Other relevant descriptions of the procurement project, if applicable">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-end mb-3">
        <button type="button" class="btn btn-red" id="add-project-btn">Add Project</button>
    </div>

    <div class="d-flex justify-content-end">
        <h4><span class="fw-bold">Total Amount: </span><span id="total-amount-display">0.00</span></h4>
    </div>
@endsection

@push('js')
    {{-- Page SPECIFIC js --}}
    {{-- <script src="{{ asset('js/head/import-app/page-specific/filepond.min.js') }}"></script> --}}

    <!-- CUSTOM js -->
    <script src="{{ asset('js/head/create-app/head-create-app.js') }}"></script>
@endpush
