{{-- Extend the main layout that you want to use --}}
@extends('layouts.supply-layout')

{{-- Define contents to show in the layout --}}
@section('title', 'Delivery Attachment | I-TRAC')

@push('css')
    <!-- Page SPECIFIC css -->
    <link rel="stylesheet" href="{{ asset('plugins/src/flatpickr/flatpickr.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/css/light/flatpickr/custom-flatpickr.css') }}">
    <link rel="stylesheet" href="{{ asset('css/supply/delivery-attachment/page-specific/custom-tree_view.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/css/light/loaders/custom-loader.css') }}">

    <!-- CUSTOM css -->
    <link rel="stylesheet" href="{{ asset('css/supply/delivery-attachment/custom-delivery-attachment.css') }}">
    <link rel="stylesheet" href="{{ asset('css/supply/delivery-attachment/partials/iar.css') }}">
    <link rel="stylesheet" href="{{ asset('css/supply/delivery-attachment/partials/ris.css') }}">
    <link rel="stylesheet" href="{{ asset('css/supply/delivery-attachment/partials/rsmi.css') }}">
    <link rel="stylesheet" href="{{ asset('css/supply/delivery-attachment/partials/ics.css') }}">
    <link rel="stylesheet" href="{{ asset('css/supply/delivery-attachment/partials/rspi.css') }}">
    <link rel="stylesheet" href="{{ asset('css/supply/delivery-attachment/partials/par.css') }}">
@endpush

@php
    // Group IARs by category based on their items
    $supplyIars = $po->iarReports->filter(function($iar) {
        $firstItem = $iar->iarItems->first();
        return $firstItem && $firstItem->poItem && $firstItem->poItem->po_items_category === 'Supply and Materials';
    });

    $semiExpendableIars = $po->iarReports->filter(function($iar) {
        $firstItem = $iar->iarItems->first();
        return $firstItem && $firstItem->poItem && $firstItem->poItem->po_items_category === 'Semi-Expendable';
    });

    $equipmentIars = $po->iarReports->filter(function($iar) {
        $firstItem = $iar->iarItems->first();
        return $firstItem && $firstItem->poItem && $firstItem->poItem->po_items_category === 'Equipment';
    });

    // Group RISs by category based on their items
    $supplyRiss = $po->risSlips->filter(function($ris) {
        $firstItem = $ris->risItems->first();
        return $firstItem && $firstItem->poItem && $firstItem->poItem->po_items_category === 'Supply and Materials';
    });

    $semiExpendableRiss = $po->risSlips->filter(function($ris) {
        $firstItem = $ris->risItems->first();
        return $firstItem && $firstItem->poItem && $firstItem->poItem->po_items_category === 'Semi-Expendable';
    });

    $transferIcsSlips = $po->icsSlips->where('is_transfer', 1);
    $transferParReceipts = $po->parReceipts->where('is_transfer', 1);
    $hasTransfers = $transferIcsSlips->isNotEmpty() || $transferParReceipts->isNotEmpty();

    // Check if each folder has contents
    $hasSupply = $supplyIars->isNotEmpty() || $supplyRiss->isNotEmpty() || $po->rsmiReports->isNotEmpty();
    $hasSemiExpendable = $semiExpendableIars->isNotEmpty() || $semiExpendableRiss->isNotEmpty() || $po->icsSlips->where('is_transfer', 0)->isNotEmpty() || $po->rspiReports->isNotEmpty();
    $hasEquipment = $equipmentIars->isNotEmpty() || $po->parReceipts->where('is_transfer', 0)->isNotEmpty();
    $hasNotDelivered = $po->ndrReports->isNotEmpty();

    // Determine the first non-empty folder to expand by default
    $firstOpenFolder = null;
    if ($hasSupply) {
        $firstOpenFolder = 'supply';
    } elseif ($hasSemiExpendable) {
        $firstOpenFolder = 'semi-expendable';
    } elseif ($hasEquipment) {
        $firstOpenFolder = 'equipment';
    } elseif ($hasNotDelivered) {
        $firstOpenFolder = 'not-delivered';
    } elseif ($hasTransfers) {
        $firstOpenFolder = 'transfers';
    }
@endphp

@section('content')
    @include('partials._loader')
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
                <h6 class="fw-bold red-text-2 ms-4 mt-4 mb-3">Title: {{ $po->po_title }}</h6>
                <div class="row g-4 ms-3">
                    <div class="col-md-6">
                        <div class="row align-items-center mb-3">
                            <div class="col-4">
                                <h6 class="mb-0 black-text fw-bold">Supplier:</h6>
                            </div>
                            <div class="col-8">
                                <h6>{{ $po->po_supplier }}</h6>
                            </div>
                        </div>

                        <div class="row align-items-center mb-3">
                            <div class="col-4">
                                <h6 class="mb-0 black-text fw-bold">Address:</h6>
                            </div>
                            <div class="col-8">
                                <h6>{{ $po->po_address }}</h6>
                            </div>
                        </div>

                        <div class="row align-items-center mb-3">
                            <div class="col-4">
                                <h6 class="mb-0 black-text fw-bold">Tel No.:</h6>
                            </div>
                            <div class="col-8">
                                <h6>{{ $po->po_tele }}</h6>
                            </div>
                        </div>

                        <div class="row align-items-center mb-3">
                            <div class="col-4">
                                <h6 class="mb-0 black-text fw-bold">TIN:</h6>
                            </div>
                            <div class="col-8">
                                <h6>{{ $po->po_tin }}</h6>
                            </div>
                        </div>

                        <div class="row align-items-center mb-3">
                            <div class="col-4">
                                <h6 class="mb-0 black-text fw-bold">Place of Delivery:</h6>
                            </div>
                            <div class="col-8">
                                <h6>{{ $po->po_place_delivery }}</h6>
                            </div>
                        </div>

                        <div class="row align-items-center">
                            <div class="col-4">
                                <h6 class="mb-0 black-text fw-bold">Date of Delivery:</h6>
                            </div>
                            <div class="col-8">
                                <h6>{{ $po->po_date_delivery }}</h6>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 border-start-md">
                        <div class="row align-items-center mb-3">
                            <div class="col-4">
                                <h6 class="mb-0 black-text fw-bold">P.O. No.:</h6>
                            </div>
                            <div class="col-8">
                                <h6>{{ $po->po_no }}</h6>
                            </div>
                        </div>

                        <div class="row align-items-center mb-3">
                            <div class="col-4">
                                <h6 class="mb-0 black-text fw-bold">Date:</h6>
                            </div>
                            <div class="col-8">
                                <h6>{{ $po->po_date }}</h6>
                            </div>
                        </div>

                        <div class="row align-items-center mb-3">
                            <div class="col-4">
                                <h6 class="mb-0 black-text fw-bold">Mode of Procurement:</h6>
                            </div>
                            <div class="col-8">
                                <h6>{{ $po->po_mode }}</h6>
                            </div>
                        </div>

                        <div class="row align-items-center mb-3">
                            <div class="col-4">
                                <h6 class="mb-0 black-text fw-bold">TUP-Taguig TIN:</h6>
                            </div>
                            <div class="col-8">
                                <h6>{{ $po->po_tuptin }}</h6>
                            </div>
                        </div>

                        <div class="row align-items-center mb-3">
                            <div class="col-4">
                                <h6 class="mb-0 black-text fw-bold">Delivery Term:</h6>
                            </div>
                            <div class="col-8">
                                <h6>{{ $po->po_delivery_term }}</h6>
                            </div>
                        </div>

                        <div class="row align-items-center">
                            <div class="col-4">
                                <h6 class="mb-0 black-text fw-bold">Payment Term:</h6>
                            </div>
                            <div class="col-8">
                                <h6>{{ $po->po_payment_term }}</h6>
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
                        <ul class="treeview folder-structure" id="treeviewFolderStructureEx" data-active-document="{{ session('active_document') }}">
                            {{-- Supply and Materials Folder --}}
                            @if($hasSupply)
                            <li class="tv-item tv-folder">
                                <div class="tv-header" id="folderSupplyHeading">
                                    <div class="tv-collapsible {{ $firstOpenFolder === 'supply' ? '' : 'collapsed' }}" data-bs-toggle="collapse" data-bs-target="#folderSupply"
                                        aria-expanded="{{ $firstOpenFolder === 'supply' ? 'true' : 'false' }}" aria-controls="folderSupply">
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
                                <div id="folderSupply" class="treeview-collapse collapse {{ $firstOpenFolder === 'supply' ? 'show' : '' }}"
                                    aria-labelledby="folderSupplyHeading" data-bs-parent="#treeviewFolderStructureEx">
                                    <ul class="treeview">
                                        @foreach($supplyIars as $iar)
                                            <li class="tv-item tv-file document-node" data-target="doc-iar-{{ $iar->iar_id }}">
                                                <span class="icon">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-file" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                                        <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                                        <path d="M14 3v4a1 1 0 0 0 1 1h4"></path>
                                                        <path d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z"></path>
                                                    </svg>
                                                </span>
                                                <p>IAR</p>
                                            </li>
                                        @endforeach

                                         @foreach($supplyRiss as $ris)
                                            <li class="tv-item tv-file document-node" data-target="doc-ris-{{ $ris->ris_id }}">
                                                <span class="icon">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-file" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                                        <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                                        <path d="M14 3v4a1 1 0 0 0 1 1h4"></path>
                                                        <path d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z"></path>
                                                    </svg>
                                                </span>
                                                <p>RIS - {{ $ris->ris_office }}</p>
                                            </li>
                                        @endforeach

                                        @foreach($po->rsmiReports as $rsmi)
                                            <li class="tv-item tv-file document-node" data-target="doc-rsmi-{{ $rsmi->rsmi_id }}">
                                                <span class="icon">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-file" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                                        <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                                        <path d="M14 3v4a1 1 0 0 0 1 1h4"></path>
                                                        <path d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z"></path>
                                                    </svg>
                                                </span>
                                                <p>RSMI</p>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            </li>
                            @endif

                            {{-- Semi-Expendable Folder --}}
                            @if($hasSemiExpendable)
                            <li class="tv-item tv-folder">
                                <div class="tv-header" id="folderSemiExpendableHeading">
                                    <div class="tv-collapsible {{ $firstOpenFolder === 'semi-expendable' ? '' : 'collapsed' }}" data-bs-toggle="collapse"
                                        data-bs-target="#folderSemiExpendable" aria-expanded="{{ $firstOpenFolder === 'semi-expendable' ? 'true' : 'false' }}"
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
                                <div id="folderSemiExpendable" class="treeview-collapse collapse {{ $firstOpenFolder === 'semi-expendable' ? 'show' : '' }}"
                                    aria-labelledby="folderSemiExpendableHeading"
                                    data-bs-parent="#treeviewFolderStructureEx">
                                    <ul class="treeview">
                                        @foreach($semiExpendableIars as $iar)
                                            <li class="tv-item tv-file document-node" data-target="doc-iar-{{ $iar->iar_id }}">
                                                <span class="icon">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-file" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                                        <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                                        <path d="M14 3v4a1 1 0 0 0 1 1h4"></path>
                                                        <path d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z"></path>
                                                    </svg>
                                                </span>
                                                <p>IAR</p>
                                            </li>
                                        @endforeach

                                         @foreach($semiExpendableRiss as $ris)
                                            <li class="tv-item tv-file document-node" data-target="doc-ris-{{ $ris->ris_id }}">
                                                <span class="icon">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-file" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                                        <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                                        <path d="M14 3v4a1 1 0 0 0 1 1h4"></path>
                                                        <path d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z"></path>
                                                    </svg>
                                                </span>
                                                <p>RIS - {{ $ris->receiver->user_fullname ?? 'User' }}</p>
                                            </li>
                                        @endforeach

                                        @foreach($po->icsSlips->where('is_transfer', 0) as $ics)
                                            <li class="tv-item tv-file document-node" data-target="doc-ics-{{ $ics->ics_id }}">
                                                <span class="icon">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-file" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                                        <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                                        <path d="M14 3v4a1 1 0 0 0 1 1h4"></path>
                                                        <path d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z"></path>
                                                    </svg>
                                                </span>
                                                <p>ICS - {{ $ics->receiver->user_fullname ?? 'User' }}</p>
                                            </li>
                                        @endforeach

                                         @foreach($po->rspiReports as $rspi)
                                            <li class="tv-item tv-file document-node" data-target="doc-rspi-{{ $rspi->rspi_id }}">
                                                <span class="icon">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-file" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                                        <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                                        <path d="M14 3v4a1 1 0 0 0 1 1h4"></path>
                                                        <path d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z"></path>
                                                    </svg>
                                                </span>
                                                <p>RSPI</p>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            </li>
                            @endif

                            {{-- Equipment Folder --}}
                            @if($hasEquipment)
                            <li class="tv-item tv-folder">
                                <div class="tv-header" id="folderEquipmentHeading">
                                    <div class="tv-collapsible {{ $firstOpenFolder === 'equipment' ? '' : 'collapsed' }}" data-bs-toggle="collapse"
                                        data-bs-target="#folderEquipment" aria-expanded="{{ $firstOpenFolder === 'equipment' ? 'true' : 'false' }}"
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
                                <div id="folderEquipment" class="treeview-collapse collapse {{ $firstOpenFolder === 'equipment' ? 'show' : '' }}"
                                    aria-labelledby="folderEquipmentHeading" data-bs-parent="#treeviewFolderStructureEx">
                                    <ul class="treeview">
                                        @foreach($equipmentIars as $iar)
                                            <li class="tv-item tv-file document-node" data-target="doc-iar-{{ $iar->iar_id }}">
                                                <span class="icon">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-file" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                                        <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                                        <path d="M14 3v4a1 1 0 0 0 1 1h4"></path>
                                                        <path d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z"></path>
                                                    </svg>
                                                </span>
                                                <p>IAR</p>
                                            </li>
                                        @endforeach

                                        @foreach($po->parReceipts->where('is_transfer', 0) as $par)
                                            <li class="tv-item tv-file document-node" data-target="doc-par-{{ $par->par_id }}">
                                                <span class="icon">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-file" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                                        <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                                        <path d="M14 3v4a1 1 0 0 0 1 1h4"></path>
                                                        <path d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z"></path>
                                                    </svg>
                                                </span>
                                                <p>PAR - {{ $par->receiver->user_fullname ?? 'User' }}</p>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            </li>
                            @endif

                            {{-- Not Delivered Folder --}}
                            @if($hasNotDelivered)
                            <li class="tv-item tv-folder">
                                <div class="tv-header" id="folderNotDeliveredHeading">
                                    <div class="tv-collapsible {{ $firstOpenFolder === 'not-delivered' ? '' : 'collapsed' }}" data-bs-toggle="collapse"
                                        data-bs-target="#folderNotDelivered" aria-expanded="{{ $firstOpenFolder === 'not-delivered' ? 'true' : 'false' }}"
                                        aria-controls="folderNotDelivered">
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
                                        <p class="title">Not Delivered</p>
                                    </div>
                                </div>
                                <div id="folderNotDelivered" class="treeview-collapse collapse {{ $firstOpenFolder === 'not-delivered' ? 'show' : '' }}"
                                    aria-labelledby="folderNotDeliveredHeading" data-bs-parent="#treeviewFolderStructureEx">
                                    <ul class="treeview">
                                        @foreach($po->ndrReports as $ndr)
                                            <li class="tv-item tv-file document-node" data-target="doc-ndr-{{ $ndr->ndr_id }}">
                                                <span class="icon">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-file" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                                        <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                                        <path d="M14 3v4a1 1 0 0 0 1 1h4"></path>
                                                        <path d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z"></path>
                                                    </svg>
                                                </span>
                                                <p>NDR</p>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            </li>
                            @endif

                            {{-- Transfer Item Form Folder --}}
                            @if($hasTransfers)
                            <li class="tv-item tv-folder">
                                <div class="tv-header" id="folderTransferHeading">
                                    <div class="tv-collapsible {{ $firstOpenFolder === 'transfers' ? '' : 'collapsed' }}" data-bs-toggle="collapse"
                                        data-bs-target="#folderTransfer" aria-expanded="{{ $firstOpenFolder === 'transfers' ? 'true' : 'false' }}"
                                        aria-controls="folderTransfer">
                                        <div class="icon">
                                            <svg xmlns="http://www.w3.org/2000/svg"
                                                class="icon icon-tabler icon-tabler-folder" width="24" height="24"
                                                viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                                                fill="none" stroke-linecap="round" stroke-linejoin="round">
                                                <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                                <path d="M5 4h4l3 3h7a2 2 0 0 1 2 2v8a2 2 0 0 1 -2 2h-14a2 2 0 0 1 -2 -2v-11a2 2 0 0 1 2 -2"></path>
                                            </svg>
                                        </div>
                                        <p class="title">Transfer Item Form</p>
                                    </div>
                                </div>
                                <div id="folderTransfer" class="treeview-collapse collapse {{ $firstOpenFolder === 'transfers' ? 'show' : '' }}"
                                    aria-labelledby="folderTransferHeading" data-bs-parent="#treeviewFolderStructureEx">
                                    <ul class="treeview">
                                        @foreach($transferIcsSlips as $ics)
                                            <li class="tv-item tv-file document-node" data-target="doc-ics-{{ $ics->ics_id }}">
                                                <span class="icon">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-file" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                                        <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                                        <path d="M14 3v4a1 1 0 0 0 1 1h4"></path>
                                                        <path d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z"></path>
                                                    </svg>
                                                </span>
                                                <p>ICS {{ $ics->receiver->user_fullname ?? 'User' }}</p>
                                            </li>
                                        @endforeach
                                        @foreach($transferParReceipts as $par)
                                            <li class="tv-item tv-file document-node" data-target="doc-par-{{ $par->par_id }}">
                                                <span class="icon">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-file" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                                        <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                                        <path d="M14 3v4a1 1 0 0 0 1 1h4"></path>
                                                        <path d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z"></path>
                                                    </svg>
                                                </span>
                                                <p>PAR - {{ $par->receiver->user_fullname ?? 'User' }}</p>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            </li>
                            @endif
                        </ul>
                    </div>
                </div>
            </div>

            {{-- Placeholder card --}}
            <div class="col-md-9" id="placeholder-view-card">
                <div class="card shadow-sm border-0 mb-3 h-100">
                    <div class="card-body d-flex align-items-center justify-content-center text-center py-5">
                        <div>
                            <img src="{{ asset('img/File.svg') }}" width="200" height="200">
                            <h6 class="mt-3 text-muted">Open a file to view</h6>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Render all IARs --}}
            @foreach($po->iarReports as $iar)
                @include('supply.pages.partials._iar', ['iar' => $iar])
            @endforeach

            {{-- Render all RISs --}}
            @foreach($po->risSlips as $ris)
                @include('supply.pages.partials._ris', ['ris' => $ris])
            @endforeach

            {{-- Render all RSMIs --}}
            @foreach($po->rsmiReports as $rsmi)
                @include('supply.pages.partials._rsmi', ['rsmi' => $rsmi])
            @endforeach

            {{-- Render all ICSs --}}
            @foreach($po->icsSlips as $ics)
                @include('supply.pages.partials._ics', ['ics' => $ics])
            @endforeach

            {{-- Render all RSPIs --}}
            @foreach($po->rspiReports as $rspi)
                @include('supply.pages.partials._rspi', ['rspi' => $rspi])
            @endforeach

            {{-- Render all PARs --}}
            @foreach($po->parReceipts as $par)
                @include('supply.pages.partials._par', ['par' => $par])
            @endforeach

            {{-- Render all NDRs --}}
            @foreach($po->ndrReports as $ndr)
                @include('supply.pages.partials._ndr', ['ndr' => $ndr])
            @endforeach


        </div>
    </div>
    @if(session('download_pdf'))
        <div id="download-pdf-trigger" data-url="{{ session('download_pdf') }}"></div>
    @endif
@endsection

@push('js')
    <!-- Page SPECIFIC js -->
    <script src="{{ asset('plugins/src/flatpickr/flatpickr.js') }}"></script>

    <!-- CUSTOM js -->
    <script src="{{ asset('js/supply/delivery-attachment/custom-delivery-attachment.js') }}"></script>
    <script src="{{ asset('js/supply/delivery-attachment/partials/iar.js') }}"></script>
    <script src="{{ asset('js/supply/delivery-attachment/partials/ris.js') }}"></script>
    <script src="{{ asset('js/supply/delivery-attachment/partials/rsmi.js') }}"></script>
    <script src="{{ asset('js/supply/delivery-attachment/partials/ics.js') }}"></script>
    <script src="{{ asset('js/supply/delivery-attachment/partials/rspi.js') }}"></script>
    <script src="{{ asset('js/supply/delivery-attachment/partials/par.js') }}"></script>
@endpush
