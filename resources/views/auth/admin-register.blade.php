<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Admin Register</title>

    {{-- Google Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:ital,wght@0,200..1000;1,200..1000&display=swap" rel="stylesheet">

    <!-- Vite for GLOBAL MANDATORY css and js-->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- Font Awesome for icons --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <link rel="icon" type="image/svg+xml" href="{{ asset('img/itrac-favicon.svg') }}">

    <!-- Inject SPECIFIC and CUSTOM css-->
    <link rel="stylesheet" href="{{ asset('css/auth-admin/admin-register.css') }}">
</head>
<body class="layout-boxed auth-page">
    <div class="main-container" id="container">
        <div class="row g-0 align-items-stretch">
            <div id="cover" class="col-md-6 p-0">
                <img src="{{ asset('img/Background.svg') }}" alt="Background" class="w-100 h-100" style="object-fit: cover;">
            </div>
            <div class="col-12 col-md-6 px-2 py-5">
                
                <div class="p-4">
                    <div class="mt-5">
                        <h2 class="black-text pl-5" >Admin Register</h2>
                        <h5 class="black-text-h5">Enter your details to register.</h5>
                    </div>
                    <form class="needs-validation" method="POST" action="{{ route('admin.register') }}" novalidate>
                        @csrf
                        
                        <!-- Display Success Message -->
                        @if (session('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        <!-- Display Validation Errors -->
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

                        <div class="form-group mb-3">
                            {{-- Username --}}
                            <div class="d-flex align-items-center mb-2">
                                <label for="username" class="form-label mb-0 flex-shrink-0 text-nowrap">Username</label>
                            </div>
                            <input type="text" class="form-control mb-2 @error('username') is-invalid @enderror" 
                                   id="username" name="username" value="{{ old('username') }}" required>
                            @error('username')
                                <div class="invalid-feedback d-block">
                                    {{ $message }}
                                </div>
                            @enderror

                            {{-- Master Key --}}
                            <div class="d-flex align-items-center mb-2">
                                <label for="master-key" class="form-label mb-0 flex-shrink-0 text-nowrap">Master Key</label>
                            </div>
                            <div class="password-field mb-2">
                                <input type="password" class="form-control @error('master_key') is-invalid @enderror" 
                                       id="master-key" name="master_key" value="{{ old('master_key') }}" required>
                                <i class="fas fa-eye-slash password-toggle-icon"></i>
                            </div>
                            @error('master_key')
                                <div class="invalid-feedback d-block">
                                    {{ $message }}
                                </div>
                            @enderror

                            {{-- Password --}}
                            <div class="d-flex align-items-center mb-2">
                                <label for="password" class="form-label mb-0 flex-shrink-0 text-nowrap">Password</label>
                            </div>
                            <div class="password-field mb-2">
                                <input type="password" class="form-control @error('password') is-invalid @enderror" 
                                       id="password" name="password" required>
                                <i class="fas fa-eye-slash password-toggle-icon"></i>
                            </div>
                            @error('password')
                                <div class="invalid-feedback d-block">
                                    {{ $message }}
                                </div>
                            @enderror

                            {{-- Confirm Password --}}
                            <div class="d-flex align-items-center mb-2">
                                <label for="password_confirmation" class="form-label mb-0 flex-shrink-0 text-nowrap">Confirm Password</label>
                            </div>
                            <div class="password-field mb-2">
                                <input type="password" class="form-control @error('password_confirmation') is-invalid @enderror" 
                                       id="password_confirmation" name="password_confirmation" required>
                                <i class="fas fa-eye-slash password-toggle-icon"></i>
                            </div>
                            @error('password_confirmation')
                                <div class="invalid-feedback d-block">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <div class="mt-4">
                            <button type="submit" class="btn btn-red w-100">Register</button>
                        </div>

                        <div class="mt-4 text-center">
                            <p class="black-text">Already have an account? <a href="{{ route('admin.show.login') }}" class="red-text">Login.</a></p>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>