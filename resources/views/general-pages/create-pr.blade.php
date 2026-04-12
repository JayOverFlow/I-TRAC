{{-- Extend the main layout that you want to use --}}
@extends('main-layout')

{{-- Define contents to show in the layout --}}
@section('title', 'Create Purchase Request | I-TRAC')

@push('css')
    <!-- Page SPECIFIC css -->
    <link rel="stylesheet" href="{{ asset('css/general-pages/tasks/page-specific/datatables.css') }}">
    <link rel="stylesheet" href="{{ asset('css/general-pages/tasks/page-specific/dt-global_style.css') }}">

    <!-- FilePond CSS -->
    <link href="https://unpkg.com/filepond/dist/filepond.css" rel="stylesheet">

    <!-- CUSTOM css -->
    <link rel="stylesheet" href="{{ asset('css/general-pages/create-pr/custom-create-pr.css') }}">
@endpush

@section('content')

    @if (isset($task))
        <div class="alert alert-info mb-3">
            <strong>PR Task Items ({{ $task->appItems->count() }}):</strong>
            <ul class="mb-0 mt-1">
                @foreach ($task->appItems as $item)
                    <li>{{ $item->app_item_proj_title }} — Est. Budget:
                        ₱{{ number_format($item->app_items_esti_budget, 2) }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card card-budget mb-3">
        <div class="card-body d-flex justify-content-between align-items-center flex">

            <div class="d-flex flex-column">
                <h4 class="fw-bold red-text">PURCHASE REQUEST</h4>
                <div class="d-flex align-items-center mt-1">
                    <img src="{{ asset('img/user-profile.jpeg') }}"
                        class="avatar-img rounded-circle border border-2 border-white">
                    <img src="{{ asset('img/user-profile.jpeg') }}"
                        class="avatar-img rounded-circle border border-2 border-white ms-n2">
                    <div class="avatar-add rounded-circle border bg-white d-flex align-items-center justify-content-center ms-n2"
                        style="width: 35px; height: 35px; color: #ccc;">
                        <span>+</span>
                    </div>
                </div>
            </div>

            <div class="d-flex flex-column align-items-end">
                <div class="text-end mb-2 d-flex align-items-center">
                    <h4 class="mb-0 black-text mb-0 pe-3 fw-normal">Allocated Budget:</h4>
                    <h4 class="card-title black-text mb-0 fw-bold">PHP 12,345.00</h4>
                </div>

                <div class="d-flex gap-2">
                    <button class="btn btn-red white-text shadow-sm">
                        <img src="{{ asset('img/download.png') }}" alt="download" width="16" height="16">
                        Export
                    </button>
                    <button class="btn btn-bg-white black-text border shadow-sm">
                        <img src="{{ asset('img/save.png') }}" alt="save" width="16" height="16">
                        Save as a draft
                    </button>
                </div>
            </div>

        </div>
    </div>

    <div class="card mb-2">
        <div class="card-body card-pad">
            <div class="container pe-0 ps-0">
                <div class="row mb-2">
                    <div class="col-md-7 d-flex">
                        <h6 class="text-nowrap me-3" style="width: 100px;">Department:</h6>
                        <h6 class="fw-bold">SOLID STEEL MACHINERY AND TOOLS INC.</h6>
                    </div>

                    <div class="col-md-5 d-flex">
                        <h6 class="text-nowrap me-3" style="width: 80px;">P.R. No.:</h6>
                        <h6 class="fw-bold">2025-02-01</h6>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-7 d-flex">
                        <h6 class="text-nowrap me-3" style="width: 100px;">Section:</h6>
                        <h6 class="fw-bold">Chemistry</h6>
                    </div>

                    <div class="col-md-5 d-flex">
                        <h6 class="text-nowrap me-3" style="width: 80px;">Date:</h6>
                        <h6 class="fw-bold">February 10, 2025</h6>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-2">
        <div class="card-body">
            <div class="pr-container mt-3">
                <h6 class="fw-bold">Consumables</h6>
                <div>
                    <table class="table">
                        <thread>
                            <tr>
                                <th class="w-6 gray-text text-center">Stock</th>
                                <th class="w-6 gray-text text-center">Unit</th>
                                <th class="w-45 gray-text text-left">Article</th>
                                <th class="w-6 gray-text text-center">Qty.</th>
                                <th class="w-13 gray-text text-center">Unit Cost</th>
                                <th class="w-13 gray-text text-center">Amount</th>
                                <th class="w-13 gray-text text-center">Category</th>
                            </tr>
                        </thread>
                        <tbody>
                            <tr>
                                <td class="ps-1 pe-1">
                                    <input type="text" class="form-control  text-center fw-bold" value="1">
                                </td>
                                <td class="ps-1 pe-1">
                                    <input type="text" class="form-control  text-center fw-bold" value="1">
                                </td>

                                <td class="ps-1 pe-1">
                                    <div class="article-cell-wrapper">
                                        <div class="input-group ">
                                            <input type="text" class="form-control border-end-0 fw-bold "
                                                value="Nintendo Switch">
                                            <span class="input-group-text bg-white border-start-0 add-description-btn">
                                                <img src="{{ asset('img/add-description-btn.png') }}" alt="Add"
                                                    style="width: 14px; height: 14px;">
                                            </span>
                                        </div>
                                    </div>
                                </td>

                                <td class="ps-1 pe-1">
                                    <input type="text" class="form-control  text-center fw-bold" value="3">
                                </td>
                                <td class="ps-1 pe-1">
                                    <input type="text" class="form-control  text-center fw-bold" value="₱ 21,500.00">
                                </td>
                                <td class="ps-1 pe-1">
                                    <input type="text" class="form-control text-center fw-bold" value="₱ 21,500.00">
                                </td>
                                <td class="ps-1 pe-1">
                                    <div class="btn-group choose-btn" role="group">
                                        <button id="btndefault" type="button"
                                            class="dropdown-toggle d-flex align-items-center justify-content-between w-100 h-100"
                                            data-bs-toggle="dropdown" data-bs-boundary="window" aria-expanded="false">
                                            Type
                                            <img src="{{ asset('img/dropdown-btn.png') }}" alt=""
                                                class="dropdown-btn">
                                            </buton>

                                            <div class="dropdown-menu" aria-labelledby="btndefault">
                                                <a href="javascript:void(0);" class="dropdown-item"><i
                                                        class="flaticon-home-fill-1 mr-1"></i>Consumable</a>
                                                <a href="javascript:void(0);" class="dropdown-item"><i
                                                        class="flaticon-gear-fill mr-1"></i>Equipments</a>
                                            </div>
                                    </div>
                                </td>
                            </tr>

                            <tr class="pr-description-row d-none">
                                <td class="border-0"></td>
                                <td class="border-0"></td>

                                <td class="ps-1 pe-1 border-0">
                                    <div class="description-box-container">
                                        <label for="description" class="form-label">Description</label>
                                        <textarea class="form-control description-box" rows="2" placeholder="Description"></textarea>
                                    </div>
                                </td>
                                <td colspan="4" class="border-0"></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-2">
        <div class="card-body">
            <div class="pr-container mt-3">
                <h6 class="fw-bold">Equipment</h6>
                <div>
                    <table class="table">
                        <thread>
                            <tr>
                                <th class="w-6 gray-text text-center">Stock</th>
                                <th class="w-6 gray-text text-center">Unit</th>
                                <th class="w-45 gray-text text-left">Article</th>
                                <th class="w-6 gray-text text-center">Qty.</th>
                                <th class="w-13 gray-text text-center">Unit Cost</th>
                                <th class="w-13 gray-text text-center">Amount</th>
                                <th class="w-13 gray-text text-center">Category</th>
                            </tr>
                        </thread>
                        <tbody>
                            <tr>
                                <td class="ps-1 pe-1">
                                    <input type="text" class="form-control  text-center fw-bold" value="1">
                                </td>
                                <td class="ps-1 pe-1">
                                    <input type="text" class="form-control  text-center fw-bold" value="1">
                                </td>

                                <td class="ps-1 pe-1">
                                    <div class="article-cell-wrapper">
                                        <div class="input-group ">
                                            <input type="text" class="form-control border-end-0 fw-bold "
                                                value="Nintendo Switch">
                                            <span class="input-group-text bg-white border-start-0 add-description-btn">
                                                <img src="{{ asset('img/add-description-btn.png') }}" alt="Add"
                                                    style="width: 14px; height: 14px;">
                                            </span>
                                        </div>
                                    </div>
                                </td>

                                <td class="ps-1 pe-1">
                                    <input type="text" class="form-control  text-center fw-bold" value="3">
                                </td>
                                <td class="ps-1 pe-1">
                                    <input type="text" class="form-control  text-center fw-bold" value="₱ 21,500.00">
                                </td>
                                <td class="ps-1 pe-1">
                                    <input type="text" class="form-control text-center fw-bold" value="₱ 21,500.00">
                                </td>
                                <td class="ps-1 pe-1">
                                    <div class="btn-group choose-btn" role="group">
                                        <button id="btndefault" type="button"
                                            class="dropdown-toggle d-flex align-items-center justify-content-between w-100 h-100"
                                            data-bs-toggle="dropdown" data-bs-boundary="window" aria-expanded="false">
                                            Type
                                            <img src="{{ asset('img/dropdown-btn.png') }}" alt=""
                                                class="dropdown-btn">
                                            </buton>

                                            <div class="dropdown-menu" aria-labelledby="btndefault">
                                                <a href="javascript:void(0);" class="dropdown-item"><i
                                                        class="flaticon-home-fill-1 mr-1"></i>Consumable</a>
                                                <a href="javascript:void(0);" class="dropdown-item"><i
                                                        class="flaticon-gear-fill mr-1"></i>Equipments</a>
                                            </div>
                                    </div>
                                </td>
                            </tr>

                            <tr class="pr-description-row d-none">
                                <td class="border-0"></td>
                                <td class="border-0"></td>

                                <td class="ps-1 pe-1 border-0">
                                    <div class="description-box-container">
                                        <label for="description" class="form-label">Description</label>
                                        <textarea class="form-control description-box" rows="2" placeholder="Description"></textarea>
                                    </div>
                                </td>
                                <td colspan="4" class="border-0"></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <div class="pr-container mt-3">
                <h6 class="fw-bold">Equipment (Php. 50,000 and above)</h6>
                <div>
                    <table class="table">
                        <thread>
                            <tr>
                                <th class="w-6 gray-text text-center">Stock</th>
                                <th class="w-6 gray-text text-center">Unit</th>
                                <th class="w-45 gray-text text-left">Article</th>
                                <th class="w-6 gray-text text-center">Qty.</th>
                                <th class="w-13 gray-text text-center">Unit Cost</th>
                                <th class="w-13 gray-text text-center">Amount</th>
                                <th class="w-13 gray-text text-center">Category</th>
                            </tr>
                        </thread>
                        <tbody>
                            <tr>
                                <td class="ps-1 pe-1">
                                    <input type="text" class="form-control  text-center fw-bold" value="1">
                                </td>
                                <td class="ps-1 pe-1">
                                    <input type="text" class="form-control  text-center fw-bold" value="1">
                                </td>

                                <td class="ps-1 pe-1">
                                    <div class="article-cell-wrapper">
                                        <div class="input-group ">
                                            <input type="text" class="form-control border-end-0 fw-bold "
                                                value="Nintendo Switch">
                                            <span class="input-group-text bg-white border-start-0 add-description-btn">
                                                <img src="{{ asset('img/add-description-btn.png') }}" alt="Add"
                                                    style="width: 14px; height: 14px;">
                                            </span>
                                        </div>
                                    </div>
                                </td>

                                <td class="ps-1 pe-1">
                                    <input type="text" class="form-control  text-center fw-bold" value="3">
                                </td>
                                <td class="ps-1 pe-1">
                                    <input type="text" class="form-control  text-center fw-bold" value="₱ 21,500.00">
                                </td>
                                <td class="ps-1 pe-1">
                                    <input type="text" class="form-control text-center fw-bold" value="₱ 21,500.00">
                                </td>
                                <td class="ps-1 pe-1">
                                    <div class="btn-group choose-btn" role="group">
                                        <button id="btndefault" type="button"
                                            class="dropdown-toggle d-flex align-items-center justify-content-between w-100 h-100"
                                            data-bs-toggle="dropdown" data-bs-boundary="window" aria-expanded="false">
                                            Type
                                            <img src="{{ asset('img/dropdown-btn.png') }}" alt=""
                                                class="dropdown-btn">
                                            </buton>

                                            <div class="dropdown-menu" aria-labelledby="btndefault">
                                                <a href="javascript:void(0);" class="dropdown-item"><i
                                                        class="flaticon-home-fill-1 mr-1"></i>Consumable</a>
                                                <a href="javascript:void(0);" class="dropdown-item"><i
                                                        class="flaticon-gear-fill mr-1"></i>Equipments</a>
                                            </div>
                                    </div>
                                </td>
                            </tr>

                            <tr class="pr-description-row d-none">
                                <td class="border-0"></td>
                                <td class="border-0"></td>

                                <td class="ps-1 pe-1 border-0">
                                    <div class="description-box-container">
                                        <label for="description" class="form-label">Description</label>
                                        <textarea class="form-control description-box" rows="2" placeholder="Description"></textarea>
                                    </div>
                                </td>
                                <td colspan="4" class="border-0"></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>


    <div class="d-flex justify-content-center align-items-center mb-2">
        <button class="btn btn-bg-white black-text shadow-sm w-25 pt-2 pb-2">+ Add Item</button>
    </div>

    <div class="d-flex justify-content-end align-items-center mb-3 mt-3">
        <h5 class="fw-bold ps-2 pe-5">Total Amount</h5>
        <h5 class="ps-2 pe-2">₱ 64,500.00</h5>
    </div>


@endsection

@push('js')
    <!-- Page SPECIFIC js -->
    <script src="{{ asset('js/general-pages/tasks/page-specific/datatables.js') }}"></script>

    <!-- FilePond JavaScript -->
    <script src="https://unpkg.com/filepond/dist/filepond.js"></script>
    <script src="https://unpkg.com/filepond-plugin-file-validate-size/dist/filepond-plugin-file-validate-size.js"></script>

    <!-- addition -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        $('#zero-config').DataTable({
            "dom": "<'dt--top-section'<'row'<'col-12 col-sm-6 d-flex justify-content-sm-start justify-content-center'l><'col-12 col-sm-6 d-flex justify-content-sm-end justify-content-center mt-sm-0 mt-3'>>>" +
                "<'table-responsive'tr>" +
                "<'dt--bottom-section d-sm-flex justify-content-sm-between text-center'<'dt--pages-count  mb-sm-0 mb-3'i><'dt--pagination'p>>",
            "oLanguage": {
                "oPaginate": {
                    "sPrevious": '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-arrow-left"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>',
                    "sNext": '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-arrow-right"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>'
                },
                "sInfo": "Showing page _PAGE_ of _PAGES_",
                "sLengthMenu": "Filter :  _MENU_",
            },
            "stripeClasses": [],
            "lengthMenu": [5, 10, 20, 50],
            "pageLength": 5,
            "initComplete": function() {
                $('.dt--top-section .col-12.d-flex.justify-content-sm-end').html(
                    '<button class="btn btn-red" id="import-app-btn" data-url="{{ route('show.import.app') }}">Import</button>'
                );
            }
        });
        $(document).ready(function() {
            $('.pr-container').on('click', '.add-description-btn', function(e) {
                e.preventDefault();
                var currentRow = $(this).closest('tr');
                var descriptionRow = currentRow.next('.pr-description-row');
                descriptionRow.toggleClass('d-none');
                descriptionRow.find('.description-box').focus();
            });
        });

        $('.pr-container').on('click', '.dropdown-item', function(e) {
            e.preventDefault();
            var selectedText = $(this).text().trim();
            var button = $(this).closest('.btn-group').find('.dropdown-toggle');
            button.find('span').text(selectedText);
        });
    </script>

    <!-- CUSTOM js -->
    <script src="{{ asset('js/head/tasks/head-tasks.js') }}"></script>
@endpush
