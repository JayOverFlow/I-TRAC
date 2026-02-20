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

    <div id="fuMultipleFile" class="col-lg-12 layout-spacing">
        <div class="statbox widget box box-shadow">
            <div class="widget-header">
                <div class="row">
                    <div class="col-xl-12 col-md-12 col-sm-12 col-12">
                        <h4 class="red-text">Import Annual Procurement Plan</h4>
                    </div>
                </div>
            </div>
            <div class="widget-content widget-content-area">

                <div class="row">
                    <div class="col-md-6 mx-auto">

                        <div class="multiple-file-upload">
                            <input type="file" class="file-upload-multiple" name="filepond" accept=".csv"
                                data-max-file-size="2MB" data-max-files="1">
                        </div>

                        <button id="import-btn" class="btn btn-red" disabled>Import</button>
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
