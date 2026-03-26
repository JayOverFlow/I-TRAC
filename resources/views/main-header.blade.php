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
            <img src="{{ asset('img/itrac-header-logo-red.svg') }}" alt="I-TRAC" width="170" height="36" style="object-fit: contain;">
        </li>
    </ul>

    <ul class="navbar-item flex-row ms-md-auto ms-0 action-area">

        <li class="nav-item dropdown notification-dropdown">
            <a href="javascript:void(0);" class="nav-link dropdown-toggle" id="notificationDropdown"
                data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <button type="button" class="btn p-0 border-0 bg-transparent" aria-label="Notifications">
                    <img src="{{ asset('img/icons/bell.png') }}" alt="Notification">
                </button>
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
                    <div class="avatar avatar-sm avatar-indicators avatar-online">
                        <img alt="avatar" src="{{ asset('img/profile-30.png') }}"
                            class="rounded-circle head-avatar">
                    </div>
                </div>
            </a>

            <div class="dropdown-menu position-absolute" aria-labelledby="userProfileDropdown">
                <div class="user-profile-section">
                    <div class="media mx-auto">
                        <div class="media-body">
                            <h5>Patrick </h5>
                            <p style="color: #8B0000 !important;">Project Leader</p>
                        </div>
                    </div>
                </div>
                <div class="dropdown-item">
                    <a href="user-profile.html">
                        <img src="{{ asset('img/icons/profile.png') }}" alt="Notification">
                        <span>Profile</span>
                    </a>
                </div>
                <div class="dropdown-item">
                    <a href="app-mailbox.html">
                        <img src="{{ asset('img/icons/inbox.png') }}" alt="Notification">
                        <span>Inbox</span>
                    </a>
                </div>
                <div class="dropdown-item">
                    <a href="auth-boxed-lockscreen.html">
                        <img src="{{ asset('img/icons/settings.png') }}" alt="Notification">
                        <span>Lock Screen</span>
                    </a>
                </div>
                <div class="dropdown-item">
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <a type="submit">
                            <img src="{{ asset('img/icons/logout.png') }}" width="26" height="26" alt="Logout">
                            <span>Log Out</span>
                        </a>

                    </form>
                </div>
            </div>
        </li>

{{--        <li class="ms-3">--}}
{{--            <div>--}}
{{--                <p class="fs-6 text-white mb-0">{{ auth()->user()->user_firstname }}</p>--}}
{{--                <p id="role" class="text-decoration-underline mb-0">Head - Full Stack Developer</p>--}}
{{--            </div>--}}
{{--        </li>--}}

        <li class="ms-3 d-flex justify-content-center align-items-center">
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="btn p-0 border-0 bg-transparent" aria-label="Logout">
                    <img src="{{ asset('img/logout.png') }}" width="26" height="26" alt="Logout">
                </button>
            </form>
        </li>
    </ul>
</header>
