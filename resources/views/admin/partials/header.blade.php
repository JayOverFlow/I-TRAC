<header class="header navbar navbar-expand-sm expand-header">

    <a href="javascript:void(0);" class="sidebarCollapse" data-placement="bottom"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-menu"><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg></a>
    
    <ul class="navbar-item theme-brand flex-row  text-center">
        <li class="nav-item theme-logo">
            <img src="{{ asset('img/itrac-header-logo-red.svg') }}" alt="I-TRAC" width="170" height="36" style="object-fit: contain;">
        </li>
    </ul>

    <ul class="navbar-item flex-row ms-md-auto ms-0 action-area">

        <li class="nav-item dropdown user-profile-dropdown order-lg-0 order-1 ms-3">
            <a href="javascript:void(0);" class="nav-link dropdown-toggle user" id="userProfileDropdown" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <div class="avatar-container">
                    <div class="avatar avatar-sm avatar-indicators avatar-online">
                        <img alt="avatar" src="{{ asset('img/admin-icon.svg') }}" class="rounded-circle">
                    </div>
                </div>
            </a>

            <div class="dropdown-menu position-absolute" aria-labelledby="userProfileDropdown">
                <div class="user-profile-section">
                    <div class="media mx-auto">
                        <div class="emoji me-2">
                            &#x1F44B;
                        </div>
                        <div class="media-body">
                            <h5>Admin</h5>
                            <p>Administrator</p>
                        </div>
                    </div>
                </div>
                <div class="dropdown-item">
                    <a href="javascript:void(0);" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-log-out"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg> <span>Log Out</span>
                    </a>
                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                        @csrf
                    </form>
                </div>
            </div>
        </li>

        @push('js')
        <script>
            $(document).ready(function() {
                $('#userProfileDropdown').on('click', function(e) {
                    e.preventDefault();
                    $(this).next('.dropdown-menu').toggleClass('show');
                });
                
                // Close dropdown when clicking outside
                $(document).on('click', function(e) {
                    if (!$(e.target).closest('.user-profile-dropdown').length) {
                        $('.user-profile-dropdown .dropdown-menu').removeClass('show');
                    }
                });
            });
        </script>
        @endpush
    </ul>
</header>
