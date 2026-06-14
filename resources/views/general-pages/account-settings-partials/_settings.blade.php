{{-- Settings Tab --}}
@php
    $allRoles = auth()->user()->roles;
    $activeRoleId = session('active_role_id') ?? ($allRoles->first()?->role_id ?? null);
    $activeRole = $allRoles->where('role_id', $activeRoleId)->first() ?? $allRoles->first();
    $userRoleGen = $activeRole?->gen_role ?? auth()->user()->user_type;
    $hasPremiumSettings = in_array($userRoleGen, ['Head', 'Procurement', 'Supply']);
@endphp

<div class="tab-pane fade" id="pane-animated-underline-settings" role="tabpanel"
    aria-labelledby="animated-underline-settings-tab">

    @if($hasPremiumSettings)
        <style>
            .archive-clickable-row,
            .archive-po-clickable-row,
            .archive-pr-clickable-row,
            .archive-project-dynamic-row {
                cursor: pointer !important;
            }
            div[id^="settings-view-archive-"] {
                min-height: 85vh;
            }
        </style>
        <div class="settings-view-container">
            <!-- LEVEL 0: Main Settings View -->
            <div id="settings-view-main" class="settings-view-pane active">
                <div class="settings-container">
                    
                    <!-- LEFT COLUMN (Theme, Notifications, FAQs) -->
            <div class="settings-left-col">
                
                <!-- CHOOSE THEME CARD -->
                <div class="settings-card choose-theme-card">
                    <div class="settings-card-header">
                        <h3>Choose Theme</h3>
                    </div>
                    
                    <div class="theme-options">
                        <!-- Light Mode Option -->
                        <div class="theme-option">
                            <label class="theme-option-label">
                                <input type="radio" name="theme_selection" value="light" checked style="display:none;">
                                <span class="custom-radio"></span>
                                <div class="theme-thumbnail-wrapper">
                                    <img src="{{ asset('img/light-mode.svg') }}" alt="Light Mode" class="theme-thumbnail">
                                </div>
                            </label>
                        </div>

                        <!-- Dark Mode Option -->
                        <div class="theme-option">
                            <label class="theme-option-label">
                                <input type="radio" name="theme_selection" value="dark" style="display:none;">
                                <span class="custom-radio"></span>
                                <div class="theme-thumbnail-wrapper">
                                    <img src="{{ asset('img/dark-mode.svg') }}" alt="Dark Mode" class="theme-thumbnail">
                                </div>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- NOTIFICATION CARD -->
                <div class="settings-card notification-card">
                    <div class="card-content-inline">
                        <div class="card-text-group">
                            <h3>Notification</h3>
                            <p class="settings-description">Enable all notifications</p>
                        </div>
                        <div class="toggle-switch-wrapper">
                            <input type="checkbox" id="notification-toggle" class="switch-input" checked>
                            <label class="switch-label" for="notification-toggle">
                                <span class="slider"></span>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- MANUAL BOOKLET CARD -->
                <div class="settings-card manual-booklet-card">
                    <h3>Manual Booklet</h3>
                    <p class="manual-booklet-text">Click <a href="#" class="manual-booklet-link"><i><u>I-TRAC Manual Booklet</u></i></a> to download.</p>
                </div>

            </div>

            <!-- RIGHT COLUMN (APP Alert, Archive, promo card) -->
            <div class="settings-right-col">
                
                <!-- ANNUAL PROCUREMENT PLAN CARD -->
                <div class="settings-card app-card">
                    <div class="app-card-header">
                        <h3>Annual Procurement Plan</h3>
                        <a href="javascript:void(0);" class="set-link" data-bs-toggle="modal" data-bs-target="#setAppModal">Click to set <span class="arrow-icon">&gt;</span></a>
                    </div>
                    
                    <div class="app-card-divider"></div>
                    
                    <div id="settings-app-alert-box" class="settings-alert-box {{ isset($activeApp) && $activeApp ? 'settings-app-success-alert' : '' }}">
                        <div id="settings-app-alert-icon-wrapper" class="alert-icon-wrapper">
                            @if(isset($activeApp) && $activeApp)
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="feather feather-check-circle"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                            @else
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-info"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
                            @endif
                        </div>
                        <span id="settings-app-alert-text" class="alert-text">{{ isset($activeApp) && $activeApp ? $activeApp->app_title . ' is currently active' : 'No APP is set' }}</span>
                    </div>
                </div>

                <!-- ARCHIVE CARD -->
                <div class="settings-card archive-card">
                    <div class="card-header-inline">
                        <h3>Archive</h3>
                        <a href="javascript:void(0);" class="view-link" id="btn-view-archive">View <span class="arrow-icon">&gt;</span></a>
                    </div>
                    <p class="settings-description mt-2">Access documents previously attached to Purchase Order</p>
                </div>

                <!-- RED PROMOTION BANNER -->
                <div class="promo-banner">
                    
                    <!-- Integrated Background SVG with 3D Phone that overflows -->
                    <img src="{{ asset('img/mobile-3d-bg.svg') }}" alt="I-TRAC Mobile App" class="promo-bg-image">
                    
                    <!-- Content overlay -->
                    <div class="promo-content">
                        <div class="promo-left">
                            <button class="btn btn-download-now">Download Now</button>
                        </div>
                        <div class="promo-right"></div>
                    </div>
                </div>

            </div>

                </div>
            </div> <!-- End settings-view-main -->

            <!-- LEVEL 1: Archive APP List View -->
            <div id="settings-view-archive-apps" class="settings-view-pane" style="display: none;">
                <div class="widget-content widget-content-area br-8 p-0">
                    
                    <!-- Top Section -->
                    <div class="dt--top-section" style="margin: 0; padding: 20px 21px 20px 21px;">
                        <div class="row">
                            <div class="col-12 col-sm-6 d-flex justify-content-sm-start align-items-center">
                                <div class="dataTables_length">
                                    <label>
                                        <div class="archive-breadcrumb">
                                            <a href="javascript:void(0);" id="btn-archive-back" class="text-decoration-none archive-back-link">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-arrow-left"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
                                                Archive
                                            </a>
                                            <span class="breadcrumb-separator">&gt;</span>
                                            <span class="breadcrumb-current fw-bold red-text-2">Annual Procurement Plan</span>
                                        </div>
                                    </label>
                                </div>
                            </div>
                            <div class="col-12 col-sm-6 d-flex justify-content-sm-end justify-content-center mt-sm-0 mt-3">
                                <div class="dataTables_filter">
                                    <div class="position-relative">
                                        <input type="search" class="form-control form-control-sm archive-search-input" placeholder="Search..." aria-controls="zero-config" style="padding-right: 32px; width: 200px; font-size: 0.85rem;">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-search position-absolute" style="right: 10px; top: 50%; transform: translateY(-50%); color: #888ea8;"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Table Responsive Section -->
                    <div class="table-responsive">
                        <table class="table dt-table-hover" style="width:100%">
                            <thead>
                                <tr>
                                    <th class="fw-bold sorting" style="width: 20%">APP-Code</th>
                                    <th class="fw-bold sorting" style="width: 60%">Title</th>
                                    <th class="fw-bold text-nowrap text-center sorting" style="width: 20%">Date Created</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($apps ?? [] as $app)
                                    <tr class="archive-clickable-row" data-app-id="{{ $app->app_id }}" data-app-code="{{ $app->app_unique_code }}" data-app-title="{{ $app->app_title }}">
                                        <td>{{ $app->app_unique_code }}</td>
                                        <td>{{ $app->app_title }}</td>
                                        <td class="text-center">{{ \Carbon\Carbon::parse($app->created_at)->format('m/d/Y') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center">No Annual Procurement Plans found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Bottom Section -->
                    <div class="dt--bottom-section d-sm-flex justify-content-sm-between text-center">
                        <div class="dt--pages-count mb-sm-0 mb-3">
                            <div class="dataTables_info">Showing page 1 of 1</div>
                        </div>
                        <div class="dt--pagination">
                            <div class="dataTables_paginate paging_simple_numbers">
                                <ul class="pagination">
                                    <li class="paginate_button page-item previous disabled">
                                        <a href="#" class="page-link">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-arrow-left"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
                                        </a>
                                    </li>
                                    <li class="paginate_button page-item active"><a href="#" class="page-link">1</a></li>
                                    <li class="paginate_button page-item next disabled">
                                        <a href="#" class="page-link">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-arrow-right"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>

                </div>
            </div> <!-- End settings-view-archive-apps -->

            <!-- LEVEL 2: Archive APP Projects View -->
            <div id="settings-view-archive-projects" class="settings-view-pane" style="display: none;">
                <div class="widget-content widget-content-area br-8 p-0">
                    
                    <!-- Top Section -->
                    <div class="dt--top-section" style="margin: 0; padding: 20px 21px 20px 21px;">
                        <div class="row">
                            <div class="col-12 col-sm-6 d-flex justify-content-sm-start align-items-center">
                                <div class="dataTables_length">
                                    <label>
                                        <div class="archive-breadcrumb">
                                            <a href="javascript:void(0);" id="btn-archive-level1-back-1" class="text-decoration-none archive-back-link">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-arrow-left"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
                                                Archive
                                            </a>
                                            <span class="breadcrumb-separator">&gt;</span>
                                            <a href="javascript:void(0);" id="btn-archive-level1-back-2" class="text-decoration-none archive-back-link">Annual Procurement Plan</a>
                                            <span class="breadcrumb-separator">&gt;</span>
                                            <span class="breadcrumb-current fw-bold red-text-2">Annual Procurement Plan for Year F.Y. 2026</span>
                                        </div>
                                    </label>
                                </div>
                            </div>
                            <div class="col-12 col-sm-6 d-flex justify-content-sm-end justify-content-center mt-sm-0 mt-3">
                                <div class="dataTables_filter d-flex align-items-center justify-content-sm-end justify-content-center gap-2">
                                    <button class="btn btn-sm px-3" id="btn-view-app-prs" style="background-color: #900b09; color: white; border: none; box-shadow: none; font-weight: 500; border-radius: 6px;">View PRs</button>
                                    <button class="btn btn-sm px-3" id="btn-view-app-pos" style="background-color: #900b09; color: white; border: none; box-shadow: none; font-weight: 500; border-radius: 6px;">View POs</button>
                                    <div class="position-relative">
                                        <input type="search" class="form-control form-control-sm archive-search-input" placeholder="Search..." aria-controls="zero-config" style="padding-right: 32px; width: 200px; font-size: 0.85rem;">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-search position-absolute" style="right: 10px; top: 50%; transform: translateY(-50%); color: #888ea8;"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Table Responsive Section -->
                    <div class="table-responsive">
                        <table class="table dt-table-hover" style="width:100%">
                            <thead>
                                <tr>
                                    <th class="fw-bold sorting" style="width: 20%">APP Project-Code</th>
                                    <th class="fw-bold sorting" style="width: 60%">Project Title</th>
                                    <th class="fw-bold text-nowrap text-center sorting" style="width: 20%">Date Created</th>
                                </tr>
                            </thead>
                            <tbody id="archive-app-projects-tbody">
                                <!-- Dynamically populated by JS -->
                            </tbody>
                        </table>
                    </div>

                    <!-- Bottom Section -->
                    <div class="dt--bottom-section d-sm-flex justify-content-sm-between text-center">
                        <div class="dt--pages-count mb-sm-0 mb-3">
                            <div class="dataTables_info">Showing page 1 of 1</div>
                        </div>
                        <div class="dt--pagination">
                            <div class="dataTables_paginate paging_simple_numbers">
                                <ul class="pagination">
                                    <li class="paginate_button page-item previous disabled">
                                        <a href="#" class="page-link">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-arrow-left"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
                                        </a>
                                    </li>
                                    <li class="paginate_button page-item active"><a href="#" class="page-link">1</a></li>
                                    <li class="paginate_button page-item next disabled">
                                        <a href="#" class="page-link">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-arrow-right"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>

                </div>
            </div> <!-- End settings-view-archive-projects -->

            <!-- LEVEL 3: Archive APP Project Details View -->
            <div id="settings-view-archive-app-project" class="settings-view-pane" style="display: none;">
                <div class="card shadow-sm border-0 mb-3 p-0">
                    <div class="card-body px-0">
                        <div class="d-flex justify-content-between align-items-center mx-4 mb-3 archive-breadcrumb">
                            <div class="d-flex align-items-center flex-wrap gap-2">
                                <a href="javascript:void(0);" id="btn-archive-level2-back-1" class="text-decoration-none archive-back-link">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-arrow-left"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
                                    Archive
                                </a>
                                <span class="breadcrumb-separator">&gt;</span>
                                <a href="javascript:void(0);" id="btn-archive-level2-back-2" class="text-decoration-none archive-back-link">Annual Procurement Plan</a>
                                <span class="breadcrumb-separator">&gt;</span>
                                <a href="javascript:void(0);" id="btn-archive-level2-back-3" class="text-decoration-none archive-back-link">Annual Procurement Plan for Year F.Y. 2026</a>
                                <span class="breadcrumb-separator">&gt;</span>
                                <span class="breadcrumb-current fw-bold red-text-2">Class Laboratory Table and Chairs</span>
                            </div>
                            <a href="javascript:void(0);" id="btn-toggle-app-details" class="btn-collapse-details text-decoration-none">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-chevron-down"><polyline points="6 9 12 15 18 9"></polyline></svg>
                            </a>
                        </div>

                        <hr class="m-0 p-0">
                        
                        <div id="archive-app-details-container">
                            <!-- Section 1.1: Procurement Project Details -->
                            <div class="app-detail-header ms-4 mt-3 mb-2">Procurement Project Details</div>
                            <div class="row g-2 ms-3 me-3 mb-2">
                                <div class="col-md-7">
                                    <div class="row mb-1">
                                        <div class="col-5 app-detail-label">Project Title:</div>
                                        <div class="col-7 app-detail-value" id="detail-proj-title">Laboratory Chemicals/Regents/Consumables</div>
                                    </div>
                                    <div class="row mb-1">
                                        <div class="col-5 app-detail-label">End-User or Implementing Unit:</div>
                                        <div class="col-7 app-detail-value" id="detail-end-user">College of Education and Sciences</div>
                                    </div>
                                    <div class="row mb-1">
                                        <div class="col-5 app-detail-label">General Description:</div>
                                        <div class="col-7 app-detail-value" id="detail-gen-desc">Goods</div>
                                    </div>
                                    <div class="row mb-1">
                                        <div class="col-5 app-detail-label">Mode of Procurement:</div>
                                        <div class="col-7 app-detail-value" id="detail-mode">Small Value Procurement</div>
                                    </div>
                                </div>
                                <div class="col-md-5">
                                    <div class="row mb-2">
                                        <div class="col-12 app-detail-label mb-0">
                                            Criteria for Bid Evaluation: <small style="font-size: 0.7rem; font-style: italic;">(Including Sustainability and Domestic Preference)</small>
                                        </div>
                                        <div class="col-12 app-detail-value" id="detail-criteria">LCRB</div>
                                    </div>
                                    <div class="row">
                                        <div class="col-12 app-detail-label mb-1">To be covered by an Early Procurement Activity</div>
                                        <div class="col-12">
                                            <div class="d-flex align-items-center gap-3">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" id="detail-early-yes" disabled>
                                                    <label class="form-check-label app-detail-label">Yes</label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" id="detail-early-no" checked disabled>
                                                    <label class="form-check-label app-detail-value">No</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <hr class="app-detail-divider">

                            <!-- Section 1.2: Projected Timeline -->
                            <div class="app-detail-header ms-4 mt-2 mb-2">Projected Timeline</div>
                            <div class="row g-2 ms-3 me-3 mb-2">
                                <div class="col-md-6">
                                    <div class="row">
                                        <div class="col-6 app-detail-label">Start of Procurement Activity:</div>
                                        <div class="col-6 app-detail-value" id="detail-start">00/00/0000</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="row">
                                        <div class="col-6 app-detail-label">End of Procurement Activity:</div>
                                        <div class="col-6 app-detail-value" id="detail-end">00/00/0000</div>
                                    </div>
                                </div>
                            </div>

                            <hr class="app-detail-divider">

                            <!-- Section 1.3: Funding Details -->
                            <div class="app-detail-header ms-4 mt-2 mb-2">Funding Details</div>
                            <div class="row g-2 ms-3 me-3 mb-1">
                                <div class="col-md-6">
                                    <div class="row">
                                        <div class="col-6 app-detail-label">Source of Fund:</div>
                                        <div class="col-6 app-detail-value" id="detail-source">Fund 5</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="row">
                                        <div class="col-6 app-detail-label">Estimated Budget/Approved Budget for the Contract (PhP):</div>
                                        <div class="col-6 app-detail-value" id="detail-budget">00/00/0000</div>
                                    </div>
                                </div>
                            </div>
                            
                            <hr class="app-detail-divider" style="width: 96%; margin: 4px auto !important;">

                            <div class="row g-2 ms-3 me-3 mb-3">
                                <div class="col-md-6">
                                    <div class="row">
                                        <div class="col-6 app-detail-label">Procurement Strategy or Tools:</div>
                                        <div class="col-6 app-detail-value" id="detail-tools">Fund 5</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="row">
                                        <div class="col-6 app-detail-label">
                                            Remarks: <small style="font-size: 0.7rem; font-style: italic;">(other relevant descriptions of the procurement project, if applicable)</small>
                                        </div>
                                        <div class="col-6 app-detail-value" id="detail-remarks">00/00/0000</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div> <!-- End settings-view-archive-app-project -->

            <!-- LEVEL 4 (PRs): Archive PRs View -->
            <div id="settings-view-archive-prs" class="settings-view-pane" style="display: none;">
                <div class="widget-content widget-content-area br-8 p-0">
                    <!-- Top Section -->
                    <div class="dt--top-section" style="margin: 0; padding: 20px 21px 20px 21px;">
                        <div class="row">
                            <div class="col-12 col-sm-6 d-flex justify-content-sm-start align-items-center">
                                <div class="dataTables_length">
                                    <label>
                                        <div class="archive-breadcrumb">
                                            <a href="javascript:void(0);" id="btn-archive-level4-pr-back-1" class="text-decoration-none archive-back-link">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-arrow-left"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
                                                Archive
                                            </a>
                                            <span class="breadcrumb-separator">&gt;</span>
                                            <a href="javascript:void(0);" id="btn-archive-level4-pr-back-2" class="text-decoration-none archive-back-link">Annual Procurement Plan</a>
                                            <span class="breadcrumb-separator">&gt;</span>
                                            <a href="javascript:void(0);" id="btn-archive-level4-pr-back-3" class="text-decoration-none archive-back-link">APP-2026-01</a>
                                            <span class="breadcrumb-separator">&gt;</span>
                                            <span class="breadcrumb-current fw-bold red-text-2">Purchase Requests</span>
                                        </div>
                                    </label>
                                </div>
                            </div>
                            <div class="col-12 col-sm-6 d-flex justify-content-sm-end justify-content-center mt-sm-0 mt-3">
                                <div class="dataTables_filter">
                                    <div class="position-relative">
                                        <input type="search" class="form-control form-control-sm archive-search-input" placeholder="Search PRs..." aria-controls="zero-config" style="padding-right: 32px; width: 200px; font-size: 0.85rem;">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-search position-absolute" style="right: 10px; top: 50%; transform: translateY(-50%); color: #888ea8;"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Table Responsive Section -->
                    <div class="table-responsive">
                        <table class="table dt-table-hover" style="width:100%">
                            <thead>
                                <tr>
                                    <th class="fw-bold sorting" style="width: 20%">PR number</th>
                                    <th class="fw-bold sorting" style="width: 40%">Purpose</th>
                                    <th class="fw-bold text-nowrap text-end sorting" style="width: 20%">Allocated Budget</th>
                                    <th class="fw-bold sorting" style="width: 20%">Requested by</th>
                                </tr>
                            </thead>
                            <tbody id="archive-prs-tbody">
                                <!-- Dynamically populated by JS -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div> <!-- End settings-view-archive-prs -->

            <!-- LEVEL 4: Archive POs View -->
            <div id="settings-view-archive-pos" class="settings-view-pane" style="display: none;">
                <div class="widget-content widget-content-area br-8 p-0">
                    <!-- Top Section -->
                    <div class="dt--top-section" style="margin: 0; padding: 20px 21px 20px 21px;">
                        <div class="row">
                            <div class="col-12 col-sm-6 d-flex justify-content-sm-start align-items-center">
                                <div class="dataTables_length">
                                    <label>
                                        <div class="archive-breadcrumb">
                                            <a href="javascript:void(0);" id="btn-archive-level4-back-1" class="text-decoration-none archive-back-link">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-arrow-left"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
                                                Archive
                                            </a>
                                            <span class="breadcrumb-separator">&gt;</span>
                                            <a href="javascript:void(0);" id="btn-archive-level4-back-2" class="text-decoration-none archive-back-link">Annual Procurement Plan</a>
                                            <span class="breadcrumb-separator">&gt;</span>
                                            <a href="javascript:void(0);" id="btn-archive-level4-back-3" class="text-decoration-none archive-back-link">APP-2026-01</a>
                                            <span class="breadcrumb-separator">&gt;</span>
                                            <span class="breadcrumb-current fw-bold red-text-2">Purchase Orders</span>
                                        </div>
                                    </label>
                                </div>
                            </div>
                            <div class="col-12 col-sm-6 d-flex justify-content-sm-end justify-content-center mt-sm-0 mt-3">
                                <div class="dataTables_filter">
                                    <div class="position-relative">
                                        <input type="search" class="form-control form-control-sm archive-search-input" placeholder="Search POs..." aria-controls="zero-config" style="padding-right: 32px; width: 200px; font-size: 0.85rem;">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-search position-absolute" style="right: 10px; top: 50%; transform: translateY(-50%); color: #888ea8;"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Table Responsive Section -->
                    <div class="table-responsive">
                        <table class="table dt-table-hover" style="width:100%">
                            <thead>
                                <tr>
                                    <th class="fw-bold sorting" style="width: 15%">PO Number</th>
                                    <th class="fw-bold sorting" style="width: 35%">Title</th>
                                    <th class="fw-bold sorting" style="width: 25%">Supplier</th>
                                    <th class="fw-bold text-nowrap text-center sorting" style="width: 15%">Date</th>
                                    <th class="fw-bold text-nowrap text-center sorting" style="width: 10%">Status</th>
                                </tr>
                            </thead>
                            <tbody id="archive-pos-tbody">
                                <!-- Dynamically populated by JS -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div> <!-- End settings-view-archive-pos -->

            <!-- LEVEL 5: Archive PO Details (Document Viewer) -->
            <div id="settings-view-archive-po-details" class="settings-view-pane" style="display: none;">
                <!-- Breadcrumbs -->
                <div class="d-flex align-items-center flex-wrap gap-2 mb-3">
                    <div class="archive-breadcrumb" style="padding-top: 5px;">
                        <a href="javascript:void(0);" id="btn-archive-level5-back-1" class="text-decoration-none archive-back-link">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-arrow-left"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
                            Archive
                        </a>
                        <span class="breadcrumb-separator">&gt;</span>
                        <a href="javascript:void(0);" id="btn-archive-level5-back-2" class="text-decoration-none archive-back-link">Annual Procurement Plan</a>
                        <span class="breadcrumb-separator">&gt;</span>
                        <a href="javascript:void(0);" id="btn-archive-level5-back-3" class="text-decoration-none archive-back-link">APP-2026-01</a>
                        <span class="breadcrumb-separator">&gt;</span>
                        <a href="javascript:void(0);" id="btn-archive-level5-back-4" class="text-decoration-none archive-back-link">Purchase Orders</a>
                        <span class="breadcrumb-separator">&gt;</span>
                        <span class="breadcrumb-current fw-bold red-text-2">PO_ArmChairs_Ariado</span>
                    </div>
                </div>

                <!-- Section 2: File Hierarchy and Viewer -->
                <div class="row g-3">
                    {{-- File Hierarchy --}}
                    <div class="col-md-3 pe-0">
                        <div class="card shadow-sm border-0 mb-3">
                            <div class="card-body">
                                <h5 class="card-title" style="color: #6a768c; font-size: 1.1rem; font-weight: 500; margin-bottom: 20px;">File Hierarchy</h5>
                                <ul class="treeview folder-structure" id="archiveTreeview">
                                    <li class="tv-item tv-folder">
                                        <div class="tv-header" id="archiveFolderDeliveryHeading">
                                            <div class="tv-collapsible" data-bs-toggle="collapse" data-bs-target="#archiveFolderDelivery" aria-expanded="true" aria-controls="archiveFolderDelivery">
                                                <div class="icon">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-folder" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"></path><path d="M5 4h4l3 3h7a2 2 0 0 1 2 2v8a2 2 0 0 1 -2 2h-14a2 2 0 0 1 -2 -2v-11a2 2 0 0 1 2 -2"></path></svg>
                                                </div>
                                                <p class="title">Delivery Attachment</p>
                                            </div>
                                        </div>
                                        <div id="archiveFolderDelivery" class="treeview-collapse collapse show" aria-labelledby="archiveFolderDeliveryHeading" data-bs-parent="#archiveTreeview">
                                            <ul class="treeview" id="archive-delivery-attachments-list">
                                                <!-- Dynamically populated via JS -->
                                            </ul>
                                        </div>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    
                    {{-- Right Form Viewer --}}
                    <div class="col-md-9">
                        <div class="card shadow-sm border-0 mb-3 h-100">
                            <div class="card-body">
                                <h5 class="fw-bold red-text-2 mb-3">Purchase Order</h5>
                                <hr class="m-0 p-0 mb-4">
                                
                                <h6 class="fw-bold red-text-2 mb-4" id="archive-po-detail-title-heading">Title: -</h6>
                                
                                <div class="row g-2 mb-4">
                                    <div class="col-md-7">
                                        <div class="row mb-3">
                                            <div class="col-4 app-detail-label">Supplier:</div>
                                            <div class="col-8 app-detail-value" id="archive-po-detail-supplier">-</div>
                                        </div>
                                        <div class="row mb-3">
                                            <div class="col-4 app-detail-label">Address:</div>
                                            <div class="col-8 app-detail-value" id="archive-po-detail-address">-</div>
                                        </div>
                                        <div class="row mb-3">
                                            <div class="col-4 app-detail-label">Tel No.:</div>
                                            <div class="col-8 app-detail-value" id="archive-po-detail-tele">-</div>
                                        </div>
                                        <div class="row mb-3">
                                            <div class="col-4 app-detail-label">TIN:</div>
                                            <div class="col-8 app-detail-value" id="archive-po-detail-tin">-</div>
                                        </div>
                                        <div class="row mb-3">
                                            <div class="col-4 app-detail-label">Place of Delivery:</div>
                                            <div class="col-8 app-detail-value" id="archive-po-detail-place-delivery">-</div>
                                        </div>
                                        <div class="row mb-1">
                                            <div class="col-4 app-detail-label">Date of Delivery:</div>
                                            <div class="col-8 app-detail-value" id="archive-po-detail-date-delivery">-</div>
                                        </div>
                                    </div>
                                    <div class="col-md-5">
                                        <div class="row mb-3">
                                            <div class="col-5 app-detail-label">Mode of Procurement:</div>
                                            <div class="col-7 app-detail-value" id="archive-po-detail-mode">-</div>
                                        </div>
                                        <div class="row mb-3">
                                            <div class="col-5 app-detail-label">TUP-Taguig TIN:</div>
                                            <div class="col-7 app-detail-value" id="archive-po-detail-tuptin">-</div>
                                        </div>
                                        <div class="row mb-3">
                                            <div class="col-5 app-detail-label">Delivery Term:</div>
                                            <div class="col-7 app-detail-value" id="archive-po-detail-delivery-term">-</div>
                                        </div>
                                        <div class="row mb-3">
                                            <div class="col-5 app-detail-label">Payment Term:</div>
                                            <div class="col-7 app-detail-value" id="archive-po-detail-payment-term">-</div>
                                        </div>
                                        <div class="row mb-3">
                                            <div class="col-5 app-detail-label">P.O. No.:</div>
                                            <div class="col-7 app-detail-value" id="archive-po-detail-po-no">-</div>
                                        </div>
                                        <div class="row mb-1">
                                            <div class="col-5 app-detail-label">Date:</div>
                                            <div class="col-7 app-detail-value" id="archive-po-detail-date">-</div>
                                        </div>
                                    </div>
                                </div>
                                
                                <hr class="m-0 p-0 mb-4">
                                <div id="archive-po-items-tables-container">
                                    <!-- Dynamically populated via JS -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div> <!-- End settings-view-archive-po-details -->
            
            <!-- LEVEL 5: Archive PR Details (Form Viewer) -->
            <div id="settings-view-archive-pr-details" class="settings-view-pane" style="display: none;">
                <!-- Breadcrumbs -->
                <div class="d-flex align-items-center flex-wrap gap-2 mb-3">
                    <div class="archive-breadcrumb" style="padding-top: 5px;">
                        <a href="javascript:void(0);" id="btn-archive-pr-level5-back-1" class="text-decoration-none archive-back-link">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-arrow-left"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
                            Archive
                        </a>
                        <span class="breadcrumb-separator">&gt;</span>
                        <a href="javascript:void(0);" id="btn-archive-pr-level5-back-2" class="text-decoration-none archive-back-link">Annual Procurement Plan</a>
                        <span class="breadcrumb-separator">&gt;</span>
                        <a href="javascript:void(0);" id="btn-archive-pr-level5-back-3" class="text-decoration-none archive-back-link">APP-2026-01</a>
                        <span class="breadcrumb-separator">&gt;</span>
                        <a href="javascript:void(0);" id="btn-archive-pr-level5-back-4" class="text-decoration-none archive-back-link">Purchase Requests</a>
                        <span class="breadcrumb-separator">&gt;</span>
                        <span class="breadcrumb-current fw-bold red-text-2" id="archive-pr-detail-breadcrumb-no">PR_Details</span>
                    </div>
                </div>

                <!-- Section 2: Form Viewer (Full Width) -->
                <div class="row g-3">
                    <div class="col-md-12">
                        <div class="card shadow-sm border-0 mb-3 h-100">
                            <div class="card-body">
                                <h5 class="fw-bold red-text-2 mb-3">Purchase Request</h5>
                                <hr class="m-0 p-0 mb-4">
                                
                                <h6 class="fw-bold red-text-2 mb-4" id="archive-pr-detail-no-heading">PR No.: -</h6>
                                
                                <div class="row g-2 mb-4">
                                    <div class="col-md-6">
                                        <div class="row mb-3">
                                            <div class="col-4 app-detail-label">Department:</div>
                                            <div class="col-8 app-detail-value" id="archive-pr-detail-department">-</div>
                                        </div>
                                        <div class="row mb-3">
                                            <div class="col-4 app-detail-label">Section:</div>
                                            <div class="col-8 app-detail-value" id="archive-pr-detail-section">-</div>
                                        </div>
                                        <div class="row mb-3">
                                            <div class="col-4 app-detail-label">Purpose:</div>
                                            <div class="col-8 app-detail-value" id="archive-pr-detail-purpose">-</div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="row mb-3">
                                            <div class="col-5 app-detail-label">PR Date:</div>
                                            <div class="col-7 app-detail-value" id="archive-pr-detail-date">-</div>
                                        </div>
                                        <div class="row mb-3">
                                            <div class="col-5 app-detail-label">Requested By:</div>
                                            <div class="col-7 app-detail-value" id="archive-pr-detail-requestor">-</div>
                                        </div>
                                        <div class="row mb-3">
                                            <div class="col-5 app-detail-label">Approved By:</div>
                                            <div class="col-7 app-detail-value" id="archive-pr-detail-approved-by">-</div>
                                        </div>
                                    </div>
                                </div>
                                
                                <hr class="m-0 p-0 mb-4">
                                
                                <div class="table-responsive mb-4">
                                    <table class="table table-borderless table-sm">
                                        <thead style="background-color: #f1f2f3;">
                                            <tr>
                                                <th class="text-center" style="width: 10%;">Qty.</th>
                                                <th class="text-center" style="width: 10%;">Unit</th>
                                                <th style="width: 50%;">Item Description / Specifications</th>
                                                <th class="text-end" style="width: 15%;">Unit Cost</th>
                                                <th class="text-end" style="width: 15%;">Total Cost</th>
                                            </tr>
                                        </thead>
                                        <tbody id="archive-pr-items-tbody">
                                            <!-- Dynamically populated via JS -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div> <!-- End settings-view-archive-pr-details -->

        </div> <!-- End settings-view-container -->
    @else
        <div class="settings-view-container">
            <div id="settings-view-main-non-premium" class="settings-view-pane active">
                <div class="settings-container">
                    
                    <!-- LEFT COLUMN (Theme, Notifications) -->
                    <div class="settings-left-col">
                        
                        <!-- CHOOSE THEME CARD -->
                        <div class="settings-card choose-theme-card">
                            <div class="settings-card-header">
                                <h3>Choose Theme</h3>
                            </div>
                            
                            <div class="theme-options">
                                <!-- Light Mode Option -->
                                <div class="theme-option">
                                    <label class="theme-option-label">
                                        <input type="radio" name="theme_selection" value="light" checked style="display:none;">
                                        <span class="custom-radio"></span>
                                        <div class="theme-thumbnail-wrapper">
                                            <img src="{{ asset('img/light-mode.svg') }}" alt="Light Mode" class="theme-thumbnail">
                                        </div>
                                    </label>
                                </div>

                                <!-- Dark Mode Option -->
                                <div class="theme-option">
                                    <label class="theme-option-label">
                                        <input type="radio" name="theme_selection" value="dark" style="display:none;">
                                        <span class="custom-radio"></span>
                                        <div class="theme-thumbnail-wrapper">
                                            <img src="{{ asset('img/dark-mode.svg') }}" alt="Dark Mode" class="theme-thumbnail">
                                        </div>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- NOTIFICATION CARD -->
                        <div class="settings-card notification-card">
                            <div class="card-content-inline">
                                <div class="card-text-group">
                                    <h3>Notification</h3>
                                    <p class="settings-description">Enable all notifications</p>
                                </div>
                                <div class="toggle-switch-wrapper">
                                    <input type="checkbox" id="notification-toggle" class="switch-input" checked>
                                    <label class="switch-label" for="notification-toggle">
                                        <span class="slider"></span>
                                    </label>
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- RIGHT COLUMN (Manual Booklet, Promo Banner) -->
                    <div class="settings-right-col">
                        
                        <!-- MANUAL BOOKLET CARD -->
                        <div class="settings-card manual-booklet-card">
                            <h3>Manual Booklet</h3>
                            <p class="manual-booklet-text">Click <a href="#" class="manual-booklet-link"><i><u>I-TRAC Manual Booklet</u></i></a> to download.</p>
                        </div>

                        <!-- RED PROMOTION BANNER -->
                        <div class="promo-banner">
                            <!-- Integrated Background SVG with 3D Phone that overflows -->
                            <img src="{{ asset('img/mobile-3d-bg.svg') }}" alt="I-TRAC Mobile App" class="promo-bg-image">
                            
                            <!-- Content overlay -->
                            <div class="promo-content">
                                <div class="promo-left">
                                    <!-- Download Now button is omitted for non-premium/unassigned roles -->
                                </div>
                                <div class="promo-right"></div>
                            </div>
                        </div>

                    </div>

                </div>
            </div>
        </div>
    @endif

</div>

<script>
(function() {
    function initThemeSettingsSelector() {
        var themeRadios = document.querySelectorAll('input[name="theme_selection"]');
        if (!themeRadios.length || !window.ThemeManager) return;
        
        // Sync radio buttons to current theme state
        function syncThemeRadios() {
            var activeValue = window.ThemeManager.isDark() ? 'dark' : 'light';
            var radioToCheck = document.querySelector('input[name="theme_selection"][value="' + activeValue + '"]');
            if (radioToCheck) {
                radioToCheck.checked = true;
            }
        }
        
        // Initial sync
        syncThemeRadios();
        
        // Listen to ThemeManager events (e.g. when toggled from header button)
        document.addEventListener('itrac:themeChanged', function() {
            syncThemeRadios();
        });
        
        // Handle radio button selection
        themeRadios.forEach(function(radio) {
            radio.addEventListener('change', function() {
                var isTargetDark = this.value === 'dark';
                var currentIsDark = window.ThemeManager.isDark();
                
                // Only trigger if state is actually changing
                if (isTargetDark !== currentIsDark) {
                    window.ThemeManager.setDark(isTargetDark);
                }
            });
        });
    }
    
    // Execute when DOM is ready or immediately if already loaded
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initThemeSettingsSelector);
    } else {
        initThemeSettingsSelector();
    }
})();
</script>

<!-- Set APP Modal -->
<div class="modal fade" id="setAppModal" tabindex="-1" aria-labelledby="setAppModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content custom-app-modal">
            <div class="modal-header custom-modal-header">
                <div class="header-left">
                    <h3 class="modal-title" id="setAppModalLabel">Annual Procurement Plan</h3>
                    <p class="modal-subtitle">Set this year's APP</p>
                </div>
                <div class="header-right">
                    <div class="modal-search-wrapper">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="search-icon feather feather-search"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                        <input type="text" id="modal-app-search" class="form-control modal-search-input" placeholder="Search here...">
                    </div>
                    <button type="button" class="btn-modal-close" data-bs-dismiss="modal" aria-label="Close">
                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" class="feather feather-x"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                    </button>
                </div>
            </div>
            <div class="modal-body custom-modal-body">
                <div class="app-plans-list">
                    @forelse($apps ?? [] as $app)
                        <div class="app-plan-item" data-search="{{ strtolower($app->app_title . ' ' . $app->app_unique_code) }}">
                            <div class="plan-details">
                                <h4 class="plan-title">{{ $app->app_title }}</h4>
                                <p class="plan-subtitle">{{ $app->app_unique_code }} | {{ \Carbon\Carbon::parse($app->created_at)->format('m/d/Y') }}</p>
                            </div>
                            @if($app->app_status === 'Done')
                                @if(isset($activeAppId) && $app->app_id == $activeAppId)
                                    <button type="button" class="btn btn-set-app active-set" data-app-id="{{ $app->app_id }}" data-app-title="{{ $app->app_title }}">Active</button>
                                @else
                                    <button type="button" class="btn btn-set-app" data-app-id="{{ $app->app_id }}" data-app-title="{{ $app->app_title }}">Set APP</button>
                                @endif
                            @else
                                <button type="button" class="btn btn-set-app-disabled" disabled>Draft</button>
                            @endif
                        </div>
                    @empty
                        <div class="text-center p-5">
                            <p class="text-muted mb-0" style="font-size: 0.9rem;">No Annual Procurement Plans found.</p>
                        </div>
                    @endforelse

                    <!-- Empty State -->
                    <div id="modal-search-empty-state" class="text-center p-5" style="display: none;">
                        <p class="text-muted mb-0" style="font-size: 0.9rem;">No Annual Procurement Plans found.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

