{{-- Purchase Order Tab --}}
<div class="tab-pane fade" id="animated-underline-annual-procurement-plan" role="tabpanel" {{-- Chnange annual-procurement-plan to purchase-order --}}
    aria-labelledby="animated-underline-annual-procurement-plan-tab"> {{-- Change annual-procurement-plan to purchase-order --}}
    <div class="widget-content widget-content-area br-8 mt-3 p-0 pt-1">
        <table id="zero-config" class="table table-striped dt-table-hover" style="width:100%">
            <thead>
                <tr>
                    <th class="fw-bold black-text text-nowrap text-center" style="width: 15%">PR-ID</th>
                    <th class="fw-bold black-text" style="width: 50%">Title</th>
                    <th class="fw-bold black-text text-center" style="width: 25%">Date Created</th>
                    <th class="fw-bold black-text text-nowrap text-center" style="width: 10%">Action</th>
                </tr>
            </thead>
            <tbody>
                {{-- @foreach ($apps as $app)
                    <tr>
                        <td class="text-center" style="cursor: pointer;"
                            onclick="window.location='{{ route('show.assign.pr', $app->app_id) }}'">
                            {{ $app->app_id }}</td>
                        <td style="cursor: pointer;"
                            onclick="window.location='{{ route('show.assign.pr', $app->app_id) }}'">
                            {{ $app->app_title ?? 'Untitled APP' }}</td>
                        <td class="text-center" style="cursor: pointer;"
                            onclick="window.location='{{ route('show.assign.pr', $app->app_id) }}'">
                            {{ $app->created_at ? $app->created_at->format('Y-m-d') : 'N/A' }}</td>
                        <td class="text-center">
                            <button class="btn bg-transparent p-0 border-0 shadow-none"><img
                                    src="{{ asset('img/Edit.svg') }}" alt="Edit" width="19"
                                    height="20"></button>
                        </td>
                    </tr>
                @endforeach --}}
            </tbody>
        </table>
    </div>
</div>

@push('css')
    <link rel="stylesheet" href="{{ asset('css/head/dashboard/page-specific/datatables.css') }}">
    <link rel="stylesheet" href="{{ asset('css/head/dashboard/page-specific/dt-global_style.css') }}">
@endpush

@push('js')
    <script src="{{ asset('js/head/dashboard/page-specific/datatables.js') }}"></script>
    <script>
        $('#zero-config').DataTable({
            "dom": "<'dt--top-section'<'row'<'col-12 col-sm-6 d-flex justify-content-sm-start align-items-center'l><'col-12 col-sm-6 d-flex justify-content-sm-end justify-content-center mt-sm-0 mt-3'<'custom-buttons'>>>>" +
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
            initComplete: function() {
                $('.custom-buttons').html(`
                    <div class="d-flex gap-2">
                        <button class="btn btn-dark-red d-flex align-items-center px-4 py-2 border-0" style="border-radius: 8px;" onclick="window.location='{{ route('show.create-app') }}'">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="me-2">
                                <path d="M14 2H6C5.46957 2 4.96086 2.21071 4.58579 2.58579C4.21071 2.96086 4 3.46957 4 4V20C4 20.5304 4.21071 21.0391 4.58579 21.4142C4.96086 21.7893 5.46957 22 6 22H18C18.5304 22 19.0391 21.7893 19.4142 21.4142C19.7893 21.0391 20 20.5304 20 20V8L14 2Z" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M14 2V8H20" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                <rect x="7" y="13" width="10" height="6" rx="1" stroke="white" stroke-width="2"/>
                                <line x1="10.5" y1="13" x2="10.5" y2="19" stroke="white" stroke-width="2"/>
                                <line x1="13.5" y1="13" x2="13.5" y2="19" stroke="white" stroke-width="2"/>
                                <line x1="7" y1="16" x2="17" y2="16" stroke="white" stroke-width="2"/>
                            </svg>
                            <span class="fw-bold">Create</span>
                        </button>
                        <button class="btn btn-white d-flex align-items-center px-4 py-2 border border-secondary shadow-sm" style="border-radius: 8px;" onclick="window.location='{{ route('show.import.app') }}'">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="me-2">
                                <path d="M14 2H6C5.46957 2 4.96086 2.21071 4.58579 2.58579C4.21071 2.96086 4 3.46957 4 4V20C4 20.5304 4.21071 21.0391 4.58579 21.4142C4.96086 21.7893 5.46957 22 6 22H18C18.5304 22 19.0391 21.7893 19.4142 21.4142C19.7893 21.0391 20 20.5304 20 20V8L14 2Z" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M14 2V8H20" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M12 18V12" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M9 15L12 18L15 15" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            <span class="fw-bold black-text">Import</span>
                        </button>
                    </div>
                `);
            },
            "stripeClasses": [],
            "lengthMenu": [5, 10, 20, 50],
            "pageLength": 5
        });
    </script>
@endpush
