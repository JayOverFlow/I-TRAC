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
        var setAppBtns = document.querySelectorAll('.btn-set-app');
        
        var alertBox = document.getElementById('settings-app-alert-box');
        var alertIconWrapper = document.getElementById('settings-app-alert-icon-wrapper');
        var alertText = document.getElementById('settings-app-alert-text');

        // 1. Sync localStorage state with backend-rendered state
        var isBackendActive = alertBox && alertBox.classList.contains('settings-app-success-alert');
        
        if (!isBackendActive) {
            // No active APP is set on the backend, clear local storage
            localStorage.removeItem('itrac_active_app_id');
            localStorage.removeItem('itrac_active_app_title');
        } else {
            // Sync local storage with backend-rendered active APP
            var activeBtn = document.querySelector('.btn-set-app.active-set');
            if (activeBtn) {
                var activeAppId = activeBtn.getAttribute('data-app-id');
                var activeAppTitle = activeBtn.getAttribute('data-app-title');
                localStorage.setItem('itrac_active_app_id', activeAppId);
                localStorage.setItem('itrac_active_app_title', activeAppTitle);
            }
        }

        // 2. Real-time Search Filter
        if (searchInput) {
            searchInput.addEventListener('input', function() {
                var query = this.value.toLowerCase().trim();
                var hasResults = false;
                
                planItems.forEach(function(item) {
                    var title = item.querySelector('.plan-title').textContent.toLowerCase();
                    var subtitle = item.querySelector('.plan-subtitle').textContent.toLowerCase();
                    
                    if (title.indexOf(query) !== -1 || subtitle.indexOf(query) !== -1) {
                        item.style.setProperty('display', 'flex', 'important');
                        hasResults = true;
                    } else {
                        item.style.setProperty('display', 'none', 'important');
                    }
                });

                var emptyState = document.getElementById('modal-search-empty-state');
                if (emptyState) {
                    emptyState.style.setProperty('display', hasResults ? 'none' : 'block', 'important');
                }
            });
        }

        // 3. Dynamic APP Setting Click Flow
        setAppBtns.forEach(function(button) {
            button.addEventListener('click', function() {
                var btn = this;
                var appId = btn.getAttribute('data-app-id');
                var appTitle = btn.getAttribute('data-app-title');
                
                // Add elegant micro-animation state
                btn.disabled = true;
                var originalWidth = btn.offsetWidth;
                btn.style.width = originalWidth + 'px'; // Lock width to prevent text wrap
                btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true" style="width: 12px; height: 12px; border-width: 2px;"></span>';
                
                fetch('/account-settings/set-active-app', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ app_id: appId })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        setTimeout(function() {
                            // Reset all set buttons inside modal first
                            setAppBtns.forEach(function(b) {
                                b.classList.remove('active-set');
                                b.textContent = 'Set APP';
                            });

                            // Mark this one as active-set
                            btn.classList.add('active-set');
                            btn.textContent = 'Active';

                            // Apply visual state transition on the main card alert box
                            applyActiveAPPState(appTitle, true);
                            
                            // Save state persistently
                            localStorage.setItem('itrac_active_app_id', appId);
                            localStorage.setItem('itrac_active_app_title', appTitle);

                            // Close modal smoothly
                            var modalEl = document.getElementById('setAppModal');
                            if (modalEl) {
                                var modalInstance = bootstrap.Modal.getInstance(modalEl);
                                if (modalInstance) {
                                    modalInstance.hide();
                                }
                            }

                            // Reload page to reflect changes
                            window.location.reload();

                        }, 600); // Elegant simulated latency delay
                    } else {
                        alert(data.message || 'Failed to set active APP.');
                        btn.disabled = false;
                        btn.textContent = 'Set APP';
                    }
                })
                .catch(error => {
                    console.error('Error setting active APP:', error);
                    alert('An error occurred. Please try again.');
                    btn.disabled = false;
                    btn.textContent = 'Set APP';
                });
            });
        });

        // Helper to apply active card state
        function applyActiveAPPState(title, animate) {
            if (!alertBox || !alertIconWrapper || !alertText) return;

            var displayText = title + ' is currently active';

            if (animate) {
                alertBox.style.transition = 'all 0.3s ease';
                alertBox.style.opacity = '0';
                
                setTimeout(function() {
                    alertBox.classList.add('settings-app-success-alert');
                    alertIconWrapper.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="feather feather-check-circle"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>';
                    alertText.innerHTML = displayText;
                    alertBox.style.opacity = '1';
                }, 300);
            } else {
                alertBox.classList.add('settings-app-success-alert');
                alertIconWrapper.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="feather feather-check-circle"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>';
                alertText.innerHTML = displayText;
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

/**
 * Settings Nested View Manager
 * Handles the SPA-like navigation between Settings Main, APP List, etc.
 */
(function() {
    'use strict';

    function initSettingsViewManager() {
        var viewArchiveBtn = document.getElementById('btn-view-archive');
        var viewBackBtn = document.getElementById('btn-archive-back');
        
        // Level 2 back buttons
        var viewLevel1BackBtn1 = document.getElementById('btn-archive-level1-back-1');
        var viewLevel1BackBtn2 = document.getElementById('btn-archive-level1-back-2');
        
        // Level 3 back buttons
        var viewLevel2BackBtn1 = document.getElementById('btn-archive-level2-back-1');
        var viewLevel2BackBtn2 = document.getElementById('btn-archive-level2-back-2');
        var viewLevel2BackBtn3 = document.getElementById('btn-archive-level2-back-3');
        
        // Level 4 back buttons (PO List)
        var viewLevel4BackBtn1 = document.getElementById('btn-archive-level4-back-1');
        var viewLevel4BackBtn2 = document.getElementById('btn-archive-level4-back-2');
        var viewLevel4BackBtn3 = document.getElementById('btn-archive-level4-back-3');

        // Level 4 back buttons (PR List)
        var viewLevel4PrBackBtn1 = document.getElementById('btn-archive-level4-pr-back-1');
        var viewLevel4PrBackBtn2 = document.getElementById('btn-archive-level4-pr-back-2');
        var viewLevel4PrBackBtn3 = document.getElementById('btn-archive-level4-pr-back-3');
        
        // Level 5 back buttons (PO Details)
        var viewLevel5BackBtn1 = document.getElementById('btn-archive-level5-back-1');
        var viewLevel5BackBtn2 = document.getElementById('btn-archive-level5-back-2');
        var viewLevel5BackBtn3 = document.getElementById('btn-archive-level5-back-3');
        var viewLevel5BackBtn4 = document.getElementById('btn-archive-level5-back-4');

        var btnViewAppPos = document.getElementById('btn-view-app-pos');
        var btnViewAppPrs = document.getElementById('btn-view-app-prs');
        
        // Clickable rows
        var archiveClickableRowsLevel1 = document.querySelectorAll('#settings-view-archive-apps .archive-clickable-row');
        var archiveClickableRowsLevel2 = document.querySelectorAll('#settings-view-archive-projects .archive-clickable-row');
        var archiveClickableRowsPO = document.querySelectorAll('.archive-po-clickable-row');

        if (viewArchiveBtn) {
            viewArchiveBtn.addEventListener('click', function(e) {
                e.preventDefault();
                navigateToSettingsView('settings-view-archive-apps');
            });
        }
        
        if (viewBackBtn) {
            viewBackBtn.addEventListener('click', function(e) {
                e.preventDefault();
                navigateToSettingsView('settings-view-main');
            });
        }
        
        if (viewLevel1BackBtn1) {
            viewLevel1BackBtn1.addEventListener('click', function(e) {
                e.preventDefault();
                navigateToSettingsView('settings-view-archive-apps');
            });
        }
        
        if (viewLevel1BackBtn2) {
            viewLevel1BackBtn2.addEventListener('click', function(e) {
                e.preventDefault();
                navigateToSettingsView('settings-view-archive-apps');
            });
        }

        if (viewLevel2BackBtn1) {
            viewLevel2BackBtn1.addEventListener('click', function(e) {
                e.preventDefault();
                navigateToSettingsView('settings-view-archive-projects');
            });
        }
        
        if (viewLevel2BackBtn2) {
            viewLevel2BackBtn2.addEventListener('click', function(e) {
                e.preventDefault();
                navigateToSettingsView('settings-view-archive-projects');
            });
        }
        
        if (viewLevel2BackBtn3) {
            viewLevel2BackBtn3.addEventListener('click', function(e) {
                e.preventDefault();
                navigateToSettingsView('settings-view-archive-projects');
            });
        }

        archiveClickableRowsLevel1.forEach(function(row) {
            row.addEventListener('click', function(e) {
                e.preventDefault();
                
                var appId = this.getAttribute('data-app-id');
                var appCode = this.getAttribute('data-app-code');
                var appTitle = this.getAttribute('data-app-title');
                
                // Fetch dynamic data
                fetch('/account-settings/archive/app-data/' + appId)
                    .then(response => response.json())
                    .then(data => {
                        // Populate Level 2 (APP Projects)
                        var tbodyProjects = document.getElementById('archive-app-projects-tbody');
                        if (tbodyProjects) {
                            tbodyProjects.innerHTML = '';
                            if (data.appItems && data.appItems.length > 0) {
                                data.appItems.forEach(item => {
                                    var tr = document.createElement('tr');
                                    tr.className = 'archive-clickable-row archive-project-dynamic-row';
                                    tr.innerHTML = `
                                        <td>${data.app_unique_code}</td>
                                        <td>${item.app_item_proj_title}</td>
                                        <td>${item.app_items_assigned_to || '-'}</td>
                                        <td class="text-center">-</td>
                                    `;
                                    tr.addEventListener('click', function(e) {
                                        e.preventDefault();
                                        // Update Level 3 breadcrumbs
                                        var level3Breadcrumb = document.querySelector('#settings-view-archive-app-project .breadcrumb-current');
                                        if (level3Breadcrumb) level3Breadcrumb.textContent = item.app_item_proj_title;
                                        
                                        // Populate Level 3 fields
                                        if (document.getElementById('detail-proj-title')) document.getElementById('detail-proj-title').textContent = item.app_item_proj_title || '-';
                                        if (document.getElementById('detail-end-user')) document.getElementById('detail-end-user').textContent = item.app_items_end_user || '-';
                                        if (document.getElementById('detail-gen-desc')) document.getElementById('detail-gen-desc').textContent = item.app_items_gen_desc || '-';
                                        if (document.getElementById('detail-mode')) document.getElementById('detail-mode').textContent = item.app_items_mode || '-';
                                        if (document.getElementById('detail-criteria')) document.getElementById('detail-criteria').textContent = item.app_items_criteria || '-';
                                        
                                        // Early procurement radios
                                        var isEarly = item.app_items_covered && item.app_items_covered.toLowerCase() === 'yes';
                                        if (document.getElementById('detail-early-yes')) document.getElementById('detail-early-yes').checked = isEarly;
                                        if (document.getElementById('detail-early-no')) document.getElementById('detail-early-no').checked = !isEarly;

                                        if (document.getElementById('detail-start')) document.getElementById('detail-start').textContent = item.app_items_start || '-';
                                        if (document.getElementById('detail-end')) document.getElementById('detail-end').textContent = item.app_items_end || '-';
                                        if (document.getElementById('detail-source')) document.getElementById('detail-source').textContent = item.app_items_source || '-';
                                        if (document.getElementById('detail-budget')) document.getElementById('detail-budget').textContent = item.app_items_esti_budget || '-';
                                        if (document.getElementById('detail-tools')) document.getElementById('detail-tools').textContent = item.app_items_tools || '-';
                                        if (document.getElementById('detail-remarks')) document.getElementById('detail-remarks').textContent = item.app_items_remarks || '-';

                                        navigateToSettingsView('settings-view-archive-app-project');
                                    });
                                    tbodyProjects.appendChild(tr);
                                });
                            } else {
                                tbodyProjects.innerHTML = '<tr><td colspan="4" class="text-center">No projects found for this APP.</td></tr>';
                            }
                        }

                        // Populate Level 4 (POs)
                        var tbodyPOs = document.getElementById('archive-pos-tbody');
                        if (tbodyPOs) {
                            tbodyPOs.innerHTML = '';
                            if (data.purchaseOrders && data.purchaseOrders.length > 0) {
                                data.purchaseOrders.forEach(po => {
                                    var tr = document.createElement('tr');
                                    tr.className = 'archive-po-clickable-row archive-po-dynamic-row';
                                    
                                    var statusText = po.po_status || 'Pending';
                                    var statusClass = statusText.toLowerCase() === 'completed' ? 'badge-success' : 'badge-warning';
                                    var statusStyle = statusText.toLowerCase() === 'completed' ? 'background-color: #1abc9c; color: white;' : 'background-color: #e2a03f; color: white;';
                                    
                                    tr.innerHTML = `
                                        <td>${po.po_unique_code || po.po_no || '-'}</td>
                                        <td>${po.po_title || '-'}</td>
                                        <td>${po.po_supplier || '-'}</td>
                                        <td class="text-center">${po.po_date || '-'}</td>
                                        <td class="text-center"><span class="badge ${statusClass}" style="${statusStyle}">${statusText}</span></td>
                                    `;
                                    tr.addEventListener('click', function(e) {
                                        e.preventDefault();
                                        // Update Level 5 breadcrumbs
                                        var level5Breadcrumb = document.querySelector('#settings-view-archive-po-details .breadcrumb-current');
                                        if (level5Breadcrumb) level5Breadcrumb.textContent = po.po_title || po.po_no;
                                        
                                        navigateToSettingsView('settings-view-archive-po-details');
                                    });
                                    tbodyPOs.appendChild(tr);
                                });
                            } else {
                                tbodyPOs.innerHTML = '<tr><td colspan="5" class="text-center">No purchase orders found for this APP.</td></tr>';
                            }
                        }

                        // Populate Level 4 (PRs)
                        var tbodyPRs = document.getElementById('archive-prs-tbody');
                        if (tbodyPRs) {
                            tbodyPRs.innerHTML = '';
                            if (data.purchaseRequests && data.purchaseRequests.length > 0) {
                                data.purchaseRequests.forEach(pr => {
                                    var tr = document.createElement('tr');
                                    tr.className = 'archive-pr-clickable-row archive-pr-dynamic-row';
                                    tr.setAttribute('data-pr-id', pr.pr_id);
                                    
                                    var formattedBudget = '₱ ' + parseFloat(pr.pr_total || 0).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                                    
                                    tr.innerHTML = `
                                        <td>${pr.pr_no || pr.pr_unique_code || '-'}</td>
                                        <td>${pr.pr_purpose || '-'}</td>
                                        <td class="text-end fw-bold">${formattedBudget}</td>
                                        <td>${pr.requested_by || '-'}</td>
                                    `;
                                    tr.addEventListener('click', function(e) {
                                        e.preventDefault();
                                        // Specific PR click actions can be added here in the future
                                    });
                                    tbodyPRs.appendChild(tr);
                                });
                            } else {
                                tbodyPRs.innerHTML = '<tr><td colspan="4" class="text-center">No purchase requests found for this APP.</td></tr>';
                            }
                        }

                        // Update all breadcrumbs to show selected APP code
                        var appBreadcrumbs = document.querySelectorAll('.archive-back-link');
                        appBreadcrumbs.forEach(link => {
                            if (link.textContent.includes('APP-')) {
                                link.textContent = data.app_unique_code;
                            }
                        });
                        
                        var level2Breadcrumb = document.querySelector('#settings-view-archive-projects .breadcrumb-current');
                        if (level2Breadcrumb) level2Breadcrumb.textContent = appTitle;

                        navigateToSettingsView('settings-view-archive-projects');
                    })
                    .catch(error => {
                        console.error('Error fetching APP data:', error);
                        // Fallback navigation if error
                        navigateToSettingsView('settings-view-archive-projects');
                    });
            });
        });
        
        var toggleDetailsBtn = document.getElementById('btn-toggle-app-details');
        if (toggleDetailsBtn) {
            toggleDetailsBtn.addEventListener('click', function(e) {
                e.preventDefault();
                $('#archive-app-details-container').slideToggle(300);
                this.classList.toggle('collapsed');
            });
        }
        
        // PO Flow interactions
        if (btnViewAppPos) {
            btnViewAppPos.addEventListener('click', function(e) {
                e.preventDefault();
                navigateToSettingsView('settings-view-archive-pos');
            });
        }

        // PR Flow interactions
        if (btnViewAppPrs) {
            btnViewAppPrs.addEventListener('click', function(e) {
                e.preventDefault();
                navigateToSettingsView('settings-view-archive-prs');
            });
        }
        
        archiveClickableRowsPO.forEach(function(row) {
            row.addEventListener('click', function(e) {
                e.preventDefault();
                navigateToSettingsView('settings-view-archive-po-details');
            });
        });
        
        // Level 4 Back Navigation
        if (viewLevel4BackBtn1) { viewLevel4BackBtn1.addEventListener('click', function(e) { e.preventDefault(); navigateToSettingsView('settings-view-main'); }); }
        if (viewLevel4BackBtn2) { viewLevel4BackBtn2.addEventListener('click', function(e) { e.preventDefault(); navigateToSettingsView('settings-view-archive-apps'); }); }
        if (viewLevel4BackBtn3) { viewLevel4BackBtn3.addEventListener('click', function(e) { e.preventDefault(); navigateToSettingsView('settings-view-archive-projects'); }); }

        // Level 4 (PRs) Back Navigation
        if (viewLevel4PrBackBtn1) { viewLevel4PrBackBtn1.addEventListener('click', function(e) { e.preventDefault(); navigateToSettingsView('settings-view-main'); }); }
        if (viewLevel4PrBackBtn2) { viewLevel4PrBackBtn2.addEventListener('click', function(e) { e.preventDefault(); navigateToSettingsView('settings-view-archive-apps'); }); }
        if (viewLevel4PrBackBtn3) { viewLevel4PrBackBtn3.addEventListener('click', function(e) { e.preventDefault(); navigateToSettingsView('settings-view-archive-projects'); }); }
        
        // Level 5 Back Navigation
        if (viewLevel5BackBtn1) { viewLevel5BackBtn1.addEventListener('click', function(e) { e.preventDefault(); navigateToSettingsView('settings-view-main'); }); }
        if (viewLevel5BackBtn2) { viewLevel5BackBtn2.addEventListener('click', function(e) { e.preventDefault(); navigateToSettingsView('settings-view-archive-apps'); }); }
        if (viewLevel5BackBtn3) { viewLevel5BackBtn3.addEventListener('click', function(e) { e.preventDefault(); navigateToSettingsView('settings-view-archive-projects'); }); }
        if (viewLevel5BackBtn4) { viewLevel5BackBtn4.addEventListener('click', function(e) { e.preventDefault(); navigateToSettingsView('settings-view-archive-pos'); }); }
    }

    function navigateToSettingsView(targetViewId) {
        var allPanes = document.querySelectorAll('.settings-view-pane');
        var targetPane = document.getElementById(targetViewId);
        
        if (!targetPane) return;
        
        // Hide all panes
        allPanes.forEach(function(pane) {
            pane.style.display = 'none';
            pane.classList.remove('active');
        });
        
        // Show target pane
        targetPane.style.display = 'block';
        
        // Force reflow for animation restart
        void targetPane.offsetWidth;
        
        targetPane.classList.add('active');
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initSettingsViewManager);
    } else {
        initSettingsViewManager();
    }
})();
