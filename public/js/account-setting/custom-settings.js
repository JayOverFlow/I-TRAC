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

        // Level 5 back buttons (PR Details)
        var viewPrLevel5BackBtn1 = document.getElementById('btn-archive-pr-level5-back-1');
        var viewPrLevel5BackBtn2 = document.getElementById('btn-archive-pr-level5-back-2');
        var viewPrLevel5BackBtn3 = document.getElementById('btn-archive-pr-level5-back-3');
        var viewPrLevel5BackBtn4 = document.getElementById('btn-archive-pr-level5-back-4');

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
                                        
                                        // Populate PO Details fields
                                        document.getElementById('archive-po-detail-title-heading').textContent = 'Title: ' + (po.po_title || '-');
                                        document.getElementById('archive-po-detail-supplier').textContent = po.po_supplier || '-';
                                        document.getElementById('archive-po-detail-address').textContent = po.po_address || '-';
                                        document.getElementById('archive-po-detail-tele').textContent = po.po_tele || '-';
                                        document.getElementById('archive-po-detail-tin').textContent = po.po_tin || '-';
                                        document.getElementById('archive-po-detail-place-delivery').textContent = po.po_place_delivery || '-';
                                        document.getElementById('archive-po-detail-date-delivery').textContent = po.po_date_delivery || '-';
                                        document.getElementById('archive-po-detail-mode').textContent = po.po_mode || '-';
                                        document.getElementById('archive-po-detail-tuptin').textContent = po.po_tuptin || '-';
                                        document.getElementById('archive-po-detail-delivery-term').textContent = po.po_delivery_term || '-';
                                        document.getElementById('archive-po-detail-payment-term').textContent = po.po_payment_term || '-';
                                        document.getElementById('archive-po-detail-po-no').textContent = po.po_no || po.po_unique_code || '-';
                                        document.getElementById('archive-po-detail-date').textContent = po.po_date || '-';

                                        // Group and render PO items by category
                                        var items = po.po_items || po.poItems || [];
                                        var grouped = {};
                                        items.forEach(function(item) {
                                            var cat = item.po_items_category || 'Supply and Materials';
                                            if (!grouped[cat]) grouped[cat] = [];
                                            grouped[cat].push(item);
                                        });

                                        var itemsHtml = '';
                                        for (var catName in grouped) {
                                            if (grouped.hasOwnProperty(catName)) {
                                                var catItems = grouped[catName];
                                                var rowsHtml = '';
                                                catItems.forEach(function(item) {
                                                    var stock = item.po_items_stockno || '';
                                                    var unit = item.po_items_unit || '';
                                                    var desc = item.po_items_descrip || '';
                                                    var qty = item.po_items_quantity || 0;
                                                    var cost = parseFloat(item.po_items_cost || 0);
                                                    var amount = parseFloat(item.po_items_amount || (qty * cost));
                                                    
                                                    var specs = item.po_specs || item.poSpecs || [];
                                                    var specsHtml = specs.map(function(s) { return s.po_spec_description; }).filter(Boolean).join('<br>');
                                                    var articleContent = '<span class="app-detail-value" style="font-size: 0.8rem;">' + desc + '</span>' +
                                                        (specsHtml ? '<br><small class="app-detail-label" style="font-size: 0.75rem;">' + specsHtml + '</small>' : '');
                                                    
                                                    rowsHtml += '<tr>' +
                                                        '<td class="text-center app-detail-value" style="font-weight: 500;">' + stock + '</td>' +
                                                        '<td class="text-center app-detail-value" style="font-weight: 500;">' + unit + '</td>' +
                                                        '<td>' + articleContent + '</td>' +
                                                        '<td class="text-center app-detail-value" style="font-weight: 500;">' + qty + '</td>' +
                                                        '<td class="text-end app-detail-value" style="font-weight: 500;">₱ ' + cost.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + '</td>' +
                                                        '<td class="text-end app-detail-value" style="font-weight: 500;">₱ ' + amount.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + '</td>' +
                                                        '</tr>';
                                                });
                                                
                                                itemsHtml += '<h6 class="fw-bold mb-3" style="color: #3b3f5c;">' + catName + '</h6>' +
                                                    '<div class="table-responsive mb-4">' +
                                                    '<table class="table table-borderless table-sm">' +
                                                    '<thead style="background-color: #f1f2f3;">' +
                                                    '<tr>' +
                                                    '<th class="text-center" style="width: 10%;">Stock</th>' +
                                                    '<th class="text-center" style="width: 10%;">Unit</th>' +
                                                    '<th style="width: 40%;">Article</th>' +
                                                    '<th class="text-center" style="width: 10%;">Qty.</th>' +
                                                    '<th class="text-end" style="width: 15%;">Unit Cost</th>' +
                                                    '<th class="text-end" style="width: 15%;">Amount</th>' +
                                                    '</tr>' +
                                                    '</thead>' +
                                                    '<tbody>' +
                                                    rowsHtml +
                                                    '</tbody>' +
                                                    '</table>' +
                                                    '</div>';
                                            }
                                        }
                                        document.getElementById('archive-po-items-tables-container').innerHTML = itemsHtml || '<div class="text-center p-3 text-muted">No items found for this PO.</div>';

                                        // Render Delivery Attachments tree
                                        var iarReports = po.iar_reports || po.iarReports || [];
                                        var risSlips = po.ris_slips || po.risSlips || [];
                                        var icsSlips = po.ics_slips || po.icsSlips || [];
                                        var parReceipts = po.par_receipts || po.parReceipts || [];
                                        var rsmiReports = po.rsmi_reports || po.rsmiReports || [];
                                        var rspiReports = po.rspi_reports || po.rspiReports || [];
                                        var ndrReports = po.ndr_reports || po.ndrReports || [];

                                        function getDocCategory(doc, itemsKey) {
                                            var items = doc[itemsKey] || [];
                                            if (items.length > 0) {
                                                var poItem = items[0].po_item || items[0].poItem;
                                                if (poItem) {
                                                    return poItem.po_items_category;
                                                }
                                            }
                                            return null;
                                        }

                                        var supplyIars = iarReports.filter(function(iar) {
                                            return getDocCategory(iar, 'iar_items') === 'Supply and Materials' || getDocCategory(iar, 'iarItems') === 'Supply and Materials';
                                        });
                                        var semiExpendableIars = iarReports.filter(function(iar) {
                                            return getDocCategory(iar, 'iar_items') === 'Semi-Expendable' || getDocCategory(iar, 'iarItems') === 'Semi-Expendable';
                                        });
                                        var equipmentIars = iarReports.filter(function(iar) {
                                            return getDocCategory(iar, 'iar_items') === 'Equipment' || getDocCategory(iar, 'iarItems') === 'Equipment';
                                        });

                                        var supplyRiss = risSlips.filter(function(ris) {
                                            return getDocCategory(ris, 'ris_items') === 'Supply and Materials' || getDocCategory(ris, 'risItems') === 'Supply and Materials';
                                        });
                                        var semiExpendableRiss = risSlips.filter(function(ris) {
                                            return getDocCategory(ris, 'ris_items') === 'Semi-Expendable' || getDocCategory(ris, 'risItems') === 'Semi-Expendable';
                                        });

                                        var hasSupply = supplyIars.length > 0 || supplyRiss.length > 0 || rsmiReports.length > 0;
                                        var hasSemiExpendable = semiExpendableIars.length > 0 || semiExpendableRiss.length > 0 || icsSlips.length > 0 || rspiReports.length > 0;
                                        var hasEquipment = equipmentIars.length > 0 || parReceipts.length > 0;
                                        var hasNotDelivered = ndrReports.length > 0;

                                        var treeHtml = '';

                                        function getFileLiHtml(docName) {
                                            return '<li class="tv-item tv-file">' +
                                                '<span class="icon">' +
                                                '<svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-file" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"></path><path d="M14 3v4a1 1 0 0 0 1 1h4"></path><path d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z"></path></svg>' +
                                                '</span>' +
                                                '<p>' + docName + '</p>' +
                                                '</li>';
                                        }

                                        // 1. Supply and Materials
                                        if (hasSupply) {
                                            var supplyFilesHtml = '';
                                            supplyIars.forEach(function() { supplyFilesHtml += getFileLiHtml('IAR'); });
                                            supplyRiss.forEach(function(ris) { supplyFilesHtml += getFileLiHtml('RIS - ' + (ris.ris_office || 'Office')); });
                                            rsmiReports.forEach(function() { supplyFilesHtml += getFileLiHtml('RSMI'); });

                                            treeHtml += '<li class="tv-item tv-folder">' +
                                                '<div class="tv-header" id="archiveFolderSupplyHeading">' +
                                                '<div class="tv-collapsible collapsed" data-bs-toggle="collapse" data-bs-target="#archiveFolderSupply" aria-expanded="false" aria-controls="archiveFolderSupply">' +
                                                '<div class="icon">' +
                                                '<svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-folder" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"></path><path d="M5 4h4l3 3h7a2 2 0 0 1 2 2v8a2 2 0 0 1 -2 2h-14a2 2 0 0 1 -2 -2v-11a2 2 0 0 1 2 -2"></path></svg>' +
                                                '</div>' +
                                                '<p class="title">Supply and Materials</p>' +
                                                '</div>' +
                                                '</div>' +
                                                '<div id="archiveFolderSupply" class="treeview-collapse collapse" aria-labelledby="archiveFolderSupplyHeading" data-bs-parent="#archiveFolderDelivery">' +
                                                '<ul class="treeview">' +
                                                supplyFilesHtml +
                                                '</ul>' +
                                                '</div>' +
                                                '</li>';
                                        }

                                        // 2. Semi-Expendable
                                        if (hasSemiExpendable) {
                                            var semiFilesHtml = '';
                                            semiExpendableIars.forEach(function() { semiFilesHtml += getFileLiHtml('IAR'); });
                                            semiExpendableRiss.forEach(function(ris) {
                                                var receiverName = (ris.receiver && ris.receiver.user_fullname) ? ris.receiver.user_fullname : 'User';
                                                semiFilesHtml += getFileLiHtml('RIS - ' + receiverName);
                                            });
                                            icsSlips.forEach(function(ics) {
                                                var receiverName = (ics.receiver && ics.receiver.user_fullname) ? ics.receiver.user_fullname : 'User';
                                                semiFilesHtml += getFileLiHtml('ICS - ' + receiverName);
                                            });
                                            rspiReports.forEach(function() { semiFilesHtml += getFileLiHtml('RSPI'); });

                                            treeHtml += '<li class="tv-item tv-folder">' +
                                                '<div class="tv-header" id="archiveFolderSemiHeading">' +
                                                '<div class="tv-collapsible collapsed" data-bs-toggle="collapse" data-bs-target="#archiveFolderSemi" aria-expanded="false" aria-controls="archiveFolderSemi">' +
                                                '<div class="icon">' +
                                                '<svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-folder" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"></path><path d="M5 4h4l3 3h7a2 2 0 0 1 2 2v8a2 2 0 0 1 -2 2h-14a2 2 0 0 1 -2 -2v-11a2 2 0 0 1 2 -2"></path></svg>' +
                                                '</div>' +
                                                '<p class="title">Semi-Expendable</p>' +
                                                '</div>' +
                                                '</div>' +
                                                '<div id="archiveFolderSemi" class="treeview-collapse collapse" aria-labelledby="archiveFolderSemiHeading" data-bs-parent="#archiveFolderDelivery">' +
                                                '<ul class="treeview">' +
                                                semiFilesHtml +
                                                '</ul>' +
                                                '</div>' +
                                                '</li>';
                                        }

                                        // 3. Equipment
                                        if (hasEquipment) {
                                            var equipFilesHtml = '';
                                            equipmentIars.forEach(function() { equipFilesHtml += getFileLiHtml('IAR'); });
                                            parReceipts.forEach(function(par) {
                                                var receiverName = (par.receiver && par.receiver.user_fullname) ? par.receiver.user_fullname : 'User';
                                                equipFilesHtml += getFileLiHtml('PAR - ' + receiverName);
                                            });

                                            treeHtml += '<li class="tv-item tv-folder">' +
                                                '<div class="tv-header" id="archiveFolderEquipHeading">' +
                                                '<div class="tv-collapsible collapsed" data-bs-toggle="collapse" data-bs-target="#archiveFolderEquip" aria-expanded="false" aria-controls="archiveFolderEquip">' +
                                                '<div class="icon">' +
                                                '<svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-folder" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"></path><path d="M5 4h4l3 3h7a2 2 0 0 1 2 2v8a2 2 0 0 1 -2 2h-14a2 2 0 0 1 -2 -2v-11a2 2 0 0 1 2 -2"></path></svg>' +
                                                '</div>' +
                                                '<p class="title">Equipment</p>' +
                                                '</div>' +
                                                '</div>' +
                                                '<div id="archiveFolderEquip" class="treeview-collapse collapse" aria-labelledby="archiveFolderEquipHeading" data-bs-parent="#archiveFolderDelivery">' +
                                                '<ul class="treeview">' +
                                                equipFilesHtml +
                                                '</ul>' +
                                                '</div>' +
                                                '</li>';
                                        }

                                        // 4. Not Delivered
                                        if (hasNotDelivered) {
                                            var ndrFilesHtml = '';
                                            ndrReports.forEach(function() { ndrFilesHtml += getFileLiHtml('NDR'); });

                                            treeHtml += '<li class="tv-item tv-folder">' +
                                                '<div class="tv-header" id="archiveFolderNotHeading">' +
                                                '<div class="tv-collapsible collapsed" data-bs-toggle="collapse" data-bs-target="#archiveFolderNot" aria-expanded="false" aria-controls="archiveFolderNot">' +
                                                '<div class="icon">' +
                                                '<svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-folder" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"></path><path d="M5 4h4l3 3h7a2 2 0 0 1 2 2v8a2 2 0 0 1 -2 2h-14a2 2 0 0 1 -2 -2v-11a2 2 0 0 1 2 -2"></path></svg>' +
                                                '</div>' +
                                                '<p class="title">Not Delivered</p>' +
                                                '</div>' +
                                                '</div>' +
                                                '<div id="archiveFolderNot" class="treeview-collapse collapse" aria-labelledby="archiveFolderNotHeading" data-bs-parent="#archiveFolderDelivery">' +
                                                '<ul class="treeview">' +
                                                ndrFilesHtml +
                                                '</ul>' +
                                                '</div>' +
                                                '</li>';
                                        }

                                        var attachmentsListEl = document.getElementById('archive-delivery-attachments-list');
                                        if (attachmentsListEl) {
                                            attachmentsListEl.innerHTML = treeHtml || '<li class="p-3 text-muted" style="list-style-type: none; font-size: 0.85rem;">No delivery attachments generated.</li>';
                                        }

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
                                        
                                        // Update Level 5 PR breadcrumbs
                                        var prBreadcrumb = document.getElementById('archive-pr-detail-breadcrumb-no');
                                        if (prBreadcrumb) prBreadcrumb.textContent = pr.pr_no || pr.pr_unique_code;

                                        // Populate PR details fields
                                        document.getElementById('archive-pr-detail-no-heading').textContent = 'PR No.: ' + (pr.pr_no || pr.pr_unique_code || '-');
                                        document.getElementById('archive-pr-detail-department').textContent = pr.pr_department_name || '-';
                                        document.getElementById('archive-pr-detail-section').textContent = pr.pr_section || '-';
                                        document.getElementById('archive-pr-detail-purpose').textContent = pr.pr_purpose || '-';
                                        document.getElementById('archive-pr-detail-date').textContent = pr.pr_date || '-';
                                        document.getElementById('archive-pr-detail-requestor').textContent = pr.requested_by || '-';
                                        document.getElementById('archive-pr-detail-approved-by').textContent = pr.pr_approved_by_name || '-';

                                        // Render PR items without category grouping
                                        var items = pr.pr_items || [];
                                        var rowsHtml = '';
                                        items.forEach(function(item) {
                                            var qty = item.pr_items_quantity || 0;
                                            var unit = item.pr_items_unit || '';
                                            var desc = item.pr_items_descrip || '';
                                            var cost = parseFloat(item.pr_items_cost || 0);
                                            var total = parseFloat(item.pr_items_total_cost || (qty * cost));
                                            
                                            var specs = item.pr_specs || [];
                                            var specsHtml = specs.map(function(s) { return s.pr_spec_spec; }).filter(Boolean).join('<br>');
                                            var descriptionContent = '<span class="app-detail-value" style="font-size: 0.8rem;">' + desc + '</span>' +
                                                (specsHtml ? '<br><small class="app-detail-label" style="font-size: 0.75rem;">' + specsHtml + '</small>' : '');
                                                
                                            rowsHtml += '<tr>' +
                                                '<td class="text-center app-detail-value" style="font-weight: 500;">' + qty + '</td>' +
                                                '<td class="text-center app-detail-value" style="font-weight: 500;">' + unit + '</td>' +
                                                '<td>' + descriptionContent + '</td>' +
                                                '<td class="text-end app-detail-value" style="font-weight: 500;">₱ ' + cost.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + '</td>' +
                                                '<td class="text-end app-detail-value" style="font-weight: 500;">₱ ' + total.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + '</td>' +
                                                '</tr>';
                                        });
                                        document.getElementById('archive-pr-items-tbody').innerHTML = rowsHtml || '<tr><td colspan="5" class="text-center p-3 text-muted">No items found for this PR.</td></tr>';

                                        navigateToSettingsView('settings-view-archive-pr-details');
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

        // Level 5 PR Back Navigation
        if (viewPrLevel5BackBtn1) { viewPrLevel5BackBtn1.addEventListener('click', function(e) { e.preventDefault(); navigateToSettingsView('settings-view-main'); }); }
        if (viewPrLevel5BackBtn2) { viewPrLevel5BackBtn2.addEventListener('click', function(e) { e.preventDefault(); navigateToSettingsView('settings-view-archive-apps'); }); }
        if (viewPrLevel5BackBtn3) { viewPrLevel5BackBtn3.addEventListener('click', function(e) { e.preventDefault(); navigateToSettingsView('settings-view-archive-projects'); }); }
        if (viewPrLevel5BackBtn4) { viewPrLevel5BackBtn4.addEventListener('click', function(e) { e.preventDefault(); navigateToSettingsView('settings-view-archive-prs'); }); }
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
