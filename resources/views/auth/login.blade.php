<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>

    {{-- Google Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:ital,wght@0,200..1000;1,200..1000&display=swap" rel="stylesheet">

    <!-- Vite for GLOBAL MANDATORY css and js-->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- Font Awesome for icons --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Inject SPECIFIC and CUSTOM css-->
    <link rel="stylesheet" href="{{ asset('css/auth/login/page-specific/auth-cover.css') }}">
    <link rel="stylesheet" href="{{ asset('css/auth/login/login.css') }}">

</head>
<body class="layout-boxed">
    <div class="main-container" id="container">
        <div class="row">
            <div id="cover" class="col-6 p-4 d-flex justify-content-center align-items-center">
                <div class="text-center">
                    <img src="{{ asset('img/itrac-cover-logo.png') }}" alt="I-TRAC logo" class="my-5" width="500" height="100">
                    <h4 class="white-text">A Digital System for Item Status Tracking and QR-Code Enabled Material Requisition Control</h4>
                </div>
            </div>
            <div class="col-6 px-4 py-2 d-flex align-items-center justify-content-center">
                <div class="row px-3">
                    <div class="mt-3">
                        <h2 class="black-text">Login</h2>
                        <h5 class="black-text">Enter your email and password to login</h5>
                    </div>
                    <form action="{{ route('login') }}" method="POST">
                    @csrf
                        <div class="col-md-12 mt-3 mb-3">
                        <div class="d-flex align-items-center mb-2">
                            <label for="email" class="form-label mb-0 flex-shrink-0 text-nowrap">
                                Email
                            </label>
                            
                            <div class="text-danger ms-2 mt-0" style="display: none;" id="login-error">
                                @error('all_fields')
                                    {{ $message }}
                                @enderror
                                @error('auth_failed')
                                    Email or password is invalid
                                @enderror
                            </div>
                        </div>
                            <input type="text" class="form-control mb-2 @error('email') is-invalid @enderror @error('password') is-invalid @enderror @error('auth_failed') is-invalid @enderror" id="email" name="email" value="{{ old('email') }}">
                        </div>
                        <div class="col-md-12 mt-3 mb-4">
                            <div class="d-flex align-items-center mb-2">
                                <label for="first-name" class="form-label mb-0 flex-shrink-0 text-nowrap">
                                    Password
                                </label>
                            </div>
                            <input type="password" class="form-control @error('email') is-invalid @enderror @error('password') is-invalid @enderror @error('auth_failed') is-invalid @enderror" id="password" name="password" >
                        </div>
                        
                        <div class="col-12 mt-3 mb-3">
                            <div class="mb-4">
                                <button class="btn btn-red w-100">LOG IN</button>
                            </div>
                        </div>
                    </form>

                    <div class="col-12">
                        <div class="text-center">
                            <p class="mb-0">Dont't have an account? <a href="javascript:void(0);" class="red-text">Register</a></p>
                        </div>
                    </div>
                    
                </div>
            </div>
        </div>
    </div>

    {{-- Modal --}}
    <div class="modal fade" id="exampleModalCenter" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title black-text" id="exampleModalCenterTitle">Email Verification</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                        <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-x"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                    </button>
                </div>
                <div class="modal-body">
                    <h4 class="modal-heading mb-4 mt-2 black-text">Send Verification Code</h4>
                    <p class="modal-text">A 6-digit verification code will be sent to your TUP email address. Please confirm you want to proceed.</p>
                    <div class="alert alert-info black-text">
                        <i class="fas fa-info-circle me-2"></i>
                        The code will be sent to: <strong id="modal-email-display"></strong>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-light-dark black-text" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-red" id="send-code-btn">Send Code</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Page SPECIFIC js -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const errorDiv = document.getElementById('login-error');
    @if($errors->has('all_fields') || $errors->has('auth_failed'))
        errorDiv.style.display = 'block';
    @endif
});
</script>
</body>
</html>