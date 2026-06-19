{{-- Extend the main layout that you want to use --}}
@extends('layouts.supply-layout')

{{-- Define contents to show in the layout --}}
@section('title', 'Inventory | I-TRAC')

@push('css')
    <!-- Page SPECIFIC css -->
    <link rel="stylesheet" href="{{ asset('css/supply/inventory/page-specific/apexcharts.css') }}">
    <link rel="stylesheet" href="{{ asset('css/supply/inventory/page-specific/dash_1.css') }}">
    <link rel="stylesheet" href="{{ asset('css/supply/inventory/page-specific/datatables.css') }}">
    <link rel="stylesheet" href="{{ asset('css/supply/inventory/page-specific/dt-global_style.css') }}">

    <!-- CUSTOM css -->
    <link rel="stylesheet" href="{{ asset('css/supply/inventory/custom-inventory.css') }}">

    <!-- DARK MODE SPECIFIC css -->
    <link rel="stylesheet" href="{{ asset('css/supply/inventory/page-specific/dark/dash_1.css') }}">
    <link rel="stylesheet" href="{{ asset('css/supply/inventory/page-specific/dark/dt-global_style.css') }}">
@endpush

@section('content')
    <div class="p-0">
        <div class="row row-cols-4">
            <div class="col">
                <div class="card">
                    <div class="card-body row p-4">
                        <div class="col-4">
                            <img src="{{ asset('img/ALL.svg') }}" alt="ALL">
                        </div>
                        <div class="col-8 text-end">
                            <h6 class="card-title fw-bold">ALL</h6>
                            <h5 class="mb-0 fw-bold"><span>{{ $counts['all'] }}</span></h5>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col">
                <div class="card">
                    <div class="card-body row p-4">
                        <div class="col-4">
                            <img src="{{ asset('img/EQUIPMENT.svg') }}" alt="Equipment">
                        </div>
                        <div class="col-8 text-end">
                            <h6 class="card-title fw-bold">Equipment</h6>
                            <h5 class="mb-0 fw-bold"><span>{{ $counts['equipment'] }}</span></h5>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col">
                <div class="card">
                    <div class="card-body row p-4">
                        <div class="col-4">
                            <img src="{{ asset('img/SEMI-EXP.svg') }}" alt="Semi-Expendable">
                        </div>
                        <div class="col-8 text-end">
                            <h6 class="card-title fw-bold">Semi-Expendable</h6>
                            <h5 class="mb-0 fw-bold"><span>{{ $counts['semi_expendable'] }}</span></h5>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col">
                <div class="card">
                    <div class="card-body row p-4">
                        <div class="col-4">
                            <img src="{{ asset('img/mr-supplies.svg') }}" alt="Supplies & Materials">
                        </div>
                        <div class="col-8 text-end">
                            <h6 class="card-title fw-bold">Supplies & Materials</h6>
                            <h5 class="mb-0 fw-bold"><span>{{ $counts['supplies'] }}</span></h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="widget-content widget-content-area br-8 mt-3 p-0">
        <table id="zero-config" class="table dt-table-hover table-w-100">
            <thead>
                <tr>
                    <th class="fw-bold text-nowrap text-center col-w-10">MR-ID</th>
                    <th class="fw-bold">Item Name</th>
                    <th class="fw-bold">Assigned to</th>
                    <th class="fw-bold text-nowrap text-center col-w-15">Office</th>
                    <th class="fw-bold text-nowrap text-center col-w-10">Date Received</th>
                    <th class="fw-bold text-nowrap text-center col-w-10">Category</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($mrItems as $item)
                    <tr>
                        <td class="text-center">{{ $item->mr_qr_code }}</td>
                        <td>{{ $item->item_name }}</td>
                        <td>{{ $item->assignedUser?->user_fullname ?? '—' }}</td>
                        <td class="text-center">{{ $item->assignedUser?->departments->first()?->dep_name ?? '—' }}</td>
                        <td class="text-center">{{ $item->date_scanned ? \Carbon\Carbon::parse($item->date_scanned)->format('Y-m-d') : '—' }}</td>
                        <td class="text-center">
                            @if ($item->category === 'Supply and Materials')
                                <span class="badge badge-light-info">Supplies and Materials</span>
                            @elseif ($item->category === 'Semi-Expendable')
                                <span class="badge badge-light-success">Semi-Expendable</span>
                            @elseif ($item->category === 'Equipment')
                                <span class="badge badge-light-danger">Equipment</span>
                            @else
                                <span>—</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection

@push('js')
    <!-- Page SPECIFIC js -->
    <script src="{{ asset('js/supply/inventory/page-specific/apexcharts.min.js') }}"></script>
    <script src="{{ asset('js/supply/inventory/page-specific/dash_1.js') }}"></script>
    <script src="{{ asset('js/supply/inventory/page-specific/datatables.js') }}"></script>

    <!-- CUSTOM js -->
    <script src="{{ asset('js/supply/inventory/custom-inventory.js') }}"></script>
@endpush