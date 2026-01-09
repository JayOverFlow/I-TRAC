<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>I-TRAC | @yield('title')</title>
    @yield('css') {{-- To inject the css content based on the string --}}
</head>
<body>
    <div class="main-content">
        @yield('content') {{-- To inject the main content based on the string --}}
    </div>

    @include('partials.sample-partial')

    @yield('js') {{-- To inject the js based on the string --}}
</body>
</html>