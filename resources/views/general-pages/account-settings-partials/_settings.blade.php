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
                                    <th class="fw-bold sorting" style="width: 15%">APP-Code</th>
                                    <th class="fw-bold sorting" style="width: 50%">Title</th>
                                    <th class="fw-bold sorting" style="width: 20%">Created by</th>
                                    <th class="fw-bold text-nowrap text-center sorting" style="width: 15%">Date Created</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>APP-2026-01</td>
                                    <td>Annual Procurement Plan for Year F.Y. 2026</td>
                                    <td>Patrick James Ariado</td>
                                    <td class="text-center">03/01/2026</td>
                                </tr>
                                <tr>
                                    <td>APP-2026-01</td>
                                    <td>Annual Procurement Plan for Year F.Y. 2025</td>
                                    <td>Miguel Ibanez</td>
                                    <td class="text-center">03/22/2021</td>
                                </tr>
                                <tr>
                                    <td>APP-2026-01</td>
                                    <td>Annual Procurement Plan for Year F.Y. 2024</td>
                                    <td>Milo Bougainvillea</td>
                                    <td class="text-center">03/22/2021</td>
                                </tr>
                                <tr>
                                    <td>APP-2026-01</td>
                                    <td>Annual Procurement Plan for Year F.Y. 2023</td>
                                    <td>Miguel Ibanez</td>
                                    <td class="text-center">03/22/2021</td>
                                </tr>
                                <tr>
                                    <td>APP-2026-01</td>
                                    <td>Annual Procurement Plan for Year F.Y. 2022</td>
                                    <td>Milo Bougainvillea</td>
                                    <td class="text-center">03/22/2021</td>
                                </tr>
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

