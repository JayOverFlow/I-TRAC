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
                        <a href="#" class="set-link">Click to set <span class="arrow-icon">&gt;</span></a>
                    </div>
                    
                    <div class="app-card-divider"></div>
                    
                    <div class="settings-alert-box">
                        <div class="alert-icon-wrapper">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-info"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
                        </div>
                        <span class="alert-text">No APP is set</span>
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
        const themeRadios = document.querySelectorAll('input[name="theme_selection"]');
        if (!themeRadios.length) return;
        
        // 1. Function to synchronize theme options with current body class
        function syncThemeRadios() {
            const isDark = document.body.classList.contains('dark');
            const activeValue = isDark ? 'dark' : 'light';
            const radioToCheck = document.querySelector(`input[name="theme_selection"][value="${activeValue}"]`);
            if (radioToCheck) {
                radioToCheck.checked = true;
            }
        }
        
        // Initial sync
        syncThemeRadios();
        
        // Listen to global toggles (e.g. standard theme toggler click)
        const globalThemeToggle = document.querySelector('.theme-toggle');
        if (globalThemeToggle) {
            globalThemeToggle.addEventListener('click', function() {
                // Wait slightly for the app.js transition fade to apply body class
                setTimeout(syncThemeRadios, 250);
            });
        }
        
        // 2. Intercept settings radio selection clicks
        themeRadios.forEach(radio => {
            radio.addEventListener('change', function() {
                const targetValue = this.value;
                const isTargetDark = targetValue === 'dark';
                const currentIsDark = document.body.classList.contains('dark');
                
                // Only trigger if state is actually changing
                if (isTargetDark !== currentIsDark) {
                    // Replicate premium global switch fade-out/in
                    document.body.style.transition = 'opacity 0.2s ease-in-out';
                    document.body.style.opacity = '0';
                    
                    setTimeout(function() {
                        // Toggle body class
                        if (isTargetDark) {
                            document.body.classList.add('dark');
                        } else {
                            document.body.classList.remove('dark');
                        }
                        
                        // Update localstorage
                        const getLocalStorage = localStorage.getItem("theme");
                        if (getLocalStorage) {
                            try {
                                const parseObj = JSON.parse(getLocalStorage);
                                if (parseObj && parseObj.settings && parseObj.settings.layout) {
                                    parseObj.settings.layout.darkMode = isTargetDark;
                                    localStorage.setItem("theme", JSON.stringify(parseObj));
                                }
                            } catch (e) {
                                console.error("Error parsing localstorage theme", e);
                            }
                        } else {
                            const settingsObject = {
                                admin: 'Equation Admin Template',
                                settings: {
                                    layout: {
                                        name: 'Horizontal Light Menu',
                                        darkMode: isTargetDark,
                                    }
                                }
                            };
                            localStorage.setItem("theme", JSON.stringify(settingsObject));
                        }
                        
                        // Update header logo
                        const logoImg = document.querySelector('.navbar-logo');
                        if (logoImg) {
                            const logoSrc = isTargetDark ? '/img/logo.svg' : '/img/itrac-header-logo.png';
                            logoImg.setAttribute('src', logoSrc);
                        }
                        
                        // Fade back in
                        document.body.style.transition = 'opacity 0.5s ease-out';
                        document.body.style.opacity = '1';
                        
                        setTimeout(function() {
                            document.body.style.transition = '';
                        }, 500);
                        
                    }, 200);
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

