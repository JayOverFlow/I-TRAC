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
            'PR Revised'    => 'PR Revised',
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
                                    'PR Revised'       => ['bg' => '#eef2ff', 'stroke' => '#4f46e5'],
                                ];
                                $color = $typeColors[$type] ?? ['bg' => '#f0f0f0', 'stroke' => '#888'];
                                $badgeColors = [
                                    'Purchase Request' => 'background:#4361ee;',
                                    'PR Assignment'    => 'background:#4361ee;',
                                    'PR Submitted'     => 'background:#00ab55;',
                                    'PO Submitted'     => 'background:#e6830a;',
                                    'PR Revised'       => 'background:#4f46e5;',
                                ];
                                $badgeStyle = $badgeColors[$type] ?? 'background:#888;';
                                $typeLabel = match($type) {
                                    'PR Submitted'  => 'PR Submitted',
                                    'PO Submitted'  => 'PO Submitted',
                                    'PR Assignment' => 'PR Assigned',
                                    'Purchase Request' => 'PR Assigned',
                                    'PR Revised'    => 'PR Revised',
                                    default         => 'Notification',
                                };
                            @endphp
                             <div class="dropdown-item preview-item-wrapper {{ is_null($task->read_at) ? 'unread' : 'read' }}" onclick="markNotifRead({{ $task->task_id }}, '{{ ($task->task_type === 'PR Revised' && $task->pr_id_fk && ($origTask = \App\Models\Task::where('pr_id_fk', $task->pr_id_fk)->where('task_type', '!=', 'PR Revised')->first())) ? route('show.create.pr', $origTask->task_id) : route('show.tasks') }}')"
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
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 22 23" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                            class="feather feather-settings">
                            <path d="M10.8333 14.1667C12.3521 14.1667 13.5833 12.8795 13.5833 11.2917C13.5833 9.70385 12.3521 8.41667 10.8333 8.41667C9.31455 8.41667 8.08333 9.70385 8.08333 11.2917C8.08333 12.8795 9.31455 14.1667 10.8333 14.1667Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M17.6167 14.1667C17.4946 14.4557 17.4582 14.7764 17.5122 15.0872C17.5661 15.3981 17.7078 15.685 17.9192 15.9108L17.9742 15.9683C18.1446 16.1463 18.2798 16.3577 18.3721 16.5904C18.4644 16.8231 18.5119 17.0725 18.5119 17.3244C18.5119 17.5763 18.4644 17.8257 18.3721 18.0583C18.2798 18.291 18.1446 18.5024 17.9742 18.6804C17.8039 18.8586 17.6017 19 17.3791 19.0964C17.1566 19.1929 16.918 19.2426 16.6771 19.2426C16.4362 19.2426 16.1976 19.1929 15.975 19.0964C15.7525 19 15.5503 18.8586 15.38 18.6804L15.325 18.6229C15.109 18.402 14.8346 18.2538 14.5372 18.1974C14.2398 18.141 13.9331 18.1791 13.6567 18.3067C13.3855 18.4281 13.1543 18.6299 12.9914 18.887C12.8286 19.1441 12.7412 19.4454 12.74 19.7538V19.9167C12.74 20.425 12.5468 20.9125 12.203 21.272C11.8592 21.6314 11.3929 21.8333 10.9067 21.8333C10.4204 21.8333 9.95412 21.6314 9.6103 21.272C9.26649 20.9125 9.07333 20.425 9.07333 19.9167V19.8304C9.06624 19.5132 8.96802 19.2056 8.79147 18.9475C8.61491 18.6894 8.36817 18.4929 8.08333 18.3833C7.80685 18.2558 7.50016 18.2177 7.20279 18.2741C6.90543 18.3304 6.63104 18.4787 6.415 18.6996L6.36 18.7571C6.18973 18.9353 5.98754 19.0767 5.76497 19.1731C5.54241 19.2696 5.30384 19.3192 5.06292 19.3192C4.82199 19.3192 4.58342 19.2696 4.36086 19.1731C4.1383 19.0767 3.9361 18.9353 3.76583 18.7571C3.59538 18.5791 3.46015 18.3677 3.36789 18.135C3.27563 17.9023 3.22814 17.6529 3.22814 17.401C3.22814 17.1492 3.27563 16.8998 3.36789 16.6671C3.46015 16.4344 3.59538 16.223 3.76583 16.045L3.82083 15.9875C4.03216 15.7616 4.17392 15.4748 4.22784 15.1639C4.28175 14.853 4.24536 14.5324 4.12333 14.2433C4.00713 13.9599 3.81419 13.7182 3.56826 13.5479C3.32233 13.3776 3.03414 13.2862 2.73917 13.285H2.58333C2.0971 13.285 1.63079 13.0831 1.28697 12.7236C0.943154 12.3642 0.75 11.8767 0.75 11.3683C0.75 10.86 0.943154 10.3725 1.28697 10.013C1.63079 9.6536 2.0971 9.45167 2.58333 9.45167H2.66583C2.96924 9.44425 3.2635 9.34157 3.51036 9.15699C3.75721 8.97241 3.94524 8.71445 4.05 8.41667C4.17202 8.12762 4.20842 7.80698 4.1545 7.4961C4.10059 7.18522 3.95883 6.89836 3.7475 6.6725L3.6925 6.615C3.52204 6.43699 3.38682 6.22561 3.29456 5.99293C3.2023 5.76025 3.15481 5.51084 3.15481 5.25896C3.15481 5.00708 3.2023 4.75767 3.29456 4.52499C3.38682 4.29231 3.52204 4.08092 3.6925 3.90292C3.86277 3.72471 4.06496 3.58334 4.28753 3.48689C4.51009 3.39043 4.74865 3.34078 4.98958 3.34078C5.23051 3.34078 5.46908 3.34078 5.69164 3.48689C5.9142 3.58334 6.1164 3.72471 6.28667 3.90292L6.34167 3.96042C6.55771 4.18135 6.8321 4.32955 7.12946 4.38592C7.42682 4.44229 7.73352 4.40424 8.01 4.27667H8.08333C8.35445 4.15519 8.58568 3.95347 8.74855 3.69636C8.91142 3.43925 8.99882 3.13796 9 2.82958V2.66667C9 2.15834 9.19315 1.67082 9.53697 1.31138C9.88079 0.951934 10.3471 0.75 10.8333 0.75C11.3196 0.75 11.7859 0.951934 12.1297 1.31138C12.4735 1.67082 12.6667 2.15834 12.6667 2.66667V2.75292C12.6678 3.0613 12.7552 3.36258 12.9181 3.6197C13.081 3.87681 13.3122 4.07852 13.5833 4.2C13.8598 4.32757 14.1665 4.36562 14.4639 4.30925C14.7612 4.25289 15.0356 4.10468 15.2517 3.88375L15.3067 3.82625C15.4769 3.64805 15.6791 3.50667 15.9017 3.41022C16.1243 3.31376 16.3628 3.26412 16.6037 3.26412C16.8447 3.26412 17.0832 3.31376 17.3058 3.41022C17.5284 3.50667 17.7306 3.64805 17.9008 3.82625C18.0713 4.00426 18.2065 4.21564 18.2988 4.44832C18.391 4.681 18.4385 4.93041 18.4385 5.18229C18.4385 5.43417 18.391 5.68358 18.2988 5.91626C18.2065 6.14894 18.0713 6.36033 17.9008 6.53833L17.8458 6.59583C17.6345 6.82169 17.4927 7.10856 17.4388 7.41944C17.3849 7.73031 17.4213 8.05095 17.5433 8.34V8.41667C17.6595 8.70011 17.8525 8.94185 18.0984 9.11212C18.3443 9.2824 18.6325 9.37377 18.9275 9.375H19.0833C19.5696 9.375 20.0359 9.57693 20.3797 9.93638C20.7235 10.2958 20.9167 10.7833 20.9167 11.2917C20.9167 11.8 20.7235 12.2875 20.3797 12.647C20.0359 13.0064 19.5696 13.2083 19.0833 13.2083H19.0008C18.7059 13.2096 18.4177 13.3009 18.1717 13.4712C17.9258 13.6415 17.7329 13.8832 17.6167 14.1667Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
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
                        'PR Revised':       { bg: '#eef2ff', stroke: '#4f46e5' },
                    };
                    const color = typeColors[n.task_type] || { bg: '#f0f0f0', stroke: '#888' };
                    const badgeColors = {
                        'Purchase Request': 'background:#4361ee;',
                        'PR Assignment':    'background:#4361ee;',
                        'PR Submitted':     'background:#00ab55;',
                        'PO Submitted':     'background:#e6830a;',
                        'PR Revised':       'background:#4f46e5;',
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