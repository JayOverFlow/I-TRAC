@push('css')
    <!-- Page SPECIFIC css -->
    <link rel="stylesheet" href="{{ asset('css/account-setting/page-specific/account-settings.css') }}">
    <link rel="stylesheet" href="{{ asset('css/account-setting/page-specific/tabs.css') }}">
    <link rel="stylesheet" href="{{ asset('css/head/dashboard/page-specific/apexcharts.css') }}">
    <link rel="stylesheet" href="{{ asset('css/head/dashboard/page-specific/dash_1.css') }}">

    <link rel="stylesheet" href="{{ asset('css/account-setting/page-specific/profile.css') }}">
    <link rel="stylesheet" href="{{ asset('css/account-setting/custom-account-setting.css') }}">
@endpush

@section('content')
    <div class="account-settings-container layout-top-spacing">

        <div class="account-content">
            <div class="row mb-3">
                <div class="col-md-12">

                    <ul class="nav nav-pills" id="animateLine" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link red-text-2 active" id="animated-underline-profile-tab"
                                data-bs-toggle="tab" href="#animated-underline-profile" role="tab"
                                aria-controls="animated-underline-profile" aria-selected="true"><img
                                    src="{{ asset('img/Profile.svg') }}" width="20" height="20"> Profile</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link red-text-2" id="animated-underline-inbox-tab" data-bs-toggle="tab"
                                href="#animated-underline-inbox" role="tab" aria-controls="animated-underline-inbox"
                                aria-selected="false" tabindex="-1"><img src="{{ asset('img/Inbox.svg') }}" width="20"
                                    height="20"> Inbox</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link red-text-2" id="animated-underline-settings-tab" data-bs-toggle="tab"
                                href="#animated-underline-settings" role="tab"
                                aria-controls="animated-underline-settings" aria-selected="false" tabindex="-1">
                                <img src="{{ asset('img/Settings.svg') }}" width="20" height="20">
                                Settings</button>
                        </li>


                        @if (auth()->user()->roles()->first()->gen_role === 'Head')
                            <li class="nav-item" role="presentation">
                                <button class="nav-link red-text-2" id="animated-underline-annual-procurement-plan-tab"
                                    data-bs-toggle="tab" href="#animated-underline-annual-procurement-plan" role="tab"
                                    aria-controls="animated-underline-annual-procurement-plan" aria-selected="false"
                                    tabindex="-1">
                                    <img src="{{ asset('img/Settings.svg') }}" width="20" height="20">
                                    Annual Procurement Plan</button>
                            </li>
                        @elseif (auth()->user()->roles()->first()->gen_role === 'Procurement')
                            <li class="nav-item" role="presentation">
                                <button class="nav-link red-text-2" id="animated-underline-annual-procurement-plan-tab"
                                    data-bs-toggle="tab" href="#animated-underline-annual-procurement-plan" role="tab"
                                    aria-controls="animated-underline-annual-procurement-plan" aria-selected="false"
                                    tabindex="-1">
                                    <img src="{{ asset('img/Settings.svg') }}" width="20" height="20">
                                    Annual Procurement Plan</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link red-text-2" id="animated-underline-purchase-request-tab"
                                    data-bs-toggle="tab" href="#animated-underline-purchase-request" role="tab"
                                    aria-controls="animated-underline-purchase-request" aria-selected="false"
                                    tabindex="-1">
                                    <img src="{{ asset('img/Settings.svg') }}" width="20" height="20">
                                    Purchase Request</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link red-text-2" id="animated-underline-purchase-order-tab"
                                    data-bs-toggle="tab" href="#animated-underline-purchase-order" role="tab"
                                    aria-controls="animated-underline-purchase-order" aria-selected="false"
                                    tabindex="-1">
                                    <img src="{{ asset('img/Settings.svg') }}" width="20" height="20">
                                    Purchase Order</button>
                            </li>
                        @endif
                    </ul>
                </div>
            </div>

            <div class="tab-content" id="animateLineContent-4">
                @include('general-pages.account-settings-partials._profile')
                {{-- End Profile Tab --}}

                @include('general-pages.account-settings-partials._inbox')

                @include('general-pages.account-settings-partials._settings')
                {{-- End Settings Tab --}}

                @if (auth()->user()->roles()->first()->gen_role === 'Head')
                    @include('general-pages.account-settings-partials._annual-procurement-plan')
                @elseif (auth()->user()->roles()->first()->gen_role === 'Procurement')
                    @include('general-pages.account-settings-partials._annual-procurement-plan')
                    @include('general-pages.account-settings-partials._purchase-request')
                    @include('general-pages.account-settings-partials._purchase-order')
                @endif
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
    {{-- <script src="{{ asset('js/account-setting/custom-account-setting.js') }}"></script> --}}
@endpush
