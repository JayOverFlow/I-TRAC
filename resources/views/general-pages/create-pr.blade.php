@push('css')
    <!-- Page SPECIFIC css -->
    <link rel="stylesheet" href="{{ asset('css/general-pages/tasks/page-specific/datatables.css') }}">
    <link rel="stylesheet" href="{{ asset('css/general-pages/tasks/page-specific/dt-global_style.css') }}">

    <!-- FilePond CSS -->
    <link href="https://unpkg.com/filepond/dist/filepond.css" rel="stylesheet">

    <!-- CUSTOM css -->
    <link rel="stylesheet" href="{{ asset('css/general-pages/create-pr/page-specific/accordions.css') }}">
    <link rel="stylesheet" href="{{ asset('css/general-pages/create-pr/custom-create-pr.css') }}">
@endpush

@section('content')
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

    <div class="card shadow-sm border-0 mb-3">
        <div class="card-body">
            <div class="row g-4">
                <div class="col-md-6">
                    <div class="row align-items-center mb-3">
                        <div class="col-4">
                            <h6 class="mb-0 black-text fw-bold">Department:</h6>
                        </div>
                        <div class="col-8">
                            <h6 class="mb-0">College of Education and Arts</h6>
                        </div>
                    </div>
                    <div class="row align-items-center">
                        <div class="col-4">
                            <h6 class="mb-0 black-text fw-bold">Section:</h6>
                        </div>
                        <div class="col-8">
                            <input type="text" class="form-control form-control-sm w-100">
                        </div>
                    </div>
                </div>

                <div class="col-md-6 border-start-md">
                    <div class="row align-items-center mb-3">
                        <div class="col-4 text-md-end">
                            <h6 class="mb-0 black-text fw-bold">Date:</h6>
                        </div>
                        <div class="col-8">
                            <h6 class="mb-0">April 14, 2026</h6>
                        </div>
                    </div>
                    <div class="row align-items-center">
                        <div class="col-4 text-md-end">
                            <h6 class="mb-0 black-text fw-bold">P.R No.:</h6>
                        </div>
                        <div class="col-8">
                            <input type="text" class="form-control form-control-sm w-100">
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    @foreach ($groupedItems as $projectTitle => $items)
        <div class="card shadow-sm border-0 mb-3 px-0 pr-card">
            <div class="card-body px-0 pb-0">
                <div class="d-flex justify-content-between ms-4 mb-1">
                    <div class="d-flex align-items-baseline gap-2">
                        <h5 class="red-text fw-bold">Project Title: {{ $projectTitle }}</h5>
                        <small class="black-text item-count">{{ $items->count() }} Item/s</small>
                    </div>
                    <div class="d-flex align-items-baseline gap-2 me-4">
                        <p class="project-total-amount mb-0 fw-bold">₱ 0.00</p>
                        <button type="button" class="collapse-toggle bg-transparent text-black btn p-0 border-0"
                            style="text-decoration: none; box-shadow: none;">
                            <svg class="arrow-icon" width="20" height="20" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="18 15 12 9 6 15"></polyline>
                            </svg>
                        </button>
                    </div>
                </div>
                <div class="pr-collapse-area" id="collapseCard{{ $loop->iteration }}">
                    <hr class="m-0 p-0">
                    <div class="table-responsive mx-3">
                        <table class="table table-sm table-borderless align-middle pr-table">
                            <thead class="bg-transparent">
                                <tr>
                                    <th class="text-center black-text fw-bold" style="width: 8%">Stock</th>
                                    <th class="text-center black-text fw-bold" style="width: 10%">Unit</th>
                                    <th class="black-text fw-bold">Item</th>
                                    <!-- Auto width takes remaining space -->
                                    <th class="text-center black-text fw-bold" style="width: 7%">Qty.</th>
                                    <th class="text-center black-text fw-bold" style="width: 13%">Unit Cost</th>
                                    <th class="text-center black-text fw-bold" style="width: 13%">Amount</th>
                                    <th class="text-center black-text fw-bold" style="width: 17%">Category</th>
                                    <th class="text-start px-0" style="width: 30px"></th>
                                    <!-- Fixed strict pixel width -->
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($items as $item)
                                    <tr class="pr-item-row" data-id="{{ $item->app_item_id }}">
                                        <td class="px-1"><input type="text"
                                                class="form-control form-control-sm text-center"
                                                oninput="this.value = this.value.replace(/[^0-9]/g, '')"></td>
                                        <td class="px-1">
                                            <select class="form-select form-control-sm">
                                                <option value="" selected disabled>Select</option>
                                                <option value="">Piece</option>
                                                <option value="">Lot</option>
                                                <option value="">Set</option>
                                                <option value="">More options</option>
                                            </select>
                                        </td>
                                        <td class="px-1">
                                            <div class="input-group input-group-sm">
                                                <input type="text" class="form-control">
                                                <span class="input-group-text bg-white border-start-0 add-description-btn"
                                                    title="Add Description" style="cursor: pointer;">
                                                    <img src="{{ asset('img/add-description-btn.png') }}" alt="Add"
                                                        style="width: 14px; height: 14px;">
                                                </span>
                                            </div>
                                        </td>
                                        <td class="px-1"><input type="text"
                                                class="form-control form-control-sm text-center qty-input"
                                                oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                                        </td>
                                        <td class="px-1"><input type="text"
                                                class="form-control form-control-sm text-center cost-input"
                                                oninput="this.value = this.value.replace(/[^0-9.]/g, '')">
                                        </td>
                                        <td class="px-1 text-center">
                                            <span class="amount-display fw-bold" data-amount="0">₱ 0.00</span>
                                        </td>
                                        <td class="px-1">
                                            <select class="form-select form-control-sm">
                                                <option value="" selected disabled>Select</option>
                                                <option value="">Consumable</option>
                                                <option value="">Equipment</option>
                                                <option value="">Equipment (50k & ↑)</option>
                                            </select>
                                        </td>
                                        <td class="text-start px-0">
                                            <button type="button"
                                                class="btn border-0 bg-transparent text-black fw-bold remove-row-btn p-0 ms-2"
                                                style="visibility: hidden;">
                                                <img src="{{ asset('img/remove.svg') }}" alt="Remove">
                                            </button>
                                        </td>
                                    </tr>
                                    <tr class="pr-description-row d-none">
                                        <td colspan="2"></td>
                                        <td class="px-1">
                                            <div class="custom-description-container">
                                                <div class="d-flex justify-content-between align-items-center bg-white border rounded-top custom-description-header toggle-description-action"
                                                    style="cursor: pointer; border-color: #ced4da !important;">
                                                    <div class="p-1 px-2 black-text flex-grow-1"
                                                        style="font-size: 0.8rem;">
                                                        Description
                                                    </div>
                                                    <div class="d-flex align-items-center pe-3">
                                                        <button type="button"
                                                            class="btn-close btn-sm remove-description-btn me-2"
                                                            aria-label="Close"
                                                            style="width: 0.5em; height: 0.5em;"></button>
                                                        <svg class="description-arrow" width="12" height="12"
                                                            viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                            stroke-width="2" stroke-linecap="round"
                                                            stroke-linejoin="round">
                                                            <polyline points="6 9 12 15 18 9"></polyline>
                                                        </svg>
                                                    </div>
                                                </div>
                                                <div class="description-body border border-top-0 rounded-bottom bg-white"
                                                    style="border-color: #ced4da !important;">
                                                    <textarea class="form-control form-control-sm border-0 shadow-none px-2" rows="2"
                                                        placeholder="Enter description details..."></textarea>
                                                </div>
                                            </div>
                                        </td>
                                        <td colspan="5"></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <hr class="m-0 p-0">
                    <div class="text-center my-2">
                        <button class="btn border-0 bg-transparent text-black fw-bold add-item-btn">+ Add Item</button>
                    </div>
                </div>
            </div>
        </div>
    @endforeach

    <div class="d-flex justify-content-end align-items-center mb-3 mt-3">
        <h5 class="fw-bold ps-2 pe-5">Total Amount</h5>
        <h5 class="ps-2 pe-2" id="grand-total-amount">₱0.00</h5>
    </div>
@endsection

@push('js')
    <!-- Page SPECIFIC js -->
    <script src="{{ asset('js/general-pages/tasks/page-specific/datatables.js') }}"></script>

    <!-- FilePond JavaScript -->
    <script src="https://unpkg.com/filepond/dist/filepond.js"></script>
    <script src="https://unpkg.com/filepond-plugin-file-validate-size/dist/filepond-plugin-file-validate-size.js"></script>

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
    </script>

    <!-- CUSTOM js -->
    <script src="{{ asset('js/general-pages/create-pr/custom-create-pr.js') }}"></script>
@endpush
