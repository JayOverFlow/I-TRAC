@push('css')
    <!-- Page SPECIFIC css -->
    <link rel="stylesheet" href="{{ asset('css/account-setting/page-specific/account-settings.css') }}">
    <link rel="stylesheet" href="{{ asset('css/account-setting/page-specific/tabs.css') }}">
    <link rel="stylesheet" href="{{ asset('css/head/dashboard/page-specific/apexcharts.css') }}">
    <link rel="stylesheet" href="{{ asset('css/head/dashboard/page-specific/dash_1.css') }}">

    <link rel="stylesheet" href="{{ asset('css/account-setting/page-specific/profile.css') }}">
    <link rel="stylesheet" href="{{ asset('css/account-setting/custom-account-setting.css') }}">

    <!-- DARK MODE SPECIFIC css -->
    <link rel="stylesheet" href="{{ asset('css/account-setting/page-specific/dark/account-settings.css') }}">
    <link rel="stylesheet" href="{{ asset('css/account-setting/page-specific/dark/user-profile.css') }}">
    <link rel="stylesheet" href="{{ asset('css/account-setting/page-specific/dark/tabs.css') }}">
@endpush

@section('content')
    <div class="account-settings-container layout-top-spacing">

        <div class="account-content">
            <div class="row mb-3">
                <div class="col-md-12">

                    <ul class="nav nav-pills" id="animateLine" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link red-text-2 active" id="animated-underline-profile-tab"
                                data-bs-toggle="tab" href="#pane-animated-underline-profile" role="tab"
                                aria-controls="animated-underline-profile" aria-selected="true"><img
                                    src="{{ asset('img/Profile.svg') }}" width="20" height="20"> Profile</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link red-text-2" id="animated-underline-inbox-tab" data-bs-toggle="tab"
                                href="#pane-animated-underline-inbox" role="tab"
                                aria-controls="animated-underline-inbox" aria-selected="false" tabindex="-1"><img
                                    src="{{ asset('img/Inbox.svg') }}" width="20" height="20"> Inbox</button>
                        </li>


                        @if (in_array(auth()->user()->roles()->first()?->gen_role, ['Head', 'Procurement', 'Supply']))
                            <li class="nav-item" role="presentation">
                                <button class="nav-link red-text-2" id="animated-underline-annual-procurement-plan-tab"
                                    data-bs-toggle="tab" href="#pane-animated-underline-annual-procurement-plan"
                                    role="tab" aria-controls="animated-underline-annual-procurement-plan"
                                    aria-selected="false" tabindex="-1">
                                    <img src="{{ asset('img/APP.svg') }}" width="20" height="20">
                                    Annual Procurement Plan</button>
                            </li>
                        @endif


                        <li class="nav-item" role="presentation">
                            <button class="nav-link red-text-2" id="animated-underline-settings-tab" data-bs-toggle="tab"
                                href="#pane-animated-underline-settings" role="tab"
                                aria-controls="animated-underline-settings" aria-selected="false" tabindex="-1">
                                <img src="{{ asset('img/Settings.svg') }}" width="20" height="20">
                                Settings</button>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="tab-content" id="animateLineContent-4">
                @include('general-pages.account-settings-partials._profile')
                {{-- End Profile Tab --}}

                @include('general-pages.account-settings-partials._inbox')

                @if (in_array(auth()->user()->roles->first()?->gen_role, ['Head', 'Procurement', 'Supply']))
                    @include('general-pages.account-settings-partials._annual-procurement-plan')
                @endif

                @include('general-pages.account-settings-partials._settings')
                {{-- End Settings Tab --}}
            </div>

        </div>

    </div>

    @include('general-pages.account-settings-partials._upload-modal')
@endsection

@push('js')
    <!-- Page SPECIFIC js -->
    {{-- <script src="{{ asset('js/account-setting/page-specific/account-settings.js') }}"></script>  --}} {{-- Not needed for now --}}
    {{-- <script src="{{ asset('js/head/dashboard/page-specific/apexcharts.min.js') }}"></script>
    <script src="{{ asset('js/head/dashboard/page-specific/dash_1.js') }}"></script> --}}

    <!-- CUSTOM js -->
    <script src="{{ asset('js/account-setting/page-specific/profile.js') }}"></script>
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

            // Listen for hash changes to support same-page navigation from the header
            $(window).on('hashchange', function() {
                activateTabFromHash();
            });
        });
    </script>
    {{-- <script src="{{ asset('js/account-setting/custom-account-setting.js') }}"></script> --}}
@endpush
