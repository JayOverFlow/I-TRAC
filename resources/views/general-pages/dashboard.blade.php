@push('css')
    <!-- Page SPECIFIC css -->
    <link rel="stylesheet" href="{{ asset('css/head/dashboard/page-specific/apexcharts.css') }}">
    <link rel="stylesheet" href="{{ asset('css/head/dashboard/page-specific/dash_1.css') }}">
    <link rel="stylesheet" href="{{ asset('css/head/dashboard/page-specific/datatables.css') }}">
    <link rel="stylesheet" href="{{ asset('css/head/dashboard/page-specific/dt-global_style.css') }}">

    <!-- CUSTOM css -->
    <link rel="stylesheet" href="{{ asset('css/head/dashboard/custom-dashboard.css') }}">

    <!-- DARK MODE SPECIFIC css -->
    <link rel="stylesheet" href="{{ asset('css/head/dashboard/page-specific/dark/dash_1.css') }}">
    <link rel="stylesheet" href="{{ asset('css/head/dashboard/page-specific/dark/dt-global_style.css') }}">
@endpush

@section('content')
    @php
        $presentYear = date('Y');
        $canGenerateReport = $activeApp && ($activeApp->app_year == $presentYear);
    @endphp
    @if(isset($isHead) && $isHead)
        <div class="row layout-top-spacing gx-3">
            <!-- Left side: Subordinates table -->
            <div class="col-xl-8 col-lg-7 col-md-12 col-sm-12 col-12 layout-spacing">
                <div class="widget-content widget-content-area br-8">
                    <table id="zero-config-head" class="table dt-table-hover" style="width:100%">
                        <thead>
                            <tr>
                                <th class="fw-bold">TUPT-ID</th>
                                <th class="fw-bold">First Name</th>
                                <th class="fw-bold">Last Name</th>
                                <th class="fw-bold">Role</th>
                                <th class="fw-bold">TUP Email</th>
                                <th class="fw-bold text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($subordinates as $sub)
                                <tr>
                                    <td class="align-middle">{{ $sub->user_tupid }}</td>
                                    <td class="align-middle">{{ $sub->user_firstname }}</td>
                                    <td class="align-middle">{{ $sub->user_lastname }}</td>
                                    <td class="align-middle">
                                        @if(!empty($sub->role_name))
                                            {{ $sub->user_type }} - {{ $sub->role_name }}
                                        @else
                                            {{ $sub->user_type }}
                                        @endif
                                    </td>
                                    <td class="align-middle">{{ $sub->user_email }}</td>
                                    <td class="text-center align-middle">
                                        @if($sub->has_task)
                                            <span class="badge-assigned">Assigned</span>
                                        @else
                                            <span class="badge-not-assigned">Not Assigned</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Right side: Budget Donut Chart & Recently Procured Items -->
            <div class="col-xl-4 col-lg-5 col-md-12 col-sm-12 col-12 layout-spacing">
                <div class="widget-content widget-content-area br-8 h-100 p-3">
                    <!-- Fiscal Year & Procurement Plan Link -->
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <a href="javascript:void(0)" class="link-danger link-offset-2 link-underline-opacity-25 link-underline-opacity-100-hover fw-bold" style="font-size: 12px;" id="view-procurement-plan-link" data-app-id="{{ $activeAppId }}">View Procurement Plan</a>
                        <div class="text-muted fw-bold" style="font-size: 12px;">
                            Fiscal Year: <span class="fiscal-year-text">{{ $fiscalYear }}</span>
                        </div>
                    </div>

                    <!-- Donut Chart Area -->
                    @if($activeApp)
                        <div class="donut-chart-container">
                            <div id="budget-donut-chart" style="width: 100%; height: 100%;"></div>
                            <div class="donut-center-label">
                                @if($utilizedBudget > 0)
                                    <span class="text-muted fw-bold" style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px;">Remaining Budget</span>
                                    <span class="donut-center-value my-1">₱{{ number_format($departmentBudget - $utilizedBudget, 2) }}</span>
                                    <span class="text-muted" style="font-size: 10px;">out of ₱{{ number_format($departmentBudget, 2) }}</span>
                                @else
                                    <span class="text-muted fw-bold" style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px;">Office Budget</span>
                                    <span class="donut-center-value mt-1">₱{{ number_format($departmentBudget, 2) }}</span>
                                @endif
                            </div>
                        </div>

                        <!-- Custom Legend -->
                        <div class="d-flex justify-content-center align-items-center gap-3 mb-3" style="font-size: 11px; color: #888ea8;">
                            <div class="d-flex align-items-center">
                                <span class="rounded-circle me-1" style="width: 8px; height: 8px; background-color: #e2a03f; display: inline-block;"></span>
                                Remaining Budget
                            </div>
                            <div class="d-flex align-items-center">
                                <span class="rounded-circle me-1" style="width: 8px; height: 8px; background-color: #a30000; display: inline-block;"></span>
                                Utilized Budget
                            </div>
                        </div>
                    @else
                        <div class="d-flex justify-content-center align-items-center my-4" style="height: 250px;">
                            <span class="text-muted fw-bold" style="font-size: 14px;">No active APP yet.</span>
                        </div>
                    @endif

                    <hr class="card-divider-full my-3">

                    <!-- Recently Procured Items Section -->
                    <div class="flex-grow-1">
                        <h6 class="fw-bold mb-3" style="color: #a30000; font-size: 14px;" id="recently-procured-title">Recently Procured Items</h6>
                        <ul class="procured-list">
                            @forelse($recentProcuredItems as $item)
                                <li class="procured-item">
                                    <div class="procured-item-icon-box">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-package"><line x1="16.5" y1="9.4" x2="7.5" y2="4.21"></line><polygon points="12 22.08 12 12 3 6.92 3 17.08 12 22.08"></polygon><polygon points="12 12 21 6.92 21 17.08 12 22.08"></polygon><polygon points="12 2 21 6.92 12 12 3 6.92 12 2"></polygon><line x1="12" y1="22.08" x2="12" y2="12"></line></svg>
                                    </div>
                                    <div class="procured-item-details">
                                        <div class="procured-item-text">
                                            <h6 class="procured-item-name" title="{{ $item->item_name }}">{{ $item->item_name }}</h6>
                                            <p class="procured-item-po text-muted">PO: {{ $item->poItem->purchaseOrder->po_no ?? 'N/A' }}</p>
                                        </div>
                                        <p class="procured-item-amount">₱{{ number_format(($item->poItem->po_items_cost ?? 0) * ($item->quantity ?? 1), 2) }}</p>
                                    </div>
                                </li>
                            @empty
                                <li class="py-3 text-center text-muted" style="font-size: 12px;">No recently procured items.</li>
                            @endforelse
                        </ul>
                    </div>

                    <!-- Generate Report Button -->
                    <div class="mt-auto pt-3">
                        @if($canGenerateReport)
                            <a href="javascript:void(0)" id="generate-report-link" class="btn btn-danger w-100 py-2 fw-bold text-white d-flex align-items-center justify-content-center" style="background-color: #a30000; border-color: #a30000;">
                                Generate Report
                            </a>
                        @else
                            <button type="button" class="btn btn-secondary w-100 py-2 fw-bold text-white d-flex align-items-center justify-content-center" style="cursor: not-allowed; opacity: 0.65;" disabled title="Report generation is only available for the present fiscal year.">
                                Generate Report
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="p-0">
            <div class="row">

                <div class="col-3">
                    <div class="card h-100">
                        <div class="card-body row p-4">
                            <div class="col-4">
                                <img src="{{ asset('img/Fiscal Year.svg') }}" alt="Fiscal Year">
                            </div>
                            <div class="col-8 text-end">
                                <h5 class="card-title fw-bold">Fiscal Year</h5>
                                <h5 class="mb-0 fw-bold">{{ $fiscalYear ?? '—' }}</h5>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-3">
                    <div class="card h-100">
                        <div class="card-body row p-4">
                            <div class="col-4">
                                <img src="{{ asset('img/DepartmentBudget.svg') }}" alt="Department">
                            </div>
                            <div class="col-8 text-end">
                                <h5 class="card-title fw-bold mb-0">Office Budget</h5>
                                <h5 class="mb-0 fw-bold">₱<span>{{ number_format($departmentBudget, 2) }}</span></h5>
                                <a href="javascript:void(0)" class="link-danger link-offset-2 link-underline-opacity-25 link-underline-opacity-100-hover fw-bold" id="view-procurement-plan-link" data-app-id="{{ $activeAppId }}">View Procurement Plan</a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-3">
                    <div class="card h-100" id="utilized-budget-card">
                        <div class="card-body row p-4">
                            <div class="col-4">
                                <img src="{{ asset('img/UtilBud.svg') }}" alt="Utilized Budget">
                            </div>
                            <div class="col-8 text-end">
                                <h5 class="card-title fw-bold mb-0">Utilized Budget</h5>
                                <h5 class="mb-0 fw-bold">₱<span>{{ number_format($utilizedBudget, 2) }}</span></h5>
                                @if($canGenerateReport)
                                    <a href="javascript:void(0)" class="link-danger link-offset-2 link-underline-opacity-25 link-underline-opacity-100-hover fw-bold" id="generate-report-link">Generate Report</a>
                                @else
                                    <span class="text-muted fw-bold" style="font-size: 12px; cursor: not-allowed;" title="Report generation is only available for the present fiscal year.">Generate Report (Unavailable)</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-3">
                    <div class="card h-100">
                        <div class="card-body row p-4">
                            <div class="col-4">
                                <img src="{{ asset('img/DepartmentBudget.svg') }}" alt="Department">
                            </div>
                            <div class="col-8 text-end">
                                <h5 class="card-title fw-bold mb-0">Remaining Budget</h5>
                                <h5 class="mb-0 fw-bold">₱<span>{{ number_format($departmentBudget - $utilizedBudget, 2) }}</span></h5>
                            </div>
                        </div>
                    </div>
                </div>
                
            </div>
        </div>

        <div class="widget-content widget-content-area br-8 mt-3">
            <table id="zero-config" class="table dt-table-hover" style="width:100%">
                <thead>
                    <tr>
                        <th class="fw-bold">TUPT-ID</th>
                        <th class="fw-bold">First Name</th>
                        <th class="fw-bold">Last Name</th>
                        <th class="fw-bold">Role</th>
                        <th class="fw-bold">TUP Email</th>
                        <th class="fw-bold text-center">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($subordinates as $sub)
                        <tr>
                            <td class="align-middle">{{ $sub->user_tupid }}</td>
                            <td class="align-middle">{{ $sub->user_firstname }}</td>
                            <td class="align-middle">{{ $sub->user_lastname }}</td>
                            <td class="align-middle">
                                @if(!empty($sub->role_name))
                                    {{ $sub->user_type }} - {{ $sub->role_name }}
                                @else
                                    {{ $sub->user_type }}
                                @endif
                            </td>
                            <td class="align-middle">{{ $sub->user_email }}</td>
                            <td class="text-center align-middle">
                                @if($sub->has_task)
                                    <span class="badge-assigned">Assigned</span>
                                @else
                                    <span class="badge-not-assigned">Not Assigned</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center">No subordinates found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @endif

    <!-- Generate Report Modal -->
    @if($canGenerateReport)
    <div class="modal fade" id="generateReportModal" tabindex="-1" aria-labelledby="generateReportModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content report-modal-content border-0 p-0">
                <div class="modal-header report-modal-header border-0 px-4 pt-2 pb-0">
                    <div>
                        <h4 class="modal-title report-modal-title fw-bold" id="generateReportModalLabel" style="color: #a30000;">Generate Report</h4>
                        <p class="text-muted report-modal-subtitle mb-0 small">Specify the details of your report down below.</p>
                    </div>
                    <button type="button" class="btn-close report-modal-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <hr class="my-1">
                <div class="modal-body report-modal-body px-4">
                    <!-- Title Row -->
                    <div class="d-flex justify-content-between align-items-center py-3 border-bottom report-row">
                        <span class="black-text report-label">Title</span>
                        <span class="fw-bold report-value text-end">{{ $activeApp->app_title }}</span>
                    </div>
                    <!-- Year Row -->
                    <div class="d-flex justify-content-between align-items-center py-3 border-bottom report-row">
                        <span class="black-text report-label">Year</span>
                        <span class="fw-bold report-value text-end" id="modal-app-year">{{ $activeApp->app_year }}</span>
                    </div>
                    <!-- Office Row -->
                    <div class="d-flex justify-content-between align-items-center py-3 border-bottom report-row">
                        <span class="black-text report-label">Office</span>
                        <span class="fw-bold report-value text-end">{{ $depName }}</span>
                    </div>
                    <!-- Month Row -->
                    <div class="d-flex justify-content-between align-items-center py-3 report-row">
                        <span class="black-text report-label">Month</span>
                        <div class="d-flex align-items-center gap-2">
                            <span class="black-text small text-end">as of</span>
                            <select class="form-select form-select-sm report-select black-text" id="filter-month-select" style="width: auto; min-width: 110px;">
                                <option value="1">January</option>
                                <option value="2">February</option>
                                <option value="3">March</option>
                                <option value="4">April</option>
                                <option value="5">May</option>
                                <option value="6">June</option>
                                <option value="7">July</option>
                                <option value="8">August</option>
                                <option value="9">September</option>
                                <option value="10">October</option>
                                <option value="11">November</option>
                                <option value="12">December</option>
                            </select>
                        </div>
                    </div>
                    
                    <!-- Action Button -->
                    <div class="d-flex justify-content-end mt-2">
                        <button type="button" class="btn btn-outline-dark px-4 py-2 fw-bold btn-cancel-report me-2" data-bs-dismiss="modal" style="border-radius: 6px;">
                            Cancel
                        </button>
                        <button type="button" class="btn btn-danger px-4 py-2 fw-bold text-white btn-export-report" id="btn-export-report" style="background-color: #a30000; border-color: #a30000; border-radius: 6px;">
                            Export Report
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    @include('partials.action-confirmation-alert')
@endsection

@push('js')
    <!-- Page SPECIFIC js -->
    <script src="{{ asset('js/head/dashboard/page-specific/apexcharts.min.js') }}"></script>
    <script src="{{ asset('js/head/dashboard/page-specific/dash_1.js') }}"></script>
    <script src="{{ asset('js/head/dashboard/page-specific/datatables.js') }}"></script>
    <script>
        var tableSelector = '{{ isset($isHead) && $isHead ? "#zero-config-head" : "#zero-config" }}';
        $(tableSelector).DataTable({
            "dom": "<'dt--top-section'<'row'<'col-12 col-sm-6 d-flex justify-content-sm-start justify-content-center'<'assigned-title'>><'col-12 col-sm-6 d-flex justify-content-sm-end justify-content-center mt-sm-0 mt-3'f>>>" +
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
            },
            "stripeClasses": [],
            "lengthMenu": [5, 10, 20, 50],
            "pageLength": 5,
            "columnDefs": [],
            "initComplete": function () {
                $('.assigned-title').html('<h5 class="fw-bold mb-0 red-text-2">Subordinates</h5>');
            }
        });

        @if(isset($isHead) && $isHead)
        (function() {
            var options = {
                chart: {
                    type: 'donut',
                    width: '100%',
                    height: '100%',
                    parentHeightOffset: 0
                },
                @if($departmentBudget == 0)
                    series: [1],
                    labels: ['No Budget yet'],
                    colors: [document.body.classList.contains('dark') ? '#192739' : '#e0e6ed'],
                @elseif($utilizedBudget > 0)
                    series: [{{ (float)($departmentBudget - $utilizedBudget) }}, {{ (float)$utilizedBudget }}],
                    labels: ['Remaining Budget', 'Utilized Budget'],
                    colors: ['#e2a03f', '#a30000'],
                @else
                    series: [{{ (float)$departmentBudget }}],
                    labels: ['Remaining Budget'],
                    colors: ['#e2a03f'],
                @endif
                dataLabels: {
                    enabled: false
                },
                legend: {
                    show: false
                },
                stroke: {
                    show: true,
                    width: 2,
                    colors: [document.body.classList.contains('dark') ? '#0e1726' : '#ffffff']
                },
                plotOptions: {
                    pie: {
                        donut: {
                            size: '88%',
                            background: 'transparent',
                            labels: {
                                show: false
                            }
                        }
                    }
                },
                tooltip: {
                    theme: document.body.classList.contains('dark') ? 'dark' : 'light',
                    y: {
                        formatter: function (val) {
                            var isZeroBudget = {{ $departmentBudget == 0 ? 'true' : 'false' }};
                            if (isZeroBudget) {
                                return '₱0.00';
                            }
                            return '₱' + val.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                        }
                    }
                }
            };

            var chartEl = document.querySelector("#budget-donut-chart");
            if (chartEl) {
                var chart = new ApexCharts(chartEl, options);
                chart.render();
            }
        })();
        @endif
    </script>

    <!-- CUSTOM js -->
    <script src="{{ asset('js/head/dashboard/custom-dashboard.js') }}"></script>
@endpush