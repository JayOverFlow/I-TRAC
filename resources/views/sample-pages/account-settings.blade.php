{{-- Extend the main layout that you want to use --}}
@extends('layouts.unassigned-layout')

{{-- Define contents to show in the layout --}}
@section('title', 'Account Settings | I-TRAC')

@push('css')
    <!-- Page SPECIFIC css -->
    <link rel="stylesheet" href="{{ asset('css/account-setting/page-specific/account-settings.css') }}">
    <link rel="stylesheet" href="{{ asset('css/account-setting/page-specific/tabs.css') }}">

    <!-- CUSTOM css -->
    <link rel="stylesheet" href="{{ asset('css/account-setting/custom-account-setting.css') }}">
@endpush

@section('content')
    <div class="account-settings-container layout-top-spacing">

        <div class="account-content">
            <div class="row mb-3">
                <div class="col-md-12">

                    <ul class="nav nav-pills" id="animateLine" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link red-text-2 active" id="animated-underline-profile-tab" data-bs-toggle="tab"
                                href="#animated-underline-profile" role="tab" aria-controls="animated-underline-profile"
                                aria-selected="true"><img src="{{ asset('img/Profile.svg') }}" width="20" height="20"> Profile</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link red-text-2" id="animated-underline-inbox-tab" data-bs-toggle="tab"
                                href="#animated-underline-inbox" role="tab" aria-controls="animated-underline-inbox"
                                aria-selected="false" tabindex="-1"><img src="{{ asset('img/Inbox.svg') }}" width="20" height="20"> Inbox</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link red-text-2" id="animated-underline-settings-tab" data-bs-toggle="tab"
                                href="#animated-underline-settings" role="tab"
                                aria-controls="animated-underline-settings" aria-selected="false" tabindex="-1">
                                <img src="{{ asset('img/Settings.svg') }}" width="20" height="20"> 
                                Settings</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link red-text-2" id="animated-underline-annual-procurement-plan-tab" data-bs-toggle="tab"
                                href="#animated-underline-annual-procurement-plan" role="tab" aria-controls="animated-underline-annual-procurement-plan"
                                aria-selected="false" tabindex="-1">
                                <img src="{{ asset('img/Settings.svg') }}" width="20" height="20">
                                 Annual Procurement Plan</button>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="tab-content" id="animateLineContent-4">
                {{-- Profile Tab --}}
                <div class="tab-pane fade show active" id="animated-underline-profile" role="tabpanel"
                    aria-labelledby="animated-underline-profile-tab">
                    <div class="row">
                        <div class="col-xl-12 col-lg-12 col-md-12 layout-spacing">
                            <form class="section general-info">
                                <div class="info">
                                    <h6 class="">General Information</h6>
                                    <div class="row">
                                        <div class="col-lg-11 mx-auto">
                                            <div class="row">
                                                <div class="col-xl-2 col-lg-12 col-md-4">
                                                    <div class="profile-image  mt-4 pe-md-4">

                                                        <!-- // The classic file input element we'll enhance
                                                                            // to a file pond, we moved the configuration
                                                                            // properties to JavaScript -->

                                                        <div class="img-uploader-content">
                                                            <input type="file" class="filepond" name="filepond"
                                                                accept="image/png, image/jpeg, image/gif" />
                                                        </div>

                                                    </div>
                                                </div>
                                                <div class="col-xl-10 col-lg-12 col-md-8 mt-md-0 mt-4">
                                                    <div class="form">
                                                        <div class="row">
                                                            <div class="col-md-6">
                                                                <div class="form-group">
                                                                    <label for="fullName">Full Name</label>
                                                                    <input type="text" class="form-control mb-3"
                                                                        id="fullName" placeholder="Full Name"
                                                                        value="Jimmy Turner">
                                                                </div>
                                                            </div>

                                                            <div class="col-md-6">
                                                                <div class="form-group">
                                                                    <label for="profession">Profession</label>
                                                                    <input type="text" class="form-control mb-3"
                                                                        id="profession" placeholder="Designer"
                                                                        value="Web Developer">
                                                                </div>
                                                            </div>

                                                            <div class="col-md-6">
                                                                <div class="form-group">
                                                                    <label for="country">Country</label>
                                                                    <select class="form-select mb-3" id="country">
                                                                        <option>All Countries</option>
                                                                        <option selected>United States</option>
                                                                        <option>India</option>
                                                                        <option>Japan</option>
                                                                        <option>China</option>
                                                                        <option>Brazil</option>
                                                                        <option>Norway</option>
                                                                        <option>Canada</option>
                                                                    </select>
                                                                </div>
                                                            </div>

                                                            <div class="col-md-6">
                                                                <div class="form-group">
                                                                    <label for="address">Address</label>
                                                                    <input type="text" class="form-control mb-3"
                                                                        id="address" placeholder="Address"
                                                                        value="New York">
                                                                </div>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <div class="form-group">
                                                                    <label for="location">Location</label>
                                                                    <input type="text" class="form-control mb-3"
                                                                        id="location" placeholder="Location">
                                                                </div>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <div class="form-group">
                                                                    <label for="phone">Phone</label>
                                                                    <input type="text" class="form-control mb-3"
                                                                        id="phone"
                                                                        placeholder="Write your phone number here"
                                                                        value="+1 (530) 555-12121">
                                                                </div>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <div class="form-group">
                                                                    <label for="email">Email</label>
                                                                    <input type="text" class="form-control mb-3"
                                                                        id="email" placeholder="Write your email here"
                                                                        value="Jimmy@gmail.com">
                                                                </div>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <div class="form-group">
                                                                    <label for="website1">Website</label>
                                                                    <input type="text" class="form-control mb-3"
                                                                        id="website1" placeholder="Enter URL">
                                                                </div>
                                                            </div>

                                                            <div class="col-md-12 mt-1">
                                                                <div class="form-check">
                                                                    <input class="form-check-input" type="checkbox"
                                                                        value="" id="customCheck1">
                                                                    <label class="form-check-label"
                                                                        for="customCheck1">Make this my default
                                                                        address</label>
                                                                </div>
                                                            </div>

                                                            <div class="col-md-12 mt-1">
                                                                <div class="form-group text-end">
                                                                    <button class="btn btn-secondary">Save</button>
                                                                </div>
                                                            </div>

                                                        </div>

                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>

                        <div class="col-xl-12 col-lg-12 col-md-12 layout-spacing">
                            <form id="social" class="section social">
                                <div class="info">
                                    <h5 class="">Social</h5>
                                    <div class="row">

                                        <div class="col-md-11 mx-auto">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="input-group social-linkedin mb-3">
                                                        <span class="input-group-text me-3" id="linkedin"><svg
                                                                xmlns="http://www.w3.org/2000/svg" width="24"
                                                                height="24" viewBox="0 0 24 24" fill="none"
                                                                stroke="currentColor" stroke-width="2"
                                                                stroke-linecap="round" stroke-linejoin="round"
                                                                class="feather feather-linkedin">
                                                                <path
                                                                    d="M16 8a6 6 0 0 1 6 6v7h-4v-7a2 2 0 0 0-2-2 2 2 0 0 0-2 2v7h-4v-7a6 6 0 0 1 6-6z">
                                                                </path>
                                                                <rect x="2" y="9" width="4" height="12"></rect>
                                                                <circle cx="4" cy="4" r="2"></circle>
                                                            </svg></span>
                                                        <input type="text" class="form-control"
                                                            placeholder="Linkedin Username" aria-label="Username"
                                                            aria-describedby="linkedin" value="jimmy_turner">
                                                    </div>
                                                </div>

                                                <div class="col-md-6">
                                                    <div class="input-group social-tweet mb-3">
                                                        <span class="input-group-text me-3" id="tweet"><svg
                                                                xmlns="http://www.w3.org/2000/svg" width="24"
                                                                height="24" viewBox="0 0 24 24" fill="none"
                                                                stroke="currentColor" stroke-width="2"
                                                                stroke-linecap="round" stroke-linejoin="round"
                                                                class="feather feather-twitter">
                                                                <path
                                                                    d="M23 3a10.9 10.9 0 0 1-3.14 1.53 4.48 4.48 0 0 0-7.86 3v1A10.66 10.66 0 0 1 3 4s-4 9 5 13a11.64 11.64 0 0 1-7 2c9 5 20 0 20-11.5a4.5 4.5 0 0 0-.08-.83A7.72 7.72 0 0 0 23 3z">
                                                                </path>
                                                            </svg></span>
                                                        <input type="text" class="form-control"
                                                            placeholder="Twitter Username" aria-label="Username"
                                                            aria-describedby="tweet" value="@jTurner">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-11 mx-auto">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="input-group social-fb mb-3">
                                                        <span class="input-group-text me-3" id="fb"><svg
                                                                xmlns="http://www.w3.org/2000/svg" width="24"
                                                                height="24" viewBox="0 0 24 24" fill="none"
                                                                stroke="currentColor" stroke-width="2"
                                                                stroke-linecap="round" stroke-linejoin="round"
                                                                class="feather feather-facebook">
                                                                <path
                                                                    d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z">
                                                                </path>
                                                            </svg></span>
                                                        <input type="text" class="form-control"
                                                            placeholder="Facebook Username" aria-label="Username"
                                                            aria-describedby="fb" value="Jimmy Turner">
                                                    </div>
                                                </div>

                                                <div class="col-md-6">
                                                    <div class="input-group social-github mb-3">
                                                        <span class="input-group-text me-3" id="github"><svg
                                                                xmlns="http://www.w3.org/2000/svg" width="24"
                                                                height="24" viewBox="0 0 24 24" fill="none"
                                                                stroke="currentColor" stroke-width="2"
                                                                stroke-linecap="round" stroke-linejoin="round"
                                                                class="feather feather-github">
                                                                <path
                                                                    d="M9 19c-5 1.5-5-2.5-7-3m14 6v-3.87a3.37 3.37 0 0 0-.94-2.61c3.14-.35 6.44-1.54 6.44-7A5.44 5.44 0 0 0 20 4.77 5.07 5.07 0 0 0 19.91 1S18.73.65 16 2.48a13.38 13.38 0 0 0-7 0C6.27.65 5.09 1 5.09 1A5.07 5.07 0 0 0 5 4.77a5.44 5.44 0 0 0-1.5 3.78c0 5.42 3.3 6.61 6.44 7A3.37 3.37 0 0 0 9 18.13V22">
                                                                </path>
                                                            </svg></span>
                                                        <input type="text" class="form-control"
                                                            placeholder="Github Username" aria-label="Username"
                                                            aria-describedby="github" value="@TurnerJimmy">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                {{-- End Profile Tab --}}

                {{-- Inbox Tab --}}
                <div class="tab-pane fade" id="animated-underline-inbox" role="tabpanel"
                    aria-labelledby="animated-underline-inbox-tab">
                    <div class="row">
                        <div class="col-xl-6 col-lg-12 col-md-12 layout-spacing">
                            <div class="section general-info payment-info">
                                <div class="info">
                                    <h6 class="">Billing Address</h6>
                                    <p>Changes to your <span class="text-success">Billing</span> information will take
                                        effect starting with scheduled payment and will be refelected on your next invoice.
                                    </p>

                                    <div class="list-group mt-4">
                                        <label class="list-group-item">
                                            <div class="d-flex w-100">
                                                <div class="billing-radio me-2">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="radio"
                                                            name="billingAddress" id="billingAddress1" checked>
                                                    </div>
                                                </div>
                                                <div class="billing-content">
                                                    <div class="fw-bold">Address #1</div>
                                                    <p>2249 Caynor Circle, New Brunswick, New Jersey</p>
                                                </div>
                                                <div class="billing-edit align-self-center ms-auto">
                                                    <button class="btn btn-dark">Edit</button>
                                                </div>
                                            </div>
                                        </label>

                                        <label class="list-group-item">
                                            <div class="d-flex w-100">
                                                <div class="billing-radio me-2">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="radio"
                                                            name="billingAddress" id="billingAddress2">
                                                    </div>
                                                </div>
                                                <div class="billing-content">
                                                    <div class="fw-bold">Address #2</div>
                                                    <p>4262 Leverton Cove Road, Springfield, Massachusetts</p>
                                                </div>
                                                <div class="billing-edit align-self-center ms-auto">
                                                    <button class="btn btn-dark">Edit</button>
                                                </div>
                                            </div>
                                        </label>
                                        <label class="list-group-item">
                                            <div class="d-flex w-100">
                                                <div class="billing-radio me-2">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="radio"
                                                            name="billingAddress" id="billingAddress3">
                                                    </div>
                                                </div>
                                                <div class="billing-content">
                                                    <div class="fw-bold">Address #3</div>
                                                    <p>2692 Berkshire Circle, Knoxville, Tennessee</p>
                                                </div>
                                                <div class="billing-edit align-self-center ms-auto">
                                                    <button class="btn btn-dark">Edit</button>
                                                </div>
                                            </div>
                                        </label>
                                    </div>

                                    <button class="btn btn-secondary mt-4 add-address">Add Address</button>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-6 col-lg-12 col-md-12 layout-spacing">
                            <div class="section general-info payment-info">
                                <div class="info">
                                    <h6 class="">Payment Method</h6>
                                    <p>Changes to your <span class="text-success">Payment Method</span> information will
                                        take effect starting with scheduled payment and will be refelected on your next
                                        invoice.</p>

                                    <div class="list-group mt-4">

                                        <label class="list-group-item">
                                            <div class="d-flex w-100">
                                                <div class="billing-radio me-2">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="radio"
                                                            name="paymentMethod" id="paymentMethod1">
                                                    </div>
                                                </div>
                                                <div class="payment-card">
                                                </div>
                                                <div class="billing-content">
                                                    <div class="fw-bold">Mastercard</div>
                                                    <p>XXXX XXXX XXXX 9704</p>
                                                </div>
                                                <div class="billing-edit align-self-center ms-auto">
                                                    <button class="btn btn-dark">Edit</button>
                                                </div>
                                            </div>
                                        </label>
                                        <label class="list-group-item">
                                            <div class="d-flex w-100">
                                                <div class="billing-radio me-2">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="radio"
                                                            name="paymentMethod" id="paymentMethod2" checked>
                                                    </div>
                                                </div>
                                                <div class="payment-card">
                                                </div>
                                                <div class="billing-content">
                                                    <div class="fw-bold">American Express</div>
                                                    <p>XXXX XXXX XXXX 310</p>
                                                </div>
                                                <div class="billing-edit align-self-center ms-auto">
                                                    <button class="btn btn-dark">Edit</button>
                                                </div>
                                            </div>
                                        </label>
                                        <label class="list-group-item">
                                            <div class="d-flex w-100">
                                                <div class="billing-radio me-2">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="radio"
                                                            name="paymentMethod" id="paymentMethod3">
                                                    </div>
                                                </div>
                                                <div class="payment-card">
                                                </div>
                                                <div class="billing-content">
                                                    <div class="fw-bold">Visa</div>
                                                    <p>XXXX XXXX XXXX 5264</p>
                                                </div>
                                                <div class="billing-edit align-self-center ms-auto">
                                                    <button class="btn btn-dark">Edit</button>
                                                </div>
                                            </div>
                                        </label>
                                    </div>

                                    <button class="btn btn-secondary mt-4 add-payment">Add Payment Method</button>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-6 col-lg-12 col-md-12 layout-spacing">
                            <div class="section general-info payment-info">
                                <div class="info">
                                    <h6 class="">Add Billing Address</h6>
                                    <p>Changes your New <span class="text-success">Billing</span> Information.</p>

                                    <div class="row mt-4">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Name</label>
                                                <input type="text" class="form-control add-billing-address-input">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Email</label>
                                                <input type="email" class="form-control">
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <div class="mb-3">
                                                <label class="form-label">Address</label>
                                                <input type="text" class="form-control">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label class="form-label">City</label>
                                                <input type="text" class="form-control">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label class="form-label">Country</label>
                                                <select class="form-select">
                                                    <option selected="">Choose...</option>
                                                    <option value="united-states">United States</option>
                                                    <option value="brazil">Brazil</option>
                                                    <option value="indonesia">Indonesia</option>
                                                    <option value="turkey">Turkey</option>
                                                    <option value="russia">Russia</option>
                                                    <option value="india">India</option>
                                                    <option value="germany">Germany</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label class="form-label">ZIP</label>
                                                <input type="tel" class="form-control">
                                            </div>
                                        </div>
                                    </div>

                                    <button class="btn btn-primary mt-4">Add</button>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-6 col-lg-12 col-md-12 layout-spacing">
                            <div class="section general-info payment-info">
                                <div class="info">
                                    <h6 class="">Add Payment Method</h6>
                                    <p>Changes your New <span class="text-success">Payment Method</span> Information.</p>

                                    <div class="row mt-4">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Card Brand</label>
                                                <div class="invoice-action-currency">
                                                    <div class="dropdown selectable-dropdown cardName-select">
                                                        <a id="cardBrandDropdown" href="javascript:void(0);"
                                                            class="dropdown-toggle" data-bs-toggle="dropdown"
                                                            aria-haspopup="true" aria-expanded="false"><svg
                                                                    xmlns="http://www.w3.org/2000/svg" width="24"
                                                                    height="24" viewBox="0 0 24 24" fill="none"
                                                                    stroke="currentColor" stroke-width="2"
                                                                    stroke-linecap="round" stroke-linejoin="round"
                                                                    class="feather feather-chevron-down">
                                                                    <polyline points="6 9 12 15 18 9"></polyline>
                                                                </svg></span></a>
                                                        <div class="dropdown-menu" aria-labelledby="cardBrandDropdown">
                                                            <a class="dropdown-item"
                                                                data-img-value="../src/assets/img/card-mastercard.svg"
                                                                data-value="GBP - British Pound"
                                                                href="javascript:void(0);"> Mastercard</a>
                                                            <a class="dropdown-item"
                                                                data-img-value="../src/assets/img/card-americanexpress.svg"
                                                                data-value="IDR - Indonesian Rupiah"
                                                                href="javascript:void(0);"> American
                                                                Express</a>
                                                            <a class="dropdown-item"
                                                                data-img-value="../src/assets/img/card-visa.svg"
                                                                data-value="USD - US Dollar"
                                                                href="javascript:void(0);"> Visa</a>
                                                            <a class="dropdown-item"
                                                                data-img-value="../src/assets/img/card-discover.svg"
                                                                data-value="INR - Indian Rupee"
                                                                href="javascript:void(0);"> Discover</a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Card Number</label>
                                                <input type="text" class="form-control add-payment-method-input">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Holder Name</label>
                                                <input type="text" class="form-control">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">CVV/CVV2</label>
                                                <input type="text" class="form-control">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Card Expiry</label>
                                                <input type="text" class="form-control">
                                            </div>
                                        </div>
                                    </div>

                                    <button class="btn btn-primary mt-4">Add</button>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
                {{-- End Inbox Tab --}}

                {{-- Settings Tab --}}
                <div class="tab-pane fade" id="animated-underline-settings" role="tabpanel"
                    aria-labelledby="animated-underline-settings-tab">
                    <div class="row">
                        <div class="col-xl-6 col-lg-12 col-md-12 layout-spacing">
                            <div class="section general-info">
                                <div class="info">
                                    <h6 class="">Choose Theme</h6>
                                    <div class="d-sm-flex justify-content-around">
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="flexRadioDefault"
                                                id="flexRadioDefault1" checked>
                                            <label class="form-check-label" for="flexRadioDefault1">
                                                
                                            </label>
                                        </div>

                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="flexRadioDefault"
                                                id="flexRadioDefault2">
                                            <label class="form-check-label" for="flexRadioDefault2">
                                                
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-6 col-lg-12 col-md-12 layout-spacing">
                            <div class="section general-info">
                                <div class="info">
                                    <h6 class="">Activity data</h6>
                                    <p>Download your Summary, Task and Payment History Data</p>
                                    <div class="form-group mt-4">
                                        <button class="btn btn-primary">Download Data</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-4 col-lg-12 col-md-12 layout-spacing">
                            <div class="section general-info">
                                <div class="info">
                                    <h6 class="">Public Profile</h6>
                                    <p>Your <span class="text-success">Profile</span> will be visible to anyone on the
                                        network.</p>
                                    <div class="form-group mt-4">
                                        <div class="switch form-switch-custom switch-inline form-switch-secondary mt-1">
                                            <input class="switch-input" type="checkbox" role="switch"
                                                id="publicProfile" checked>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-4 col-lg-12 col-md-12 layout-spacing">
                            <div class="section general-info">
                                <div class="info">
                                    <h6 class="">Show my email</h6>
                                    <p>Your <span class="text-success">Email</span> will be visible to anyone on the
                                        network.</p>
                                    <div class="form-group mt-4">
                                        <div class="switch form-switch-custom switch-inline form-switch-secondary mt-1">
                                            <input class="switch-input" type="checkbox" role="switch" id="showMyEmail">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-4 col-lg-12 col-md-12 layout-spacing">
                            <div class="section general-info">
                                <div class="info">
                                    <h6 class="">Enable keyboard shortcuts</h6>
                                    <p>When enabled, press <code class="text-success">ctrl</code> for help</p>
                                    <div class="form-group mt-4">
                                        <div class="switch form-switch-custom switch-inline form-switch-secondary mt-1">
                                            <input class="switch-input" type="checkbox" role="switch"
                                                id="EnableKeyboardShortcut">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-4 col-lg-12 col-md-12 layout-spacing">
                            <div class="section general-info">
                                <div class="info">
                                    <h6 class="">Hide left navigation</h6>
                                    <p>Sidebar will be <span class="text-success">hidden</span> by default</p>
                                    <div class="form-group mt-4">
                                        <div class="switch form-switch-custom switch-inline form-switch-secondary mt-1">
                                            <input class="switch-input" type="checkbox" role="switch"
                                                id="hideLeftNavigation">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-4 col-lg-12 col-md-12 layout-spacing">
                            <div class="section general-info">
                                <div class="info">
                                    <h6 class="">Advertisements</h6>
                                    <p>Display <span class="text-success">Ads</span> on your dashboard</p>
                                    <div class="form-group mt-4">
                                        <div class="switch form-switch-custom switch-inline form-switch-secondary mt-1">
                                            <input class="switch-input" type="checkbox" role="switch"
                                                id="advertisements">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-4 col-lg-12 col-md-12 layout-spacing">
                            <div class="section general-info">
                                <div class="info">
                                    <h6 class="">Social Profile</h6>
                                    <p>Enable your <span class="text-success">social</span> profiles on this network</p>
                                    <div class="form-group mt-4">
                                        <div class="switch form-switch-custom switch-inline form-switch-secondary mt-1">
                                            <input class="switch-input" type="checkbox" role="switch"
                                                id="socialprofile" checked>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                {{-- End Settings Tab --}}

                {{-- Annual Procurement Plan Tab --}}
                <div class="tab-pane fade" id="animated-underline-annual-procurement-plan" role="tabpanel"
                    aria-labelledby="animated-underline-annual-procurement-plan-tab">
                    <div>
                        Data table for annual procurement plan
                    </div>
                </div>
                {{-- End Annual Procurement Plan Tab --}}
            </div>

        </div>

    </div>
@endsection

@push('js')
    <!-- Page SPECIFIC js -->
    {{-- <script src="{{ asset('js/account-setting/page-specific/account-settings.js') }}"></script>  --}} {{-- Not needed for now --}}

    <!-- CUSTOM js -->
    {{-- <script src="{{ asset('js/account-setting/custom-account-setting.js') }}"></script> --}}
@endpush
