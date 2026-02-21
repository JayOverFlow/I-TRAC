<nav id="sidebar">

    <div class="navbar-nav theme-brand flex-row  text-center">
        <div class="nav-logo">
            <div class="nav-item theme-logo">
                <a href="./index.html">
                    <img src="{{ asset('img/itrac-header-logo.png') }}" class="navbar-logo" alt="logo">
                </a>
            </div>
        </div>
        <div class="nav-item sidebar-toggle">
            <div class="btn-toggle sidebarCollapse">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-chevrons-left"><polyline points="11 17 6 12 11 7"></polyline><polyline points="18 17 13 12 18 7"></polyline></svg>
            </div>
        </div>
    </div>
    <ul id="nav-bar" class="list-unstyled menu-categories" id="accordionExample">

        
        <li class="menu {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
            <a href="{{ route('admin.dashboard') }}" class="dropdown-toggle d-flex align-items-center gap-2">
                <span>Users</span>
            </a>
        </li>

        <li class="menu {{ request()->routeIs('admin.roles-offices') ? 'active' : '' }}">
            <a href="{{ route('admin.roles-offices') }}" class="dropdown-toggle d-flex align-items-center gap-2">
                <span>Roles and Offices</span>
            </a>
        </li>

        <li class="menu {{ request()->routeIs('admin.roles-assignment') ? 'active' : '' }}">
            <a href="{{ route('admin.roles-assignment') }}" class="dropdown-toggle d-flex align-items-center gap-2">
                <span>Roles Assignment</span>
            </a>
        </li>

    </ul>
    
</nav>
