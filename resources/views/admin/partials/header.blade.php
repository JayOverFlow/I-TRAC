<header class="header navbar navbar-expand-sm expand-header">

    <a href="javascript:void(0);" class="sidebarCollapse" data-placement="bottom"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-menu"><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg></a>
    
    <ul class="navbar-item theme-brand flex-row  text-center">
        <li class="nav-item theme-logo">
            <img src="{{ asset('img/itrac-header-logo.png') }}" alt="I-TRAC">
        </li>
    </ul>

    <ul class="navbar-item flex-row ms-md-auto ms-0 action-area">

    
        <li class="ms-3">
            <div>
                <p class="fs-6 text-white mb-0">Name</p>
                <p id="role" class="text-decoration-underline mb-0">Admin</p>
            </div>
        </li>

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
