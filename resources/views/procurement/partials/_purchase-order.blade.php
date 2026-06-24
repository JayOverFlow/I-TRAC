{{-- Purchase Order Tab --}}
<div class="tab-pane fade" id="pane-animated-underline-purchase-order" role="tabpanel"
    aria-labelledby="animated-underline-purchase-order-tab">
    <div class="widget-content widget-content-area br-8 mt-3 p-0 pt-1">
        <table id="po-table" class="table dt-table-hover" style="width:100%">
            <thead>
                <tr>
                    <th class="fw-bold black-text text-nowrap text-center" style="width: 20%">PO-ID</th>
                    <th class="fw-bold black-text text-nowrap text-center" style="width: 20%">Created From</th>
                    <th class="fw-bold black-text text-nowrap text-center" style="width: 40%">Title</th>
                    <th class="fw-bold black-text text-center" style="width: 10%">Date Created</th>
                    <th class="fw-bold black-text text-nowrap text-center" style="width: 10%">Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($pos as $po)
                    <tr class="clickable-row" data-id="{{ $po->po_id }}">
                        <td class="text-center">
                            {{ $po->po_id }}</td>
                        <td class="text-center">
                            {{ $po->purchaseRequest?->pr_unique_code ?? 'N/A' }}</td>
                        <td>
                            {{ $po->po_title }}</td>
                        <td class="text-center">
                            {{ $po->created_at ? $po->created_at->format('Y-m-d') : 'N/A' }}</td>
                        <td class="text-center">
                            <span class="badge {{ $po->po_status == 'Draft' ? 'bg-warning' : 'bg-info' }}">
                                {{ $po->po_status }}
                            </span>
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

    <!-- DARK MODE SPECIFIC css -->
    <link rel="stylesheet" href="{{ asset('css/head/dashboard/page-specific/dark/datatables.css') }}">
    <link rel="stylesheet" href="{{ asset('css/head/dashboard/page-specific/dark/dt-global_style.css') }}">
@endpush

@push('js')
    <script src="{{ asset('js/head/dashboard/page-specific/datatables.js') }}"></script>
    <script>
        $('#po-table').DataTable({
            "dom": "<'dt--top-section'<'row'<'col-12 col-sm-6 d-flex justify-content-sm-start align-items-center'f><'col-12 col-sm-6 d-flex justify-content-sm-end justify-content-center mt-sm-0 mt-3'>>>" +
                "<'table-responsive'tr>" +
                "<'dt--bottom-section d-sm-flex justify-content-sm-between text-center'<'dt--pages-count  mb-sm-0 mb-3'i><'dt--pagination'p>>",
            "oLanguage": {
                "oPaginate": {
                    "sPrevious": '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-arrow-left"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>',
                    "sNext": '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-arrow-right"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>'
                },
                "sInfo": "Showing page _PAGE_ of _PAGES_",
                "sSearch": '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-search"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>',
                "sSearchPlaceholder": "Search...",
                "sLengthMenu": "Results :  _MENU_",
            },
            "order": [[3, "desc"]],
            "stripeClasses": [],
            "lengthMenu": [5, 10, 20, 50],
            "pageLength": 5
        });

        // Handle row click for PO preview
        $('#po-table tbody').on('click', 'tr.clickable-row', function() {
            var poId = $(this).data('id');
            var url = "{{ route('show.create.po', ':id') }}".replace(':id', poId);
            window.location.href = url;
        });
    </script>
@endpush
