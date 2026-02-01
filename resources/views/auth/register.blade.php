<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    {{-- Used for Email Verification for security --}}
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Document</title>

    {{-- Google Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:ital,wght@0,200..1000;1,200..1000&display=swap" rel="stylesheet">

    <!-- Vite for GLOBAL MANDATORY css and js-->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- Font Awesome for icons --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    {{-- NOTE: FIX THIS AND SET THIS TO A GLOBAL IN VITE --}}
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>

    <!-- Inject SPECIFIC and CUSTOM css-->
    <link rel="stylesheet" href="{{ asset('css/auth/register/register.css') }}">
    <link rel="stylesheet" href="{{ asset('css/auth/register/page-specific/bsStepper.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/auth/register/page-specific/scrollspyNav.css') }}">
    <link rel="stylesheet" href="{{ asset('css/auth/register/page-specific/custom-bsStepper.css') }}">
    <link rel="stylesheet" href="{{ asset('css/auth/register/page-specific/modal.css') }}">

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
            <div class="col-6 px-4 py-2">
                <div class="mt-3 ms-4">
                    <h2 class="black-text">Register</h2>
                    <h5 class="black-text">Fill out the form to Register</h5>
                </div>
                <div class="bs-stepper stepper-form-validation-one">
                    <div class="bs-stepper-header" role="tablist">
                        <div class="step" data-target="#validationStep-one">
                            <button type="button" class="step-trigger" role="tab" >
                                <span class="bs-stepper-circle">1</span>
                                <span class="bs-stepper-label black-text">Step One</span>
                            </button>
                        </div>
                        <div class="line"></div>
                        <div class="step" data-target="#validationStep-two">
                            <button type="button" class="step-trigger" role="tab"  >
                                <span class="bs-stepper-circle">2</span>
                                <span class="bs-stepper-label black-text">Step Two</span>
                            </button>
                        </div>
                        <div class="line"></div>
                        <div class="step" data-target="#validationStep-three">
                            <button type="button" class="step-trigger" role="tab"  >
                                <span class="bs-stepper-circle">3</span>
                                <span class="bs-stepper-label black-text">
                                    <span class="bs-stepper-title">Step Three</span>
                                </span>
                            </button>
                        </div>
                        <div class="line"></div>
                        <div class="step" data-target="#validationStep-four">
                            <button type="button" class="step-trigger" role="tab"  >
                                <span class="bs-stepper-circle">4</span>
                                <span class="bs-stepper-label black-text">
                                    <span class="bs-stepper-title">Step Four</span>
                                </span>
                            </button>
                        </div>
                    </div>
                    <div class="bs-stepper-content">
                        <form class="needs-validation" onsubmit="return false" novalidate>
                        @csrf

                            <div id="validationStep-one" class="content" role="tabpanel">
                                <div id="test-form-1" class="needs-validation">
                                    <div class="form-group mb-2">
                                        {{-- First Name --}}
                                        <div class="d-flex align-items-center mb-2">
                                            <label for="first-name" class="form-label mb-0 flex-shrink-0 text-nowrap">
                                                First Name
                                            </label>
                                            
                                            <div class="invalid-feedback ms-2 mt-0">
                                                Please enter your first name
                                            </div>
                                        </div>
                                        <input type="text" class="form-control mb-2" id="first-name" name="first_name" >

                                        {{-- Middle Name --}}
                                        <div class="d-flex align-items-center mb-2">
                                            <label for="middle-name" class="form-label mb-0 flex-shrink-0 text-nowrap">
                                                Middle Name
                                            </label>
                                            
                                            <div class="invalid-feedback ms-2 mt-0">
                                                Please enter your middle name
                                            </div>
                                        </div>
                                        <input type="text" class="form-control mb-2" id="middle-name" name="middle_name" >
                                        

                                        {{-- Last Name --}}
                                        <div class="d-flex align-items-center mb-2">
                                            <label for="last-name" class="form-label mb-0 flex-shrink-0 text-nowrap">
                                                Last Name
                                            </label>
                                            
                                            <div class="invalid-feedback ms-2 mt-0">
                                                Please enter your last name
                                            </div>
                                        </div>
                                        <input type="text" class="form-control mb-2" id="last-name" name="last_name" >
                                        
                                        <div class="row">
                                            <div class="col-6">
                                                {{-- Suffix --}}
                                                <div class="d-flex align-items-center mb-2">
                                                    <label for="suffix" class="form-label mb-0 flex-shrink-0 text-nowrap">
                                                        Suffix
                                                    </label>
                                                    
                                                    <div class="invalid-feedback ms-2 mt-0">
                                                        Please enter your suffix
                                                    </div>
                                                </div>
                                                <input type="text" class="form-control" id="suffix" name="suffix" placeholder="Optional">
                                            </div>

                                            <div class="col-6">
                                                {{-- TUP-ID --}}
                                                <div class="d-flex align-items-center mb-2">
                                                    <label for="tup-id" class="form-label mb-0 flex-shrink-0 text-nowrap">
                                                        TUP ID
                                                    </label>
                                                    
                                                    <div class="invalid-feedback ms-2 mt-0">
                                                        Please enter your TUP ID
                                                    </div>
                                                </div>
                                                <input type="text" class="form-control" id="tup-id" name="tup_id" placeholder="6 digits">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="button-action mt-5 d-flex justify-content-center">
                                    <button class="btn btn-red btn-back me-3" disabled>Back</button>
                                    <button class="btn btn-red btn-nxt">Next</button>
                                </div>
                            </div>
                            <div id="validationStep-two" class="content" role="tabpanel">
                                <div class="needs-validation">
                                    <div class="form-group mb-2">
                                        {{-- TUP Email --}}
                                        <div class="d-flex align-items-center mb-2">
                                            <label for="email" class="form-label mb-0 flex-shrink-0 text-nowrap">Email</label>
                                            <div class="invalid-feedback ms-2 mt-0">Please fill the email field</div>
                                        </div>
                                        <input type="email" class="form-control mb-2" id="email" name="email" placeholder="example@tup.edu.ph">

                                        {{-- Password --}}
                                        <div class="d-flex align-items-center mb-2">
                                            <label for="password" class="form-label mb-0 flex-shrink-0 text-nowrap">Password</label>
                                            <div class="invalid-feedback ms-2 mt-0">Please fill the password field</div>
                                        </div>
                                        <input type="password" class="form-control mb-2" id="password" id="password" placeholder="At least 8 characters with one letter and one number.">

                                        {{-- Confirm Password --}}
                                        <div class="d-flex align-items-center mb-2">
                                            <label for="confirm-password" class="form-label mb-0 flex-shrink-0 text-nowrap">Confirm Password</label>
                                            <div class="invalid-feedback ms-2 mt-0">Please fill the confirm password field</div>
                                        </div>
                                        <input type="password" class="form-control mb-2" id="confirm-password" name="confirm_password">

                                        <div class="row">
                                            <div class="col-6">
                                                {{-- User Type --}}
                                                <div class="d-flex align-items-center mb-2">
                                                    <label class="form-label mb-0 flex-shrink-0 text-nowrap">User Type</label>
                                                    <div class="invalid-feedback ms-2 mt-0">Please choose your user type</div>
                                                </div>
                                                
                                                <div class="mt-4">
                                                    <input class="form-check-input" type="radio" name="user_type" id="user-type-faculty" value="Faculty">
                                                <label class="form-check-label me-4" for="user-type-faculty">
                                                    Faculty
                                                </label>
                                                
                                                <input class="form-check-input" type="radio" name="user_type" id="user-type-staff" value="Staff">
                                                <label class="form-check-label" for="user-type-staff">
                                                    Staff
                                                </label>
                                                
                                                <div class="invalid-feedback mt-2">Please select a user type.</div>
                                                </div>
                                            </div>

                                            <div class="col-6">
                                                {{-- Department/Office --}}
                                                <div class="d-flex align-items-center mb-2">
                                                    <label for="department" class="form-label mb-0 flex-shrink-0 text-nowrap">Department/Office</label>
                                                    <div class="invalid-feedback ms-2 mt-0">Select Department/Office</div>
                                                </div>
                                                <select class="form-select" aria-label="Default select example" id="department">
                                                    <option value="" selected disabled>Select</option>
                                                    @foreach ($departments as $deps)
                                                        <option value="{{ $deps->dep_id }}">{{ $deps->dep_name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="button-action mt-5 d-flex justify-content-center">
                                    <button class="btn btn-red btn-back me-3">Back</button>
                                    <button class="btn btn-red btn-nxt">Next</button>
                                </div>
                            </div>
                            <div id="validationStep-three" class="content" role="tabpanel" >
                                <div class="row g-3 needs-validation mb-0">
                                    <div class="col-6">
                                        <div class="mb-3">
                                            <h6 class="mb-0">First Name</h6>
                                            <h5 class="black-text" id="review-first-name"></h5>
                                        </div>

                                        <div class="mb-3">
                                            <h6 class="mb-0">Last Name</h6>
                                            <h5 class="black-text" id="review-last-name"></h5>
                                        </div>

                                        <div class="mb-3">
                                            <h6 class="mb-0">TUP Email</h6>
                                            <h5 class="black-text" id="review-email"></h5>
                                        </div>

                                        <div>
                                            <h6 class="mb-0">
                                                Password
                                                <button type="button" class="btn btn-link p-0 text-decoration-none ms-2 bg-transparent border-0" id="toggle-password-review">
                                                    <i class="fas fa-eye" id="password-toggle-icon"></i>
                                                </button>
                                            </h6>
                                            <h5 class="black-text" id="review-password"></h5>
                                        </div>
                                    </div>

                                    <div class="col-6">
                                        <div class="mb-3">
                                            <h6 class="mb-0">Middle Name</h6>
                                            <h5 class="black-text" id="review-middle-name"></h5>
                                        </div>

                                        <div class="mb-3">
                                            <h6 class="mb-0">Suffix</h6>
                                            <h5 class="black-text" id="review-suffix"></h5>
                                        </div>

                                        <div class="mb-3">
                                            <h6 class="mb-0">TUP ID</h6>
                                            <h5 class="black-text" id="review-tup-id"></h5>
                                        </div>

                                        <div>
                                            <h6 class="mb-0">User Type</h6>
                                            <h5 class="black-text" id="review-user-type"></h5>
                                        </div>
                                    </div>

                                    <div>
                                        <div class="mb-5 mt-1">
                                            <h6 class="mb-0">Department/Office</h6>
                                            <h5 class="black-text" id="review-department"></h5>
                                        </div>
                                    </div>
                                </div>

                                <div class="button-action mt-5 d-flex justify-content-center">
                                    <button class="btn btn-red btn-back me-3">Back</button>
                                    <button class="btn btn-red btn-nxt" id="step3-next-btn" data-bs-toggle="modal" data-bs-target="#exampleModalCenter">Next</button>
                                </div>
                            </div>
                            <div id="validationStep-four" class="content" role="tabpanel" >
                                <div class="row">
                                    <div class="col-md-12 mb-3">
                                        
                                        <h2 class="black-text">Email Verification</h2>
                                        <p>Enter the code sent to your TUP email for verification.</p>
                                        
                                    </div>
                                    <div class="col-sm-2 col-3 ms-auto">
                                        <div class="mb-3">
                                            <input type="text" class="form-control opt-input text-center">
                                        </div>
                                    </div>
                                    <div class="col-sm-2 col-3">
                                        <div class="mb-3">
                                            <input type="text" class="form-control opt-input text-center">
                                        </div>
                                    </div>
                                    <div class="col-sm-2 col-3">
                                        <div class="mb-3">
                                            <input type="text" class="form-control opt-input text-center">
                                        </div>
                                    </div>
                                    <div class="col-sm-2 col-3">
                                        <div class="mb-3">
                                            <input type="text" class="form-control opt-input text-center">
                                        </div>
                                    </div>
                                    <div class="col-sm-2 col-3">
                                        <div class="mb-3">
                                            <input type="text" class="form-control opt-input text-center">
                                        </div>
                                    </div>
                                    <div class="col-sm-2 col-3 me-auto">
                                        <div class="mb-3">
                                            <input type="text" class="form-control opt-input text-center">
                                        </div>
                                    </div>
                                    
                                    <div class="col-12 mt-5">
                                        <div class="mb-4">
                                            <button class="btn btn-red w-100 btn-submit">VERIFY</button>
                                        </div>
                                    </div>
                                    
                                    <div class="col-12">
                                        <div class="text-center">
                                            <p class="mb-0 black-text">Didn't receive the code? <a href="javascript:void(0);" class="red-text" id="resend-code-btn" disabled>Resend</a> <span id="resend-timer" class="text-muted"></span></p>
                                        </div>
                                    </div>
                                        
                                </div>
                            </div>

                        </form>
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
    <script src="{{ asset('js/auth/register/page-specific/bsStepper.min.js') }}" ></script>
    <script src="{{ asset('js/auth/register/page-specific/custom-bsStepper.min.js') }}" ></script>
    <script src="{{ asset('js/auth/register/page-specific/2-Step-Verification.js') }}" ></script>
</body>
</html>