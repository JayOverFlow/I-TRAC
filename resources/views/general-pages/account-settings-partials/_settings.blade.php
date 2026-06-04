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

                <!-- FAQS CARD -->
                <div class="settings-card faqs-card">
                    <div class="card-content-inline">
                        <div class="card-text-group">
                            <h3>FAQs</h3>
                            <p class="settings-description">Your Profile will be visible to anyone on the network.</p>
                        </div>
                        <div class="card-action-link">
                            <a href="#" class="learn-more-link">Learn more <span class="arrow-icon">&gt;</span></a>
                        </div>
                    </div>
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
                    
                    <div id="settings-app-alert-box" class="settings-alert-box">
                        <div id="settings-app-alert-icon-wrapper" class="alert-icon-wrapper">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-info"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
                        </div>
                        <span id="settings-app-alert-text" class="alert-text">No APP is set</span>
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
                        <table class="table table-striped dt-table-hover dataTable" style="width:100%">
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
                        <table class="table table-striped dt-table-hover dataTable" style="width:100%">
                            <thead>
                                <tr>
                                    <th class="fw-bold sorting" style="width: 15%">APP Project-Code</th>
                                    <th class="fw-bold sorting" style="width: 50%">Project Title</th>
                                    <th class="fw-bold sorting" style="width: 20%">Assigned to</th>
                                    <th class="fw-bold text-nowrap text-center sorting" style="width: 15%">Date Created</th>
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
                        <table class="table table-striped dt-table-hover dataTable" style="width:100%">
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
                                    <li class="tv-item tv-file">
                                        <span class="icon">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-file" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"></path><path d="M14 3v4a1 1 0 0 0 1 1h4"></path><path d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z"></path></svg>
                                        </span>
                                        <p>Purchase Request</p>
                                    </li>
                                    <li class="tv-item tv-file">
                                        <span class="icon">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-file" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"></path><path d="M14 3v4a1 1 0 0 0 1 1h4"></path><path d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z"></path></svg>
                                        </span>
                                        <p>Purchase Order</p>
                                    </li>
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
                                            <ul class="treeview">
                                                <li class="tv-item tv-folder">
                                                    <div class="tv-header" id="archiveFolderEquipHeading">
                                                        <div class="tv-collapsible collapsed" data-bs-toggle="collapse" data-bs-target="#archiveFolderEquip" aria-expanded="false" aria-controls="archiveFolderEquip">
                                                            <div class="icon">
                                                                <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-folder" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"></path><path d="M5 4h4l3 3h7a2 2 0 0 1 2 2v8a2 2 0 0 1 -2 2h-14a2 2 0 0 1 -2 -2v-11a2 2 0 0 1 2 -2"></path></svg>
                                                            </div>
                                                            <p class="title">Equipment</p>
                                                        </div>
                                                    </div>
                                                    <div id="archiveFolderEquip" class="treeview-collapse collapse" aria-labelledby="archiveFolderEquipHeading" data-bs-parent="#archiveFolderDelivery">
                                                        <ul class="treeview">
                                                            <li class="tv-item tv-file">
                                                                <span class="icon">
                                                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-file" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"></path><path d="M14 3v4a1 1 0 0 0 1 1h4"></path><path d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z"></path></svg>
                                                                </span>
                                                                <p>Inspection and Acceptance Report</p>
                                                            </li>
                                                            <li class="tv-item tv-file">
                                                                <span class="icon">
                                                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-file" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"></path><path d="M14 3v4a1 1 0 0 0 1 1h4"></path><path d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z"></path></svg>
                                                                </span>
                                                                <p>Property Acknowledgement Receipt</p>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                </li>
                                                <li class="tv-item tv-folder">
                                                    <div class="tv-header" id="archiveFolderSemiHeading">
                                                        <div class="tv-collapsible show" data-bs-toggle="collapse" data-bs-target="#archiveFolderSemi" aria-expanded="true" aria-controls="archiveFolderSemi">
                                                            <div class="icon">
                                                                <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-folder" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"></path><path d="M5 4h4l3 3h7a2 2 0 0 1 2 2v8a2 2 0 0 1 -2 2h-14a2 2 0 0 1 -2 -2v-11a2 2 0 0 1 2 -2"></path></svg>
                                                            </div>
                                                            <p class="title">Semi-Expendable</p>
                                                        </div>
                                                    </div>
                                                    <div id="archiveFolderSemi" class="treeview-collapse collapse show" aria-labelledby="archiveFolderSemiHeading" data-bs-parent="#archiveFolderDelivery">
                                                        <ul class="treeview">
                                                            <li class="tv-item tv-file">
                                                                <span class="icon">
                                                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-file" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"></path><path d="M14 3v4a1 1 0 0 0 1 1h4"></path><path d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z"></path></svg>
                                                                </span>
                                                                <p>Inspection and Acceptance Report</p>
                                                            </li>
                                                            <li class="tv-item tv-file">
                                                                <span class="icon">
                                                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-file" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"></path><path d="M14 3v4a1 1 0 0 0 1 1h4"></path><path d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z"></path></svg>
                                                                </span>
                                                                <p class="red-text-2 fw-bold">Requisition and Issue Slip</p>
                                                            </li>
                                                            <li class="tv-item tv-file">
                                                                <span class="icon">
                                                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-file" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"></path><path d="M14 3v4a1 1 0 0 0 1 1h4"></path><path d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z"></path></svg>
                                                                </span>
                                                                <p>Inventory Custodian Slip</p>
                                                            </li>
                                                            <li class="tv-item tv-file">
                                                                <span class="icon">
                                                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-file" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"></path><path d="M14 3v4a1 1 0 0 0 1 1h4"></path><path d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z"></path></svg>
                                                                </span>
                                                                <p>Report of Semi Expendable property issued</p>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                </li>
                                                <li class="tv-item tv-folder">
                                                    <div class="tv-header" id="archiveFolderSupplyHeading">
                                                        <div class="tv-collapsible collapsed" data-bs-toggle="collapse" data-bs-target="#archiveFolderSupply" aria-expanded="false" aria-controls="archiveFolderSupply">
                                                            <div class="icon">
                                                                <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-folder" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"></path><path d="M5 4h4l3 3h7a2 2 0 0 1 2 2v8a2 2 0 0 1 -2 2h-14a2 2 0 0 1 -2 -2v-11a2 2 0 0 1 2 -2"></path></svg>
                                                            </div>
                                                            <p class="title">Supply and Materials</p>
                                                        </div>
                                                    </div>
                                                    <div id="archiveFolderSupply" class="treeview-collapse collapse" aria-labelledby="archiveFolderSupplyHeading" data-bs-parent="#archiveFolderDelivery">
                                                        <ul class="treeview">
                                                            <!-- more files -->
                                                        </ul>
                                                    </div>
                                                </li>
                                                <li class="tv-item tv-folder">
                                                    <div class="tv-header" id="archiveFolderNotHeading">
                                                        <div class="tv-collapsible collapsed" data-bs-toggle="collapse" data-bs-target="#archiveFolderNot" aria-expanded="false" aria-controls="archiveFolderNot">
                                                            <div class="icon">
                                                                <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-folder" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"></path><path d="M5 4h4l3 3h7a2 2 0 0 1 2 2v8a2 2 0 0 1 -2 2h-14a2 2 0 0 1 -2 -2v-11a2 2 0 0 1 2 -2"></path></svg>
                                                            </div>
                                                            <p class="title">Not Delivered</p>
                                                        </div>
                                                    </div>
                                                    <div id="archiveFolderNot" class="treeview-collapse collapse" aria-labelledby="archiveFolderNotHeading" data-bs-parent="#archiveFolderDelivery">
                                                        <ul class="treeview"></ul>
                                                    </div>
                                                </li>
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
                                
                                <h6 class="fw-bold red-text-2 mb-4">Title: PO_ArmChairs_Ariado</h6>
                                
                                <div class="row g-2 mb-4">
                                    <div class="col-md-7">
                                        <div class="row mb-3">
                                            <div class="col-4 app-detail-label">Supplier:</div>
                                            <div class="col-8 app-detail-value">SOLID STEEL MACHINERY AND TOOLS INC.</div>
                                        </div>
                                        <div class="row mb-3">
                                            <div class="col-4 app-detail-label">Address:</div>
                                            <div class="col-8 app-detail-value">#67 Sen. Gil J. Puyat Ave., Makati City</div>
                                        </div>
                                        <div class="row mb-3">
                                            <div class="col-4 app-detail-label">Tel No.:</div>
                                            <div class="col-8 app-detail-value">0933-5522253</div>
                                        </div>
                                        <div class="row mb-3">
                                            <div class="col-4 app-detail-label">TIN:</div>
                                            <div class="col-8 app-detail-value">007-877-300-000</div>
                                        </div>
                                        <div class="row mb-3">
                                            <div class="col-4 app-detail-label">Place of Delivery:</div>
                                            <div class="col-8 app-detail-value">TUP-Taguig Supply Office</div>
                                        </div>
                                        <div class="row mb-1">
                                            <div class="col-4 app-detail-label">Date of Delivery:</div>
                                            <div class="col-8 app-detail-value"></div>
                                        </div>
                                    </div>
                                    <div class="col-md-5">
                                        <div class="row mb-3">
                                            <div class="col-5 app-detail-label">Mode of Procurement:</div>
                                            <div class="col-7 app-detail-value">Shopping Under Section 52. 1. b</div>
                                        </div>
                                        <div class="row mb-3">
                                            <div class="col-5 app-detail-label">TUP-Taguig TIN:</div>
                                            <div class="col-7 app-detail-value">000-824-548-001</div>
                                        </div>
                                        <div class="row mb-3">
                                            <div class="col-5 app-detail-label">Delivery Term:</div>
                                            <div class="col-7 app-detail-value">30 Calendar Days</div>
                                        </div>
                                        <div class="row mb-3">
                                            <div class="col-5 app-detail-label">Payment Term:</div>
                                            <div class="col-7 app-detail-value">DOST Funds</div>
                                        </div>
                                        <div class="row mb-3">
                                            <div class="col-5 app-detail-label">P.O. No.:</div>
                                            <div class="col-7 app-detail-value">2025-02-01</div>
                                        </div>
                                        <div class="row mb-1">
                                            <div class="col-5 app-detail-label">Date:</div>
                                            <div class="col-7 app-detail-value">February 10, 2025</div>
                                        </div>
                                    </div>
                                </div>
                                
                                <hr class="m-0 p-0 mb-4">
                                <h6 class="fw-bold mb-3" style="color: #3b3f5c;">Supply and Materials</h6>
                                
                                <div class="table-responsive">
                                    <table class="table table-borderless table-sm">
                                        <thead style="background-color: #f1f2f3;">
                                            <tr>
                                                <th class="text-center" style="width: 10%;">Stock</th>
                                                <th class="text-center" style="width: 10%;">Unit</th>
                                                <th style="width: 40%;">Article</th>
                                                <th class="text-center" style="width: 10%;">Qty.</th>
                                                <th class="text-end" style="width: 15%;">Unit Cost</th>
                                                <th class="text-end" style="width: 15%;">Amount</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td class="text-center"></td>
                                                <td class="text-center app-detail-value" style="font-weight: 500;">pc</td>
                                                <td>
                                                    <span class="app-detail-value" style="font-size: 0.8rem;">Mobile TV Cart Set</span><br>
                                                    <small class="app-detail-label" style="font-size: 0.75rem;">Processor: Custom NVIDIA chip (with AI + ray tracing support)<br>Size: 7.9-inch LCD touchscreen<br>Refresh rate: Up to 120Hz with VRR<br>Internal storage: 256 GB (UFS)</small>
                                                </td>
                                                <td class="text-center app-detail-value" style="font-weight: 500;">5</td>
                                                <td class="text-end app-detail-value" style="font-weight: 500;">₱ 21,500.00</td>
                                                <td class="text-end app-detail-value" style="font-weight: 500;">₱ 21,500.00</td>
                                            </tr>
                                            <tr>
                                                <td class="text-center"></td>
                                                <td class="text-center app-detail-value" style="font-weight: 500;">pc</td>
                                                <td>
                                                    <span class="app-detail-value" style="font-size: 0.8rem;">Mobile TV Cart Set</span>
                                                </td>
                                                <td class="text-center app-detail-value" style="font-weight: 500;">5</td>
                                                <td class="text-end app-detail-value" style="font-weight: 500;">₱ 21,500.00</td>
                                                <td class="text-end app-detail-value" style="font-weight: 500;">₱ 21,500.00</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <hr class="m-0 p-0 mb-4">
                                <h6 class="fw-bold mb-3" style="color: #3b3f5c;">Equipment</h6>
                            </div>
                        </div>
                    </div>
                </div>
            </div> <!-- End settings-view-archive-po-details -->

        </div> <!-- End settings-view-container -->
    @else
        <!-- Placeholder for Unassigned and general roles -->
        <div class="settings-placeholder-container">
            <div class="settings-placeholder-card">
                <h3>Settings Customize</h3>
                <p>Customization settings options are tailored to your department's specific procurement role. Features for your account role will be available in the next release.</p>
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
                    <!-- F.Y. 2026 Active Plan -->
                    <div class="app-plan-item" data-search="2026">
                        <div class="plan-details">
                            <h4 class="plan-title">Annual Procurement Plan for Year F.Y. 2026</h4>
                            <p class="plan-subtitle">APP-0000 | 01/02/2026</p>
                        </div>
                        <button type="button" class="btn btn-set-app active-set" id="btn-set-app-2026">Set APP</button>
                    </div>

                    <!-- F.Y. 2026 Disabled Plan -->
                    <div class="app-plan-item" data-search="2026">
                        <div class="plan-details">
                            <h4 class="plan-title">Annual Procurement Plan for Year F.Y. 2026</h4>
                            <p class="plan-subtitle">APP-0000 | 01/02/2026</p>
                        </div>
                        <button type="button" class="btn btn-set-app-disabled" disabled>Disabled</button>
                    </div>

                    <!-- F.Y. 2024 Disabled Plan -->
                    <div class="app-plan-item" data-search="2024">
                        <div class="plan-details">
                            <h4 class="plan-title">Annual Procurement Plan for Year F.Y. 2024</h4>
                            <p class="plan-subtitle">APP-0000 | 01/02/2026</p>
                        </div>
                        <button type="button" class="btn btn-set-app-disabled" disabled>Disabled</button>
                    </div>

                    <!-- F.Y. 2023 Disabled Plan -->
                    <div class="app-plan-item" data-search="2023">
                        <div class="plan-details">
                            <h4 class="plan-title">Annual Procurement Plan for Year F.Y. 2023</h4>
                            <p class="plan-subtitle">APP-0000 | 01/02/2026</p>
                        </div>
                        <button type="button" class="btn btn-set-app-disabled" disabled>Disabled</button>
                    </div>

                    <!-- F.Y. 2022 Disabled Plan -->
                    <div class="app-plan-item" data-search="2022">
                        <div class="plan-details">
                            <h4 class="plan-title">Annual Procurement Plan for Year F.Y. 2022</h4>
                            <p class="plan-subtitle">APP-0000 | 01/02/2026</p>
                        </div>
                        <button type="button" class="btn btn-set-app-disabled" disabled>Disabled</button>
                    </div>

                    <!-- F.Y. 2021 Disabled Plan -->
                    <div class="app-plan-item" data-search="2021">
                        <div class="plan-details">
                            <h4 class="plan-title">Annual Procurement Plan for Year F.Y. 2021</h4>
                            <p class="plan-subtitle">APP-0000 | 01/02/2026</p>
                        </div>
                        <button type="button" class="btn btn-set-app-disabled" disabled>Disabled</button>
                    </div>

                    <!-- F.Y. 2020 Disabled Plan -->
                    <div class="app-plan-item" data-search="2020">
                        <div class="plan-details">
                            <h4 class="plan-title">Annual Procurement Plan for Year F.Y. 2020</h4>
                            <p class="plan-subtitle">APP-0000 | 01/02/2026</p>
                        </div>
                        <button type="button" class="btn btn-set-app-disabled" disabled>Disabled</button>
                    </div>

                    <!-- F.Y. 2019 Disabled Plan -->
                    <div class="app-plan-item" data-search="2019">
                        <div class="plan-details">
                            <h4 class="plan-title">Annual Procurement Plan for Year F.Y. 2019</h4>
                            <p class="plan-subtitle">APP-0000 | 01/02/2026</p>
                        </div>
                        <button type="button" class="btn btn-set-app-disabled" disabled>Disabled</button>
                    </div>

                    <!-- Empty State -->
                    <div id="modal-search-empty-state" class="text-center p-5" style="display: none;">
                        <p class="text-muted mb-0" style="font-size: 0.9rem;">No Annual Procurement Plans found.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

