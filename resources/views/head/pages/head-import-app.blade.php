{{-- Extend the main layout that you want to use --}}
@extends('head/layout/head-layout')

{{-- Define contents to show in the layout --}}
@section('title', 'Import | I-TRAC')

@push('css')
    {{-- Page SPECIFIC css --}}
    <link rel="stylesheet" href="{{ asset('css/head/import-app/page-specific/filepond.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/head/import-app/page-specific/custom-filepond.css') }}">

    <!-- CUSTOM css -->
    <link rel="stylesheet" href="{{ asset('css/head/import-app/import-app.css') }}">
@endpush

@section('content')
    <meta name="csrf-token" content="{{ csrf_token() }}">

<div class="row g-3 align-items-start mt-0 ms-0">
    <div class="col-md-5 col-lg-4 col-xl-4 mb-4 mb-md-0 mt-1 h-100">
        <div class="card border border-light-subtle border-opacity-50">
                <div class="card-body">
                    <div class="widget-header">
                        <div class="row">
                             <div class="col-xl-12 col-md-12 col-sm-12 col-12 d-flex align-items-center">
                                <a href="#" class="me-2">
                                    <img src="{{ asset('img/back-btn-icon.png') }}" alt="Back Button" width="20">
                                </a>
                                
                                <h4 class="red-text fw-bold fs-5 mb-0">Import Annual Procurement Plan</h4>
                                
                            </div>
                            <hr class="mt-3">

                            <div>
                                <h6 class="red-text"> File Requirements: </h6>
                                    <ul>
                                        <li> File must have a .csv extension. </li>
                                        <li> Fields must be separated by commas. </li>
                                        <li> Each row represents one procurement record. </li>
                                        <li> If your file has a header row, please check "Has header row" so the first row will be ignored during import. </li>
                                        <li> Submit this Page and you will be prompted to map the fields in your file to the fields above to complete the import. </li>
                                    </ul>
                            </div>

                            <div>
                                <h6 class="red-text"> Your CSV file may include the following fields: </h6>
                                    <ul>
                                        <li> Code (required) </li>
                                        <li> Procurement Project/ Project (required) </li>
                                        <li> Ads/Post of IB/REI </li>
                                        <li> Sub/Open of Bids </li>
                                        <li> Notice of Award </li>
                                        <li> Contract Signing </li>
                                        <li> Total </li>
                                    </ul>
                            </div>

                        </div>
                    </div>
            </div>
        </div>
    </div>

    <div id="fuMultipleFile" class="col-md-7 col-lg-8 col-xl-8 layout-spacing mt-1 h-100">
    <div class="statbox widget h-100"> <div class="widget-content widget-content-area border border-2 border-light-subtle border-opacity-50 d-flex flex-column justify-content-center align-items-center h-100" style="min-height: 510px;">
            
            <div class="row w-100">
                <div class="col-12">
                    <p class="red-text text-center fs-4 fw-bold">Import your file</p>
                </div>
            </div>

            <div class="row w-100">
                <div class="col-md-6 mx-auto">
                    <div class="multiple-file-upload">
                        <input type="file" class="file-upload-multiple" name="filepond" accept=".csv"
                            data-max-file-size="2MB" data-max-files="1">
                    </div>
                    <div class="d-flex justify-content-center mt-3">
                        <button id="import-btn" class="btn btn-red" disabled>Import</button>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

</div>
@endsection

@push('js')
    {{-- Page SPECIFIC js --}}
    <script src="{{ asset('js/head/import-app/page-specific/filepond.min.js') }}"></script>
    <script src="{{ asset('js/head/import-app/page-specific/filepondPluginFileValidateSize.min.js') }}"></script>
    <script src="{{ asset('js/head/import-app/page-specific/FilePondPluginFileValidateType.min.js') }}"></script>

    <!-- CUSTOM js -->
    <script src="{{ asset('js/head/import-app/import-app.js') }}"></script>
@endpush
