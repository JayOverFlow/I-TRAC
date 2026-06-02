/**
 * I-TRAC Annual Procurement Plan Settings Modal Interactions
 * Handles real-time search filtering, simulated button states,
 * and persistent settings card state updates.
 */

(function() {
    'use strict';

    function initSettingsModalInteractions() {
        var searchInput = document.getElementById('modal-app-search');
        var planItems = document.querySelectorAll('.app-plan-item');
        var setApp2026Btn = document.getElementById('btn-set-app-2026');
        
        var alertBox = document.getElementById('settings-app-alert-box');
        var alertIconWrapper = document.getElementById('settings-app-alert-icon-wrapper');
        var alertText = document.getElementById('settings-app-alert-text');

        // 1. Restore Persistent State from localStorage
        var savedAppState = localStorage.getItem('itrac_active_app_state');
        if (savedAppState === 'active_2026' && alertBox && alertIconWrapper && alertText) {
            applyActiveAPPState(false); // Restore instantly without animations
        }

        // 2. Real-time Search Filter
        if (searchInput) {
            searchInput.addEventListener('input', function() {
                var query = this.value.toLowerCase().trim();
                
                planItems.forEach(function(item) {
                    var title = item.querySelector('.plan-title').textContent.toLowerCase();
                    var subtitle = item.querySelector('.plan-subtitle').textContent.toLowerCase();
                    
                    if (title.indexOf(query) !== -1 || subtitle.indexOf(query) !== -1) {
                        item.style.setProperty('display', 'flex', 'important');
                    } else {
                        item.style.setProperty('display', 'none', 'important');
                    }
                });
            });
        }

        // 3. Simulated APP Setting Click Flow
        if (setApp2026Btn) {
            setApp2026Btn.addEventListener('click', function() {
                var button = this;
                
                // Add elegant micro-animation state
                button.disabled = true;
                button.style.width = button.offsetWidth + 'px'; // Lock width to prevent text wrap
                button.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true" style="width: 12px; height: 12px; border-width: 2px;"></span>';
                
                setTimeout(function() {
                    // Apply visual state transition
                    applyActiveAPPState(true);
                    
                    // Save state persistently
                    localStorage.setItem('itrac_active_app_state', 'active_2026');

                    // Close modal smoothly
                    var modalEl = document.getElementById('setAppModal');
                    if (modalEl) {
                        var modalInstance = bootstrap.Modal.getInstance(modalEl);
                        if (modalInstance) {
                            modalInstance.hide();
                        }
                    }

                    // Reset button after modal closes
                    setTimeout(function() {
                        button.disabled = false;
                        button.innerHTML = 'Set APP';
                    }, 500);

                }, 600); // Elegant simulated latency delay
            });
        }

        // Helper to apply active card state
        function applyActiveAPPState(animate) {
            if (!alertBox || !alertIconWrapper || !alertText) return;

            if (animate) {
                alertBox.style.transition = 'all 0.3s ease';
                alertBox.style.opacity = '0';
                
                setTimeout(function() {
                    alertBox.classList.add('settings-app-success-alert');
                    alertIconWrapper.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="feather feather-check-circle"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>';
                    alertText.innerHTML = 'Annual Procurement Plan for Year F.Y. 2026 is currently active';
                    alertBox.style.opacity = '1';
                }, 300);
            } else {
                alertBox.classList.add('settings-app-success-alert');
                alertIconWrapper.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="feather feather-check-circle"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>';
                alertText.innerHTML = 'Annual Procurement Plan for Year F.Y. 2026 is currently active';
            }
        }
    }

    // Execute initialization on DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initSettingsModalInteractions);
    } else {
        initSettingsModalInteractions();
    }
})();
