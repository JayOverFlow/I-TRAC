@php
    $allRoles = auth()->user()->roles;
    $activeRoleId = session('active_role_id') ?? ($allRoles->first()?->role_id ?? null);
    $activeRole = $allRoles->where('role_id', $activeRoleId)->first() ?? $allRoles->first();

    // Helper to format role names according to specific rules
    $formatRoleDisplayName = function ($role) {
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

    $unreadMessagesCount = \App\Models\Message::where('receiver_id', auth()->id())
        ->whereNull('read_at')
        ->count();

    $allRecentMessages = \App\Models\Message::where('receiver_id', auth()->id())
        ->with('sender')
        ->latest('message_id')
        ->take(50)
        ->get();

    // Notifications = tasks assigned to the user that have NOT been read yet
    $unreadNotificationsCount = \App\Models\Task::where('assigned_to', auth()->id())
        ->whereNull('read_at')
        ->count();

    $allRecentNotifications = \App\Models\Task::where('assigned_to', auth()->id())
        ->with('assignedBy')
        ->latest('task_id')
        ->take(50)
        ->get();

    // Filter logic for initial SSR load:
    // 1. Get all unread. If >= 3 unread, display top 3 unread.
    // 2. If < 3 unread, display all unread, and fill the remaining slots with the most recent read items to make it exactly 3.
    $getQueueItems = function ($items) {
        $unread = $items->filter(fn($item) => is_null($item->read_at));
        $read = $items->filter(fn($item) => !is_null($item->read_at));
        
        if ($unread->count() >= 3) {
            return $unread->take(3);
        }
        
        return $unread->concat($read->take(3 - $unread->count()))->take(3);
    };

    $recentMessages = $getQueueItems($allRecentMessages);
    $recentNotifications = $getQueueItems($allRecentNotifications);

    $serializedMessages = $allRecentMessages->map(function ($msg) {
        return [
            'id'           => $msg->message_id,
            'sender_name'  => $msg->sender->user_fullname_no_middle ?? 'User',
            'sender_avatar'=> $msg->sender->user_profile_photo
                                ? asset($msg->sender->user_profile_photo)
                                : asset('img/profiles/blank.avif'),
            'message'      => $msg->message,
            'time'         => $msg->created_at ? $msg->created_at->diffForHumans() : '',
            'is_read'      => !is_null($msg->read_at),
        ];
    })->toArray();

    $serializedNotifications = $allRecentNotifications->map(function ($task) {
        $type = $task->task_type;
        $typeLabel = match($type) {
            'PR Submitted'  => 'PR Submitted',
            'PO Submitted'  => 'PO Submitted',
            'PR Assignment' => 'PR Assigned',
            'Purchase Request' => 'PR Assigned',
            default         => 'Notification',
        };
        return [
            'task_id'          => $task->task_id,
            'task_description' => $task->task_description,
            'task_type'        => $type,
            'type_label'       => $typeLabel,
            'time'             => $task->created_at
                                    ? \Carbon\Carbon::parse($task->created_at)->diffForHumans()
                                    : '',
            'assigned_by_name' => $task->assignedBy->user_fullname_no_middle ?? 'System',
            'url'              => route('show.tasks'),
            'is_read'          => !is_null($task->read_at),
        ];
    })->toArray();
@endphp
<header class="header navbar navbar-expand-sm expand-header">

    <a href="javascript:void(0);" class="sidebarCollapse" data-placement="bottom"><svg
            xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
            class="feather feather-menu">
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
            <a href="javascript:void(0);" class="nav-link dropdown-toggle" id="notificationDropdown"
                data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <span style="position: relative; display: inline-block;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                        class="feather feather-bell">
                        <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                        <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                    </svg>
                    <span id="header-notification-dot" class="badge badge-success badge-dot"
                        style="position: absolute; top: -3px; right: -3px; width: 8px; height: 8px; border-radius: 50%; padding: 0; background-color: #00ab55; transition: opacity 0.5s ease; {{ ($unreadMessagesCount + $unreadNotificationsCount > 0) ? 'opacity: 1; display: block;' : 'opacity: 0; display: none;' }}">&nbsp;</span>
                </span>
            </a>

            <div class="dropdown-menu position-absolute" id="notificationDropdownMenu" aria-labelledby="notificationDropdown">
                <div class="drodpown-title message">
                    <h6 class="d-flex justify-content-between">
                        <span class="align-self-center">Messages</span>
                        <span id="header-messages-count" class="badge badge-primary">{{ $unreadMessagesCount }} Unread</span>
                    </h6>
                </div>
                <div class="notification-scroll">
                    {{-- Messages section --}}
                    <div id="header-messages-list">
                        @forelse ($recentMessages as $msg)
                            <div class="dropdown-item preview-item-wrapper {{ is_null($msg->read_at) ? 'unread' : 'read' }}"
                                onclick="markMsgRead({{ $msg->message_id }}, '{{ route('account.settings') }}#animated-underline-inbox')"
                                style="cursor: pointer;">
                                <div class="media">
                                    <img src="{{ $msg->sender->user_profile_photo ? asset($msg->sender->user_profile_photo) : asset('img/profiles/blank.avif') }}"
                                        class="img-fluid me-2 rounded-circle" alt="avatar"
                                        style="width: 38px; height: 38px; object-fit: cover; flex-shrink: 0;">
                                    <div class="media-body" style="overflow: hidden;">
                                        <div class="data-info" style="display: flex; justify-content: space-between; align-items: center; width: 100%;">
                                            <div>
                                                <h6 class="preview-title">{{ $msg->sender->user_fullname_no_middle }}</h6>
                                                <p style="font-size: 0.72rem; color: #888; margin-bottom: 2px;">{{ $msg->created_at->diffForHumans() }}</p>
                                            </div>
                                            @if(is_null($msg->read_at))
                                                <span class="unread-blue-dot"></span>
                                            @endif
                                        </div>
                                        <p class="text-truncate mb-0 preview-text">{{ $msg->message }}</p>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="dropdown-item text-center py-3">
                                <p class="text-muted mb-0" style="font-size: 0.8rem;">No unread messages</p>
                            </div>
                        @endforelse
                    </div>

                    {{-- Notifications section --}}
                    <div class="drodpown-title notification mt-2">
                        <h6 class="d-flex justify-content-between">
                            <span class="align-self-center">Notifications</span>
                            <span id="header-notifications-count" class="badge badge-secondary">{{ $unreadNotificationsCount }} New</span>
                        </h6>
                    </div>

                    <div id="header-notifications-list">
                        @forelse ($recentNotifications as $task)
                            @php
                                $type = $task->task_type;
                                $typeColors = [
                                    'Purchase Request' => ['bg' => '#eceffe', 'stroke' => '#4361ee'],
                                    'PR Assignment'    => ['bg' => '#eceffe', 'stroke' => '#4361ee'],
                                    'PR Submitted'     => ['bg' => '#e8f8f0', 'stroke' => '#00ab55'],
                                    'PO Submitted'     => ['bg' => '#fff4e6', 'stroke' => '#e6830a'],
                                ];
                                $color = $typeColors[$type] ?? ['bg' => '#f0f0f0', 'stroke' => '#888'];
                                $badgeColors = [
                                    'Purchase Request' => 'background:#4361ee;',
                                    'PR Assignment'    => 'background:#4361ee;',
                                    'PR Submitted'     => 'background:#00ab55;',
                                    'PO Submitted'     => 'background:#e6830a;',
                                ];
                                $badgeStyle = $badgeColors[$type] ?? 'background:#888;';
                                $typeLabel = match($type) {
                                    'PR Submitted'  => 'PR Submitted',
                                    'PO Submitted'  => 'PO Submitted',
                                    'PR Assignment' => 'PR Assigned',
                                    'Purchase Request' => 'PR Assigned',
                                    default         => 'Notification',
                                };
                            @endphp
                             <div class="dropdown-item preview-item-wrapper {{ is_null($task->read_at) ? 'unread' : 'read' }}" onclick="markNotifRead({{ $task->task_id }}, '{{ route('show.tasks') }}')"
                                style="cursor: pointer;">
                                <div class="media" style="align-items: flex-start;">
                                    <div class="notif-icon-circle" style="width: 36px; height: 36px; border-radius: 50%; background: {{ $color['bg'] }}; display: flex; align-items: center; justify-content: center; flex-shrink: 0; margin-right: 10px;">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                                            fill="none" stroke="{{ $color['stroke'] }}" stroke-width="2" stroke-linecap="round"
                                            stroke-linejoin="round" class="feather feather-file-text">
                                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                            <polyline points="14 2 14 8 20 8"></polyline>
                                            <line x1="16" y1="13" x2="8" y2="13"></line>
                                            <line x1="16" y1="17" x2="8" y2="17"></line>
                                        </svg>
                                    </div>
                                    <div class="media-body" style="overflow: hidden; display: flex; flex-direction: column; gap: 4px;">
                                        <div style="display: flex; justify-content: space-between; align-items: flex-start; width: 100%;">
                                            <h6 class="preview-title" style="white-space: normal; line-height: 1.3; margin-bottom: 2px; font-size: 0.82rem; flex: 1;">
                                                {{ $task->task_description ?? 'New Task Assigned' }}
                                            </h6>
                                            @if(is_null($task->read_at))
                                                <span class="unread-blue-dot"></span>
                                            @endif
                                        </div>
                                        <div style="display: flex; align-items: center; gap: 6px;">
                                            <span style="font-size: 0.65rem; padding: 1px 6px; border-radius: 20px; color: #fff; {{ $badgeStyle }}">{{ $typeLabel }}</span>
                                            <p style="font-size: 0.72rem; color: #888; margin-bottom: 0;">
                                                {{ \Carbon\Carbon::parse($task->created_at)->diffForHumans() }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="dropdown-item text-center py-3">
                                <p class="text-muted mb-0" style="font-size: 0.8rem;">No new notifications</p>
                            </div>
                        @endforelse
                    </div>

                </div>
            </div>

        </li>

        <li class="nav-item dropdown user-profile-dropdown  order-lg-0 order-1">
            <a href="javascript:void(0);" class="nav-link dropdown-toggle user" id="userProfileDropdown"
                data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <div class="avatar-container">
                    <div class="avatar avatar-sm">
                        <img alt="avatar"
                            src="{{ auth()->user()->user_profile_photo ? asset(auth()->user()->user_profile_photo) : asset('img/profiles/blank.avif') }}"
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
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                            class="feather feather-user">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                            <circle cx="12" cy="7" r="4"></circle>
                        </svg> <span>Profile</span>
                    </a>
                </div>
                <div class="dropdown-item">
                    <a href="{{ route('account.settings') }}#animated-underline-inbox">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                            class="feather feather-mail">
                            <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z">
                            </path>
                            <polyline points="22,6 12,13 2,6"></polyline>
                        </svg>
                        <span>Inbox</span>
                    </a>
                </div>
                @if ($showApp)
                    <div class="dropdown-item">
                        <a href="{{ route('account.settings') }}#animated-underline-annual-procurement-plan">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                class="feather feather-file-text">
                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                <polyline points="14 2 14 8 20 8"></polyline>
                                <line x1="16" y1="13" x2="8" y2="13"></line>
                                <line x1="16" y1="17" x2="8" y2="17"></line>
                                <polyline points="10 9 9 9 8 9"></polyline>
                            </svg>
                            <span>APP</span>
                        </a>
                    </div>
                @endif
                <div class="dropdown-item">
                    <a href="{{ route('account.settings') }}#animated-underline-settings">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                            class="feather feather-lock">
                            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                            <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                        </svg>
                        <span>Settings</span>
                    </a>
                </div>
                <div class="dropdown-item">
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <a href="javascript:void(0);" onclick="this.closest('form').submit();">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" class="feather feather-log-out">
                                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                                <polyline points="16 17 21 12 16 7"></polyline>
                                <line x1="21" y1="12" x2="9" y2="12"></line>
                            </svg>
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
                            <div class="switch-account-item d-flex align-items-center justify-content-between"
                                data-role-id="{{ $role->role_id }}">
                                <div class="account-info">
                                    <div class="account-role">{{ $displayRoleName }}</div>
                                </div>
                                <div class="account-selector">
                                    <input type="radio" name="active_role_selector" value="{{ $role->role_id }}"
                                        class="custom-radio-selector" {{ $isActive ? 'checked' : '' }}>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    {{-- Click Listener to switch active account/department with premium loading transition --}}
                    <script>
                        document.querySelectorAll('.switch-account-item').forEach(item => {
                            item.addEventListener('click', function (e) {
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
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const dot = document.getElementById('header-notification-dot');
            const msgCount = document.getElementById('header-messages-count');
            const notifCount = document.getElementById('header-notifications-count');
            const msgList = document.getElementById('header-messages-list');
            const notifList = document.getElementById('header-notifications-list');
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
            let fadeTimeout = null;

            // Local state arrays containing the top 50 items
            let rawMessages = @json($serializedMessages);
            let rawNotifications = @json($serializedNotifications);

            // Server-reported counts (could be larger than 50)
            let serverUnreadMsgCount = {{ $unreadMessagesCount }};
            let serverUnreadNotifCount = {{ $unreadNotificationsCount }};

            // Dynamic queuing algorithm:
            // 1. Filter out read items from the queue first.
            // 2. If we have at least 3 unread items, take the 3 most recent unread.
            // 3. Otherwise, fill the remaining slots with the most recently read items.
            function getFilteredQueue(items) {
                const unread = items.filter(i => !i.is_read);
                const read = items.filter(i => i.is_read);
                if (unread.length >= 3) {
                    return unread.slice(0, 3);
                }
                return [...unread, ...read.slice(0, 3 - unread.length)];
            }

            function renderMessages() {
                if (!msgList) return;
                const list = getFilteredQueue(rawMessages);
                if (list.length === 0) {
                    msgList.innerHTML = '<div class="dropdown-item text-center py-3"><p class="text-muted mb-0" style="font-size:0.8rem;">No unread messages</p></div>';
                    return;
                }
                msgList.innerHTML = list.map(m => {
                    const unreadClass = !m.is_read ? 'unread' : 'read';
                    const blueDot = !m.is_read ? '<span class="unread-blue-dot"></span>' : '';
                    return `
                    <div class="dropdown-item preview-item-wrapper ${unreadClass}" onclick="markMsgRead(${m.id}, '{{ route('account.settings') }}#animated-underline-inbox')" style="cursor:pointer;">
                        <div class="media">
                            <img src="${m.sender_avatar}" class="img-fluid me-2 rounded-circle" style="width:38px;height:38px;object-fit:cover;flex-shrink:0;" alt="avatar">
                            <div class="media-body" style="overflow:hidden;">
                                <div class="data-info" style="display:flex; justify-content:space-between; align-items:center; width:100%;">
                                    <div>
                                        <h6 class="preview-title">${m.sender_name}</h6>
                                        <p style="font-size:0.72rem;color:#888;margin-bottom:2px;">${m.time}</p>
                                    </div>
                                    ${blueDot}
                                </div>
                                <p class="text-truncate mb-0 preview-text">${m.message}</p>
                            </div>
                        </div>
                    </div>`;
                }).join('');
            }

            function renderNotifications() {
                if (!notifList) return;
                const list = getFilteredQueue(rawNotifications);
                if (list.length === 0) {
                    notifList.innerHTML = '<div class="dropdown-item text-center py-3"><p class="text-muted mb-0" style="font-size:0.8rem;">No new notifications</p></div>';
                    return;
                }
                notifList.innerHTML = list.map(n => {
                    // Color-code icon background by task type
                    const typeColors = {
                        'Purchase Request': { bg: '#eceffe', stroke: '#4361ee' },
                        'PR Assignment':    { bg: '#eceffe', stroke: '#4361ee' },
                        'PR Submitted':     { bg: '#e8f8f0', stroke: '#00ab55' },
                        'PO Submitted':     { bg: '#fff4e6', stroke: '#e6830a' },
                    };
                    const color = typeColors[n.task_type] || { bg: '#f0f0f0', stroke: '#888' };
                    const badgeColors = {
                        'Purchase Request': 'background:#4361ee;',
                        'PR Assignment':    'background:#4361ee;',
                        'PR Submitted':     'background:#00ab55;',
                        'PO Submitted':     'background:#e6830a;',
                    };
                    const badgeStyle = badgeColors[n.task_type] || 'background:#888;';
                    const label = n.type_label || 'Notification';
                    const unreadClass = !n.is_read ? 'unread' : 'read';
                    const blueDot = !n.is_read ? '<span class="unread-blue-dot"></span>' : '';
                    const description = n.task_description || 'New Task Assigned';

                    return `
                    <div class="dropdown-item preview-item-wrapper ${unreadClass}" onclick="markNotifRead(${n.task_id}, '${n.url}')" style="cursor:pointer;">
                        <div class="media" style="align-items:flex-start;">
                            <div class="notif-icon-circle" style="width:36px;height:36px;border-radius:50%;background:${color.bg};display:flex;align-items:center;justify-content:center;flex-shrink:0;margin-right:10px;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="${color.stroke}" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                    <polyline points="14 2 14 8 20 8"></polyline>
                                    <line x1="16" y1="13" x2="8" y2="13"></line>
                                    <line x1="16" y1="17" x2="8" y2="17"></line>
                                </svg>
                            </div>
                            <div class="media-body" style="overflow:hidden; display:flex; flex-direction:column; gap:4px;">
                                <div style="display:flex; justify-content:space-between; align-items:flex-start; width:100%;">
                                    <h6 class="preview-title" style="white-space: normal; line-height: 1.3; margin-bottom: 2px; font-size: 0.82rem; flex:1;">${description}</h6>
                                    ${blueDot}
                                </div>
                                <div style="display:flex;align-items:center;gap:6px;">
                                    <span style="font-size:0.65rem;padding:1px 6px;border-radius:20px;color:#fff;${badgeStyle}">${label}</span>
                                    <p style="font-size:0.7rem;color:#888;margin-bottom:0;">${n.time}</p>
                                </div>
                            </div>
                        </div>
                    </div>`;
                }).join('');
            }

            // Mark a single message as read, update local UI state immediately, then redirect
            window.markMsgRead = function(messageId, url) {
                const msg = rawMessages.find(m => m.id === messageId);
                if (msg && !msg.is_read) {
                    msg.is_read = true;

                    // Decrement unread message count
                    serverUnreadMsgCount = Math.max(0, serverUnreadMsgCount - 1);
                    if (msgCount) msgCount.textContent = serverUnreadMsgCount + ' Unread';

                    // Rerender list immediately
                    renderMessages();
                    updateBadgeDot();

                    fetch('{{ route('messages.mark.single.read') }}', {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': csrfToken, 'Content-Type': 'application/json' },
                        body: JSON.stringify({ message_id: messageId })
                    }).finally(() => {
                        window.location.href = url;
                    });
                } else {
                    window.location.href = url;
                }
            };

            // Mark a single notification as read, update local UI state immediately, then redirect
            window.markNotifRead = function(taskId, url) {
                const notif = rawNotifications.find(n => n.task_id === taskId);
                if (notif && !notif.is_read) {
                    notif.is_read = true;

                    // Decrement unread notification count
                    serverUnreadNotifCount = Math.max(0, serverUnreadNotifCount - 1);
                    if (notifCount) notifCount.textContent = serverUnreadNotifCount + ' New';

                    // Rerender list immediately
                    renderNotifications();
                    updateBadgeDot();

                    fetch('{{ route('notifications.mark.single.read') }}', {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': csrfToken, 'Content-Type': 'application/json' },
                        body: JSON.stringify({ task_id: taskId })
                    }).finally(() => {
                        window.location.href = url;
                    });
                } else {
                    window.location.href = url;
                }
            };

            function updateBadgeDot() {
                const totalUnread = serverUnreadMsgCount + serverUnreadNotifCount;
                if (dot) {
                    if (totalUnread > 0) {
                        if (fadeTimeout) { clearTimeout(fadeTimeout); fadeTimeout = null; }
                        dot.style.display = 'block';
                        setTimeout(() => { dot.style.opacity = '1'; }, 20);
                    } else {
                        if (dot.style.opacity !== '0' && !fadeTimeout) {
                            fadeTimeout = setTimeout(() => {
                                dot.style.opacity = '0';
                                setTimeout(() => { if (dot.style.opacity === '0') dot.style.display = 'none'; }, 500);
                                fadeTimeout = null;
                            }, 3000);
                        }
                    }
                }
            }

            function checkUnreadCounts() {
                fetch('{{ route('notifications.unread.count') }}')
                    .then(response => response.json())
                    .then(data => {
                        // Re-assign local arrays from the server response
                        rawMessages = data.messages_list;
                        rawNotifications = data.notifications_list;
                        serverUnreadMsgCount = data.unread_messages;
                        serverUnreadNotifCount = data.unread_notifications;

                        // Update counts text
                        if (msgCount) msgCount.textContent = serverUnreadMsgCount + ' Unread';
                        if (notifCount) notifCount.textContent = serverUnreadNotifCount + ' New';

                        // Re-render lists
                        renderMessages();
                        renderNotifications();

                        // Update the badge dot
                        updateBadgeDot();
                    })
                    .catch(error => console.error('Error fetching unread counts:', error));
            }

            // Poll every 5 seconds
            setInterval(checkUnreadCounts, 5000);
        });
    </script>

    <style>
        /* Custom styling for read/unread preview items in the dropdown */
        .preview-item-wrapper {
            position: relative;
            transition: background-color 0.3s ease;
        }

        .preview-item-wrapper.unread {
            background-color: rgba(67, 97, 238, 0.05) !important;
        }

        .preview-item-wrapper.read {
            background-color: transparent !important;
        }

        .preview-title {
            margin-bottom: 2px;
            font-size: 0.82rem;
            transition: color 0.3s ease, font-weight 0.3s ease;
        }

        .unread .preview-title {
            font-weight: 700 !important;
            color: #0f172a !important; /* High contrast dark text for light theme */
        }

        body.dark .unread .preview-title {
            color: #f1f5f9 !important; /* High contrast light text for dark theme */
        }

        .read .preview-title {
            font-weight: 400 !important;
            color: #888888 !important; /* regular/light gray text for read items */
        }

        .preview-text {
            font-size: 0.78rem;
            transition: color 0.3s ease, font-weight 0.3s ease;
        }

        .unread .preview-text {
            font-weight: 700 !important;
            color: #1e293b !important;
        }

        body.dark .unread .preview-text {
            color: #cbd5e1 !important;
        }

        .read .preview-text {
            font-weight: 400 !important;
            color: #aaaaaa !important;
        }

        /* Blue dot indicator for unread items */
        .unread-blue-dot {
            width: 8px;
            height: 8px;
            background-color: #4361ee;
            border-radius: 50%;
            flex-shrink: 0;
            margin-left: 8px;
            align-self: center;
            box-shadow: 0 0 6px rgba(67, 97, 238, 0.8);
            display: inline-block;
        }

        /* Ensure notifications icons inside circular wrappers are centered without right margin */
        .notif-icon-circle svg {
            margin: 0 !important;
            width: 16px !important;
            height: 16px !important;
        }
    </style>
</header>