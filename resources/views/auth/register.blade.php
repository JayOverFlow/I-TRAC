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

    {{-- NOTE: FIX THIS AND SET THIS TO A GLOBAL IN VITE --}}
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>

    <!-- Inject SPECIFIC and CUSTOM css-->
    <link rel="stylesheet" href="{{ asset('css/auth/register/register.css') }}">
    <link rel="stylesheet" href="{{ asset('css/auth/register/page-specific/bsStepper.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/auth/register/page-specific/scrollspyNav.css') }}">
    <link rel="stylesheet" href="{{ asset('css/auth/register/page-specific/custom-bsStepper.css') }}">

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

                            <div id="validationStep-one" class="content" role="tabpanel">
                                <div id="test-form-1" class="needs-validation">
                                    <div class="form-group mb-2">
                                        {{-- First Name --}}
                                        <div class="d-flex align-items-center">
                                            <label for="validationStepform-first-name">First Name</label>
                                            <div class="invalid-feedback ms-2">Please enter your first name</div>
                                        </div>
                                        <input type="text" class="form-control mb-2" id="validationStepform-first-name" placeholder="" value="test">

                                        {{-- Middle Name --}}
                                        <div class="d-flex align-items-center">
                                            <label for="validationStepform-middle-name">Middle Name</label>
                                            <div class="invalid-feedback">Please enter your middle name</div>
                                        </div>
                                        <input type="text" class="form-control mb-2" id="validationStepform-middle-name" placeholder="">
                                        

                                        {{-- Last Name --}}
                                        <div class="d-flex align-items-center">
                                            <label for="validationStepform-last-name">Last Name</label>
                                            <div class="invalid-feedback">Please enter your last name</div>
                                        </div>
                                        <input type="text" class="form-control mb-2" id="validationStepform-last-name" placeholder="">
                                        
                                        <div class="row">
                                            <div class="col-6">
                                                {{-- Suffix --}}
                                                <div class="d-flex align-items-center">
                                                    <label for="validationStepform-suffix">Suffix</label>
                                                    <div class="invalid-feedback">Please enter your suffix</div>
                                                </div>
                                                <input type="text" class="form-control" id="validationStepform-suffix" placeholder="">
                                            </div>

                                            <div class="col-6">
                                                {{-- TUPT-ID --}}
                                                <div class="d-flex align-items-center">
                                                    <label for="validationStepform-tupt-id">TUPT-ID</label>
                                                    <div class="invalid-feedback">Please enter your TUPT-ID</div>
                                                </div>
                                                <input type="text" class="form-control" id="validationStepform-tupt-id" placeholder="">
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
                                        {{-- TUPT Email --}}
                                        <div class="d-flex align-items-center">
                                            <label for="validationStepEmailAddress">TUPT Email</label>
                                            <div class="invalid-feedback ms-2">Please fill the TUPT email field</div>
                                        </div>
                                        <input type="email" class="form-control mb-2" id="validationStepEmailAddress" placeholder="" value="test">

                                        {{-- Password --}}
                                            <label for="validationStepEmailAddress">Password</label>
                                        <input type="email" class="form-control mb-2" id="validationStepEmailAddress" placeholder="">
                                        <div class="invalid-feedback">Please fill the password field</div>

                                        {{-- Confirm Password --}}
                                        <label for="validationStepEmailAddress">Confirm Password</label>
                                        <input type="email" class="form-control mb-2" id="validationStepEmailAddress" placeholder="">
                                        <div class="invalid-feedback">Please fill the confirm password field</div>

                                        <div class="row">
                                            <div class="col-6">
                                                {{-- User Type --}}
                                                <div class="mb-3">
                                                    <label for="">User Type</label>
                                                </div>
                                                
                                                <div>
                                                    <input class="form-check-input" type="radio" name="flexRadioDefault" id="form-check-radio-red">
                                                <label class="form-check-label me-4" for="form-check-radio-danger">
                                                    Faculty
                                                </label>
                                                
                                                <input class="form-check-input" type="radio" name="flexRadioDefault" id="form-check-radio-red">
                                                <label class="form-check-label" for="form-check-radio-danger">
                                                    Staff
                                                </label>
                                                </div>
                                                <div class="invalid-feedback">Please choose your user type</div>
                                            </div>

                                            <div class="col-6">
                                                {{-- Department/Office --}}
                                                <label for="validationStepform-name">Department/Office</label>
                                                <select class="form-select" aria-label="Default select example">
                                                    <option selected>Open this select menu</option>
                                                    <option value="1">One</option>
                                                    <option value="2">Two</option>
                                                    <option value="3">Three</option>
                                                </select>
                                                <div class="invalid-feedback">Please enter your Department/Office</div>
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
                                            <h5 class="black-text">John Rex</h5>
                                        </div>

                                        <div class="mb-3">
                                            <h6 class="mb-0">Last Name</h6>
                                            <h5 class="black-text">Duran</h5>
                                        </div>

                                        <div class="mb-3">
                                            <h6 class="mb-0">TUP Email</h6>
                                            <h5 class="black-text">jay@example.com</h5>
                                        </div>

                                        <div>
                                            <h6 class="mb-0">Password</h6>
                                            <h5 class="black-text">password</h5>
                                        </div>
                                    </div>

                                    <div class="col-6">
                                        <div class="mb-3">
                                            <h6 class="mb-0">Middle Name</h6>
                                            <h5 class="black-text">Bautista</h5>
                                        </div>

                                        <div class="mb-3">
                                            <h6 class="mb-0">Suffix</h6>
                                            <h5 class="black-text">N/A</h5>
                                        </div>

                                        <div class="mb-3">
                                            <h6 class="mb-0">TUP ID</h6>
                                            <h5 class="black-text">230265</h5>
                                        </div>

                                        <div>
                                            <h6 class="mb-0">User Type</h6>
                                            <h5 class="black-text">Developer</h5>
                                        </div>
                                    </div>

                                    <div>
                                        <div class="mb-5 mt-1">
                                            <h6 class="mb-0">Department/Office</h6>
                                            <h5 class="black-text">Basic Arts and Sciences Department</h5>
                                        </div>
                                    </div>
                                </div>

                                <div class="button-action mt-5 d-flex justify-content-center">
                                    <button class="btn btn-red btn-back me-3">Back</button>
                                    <button class="btn btn-red btn-nxt">Next</button>
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
                                            <input type="email" class="form-control opt-input text-center">
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
                                            <p class="mb-0 black-text">Didn't receive the code? <a href="javascript:void(0);" class="red-text">Resend</a></p>
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

    <!-- Page SPECIFIC js -->
    <script src="{{ asset('js/auth/register/page-specific/bsStepper.min.js') }}" ></script>
    <script src="{{ asset('js/auth/register/page-specific/custom-bsStepper.min.js') }}" ></script>
    <script src="{{ asset('js/auth/register/page-specific/2-Step-Verification.js') }}" ></script>

</body>
</html>