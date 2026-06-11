{{-- Extend the main layout that you want to use --}}
@extends('layouts.procurement-layout')

{{-- Define contents to show in the layout --}}
@section('title', 'Procurement | I-TRAC')

@push('css')
    <!-- Page SPECIFIC css -->
    <link rel="stylesheet" href="{{ asset('css/account-setting/page-specific/account-settings.css') }}">
    <link rel="stylesheet" href="{{ asset('css/account-setting/page-specific/tabs.css') }}">
    <link rel="stylesheet" href="{{ asset('css/head/dashboard/page-specific/apexcharts.css') }}">
    <link rel="stylesheet" href="{{ asset('css/head/dashboard/page-specific/dash_1.css') }}">
    <link rel="stylesheet" href="{{ asset('css/procurement/procure/custom-procure.css') }}">
    <link rel="stylesheet" href="{{ asset('css/custom-code-input.css') }}">

    <!-- DARK MODE SPECIFIC css -->
@endpush

@section('content')
    <div class="procure-container layout-top-spacing">

        <div class="account-content">
            <div class="row mb-3">
                <div class="col-md-12">

                    <ul class="nav nav-pills" id="animateLine" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link red-text-2 active" id="animated-underline-purchase-request-tab"
                                data-bs-toggle="tab" href="#pane-animated-underline-purchase-request" role="tab"
                                aria-controls="animated-underline-purchase-request" aria-selected="true">
                                <img src="{{ asset('img/PR.svg') }}" width="20" height="20">
                                Purchase Request</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link red-text-2" id="animated-underline-purchase-order-tab"
                                data-bs-toggle="tab" href="#pane-animated-underline-purchase-order" role="tab"
                                aria-controls="animated-underline-purchase-order" aria-selected="false" tabindex="-1">
                                <img src="{{ asset('img/PO.svg') }}" width="20" height="20">
                                Purchase Order</button>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="tab-content" id="animateLineContent-4">
                @include('procurement.partials._purchase-request')
                @include('procurement.partials._purchase-order')
            </div>

        </div>

    </div>
@endsection

@push('js')
    <!-- Page SPECIFIC js -->
    {{-- <script src="{{ asset('js/account-setting/page-specific/account-settings.js') }}"></script>  --}} {{-- Not needed for now --}}
    {{-- <script src="{{ asset('js/head/dashboard/page-specific/apexcharts.min.js') }}"></script>
    <script src="{{ asset('js/head/dashboard/page-specific/dash_1.js') }}"></script> --}}

    <!-- CUSTOM js -->
    <script>
        $(document).ready(function() {
            /**
             * Activates the Bootstrap tab based on the current URL hash.
             * We use hashes that match the button IDs (minus the '-tab' suffix)
             * but don't match the tab-pane IDs exactly, preventing native browser jumps.
             */
            function activateTabFromHash() {
                var hash = window.location.hash;
                if (hash) {
                    // Try to find a button whose ID matches [hash]-tab
                    var tabButtonId = hash.substring(1) + '-tab';
                    var tabTriggerEl = document.getElementById(tabButtonId);

                    if (tabTriggerEl) {
                        var tab = new bootstrap.Tab(tabTriggerEl);
                        tab.show();

                        // Force scroll to top as an extra precaution
                        window.scrollTo(0, 0);
                    }
                }
            }

            // Initial activation on page load
            activateTabFromHash();

            // Update URL hash when a tab is shown
            $('button[data-bs-toggle="tab"]').on('shown.bs.tab', function(e) {
                var hash = e.target.id.replace('-tab', '');
                history.replaceState(null, null, '#' + hash);
            });

            // Listen for hash changes to support same-page navigation from the header
            $(window).on('hashchange', function() {
                activateTabFromHash();
            });
        });
    </script>
    {{-- <script src="{{ asset('js/account-setting/custom-account-setting.js') }}"></script> --}}
@endpush
