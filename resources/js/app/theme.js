/**
 * ThemeManager — Centralized dark mode state manager for I-TRAC.
 *
 * Reads/writes the existing localStorage "theme" object (theme.settings.layout.darkMode),
 * toggles the .dark class on document.body, swaps the legacy .navbar-logo src,
 * and dispatches an "itrac:themeChanged" custom event so all UI components stay in sync.
 *
 * Usage:
 *   ThemeManager.isDark()        // returns true/false
 *   ThemeManager.setDark(bool)   // sets dark mode on/off
 *   ThemeManager.toggle()        // flips current state
 *
 *   document.addEventListener('itrac:themeChanged', function(e) {
 *       console.log('Dark mode is now:', e.detail.isDark);
 *   });
 */
(function () {

    var STORAGE_KEY = 'theme';
    var DARK_LOGO = '/img/logo.svg';
    var LIGHT_LOGO = '/img/itrac-header-logo.png';

    /**
     * Read the current darkMode flag from localStorage.
     * Returns true if dark mode is enabled, false otherwise.
     */
    function isDark() {
        try {
            var raw = localStorage.getItem(STORAGE_KEY);
            if (raw) {
                var obj = JSON.parse(raw);
                if (obj && obj.settings && obj.settings.layout) {
                    return !!obj.settings.layout.darkMode;
                }
            }
        } catch (e) {
            console.error('[ThemeManager] Error reading theme from localStorage:', e);
        }
        return false;
    }

    /**
     * Set dark mode on or off.
     * Updates localStorage, toggles .dark on body, swaps logo, and dispatches event.
     *
     * @param {boolean} dark — true to enable dark mode, false for light mode
     */
    function setDark(dark) {
        // 1. Update body class
        if (dark) {
            document.body.classList.add('dark');
            document.documentElement.classList.add('dark');
        } else {
            document.body.classList.remove('dark');
            document.documentElement.classList.remove('dark');
        }

        // 2. Update localStorage (preserve existing structure)
        try {
            var raw = localStorage.getItem(STORAGE_KEY);
            var obj;
            if (raw) {
                obj = JSON.parse(raw);
                if (obj && obj.settings && obj.settings.layout) {
                    obj.settings.layout.darkMode = dark;
                }
            } else {
                // Bootstrap a new theme object if none exists
                obj = {
                    admin: 'Equation Admin Template',
                    settings: {
                        layout: {
                            name: 'Horizontal Light Menu',
                            toggle: true,
                            darkMode: dark,
                            boxed: true,
                            logo: {
                                darkLogo: DARK_LOGO,
                                lightLogo: LIGHT_LOGO
                            }
                        }
                    },
                    reset: false
                };
            }
            localStorage.setItem(STORAGE_KEY, JSON.stringify(obj));
        } catch (e) {
            console.error('[ThemeManager] Error writing theme to localStorage:', e);
        }

        // 3. Swap legacy .navbar-logo src (for backward compatibility)
        var navbarLogo = document.querySelector('.navbar-logo');
        if (navbarLogo) {
            navbarLogo.setAttribute('src', dark ? DARK_LOGO : LIGHT_LOGO);
        }

        // 4. Dispatch custom event for all listeners
        document.dispatchEvent(new CustomEvent('itrac:themeChanged', {
            detail: { isDark: dark }
        }));
    }

    /**
     * Toggle dark mode (flip current state).
     */
    function toggle() {
        setDark(!isDark());
    }

    // Expose globally
    window.ThemeManager = {
        isDark: isDark,
        setDark: setDark,
        toggle: toggle
    };

    // Auto-apply on load to ensure <body> gets the class
    if (isDark()) {
        if (document.body) {
            setDark(true);
        } else {
            document.addEventListener('DOMContentLoaded', function() {
                setDark(true);
            });
        }
    }

})();
