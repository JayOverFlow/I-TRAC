<header class="header navbar navbar-expand-sm expand-header">

    <a href="javascript:void(0);" class="sidebarCollapse" data-placement="bottom"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-menu"><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg></a>
    
    <ul class="navbar-item theme-brand flex-row  text-center">
        <li class="nav-item theme-logo">
            <img src="{{ asset('img/itrac-header-logo-red.svg') }}" class="light-logo" alt="I-TRAC" width="170" height="36" style="object-fit: contain;">
            <img src="{{ asset('img/itrac-header-logo-white.svg') }}" class="dark-logo" alt="I-TRAC" width="170" height="36" style="object-fit: contain;">
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
                <img src="{{ asset('img/logs.svg') }}" class="header-logs-icon" alt="Activity Log" width="19" height="19" style="object-fit: contain;">
            </a>

            <div class="dropdown-menu position-absolute" aria-labelledby="notificationDropdown">
                <div class="drodpown-title message text-center">
                    <h6 class="text-center">
                        <span class="header-log-title">Recent Activities</span> 
                    </h6>
                </div>
                @php
                    $adminLogs = \App\Models\ActivityLog::where('log_admin_id', session('admin_id'))
                        ->orderBy('log_created_at', 'desc')
                        ->limit(5)
                        ->get();
                @endphp

                <div class="notification-scroll" id="headerActivityLogs">
                    @forelse($adminLogs as $log)
                        <div class="dropdown-item">
                            <div class="media">
                                <div class="media-body">
                                    <div class="data-info">
                                        <h6 class="">{{ $log->log_short_description }}</h6>
                                        <p class="">{{ \Carbon\Carbon::parse($log->log_created_at)->diffForHumans() }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="dropdown-item">
                            <div class="media">
                                <div class="media-body">
                                    <div class="data-info">
                                        <h6 class="">No recent activities</h6>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforelse
                </div>

                <div class="dropdown-item">
                    <div class="text-center pb-2">
                        <a href="{{ route('admin.activity-logs') }}" class="text-primary fw-bold">View All</a>
                    </div>
                </div>

            </div>

        </li>

        <li class="nav-item dropdown user-profile-dropdown order-lg-0 order-1 ms-3">
            <a href="javascript:void(0);" class="nav-link dropdown-toggle user" id="userProfileDropdown" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <div class="avatar-container">
                    <div class="avatar avatar-sm">
                        <img alt="avatar" src="{{ asset('img/admin-icon.svg') }}" class="rounded-circle">
                    </div>
                </div>
            </a>

            <div class="dropdown-menu position-absolute" aria-labelledby="userProfileDropdown">
                <div class="user-profile-section">
                    <div class="media mx-auto">
                        @php
                            $admin = \App\Models\Admin::find(session('admin_id'));
                        @endphp
                        <div class="media-body">
                            <h5>{{ session('admin_username') }}</h5>
                            <p>Admin {{ $admin ? $admin->admin_key : '' }}</p>
                        </div>
                    </div>
                </div>
                <div class="dropdown-item">
                    <a href="javascript:void(0);" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-log-out"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg> <span>Log Out</span>
                    </a>
                    <form id="logout-form" action="{{ route('admin.logout') }}" method="POST" class="d-none">
                        @csrf
                    </form>
                </div>
            </div>
        </li>

        @push('js')
        <script>
            $(document).ready(function() {
                $('#userProfileDropdown, #notificationDropdown').on('click', function(e) {
                    e.preventDefault();
                    
                    var $dropdown = $(this).next('.dropdown-menu');
                    var isOpening = !$dropdown.hasClass('show');
                    
                    // Close other dropdowns
                    $('.user-profile-dropdown .dropdown-menu, .notification-dropdown .dropdown-menu').not($dropdown).removeClass('show');
                    
                    $dropdown.toggleClass('show');

                    // If opening notification dropdown, fetch latest logs
                    if (isOpening && $(this).attr('id') === 'notificationDropdown') {
                        fetchLatestLogs();
                    }
                });

                function fetchLatestLogs() {
                    $.ajax({
                        url: '{{ route("admin.activity-logs.latest") }}',
                        method: 'GET',
                        success: function(logs) {
                            var $container = $('#headerActivityLogs');
                            $container.empty();

                            if (logs.length > 0) {
                                logs.forEach(function(log) {
                                    $container.append(`
                                        <div class="dropdown-item">
                                            <div class="media">
                                                <div class="media-body">
                                                    <div class="data-info">
                                                        <h6 class="">${log.short_description}</h6>
                                                        <p class="">${log.created_at_human}</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    `);
                                });
                            } else {
                                $container.append(`
                                    <div class="dropdown-item">
                                        <div class="media">
                                            <div class="media-body">
                                                <div class="data-info">
                                                    <h6 class="">No recent activities</h6>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                `);
                            }
                        }
                    });
                }
                
                // Close dropdown when clicking outside
                $(document).on('click', function(e) {
                    if (!$(e.target).closest('.user-profile-dropdown, .notification-dropdown').length) {
                        $('.user-profile-dropdown .dropdown-menu, .notification-dropdown .dropdown-menu').removeClass('show');
                    }
                });
            });
        </script>
        @endpush
    </ul>
</header>
