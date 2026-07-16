{{-- Annual Procurement Plan Tab --}}
<div class="tab-pane fade" id="pane-animated-underline-annual-procurement-plan" role="tabpanel"
    aria-labelledby="animated-underline-annual-procurement-plan-tab">
    <div class="widget-content widget-content-area br-8 mt-3 p-0 pt-1">
        <table id="zero-config" class="table table-striped dt-table-hover" style="width:100%">
            <thead>
                <tr>
                    <th class="fw-bold black-text text-nowrap text-center" style="width: 15%">APP-ID</th>
                    <th class="fw-bold black-text" style="width: 40%">Title</th>
                    <th class="fw-bold black-text text-center" style="width: 25%">Date Created</th>
                    <th class="fw-bold black-text text-nowrap text-center" style="width: 10%">Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($apps as $app)
                    @php
                        $targetRoute = route('show.create-app', $app->app_id);
                    @endphp
                    <tr>
                        <td class="text-center" style="cursor: pointer;"
                            onclick="window.location='{{ $targetRoute }}'">
                            {{ $app->app_id }}</td>
                        <td style="cursor: pointer;" onclick="window.location='{{ $targetRoute }}'">
                            {{ $app->app_title ?? 'Untitled APP' }}</td>
                        <td class="text-center" style="cursor: pointer;"
                            onclick="window.location='{{ $targetRoute }}'">
                            {{ $app->created_at ? $app->created_at->format('Y-m-d') : 'N/A' }}</td>
                        <td class="text-center" style="cursor: pointer;"
                            onclick="window.location='{{ $targetRoute }}'">
                            @if ($app->app_status === 'Done')
                                <span class="badge badge-light-success mb-2 me-4">Done</span>
                            @else
                                <span class="badge badge-light-dark mb-2 me-4">Draft</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

{{-- Create APP Modal --}}
<div class="modal fade" id="createAppModal" tabindex="-1" aria-labelledby="createAppModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content border-0 shadow bg-white p-0">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold text-dark-red" id="createAppModalLabel">Create Annual Procurement Plan</h5>
            </div>
            <hr class="my-3">
            <form id="createAppModalForm" action="{{ route('create.app.init') }}" method="POST">
                @csrf
                <div class="modal-body py-0">
                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label for="app_title" class="form-label fw-bold black-text">Annual Procurement Plan Title <span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-sm" id="app_title"
                                name="app_title" placeholder="Enter a descriptive title">
                                <span class="text-danger d-none" id="app_title_error" style="font-size: 0.85rem;"></span>
                            <div class="form-text mt-2 black-text" style="font-size: 0.85rem;">Example:
                                APP_CES</div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="year" class="form-label fw-bold black-tex">Fiscal Year <span class="text-danger">*</span></label>
                            <select class="form-select form-control-sm" id="year"
                                name="year">
                                <option value="" disabled selected>Select Year</option>
                                @php
                                    $currentYear = date('Y');
                                @endphp
                                @for ($y = $currentYear - 2; $y <= $currentYear + 10; $y++)
                                    <option value="{{ $y }}">{{ $y }}</option>
                                @endfor
                            </select>
                            <span class="text-danger d-none" id="year_error" style="font-size: 0.85rem;"></span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 d-flex justify-content-end gap-2">
                    <button type="button" class="btn btn-light fw-bold text-black border px-4 py-2"
                        data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" id="btnCreatePlanSubmit" class="btn btn-dark-red fw-bold px-4 py-2">Create
                        Plan</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('css')
    <link rel="stylesheet" href="{{ asset('css/head/dashboard/page-specific/datatables.css') }}">
    <link rel="stylesheet" href="{{ asset('css/head/dashboard/page-specific/dt-global_style.css') }}">

    <!-- DARK MODE SPECIFIC css -->
    <link rel="stylesheet" href="{{ asset('css/head/dashboard/page-specific/dark/datatables.css') }}">
    <link rel="stylesheet" href="{{ asset('css/head/dashboard/page-specific/dark/dt-global_style.css') }}">

    <link class="page-specific-css" rel="stylesheet"
        href="{{ asset('css/account-setting/page-specific/annual-procurement-plan.css') }}">
@endpush

@push('js')
    <script src="{{ asset('js/head/dashboard/page-specific/datatables.js') }}"></script>
    <script src="{{ asset('js/account-setting/page-specific/annual-procurement-plan.js') }}"></script>
@endpush
