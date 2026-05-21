{{-- Extend the main layout that you want to use --}}
@extends('layouts.supply-layout')

{{-- Define contents to show in the layout --}}
@section('title', 'Delivery Attachment | I-TRAC')

@push('css')
    <!-- Page SPECIFIC css -->
    <link rel="stylesheet" href="{{ asset('css/supply/delivery-attachment/page-specific/custom-tree_view.css') }}">

    <!-- CUSTOM css -->
    <link rel="stylesheet" href="{{ asset('css/supply/delivery-attachment/custom-delivery-attachment.css') }}">
@endpush

@section('content')
    <div class="col-12 p-0">
        <div class="card shadow-sm border-0 mb-3 p-0">
            <div class="card-body px-0">
                <div class="d-flex justify-content-start ms-4 mb-3">
                    {{-- Back Button --}}
                    <a href="{{ route('show.procure') }}" class="me-3">
                        <img src="{{ asset('img/Back.svg') }}" width="24" height="24">
                    </a>
                    <h5 class="fw-bold red-text-2 ms-1 mb-0">Purchase Order</h5>
                </div>

                <hr class="m-0 p-0">
                <h6 class="fw-bold red-text-2 ms-4 mt-4 mb-3">Title: $po->po_title</h6>
                <div class="row g-4 ms-3">
                    <div class="col-md-6">
                        <div class="row align-items-center mb-3">
                            <div class="col-4">
                                <h6 class="mb-0 black-text fw-bold">Supplier:</h6>
                            </div>
                            <div class="col-8">
                                <h6>$po->po_supplier</h6>
                            </div>
                        </div>

                        <div class="row align-items-center mb-3">
                            <div class="col-4">
                                <h6 class="mb-0 black-text fw-bold">Address:</h6>
                            </div>
                            <div class="col-8">
                                <h6>$po->po_address</h6>
                            </div>
                        </div>

                        <div class="row align-items-center mb-3">
                            <div class="col-4">
                                <h6 class="mb-0 black-text fw-bold">Tel No.:</h6>
                            </div>
                            <div class="col-8">
                                <h6>$po->po_tele</h6>
                            </div>
                        </div>

                        <div class="row align-items-center mb-3">
                            <div class="col-4">
                                <h6 class="mb-0 black-text fw-bold">TIN:</h6>
                            </div>
                            <div class="col-8">
                                <h6>$po->po_tin</h6>
                            </div>
                        </div>

                        <div class="row align-items-center mb-3">
                            <div class="col-4">
                                <h6 class="mb-0 black-text fw-bold">Place of Delivery:</h6>
                            </div>
                            <div class="col-8">
                                <h6>$po->po_place_delivery</h6>
                            </div>
                        </div>

                        <div class="row align-items-center">
                            <div class="col-4">
                                <h6 class="mb-0 black-text fw-bold">Date of Delivery:</h6>
                            </div>
                            <div class="col-8">
                                <h6>$po->po_date_delivery</h6>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 border-start-md">
                        <div class="row align-items-center mb-3">
                            <div class="col-4">
                                <h6 class="mb-0 black-text fw-bold">P.O. No.:</h6>
                            </div>
                            <div class="col-8">
                                <h6>$po->po_no</h6>
                            </div>
                        </div>

                        <div class="row align-items-center mb-3">
                            <div class="col-4">
                                <h6 class="mb-0 black-text fw-bold">Date:</h6>
                            </div>
                            <div class="col-8">
                                <h6>$po->po_date</h6>
                            </div>
                        </div>

                        <div class="row align-items-center mb-3">
                            <div class="col-4">
                                <h6 class="mb-0 black-text fw-bold">Mode of Procurement:</h6>
                            </div>
                            <div class="col-8">
                                <h6>$po->po_mode</h6>
                            </div>
                        </div>

                        <div class="row align-items-center mb-3">
                            <div class="col-4">
                                <h6 class="mb-0 black-text fw-bold">TUP-Taguig TIN:</h6>
                            </div>
                            <div class="col-8">
                                <h6>$po->po_tuptin</h6>
                            </div>
                        </div>

                        <div class="row align-items-center mb-3">
                            <div class="col-4">
                                <h6 class="mb-0 black-text fw-bold">Delivery Term:</h6>
                            </div>
                            <div class="col-8">
                                <h6>$po->po_delivery_term</h6>
                            </div>
                        </div>

                        <div class="row align-items-center">
                            <div class="col-4">
                                <h6 class="mb-0 black-text fw-bold">Payment Term:</h6>
                            </div>
                            <div class="col-8">
                                <h6>$po->po_payment_term</h6>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <div class="row g-3">
            {{-- Treeview --}}
            <div class="col-md-3 pe-0">
                <div class="card shadow-sm border-0 mb-3">
                    <div class="card-body">
                        <h5 class="card-title">Delivery Attachments</h5>
                        <ul class="treeview folder-structure" id="treeviewFolderStructureEx">
                            <li class="tv-item tv-folder">
                                <div class="tv-header" id="folderSupplyHeading">
                                    <div class="tv-collapsible" data-bs-toggle="collapse" data-bs-target="#folderSupply"
                                        aria-expanded="true" aria-controls="folderSupply">
                                        <div class="icon">
                                            <svg xmlns="http://www.w3.org/2000/svg"
                                                class="icon icon-tabler icon-tabler-folder" width="24" height="24"
                                                viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"
                                                stroke-linecap="round" stroke-linejoin="round">
                                                <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                                <path
                                                    d="M5 4h4l3 3h7a2 2 0 0 1 2 2v8a2 2 0 0 1 -2 2h-14a2 2 0 0 1 -2 -2v-11a2 2 0 0 1 2 -2">
                                                </path>
                                            </svg>
                                        </div>
                                        <p class="title">Supply and Materials</p>
                                    </div>
                                </div>
                                <div id="folderSupply" class="treeview-collapse collapse show"
                                    aria-labelledby="folderSupplyHeading" data-bs-parent="#treeviewFolderStructureEx">
                                    <ul class="treeview">
                                        <li class="tv-item tv-file">
                                            <span class="icon">
                                                <svg xmlns="http://www.w3.org/2000/svg"
                                                    class="icon icon-tabler icon-tabler-file" width="24"
                                                    height="24" viewBox="0 0 24 24" stroke-width="2"
                                                    stroke="currentColor" fill="none" stroke-linecap="round"
                                                    stroke-linejoin="round">
                                                    <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                                    <path d="M14 3v4a1 1 0 0 0 1 1h4"></path>
                                                    <path
                                                        d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z">
                                                    </path>
                                                </svg>
                                            </span>
                                            <p>Inspection and Acceptance Report</p>
                                        </li>
                                        <li class="tv-item tv-file">
                                            <span class="icon">
                                                <svg xmlns="http://www.w3.org/2000/svg"
                                                    class="icon icon-tabler icon-tabler-file" width="24"
                                                    height="24" viewBox="0 0 24 24" stroke-width="2"
                                                    stroke="currentColor" fill="none" stroke-linecap="round"
                                                    stroke-linejoin="round">
                                                    <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                                    <path d="M14 3v4a1 1 0 0 0 1 1h4"></path>
                                                    <path
                                                        d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z">
                                                    </path>
                                                </svg>
                                            </span>
                                            <p>Requisition and Issue Slip</p>
                                        </li>
                                        <li class="tv-item tv-file">
                                            <span class="icon">
                                                <svg xmlns="http://www.w3.org/2000/svg"
                                                    class="icon icon-tabler icon-tabler-file" width="24"
                                                    height="24" viewBox="0 0 24 24" stroke-width="2"
                                                    stroke="currentColor" fill="none" stroke-linecap="round"
                                                    stroke-linejoin="round">
                                                    <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                                    <path d="M14 3v4a1 1 0 0 0 1 1h4"></path>
                                                    <path
                                                        d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z">
                                                    </path>
                                                </svg>
                                            </span>
                                            <p>Report of Supplies and Materials Issued</p>
                                        </li>
                                    </ul>
                                </div>
                            </li>

                            <li class="tv-item tv-folder">
                                <div class="tv-header" id="folderSemiExpendableHeading">
                                    <div class="tv-collapsible collapsed" data-bs-toggle="collapse"
                                        data-bs-target="#folderSemiExpendable" aria-expanded="false"
                                        aria-controls="folderSemiExpendable">
                                        <div class="icon">
                                            <svg xmlns="http://www.w3.org/2000/svg"
                                                class="icon icon-tabler icon-tabler-folder" width="24" height="24"
                                                viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                                                fill="none" stroke-linecap="round" stroke-linejoin="round">
                                                <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                                <path
                                                    d="M5 4h4l3 3h7a2 2 0 0 1 2 2v8a2 2 0 0 1 -2 2h-14a2 2 0 0 1 -2 -2v-11a2 2 0 0 1 2 -2">
                                                </path>
                                            </svg>
                                        </div>
                                        <p class="title">Semi-Expendable</p>
                                    </div>
                                </div>
                                <div id="folderSemiExpendable" class="treeview-collapse collapse"
                                    aria-labelledby="folderSemiExpendableHeading"
                                    data-bs-parent="#treeviewFolderStructureEx">
                                    <ul class="treeview">
                                        <li class="tv-item tv-file">
                                            <span class="icon">
                                                <svg xmlns="http://www.w3.org/2000/svg"
                                                    class="icon icon-tabler icon-tabler-file" width="24"
                                                    height="24" viewBox="0 0 24 24" stroke-width="2"
                                                    stroke="currentColor" fill="none" stroke-linecap="round"
                                                    stroke-linejoin="round">
                                                    <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                                    <path d="M14 3v4a1 1 0 0 0 1 1h4"></path>
                                                    <path
                                                        d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z">
                                                    </path>
                                                </svg>
                                            </span>
                                            <p>Inspection and Acceptance Report</p>
                                        </li>
                                        <li class="tv-item tv-file">
                                            <span class="icon">
                                                <svg xmlns="http://www.w3.org/2000/svg"
                                                    class="icon icon-tabler icon-tabler-file" width="24"
                                                    height="24" viewBox="0 0 24 24" stroke-width="2"
                                                    stroke="currentColor" fill="none" stroke-linecap="round"
                                                    stroke-linejoin="round">
                                                    <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                                    <path d="M14 3v4a1 1 0 0 0 1 1h4"></path>
                                                    <path
                                                        d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z">
                                                    </path>
                                                </svg>
                                            </span>
                                            <p>Requisition and Issue Slip</p>
                                        </li>
                                        <li class="tv-item tv-file">
                                            <span class="icon">
                                                <svg xmlns="http://www.w3.org/2000/svg"
                                                    class="icon icon-tabler icon-tabler-file" width="24"
                                                    height="24" viewBox="0 0 24 24" stroke-width="2"
                                                    stroke="currentColor" fill="none" stroke-linecap="round"
                                                    stroke-linejoin="round">
                                                    <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                                    <path d="M14 3v4a1 1 0 0 0 1 1h4"></path>
                                                    <path
                                                        d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z">
                                                    </path>
                                                </svg>
                                            </span>
                                            <p>Inventory Custodian Slip</p>
                                        </li>
                                        <li class="tv-item tv-file">
                                            <span class="icon">
                                                <svg xmlns="http://www.w3.org/2000/svg"
                                                    class="icon icon-tabler icon-tabler-file" width="24"
                                                    height="24" viewBox="0 0 24 24" stroke-width="2"
                                                    stroke="currentColor" fill="none" stroke-linecap="round"
                                                    stroke-linejoin="round">
                                                    <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                                    <path d="M14 3v4a1 1 0 0 0 1 1h4"></path>
                                                    <path
                                                        d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z">
                                                    </path>
                                                </svg>
                                            </span>
                                            <p>Report of Semi Expendable property issued</p>
                                        </li>
                                    </ul>
                                </div>
                            </li>

                            <li class="tv-item tv-folder">
                                <div class="tv-header" id="folderEquipmentHeading">
                                    <div class="tv-collapsible collapsed" data-bs-toggle="collapse"
                                        data-bs-target="#folderEquipment" aria-expanded="false"
                                        aria-controls="folderEquipment">
                                        <div class="icon">
                                            <svg xmlns="http://www.w3.org/2000/svg"
                                                class="icon icon-tabler icon-tabler-folder" width="24" height="24"
                                                viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                                                fill="none" stroke-linecap="round" stroke-linejoin="round">
                                                <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                                <path
                                                    d="M5 4h4l3 3h7a2 2 0 0 1 2 2v8a2 2 0 0 1 -2 2h-14a2 2 0 0 1 -2 -2v-11a2 2 0 0 1 2 -2">
                                                </path>
                                            </svg>
                                        </div>
                                        <p class="title">Equipment</p>
                                    </div>
                                </div>
                                <div id="folderEquipment" class="treeview-collapse collapse"
                                    aria-labelledby="folderEquipmentHeading" data-bs-parent="#treeviewFolderStructureEx">
                                    <ul class="treeview">
                                        <li class="tv-item tv-file">
                                            <span class="icon">
                                                <svg xmlns="http://www.w3.org/2000/svg"
                                                    class="icon icon-tabler icon-tabler-file" width="24"
                                                    height="24" viewBox="0 0 24 24" stroke-width="2"
                                                    stroke="currentColor" fill="none" stroke-linecap="round"
                                                    stroke-linejoin="round">
                                                    <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                                    <path d="M14 3v4a1 1 0 0 0 1 1h4"></path>
                                                    <path
                                                        d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z">
                                                    </path>
                                                </svg>
                                            </span>
                                            <p>Inspection and Acceptance Report</p>
                                        </li>
                                        <li class="tv-item tv-file">
                                            <span class="icon">
                                                <svg xmlns="http://www.w3.org/2000/svg"
                                                    class="icon icon-tabler icon-tabler-file" width="24"
                                                    height="24" viewBox="0 0 24 24" stroke-width="2"
                                                    stroke="currentColor" fill="none" stroke-linecap="round"
                                                    stroke-linejoin="round">
                                                    <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                                    <path d="M14 3v4a1 1 0 0 0 1 1h4"></path>
                                                    <path
                                                        d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z">
                                                    </path>
                                                </svg>
                                            </span>
                                            <p>Property Acknowledgement Receipt</p>
                                        </li>
                                    </ul>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            {{-- View File Area --}}
            {{-- <div class="col-md-9">
                <div class="card shadow-sm border-0 mb-3 h-100">
                    <div class="card-body d-flex align-items-center justify-content-center text-center">
                        <div>
                            <img src="{{ asset('img/File.svg') }}" width="200" height="200">
                            <h6>Open a file to view</h6>
                        </div>
                    </div>
                </div>
            </div> --}}

            {{-- RIS --}}
            @include('supply.pages.partials._ris')
        </div>
    </div>
@endsection

@push('js')
    <!-- Page SPECIFIC css -->

    <!-- CUSTOM css -->
    <script src="{{ asset('js/supply/delivery-attachment/custom-delivery-attachment.js') }}"></script>
    
@endpush
