{{-- Purchase Request Tab --}}
<div class="tab-pane fade" id="pane-animated-underline-purchase-request" role="tabpanel"
    aria-labelledby="animated-underline-purchase-request-tab">
    <div class="widget-content widget-content-area br-8 mt-3 p-0 pt-1">
        <table id="pr-table" class="table table-striped dt-table-hover" style="width:100%"
            data-route="{{ route('account.settings.retrieve.pr') }}">
            <thead>
                <tr>
                    <th class="fw-bold black-text text-nowrap text-center" style="width: 15%">PR-ID</th>
                    <th class="fw-bold black-text" style="width: 50%">Title</th>
                    <th class="fw-bold black-text text-center" style="width: 25%">Date Created</th>
                    <th class="fw-bold black-text text-nowrap text-center" style="width: 10%">Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($loadedPrs as $loadedPr)
                    <tr>
                        <td class="text-center" style="cursor: pointer;">
                            {{ $loadedPr->pr_unique_code ?? $loadedPr->pr_id }}</td>
                        <td style="cursor: pointer;">
                            {{ $loadedPr->pr_purpose ?? 'Untitled PR' }}</td>
                        <td class="text-center" style="cursor: pointer;">
                            {{ $loadedPr->created_at ? $loadedPr->created_at->format('Y-m-d') : 'N/A' }}</td>
                        <td class="text-center">
                            <button class="btn bg-transparent p-0 border-0 shadow-none"><img
                                    src="{{ asset('img/Edit.svg') }}" alt="Edit" width="19"
                                    height="20"></button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

@push('css')
    <link rel="stylesheet" href="{{ asset('css/head/dashboard/page-specific/datatables.css') }}">
    <link rel="stylesheet" href="{{ asset('css/head/dashboard/page-specific/dt-global_style.css') }}">
    <style>
        .custom-search-wrapper {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .search-input-container {
            position: relative;
        }
        .search-input-container svg {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #888ea8;
        }
        .search-input-container input {
            padding-left: 40px !important;
            width: 300px !important;
        }
    </style>
@endpush

@push('js')
    <script src="{{ asset('js/head/dashboard/page-specific/datatables.js') }}"></script>
    <script>
        $(document).ready(function() {
            var table = $('#pr-table').DataTable({
                "dom": "<'dt--top-section'<'row'<'col-12 col-sm-6 d-flex justify-content-sm-start align-items-center'l><'col-12 col-sm-6 d-flex justify-content-sm-end justify-content-center mt-sm-0 mt-3'<'#custom-search-box'>>>>" +
                    "<'table-responsive'tr>" +
                    "<'dt--bottom-section d-sm-flex justify-content-sm-between text-center'<'dt--pages-count  mb-sm-0 mb-3'i><'dt--pagination'p>>",
                "oLanguage": {
                    "oPaginate": {
                        "sPrevious": '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-arrow-left"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>',
                        "sNext": '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-arrow-right"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>'
                    },
                    "sInfo": "Showing page _PAGE_ of _PAGES_",
                    "sLengthMenu": "Results :  _MENU_",
                },
                "stripeClasses": [],
                "lengthMenu": [5, 10, 20, 50],
                "pageLength": 5
            });
        });
    </script>
    <script src="{{ asset('js/account-setting/custom-account-settings.js') }}"></script>
@endpush
