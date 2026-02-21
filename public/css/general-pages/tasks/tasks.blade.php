@push('css')
    <!-- Page SPECIFIC css -->
    <link rel="stylesheet" href="{{ asset('css/head/tasks/page-specific/datatables.css') }}">
    <link rel="stylesheet" href="{{ asset('css/head/tasks/page-specific/dt-global_style.css') }}">

    <!-- CUSTOM css -->
    <link rel="stylesheet" href="{{ asset('css/head/tasks/custom-tasks.css') }}">
@endpush

@section('content')
    <div class="widget-content widget-content-area br-8">
        <table id="zero-config" class="table table-striped dt-table-hover" style="width:100%">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Document</th>
                    <th>Data Received</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <p>Kemberlet</p>
                    </td>
                    <td>System Architect</td>
                    <td>2011/04/25</td>
                </tr>
                <tr>
                    <td>
                        <p>Kemberlet</p>
                    </td>
                    <td>System Architect</td>
                    <td>2011/04/25</td>
                </tr>
                <tr>
                    <td>
                        <p>Kemberlet</p>
                    </td>
                    <td>System Architect</td>
                    <td>2011/04/25</td>
                </tr>
                <tr>
                    <td>
                        <p>Kemberlet</p>
                    </td>
                    <td>System Architect</td>
                    <td>2011/04/25</td>
                </tr>
                <tr>
                    <td>
                        <p>Kemberlet</p>
                    </td>
                    <td>System Architect</td>
                    <td>2011/04/25</td>
                </tr>
                <tr>
                    <td>
                        <p>Kemberlet</p>
                    </td>
                    <td>System Architect</td>
                    <td>2011/04/25</td>
                </tr>
                <tr>
                    <td>
                        <p>Pak Juna</p>
                    </td>
                    <td>Accountant</td>
                    <td>2011/07/25</td>
                </tr>
                <tr>
                    <td>
                        <p>Kem</p>
                    </td>
                    <td>Junior Technical Author</td>
                    <td>2009/01/12</td>
                </tr>
                <tr>
                    <td>
                        <p>Jay</p>
                    </td>
                    <td>Senior Javascript Developer</td>
                    <td>2012/03/29</td>
                </tr>
            </tbody>
        </table>
    </div>
@endsection

@push('js')
    <!-- Page SPECIFIC js -->
    <script src="{{ asset('js/head/tasks/page-specific/datatables.js') }}"></script>
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
            "lengthMenu": [7, 10, 20, 50],
            "pageLength": 5,
            "initComplete": function() {
                $('.dt--top-section .col-12.d-flex.justify-content-sm-end').html(
                    '<button class="btn btn-red" id="import-app">Import</button>');
            }
        });
    </script>

    <!-- CUSTOM js -->
@endpush
