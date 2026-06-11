<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title')</title>
    <script>
        (function() {
            try {
                var t = localStorage.getItem("theme");
                if (t) {
                    var p = JSON.parse(t);
                    if (p && p.settings && p.settings.layout && p.settings.layout.darkMode) {
                        document.documentElement.classList.add('dark');
                    }
                }
            } catch(e) {}
        })();
    </script>
    <link rel="icon" type="image/svg+xml" href="{{ asset('img/itrac-favicon.svg') }}" />
    {{-- Google Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:ital,wght@0,200..1000;1,200..1000&display=swap"
        rel="stylesheet">

    <!-- Vite for GLOBAL MANDATORY css and js-->
    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/js/bootstrap/bootstrap.bundle.min.js'])

    {{-- NOTE: FIX THIS AND SET THIS TO A GLOBAL IN VITE --}}
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"
        integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>

    <!-- Inject SPECIFIC and CUSTOM css-->
    @stack('css')

</head>

<body class="layout-boxed enable-secondaryNav">
    <!-- BEGIN LOADER -->
    <div id="load_screen">
        <div class="loader">
            <div class="loader-content">
                <div class="spinner-grow align-self-center"></div>
            </div>
        </div>
    </div>
    <!--  END LOADER -->

    <!--  BEGIN HEADER  -->
    <div class="header-container container-xxl">
        @include('partials.main-header')
    </div>
    <!--  END HEADER  -->

    <!--  BEGIN MAIN CONTAINER  -->
    <div class="main-container" id="container">

        <div class="overlay"></div>
        <div class="search-overlay"></div>

        <!--  BEGIN NAVBAR  -->
        <div class="sidebar-wrapper sidebar-theme">
            @include('partials.unassigned-nav-bar')
        </div>
        <!--  END NAVBAR  -->

        <!--  BEGIN CONTENT AREA  -->
        <div id="content" class="main-content">
            <div class="layout-px-spacing">

                <div class="middle-content container-xxl p-0">

                    <div class="row layout-top-spacing">
                        @yield('content')
                    </div>

                </div>

            </div>
            <!--  BEGIN FOOTER  -->
            @include('partials.footer')
            <!--  END FOOTER  -->
        </div>
        <!--  END CONTENT AREA  -->

    </div>
    <!-- END MAIN CONTAINER -->

    @include('partials.toast-feedback')

    <!-- Inject SPECIFIC and CUSTOM js-->
    @stack('js')
</body>

</html>
