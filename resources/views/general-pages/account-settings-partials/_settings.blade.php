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
                        <a href="#" class="view-link">View <span class="arrow-icon">&gt;</span></a>
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
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" class="search-icon" style="transform: rotate(45deg);"><path d="M14 7 A6 6 0 1 0 7 14"></path></svg>
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
                </div>
            </div>
        </div>
    </div>
</div>

