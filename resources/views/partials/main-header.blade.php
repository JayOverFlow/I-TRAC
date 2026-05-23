@php
    $allRoles = auth()->user()->roles;
    $activeRoleId = session('active_role_id') ?? ($allRoles->first()?->role_id ?? null);
    $activeRole = $allRoles->where('role_id', $activeRoleId)->first() ?? $allRoles->first();
    
    // Helper to format role names according to specific rules
    $formatRoleDisplayName = function($role) {
        if (!$role) {
            $userType = auth()->user()->user_type;
            $firstDep = auth()->user()->departments->first();
            if ($firstDep) {
                $depAcronym = $firstDep->dep_acronym;
                $depName = !empty($depAcronym) ? $depAcronym : $firstDep->dep_name;
                return $userType . ' - ' . $depName;
            }
            return $userType;
        }
        
        // 1. If role has a database-configured acronym, use it directly
        if (!empty($role->role_acronym)) {
            return $role->role_acronym;
        }
        
        $roleName = $role->role_name;
        
        // 2. Roles that shouldn't have office/department appended
        $excludeOfficeRoles = [
            'Assistant Director for Academic Affairs',
            'Assistant Director for Administration and Finance',
            'Assistant Director for Research and Extension',
            'Assistant Director in Research and Extension',
            'Campus Director',
        ];
        
        if (in_array($roleName, $excludeOfficeRoles)) {
            return $roleName;
        }
        
        // 3. Fallback to department acronym/name appending
        $depName = $role->department ? $role->department->dep_name : '';
        if (empty($depName)) {
            return $roleName;
        }
        
        $depAcronym = $role->department ? $role->department->dep_acronym : null;
        $shortDepName = !empty($depAcronym) ? $depAcronym : $depName;
        
        // Prevent duplicate department suffix if already part of role_name
        if (str_contains($roleName, $depName) || str_contains($roleName, $shortDepName)) {
            return $roleName;
        }
        
        // Handle "Head - [Department Name]" and shorten to "Head - [Acronym]"
        if (str_starts_with($roleName, 'Head - ') && !empty($depAcronym)) {
            return 'Head - ' . $depAcronym;
        }
        
        return $roleName . ' - ' . $shortDepName;
    };
    
    $activeRoleDisplayName = $formatRoleDisplayName($activeRole);
    $userRoleGen = $activeRole?->gen_role ?? auth()->user()->user_type;
    $showApp = in_array($userRoleGen, ['Head', 'Procurement', 'Supply']);

    // Dynamic Role-based border class (Head, Procurement, Supply vs Faculty/Staff)
    $avatarBorderClass = (in_array($userRoleGen, ['Head', 'Procurement', 'Supply']) || str_contains(strtolower($userRoleGen), 'head'))
        ? 'head-avatar'
        : 'faculty-avatar';
@endphp
<header class="header navbar navbar-expand-sm expand-header">

    <a href="javascript:void(0);" class="sidebarCollapse" data-placement="bottom"><svg xmlns="http://www.w3.org/2000/svg"
            width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
            stroke-linecap="round" stroke-linejoin="round" class="feather feather-menu">
            <line x1="3" y1="12" x2="21" y2="12"></line>
            <line x1="3" y1="6" x2="21" y2="6"></line>
            <line x1="3" y1="18" x2="21" y2="18"></line>
        </svg>
    </a>

    <ul class="navbar-item theme-brand flex-row  text-center">
        <li class="nav-item theme-logo">
            <img src="{{ asset('img/itrac-header-logo-red.svg') }}" class="light-logo" alt="I-TRAC" width="170"
                height="36" style="object-fit: contain;">
            <img src="{{ asset('img/itrac-header-logo-white.svg') }}" class="dark-logo" alt="I-TRAC" width="170"
                height="36" style="object-fit: contain;">
        </li>
    </ul>

    <ul class="navbar-item flex-row ms-md-auto ms-0 action-area">

        <li class="nav-item theme-toggle-item">
            <a href="javascript:void(0);" class="nav-link theme-toggle">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                    class="feather feather-moon dark-mode">
                    <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path>
                </svg>
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                    class="feather feather-sun light-mode">
                    <circle cx="12" cy="12" r="5"></circle>
                    <line x1="12" y1="1" x2="12" y2="3"></line>
                    <line x1="12" y1="21" x2="12" y2="23"></line>
                    <line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line>
                    <line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line>
                    <line x1="1" y1="12" x2="3" y2="12"></line>
                    <line x1="21" y1="12" x2="23" y2="12"></line>
                    <line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line>
                    <line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line>
                </svg>
            </a>
        </li>

        <li class="nav-item dropdown notification-dropdown">
            <a href="javascript:void(0);" class="nav-link dropdown-toggle" id="notificationDropdown" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-bell"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path><path d="M13.73 21a2 2 0 0 1-3.46 0"></path></svg>
                    </a>

            <div class="dropdown-menu position-absolute" aria-labelledby="notificationDropdown">
                <div class="drodpown-title message">
                    <h6 class="d-flex justify-content-between"><span class="align-self-center">Messages</span> <span
                            class="badge badge-primary">9 Unread</span></h6>
                </div>
                <div class="notification-scroll">
                    <div class="dropdown-item">
                        <div class="media server-log">
                            <img src="{{ asset('img/profile-16.jpeg') }}" class="img-fluid me-2" alt="avatar">
                            <div class="media-body">
                                <div class="data-info">
                                    <h6 class="">Kara Young</h6>
                                    <p class="">1 hr ago</p>
                                </div>

                                <div class="icon-status">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                        stroke-linecap="round" stroke-linejoin="round" class="feather feather-x">
                                        <line x1="18" y1="6" x2="6" y2="18"></line>
                                        <line x1="6" y1="6" x2="18" y2="18"></line>
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="dropdown-item">
                        <div class="media ">
                            <img src="{{ asset('img/profile-15.jpeg') }}" class="img-fluid me-2" alt="avatar">
                            <div class="media-body">
                                <div class="data-info">
                                    <h6 class="">Daisy Anderson</h6>
                                    <p class="">8 hrs ago</p>
                                </div>

                                <div class="icon-status">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                        stroke-linecap="round" stroke-linejoin="round" class="feather feather-x">
                                        <line x1="18" y1="6" x2="6" y2="18"></line>
                                        <line x1="6" y1="6" x2="18" y2="18"></line>
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="dropdown-item">
                        <div class="media file-upload">
                            <img src="{{ asset('img/profile-21.jpeg') }}" class="img-fluid me-2" alt="avatar">
                            <div class="media-body">
                                <div class="data-info">
                                    <h6 class="">Oscar Garner</h6>
                                    <p class="">14 hrs ago</p>
                                </div>

                                <div class="icon-status">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                        stroke-linecap="round" stroke-linejoin="round" class="feather feather-x">
                                        <line x1="18" y1="6" x2="6" y2="18"></line>
                                        <line x1="6" y1="6" x2="18" y2="18"></line>
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="drodpown-title notification mt-2">
                        <h6 class="d-flex justify-content-between"><span
                                class="align-self-center">Notifications</span> <span class="badge badge-secondary">16
                                New</span></h6>
                    </div>

                    <div class="dropdown-item">
                        <div class="media server-log">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                stroke-linecap="round" stroke-linejoin="round" class="feather feather-server">
                                <rect x="2" y="2" width="20" height="8" rx="2" ry="2">
                                </rect>
                                <rect x="2" y="14" width="20" height="8" rx="2" ry="2">
                                </rect>
                                <line x1="6" y1="6" x2="6" y2="6"></line>
                                <line x1="6" y1="18" x2="6" y2="18"></line>
                            </svg>
                            <div class="media-body">
                                <div class="data-info">
                                    <h6 class="">Server Rebooted</h6>
                                    <p class="">45 min ago</p>
                                </div>

                                <div class="icon-status">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                        stroke-linecap="round" stroke-linejoin="round" class="feather feather-x">
                                        <line x1="18" y1="6" x2="6" y2="18"></line>
                                        <line x1="6" y1="6" x2="18" y2="18"></line>
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="dropdown-item">
                        <div class="media file-upload">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                stroke-linecap="round" stroke-linejoin="round" class="feather feather-file-text">
                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                <polyline points="14 2 14 8 20 8"></polyline>
                                <line x1="16" y1="13" x2="8" y2="13"></line>
                                <line x1="16" y1="17" x2="8" y2="17"></line>
                                <polyline points="10 9 9 9 8 9"></polyline>
                            </svg>
                            <div class="media-body">
                                <div class="data-info">
                                    <h6 class="">Kelly Portfolio.pdf</h6>
                                    <p class="">670 kb</p>
                                </div>

                                <div class="icon-status">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                        stroke-linecap="round" stroke-linejoin="round" class="feather feather-x">
                                        <line x1="18" y1="6" x2="6" y2="18"></line>
                                        <line x1="6" y1="6" x2="18" y2="18"></line>
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="dropdown-item">
                        <div class="media ">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                stroke-linecap="round" stroke-linejoin="round" class="feather feather-heart">
                                <path
                                    d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z">
                                </path>
                            </svg>
                            <div class="media-body">
                                <div class="data-info">
                                    <h6 class="">Licence Expiring Soon</h6>
                                    <p class="">8 hrs ago</p>
                                </div>

                                <div class="icon-status">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                        stroke-linecap="round" stroke-linejoin="round" class="feather feather-x">
                                        <line x1="18" y1="6" x2="6" y2="18"></line>
                                        <line x1="6" y1="6" x2="18" y2="18"></line>
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

        </li>

        <li class="nav-item dropdown user-profile-dropdown  order-lg-0 order-1">
            <a href="javascript:void(0);" class="nav-link dropdown-toggle user" id="userProfileDropdown"
                data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <div class="avatar-container">
                    <div class="avatar avatar-sm">
                        <img alt="avatar" src="{{ auth()->user()->user_profile_photo ? asset(auth()->user()->user_profile_photo) : asset('img/profiles/blank.avif') }}"
                            class="rounded-circle {{ $avatarBorderClass }}">
                    </div>
                </div>
            </a>

            <div class="dropdown-menu position-absolute" aria-labelledby="userProfileDropdown">

                <div class="user-profile-section">
                    <div class="media mx-auto">
                        <div class="media-body">
                            <small>{{ auth()->user()->user_fullname_no_middle }}</small> <br>
                            <small class="red-text-2">
                                {{ $activeRoleDisplayName }}</small>
                        </div>
                    </div>
                </div>

                <div class="dropdown-item">
                    <a href="{{ route('account.settings') }}#animated-underline-profile">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-user"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg> <span>Profile</span>
                    </a>
                </div>
                <div class="dropdown-item">
                    <a href="{{ route('account.settings') }}#animated-underline-inbox">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-mail"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
                        <span>Inbox</span>
                    </a>
                </div>
                @if ($showApp)
                <div class="dropdown-item">
                    <a href="{{ route('account.settings') }}#animated-underline-annual-procurement-plan">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-file-text"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                        <span>APP</span>
                    </a>
                </div>
                @endif
                <div class="dropdown-item">
                    <a href="{{ route('account.settings') }}#animated-underline-settings">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-lock"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg> 
                        <span>Settings</span>
                    </a>
                </div>
                <div class="dropdown-item">
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <a href="javascript:void(0);" onclick="this.closest('form').submit();">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-log-out"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
                            <span>Log Out</span>
                        </a>
                    </form>
                </div>

                @if ($allRoles->count() > 1)
                    {{-- Divider and Switch Accounts Selection --}}
                    <div class="dropdown-divider-switch"></div>
                    <div class="switch-accounts-label">Switch accounts</div>
                    <div class="switch-accounts-list">
                        @foreach ($allRoles as $role)
                            @php
                                $displayRoleName = $formatRoleDisplayName($role);
                                $isActive = ($role->role_id == $activeRoleId);
                            @endphp
                            <div class="switch-account-item d-flex align-items-center justify-content-between" data-role-id="{{ $role->role_id }}">
                                <div class="account-info">
                                    <div class="account-role">{{ $displayRoleName }}</div>
                                </div>
                                <div class="account-selector">
                                    <input type="radio" name="active_role_selector" value="{{ $role->role_id }}" class="custom-radio-selector" {{ $isActive ? 'checked' : '' }}>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    {{-- Click Listener to switch active account/department with premium loading transition --}}
                    <script>
                        document.querySelectorAll('.switch-account-item').forEach(item => {
                            item.addEventListener('click', function(e) {
                                const roleId = this.dataset.roleId;
                                const radio = this.querySelector('.custom-radio-selector');
                                
                                // If clicked row is already the active checked row, do nothing
                                if (radio && radio.checked && e.target.tagName === 'INPUT') {
                                    return;
                                }

                                // Check the radio visually
                                if (radio) {
                                    radio.checked = true;
                                }

                                // 1. Render and inject the fullscreen loader spinner overlay
                                const loadScreen = document.createElement('div');
                                loadScreen.id = 'load_screen';
                                loadScreen.innerHTML = `
                                    <div class="loader">
                                        <div class="loader-content">
                                            <div class="spinner-grow align-self-center" style="color: var(--red-text-2) !important;"></div>
                                        </div>
                                    </div>
                                `;
                                document.body.appendChild(loadScreen);

                                // 2. Fire the AJAX POST request to update active role in session
                                fetch('{{ route("switch.account") }}', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                    },
                                    body: JSON.stringify({ role_id: roleId })
                                })
                                .then(response => response.json())
                                .then(data => {
                                    if (data.success) {
                                        // 3. Refresh page to dynamically reload dashboard and task filters
                                        window.location.reload();
                                    } else {
                                        // Clean up loading overlay on failure
                                        if (loadScreen.parentNode) {
                                            document.body.removeChild(loadScreen);
                                        }
                                        alert('Failed to switch department: ' + data.message);
                                    }
                                })
                                .catch(error => {
                                    console.error('Error switching account:', error);
                                    if (loadScreen.parentNode) {
                                        document.body.removeChild(loadScreen);
                                    }
                                    alert('An error occurred. Please try again.');
                                });
                            });
                        });
                    </script>
                @endif
            </div>
        </li>
    </ul>
</header>
