$(document).ready(function() {
    let activeUserId = null;
    let messagePolling = null;
    let usersPolling = null;
    let lastMessageCount = 0;
    let activeFilter = 'all'; // 'all' or 'unread'
    let lastDateLabel = null;

    // Helper: format time (e.g. 05:38 PM)
    function formatTime(dateString) {
        if (!dateString) return '';
        const date = new Date(dateString);
        return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
    }

    // Helper: format date label (e.g. Today 05:38 PM, Monday 05:38 PM)
    function getMessageDateLabel(dateString) {
        if (!dateString) return '';
        const date = new Date(dateString);
        const today = new Date();
        const yesterday = new Date();
        yesterday.setDate(today.getDate() - 1);

        const timeString = date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });

        if (date.toDateString() === today.toDateString()) {
            return `Today ${timeString}`;
        } else if (date.toDateString() === yesterday.toDateString()) {
            return `Yesterday ${timeString}`;
        } else {
            const options = { weekday: 'long' };
            const dayName = date.toLocaleDateString([], options);
            return `${dayName} ${timeString}`;
        }
    }

    // Helper: escape HTML to prevent XSS
    function escapeHtml(string) {
        return String(string)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    // Helper: debounce
    function debounce(func, wait) {
        let timeout;
        return function(...args) {
            clearTimeout(timeout);
            timeout = setTimeout(() => func.apply(this, args), wait);
        };
    }

    // Load users with existing chats
    function loadUsers(isSearch = false, searchData = null) {
        // If we are currently searching, don't overwrite with normal poll data
        const searchInput = $('.search > input').val().trim();
        if (searchInput !== '' && !isSearch) {
            return; 
        }

        const url = isSearch ? '/chat/search-users' : '/chat/users';
        const data = isSearch ? { q: searchData } : {};

        $.ajax({
            url: url,
            method: 'GET',
            data: data,
            success: function(response) {
                const peopleList = $('#chat-people-list');
                const scrollPos = peopleList.scrollTop(); // Save scroll position
                peopleList.empty();

                if (response.users.length === 0) {
                    if (isSearch) {
                        peopleList.append('<div class="p-3 text-center text-muted"><small>No users found</small></div>');
                    } else {
                        peopleList.append(`
                            <div class="no-chat-history text-center p-4 mt-5">
                                <img src="/img/search-user-chat.svg" alt="Search User" class="img-fluid mb-4" style="max-width: 150px;">
                                <h6 style="font-size: 14px; font-weight: 600;">Search user and start chatting now</h6>
                            </div>
                        `);
                    }
                    return;
                }

                // Filter users when Unread tab is active
                const usersToRender = activeFilter === 'unread'
                    ? response.users.filter(u => u.unread_count > 0)
                    : response.users;

                if (usersToRender.length === 0 && activeFilter === 'unread') {
                    peopleList.append('<div class="p-3 text-center text-muted"><small>No unread messages</small></div>');
                    return;
                }

                usersToRender.forEach(function(user) {
                    const avatar = user.user_profile_photo ? `/${user.user_profile_photo}` : '/img/profiles/blank.avif';
                    const name = `${user.user_firstname} ${user.user_lastname}`;
                    const time = formatTime(user.latest_message_date);
                    const preview = user.latest_message ? user.latest_message : 'No messages yet';
                    
                    let unreadBadge = '';
                    if (user.unread_count > 0) {
                        unreadBadge = `<span class="badge bg-danger rounded-circle float-end" style="width: 20px; height: 20px; line-height: 20px; text-align: center; padding: 0; font-size: 10px;">${user.unread_count}</span>`;
                    }

                    const isActive = activeUserId === user.user_id ? 'active' : '';
                    const onlineDot = user.is_online ? '<span class="online-dot"></span>' : '';

                    const personHtml = `
                        <div class="person ${isActive}" data-chat="person-${user.user_id}" data-id="${user.user_id}">
                            <div class="user-info">
                                <div class="f-head">
                                    <img src="${avatar}" alt="avatar">
                                    ${onlineDot}
                                </div>
                                <div class="f-body">
                                    <div class="meta-info">
                                        <span class="user-name" data-name="${name}">${name}</span>
                                        <span class="user-meta-time">${time}</span>
                                    </div>
                                    <span class="preview" style="display: block;">${preview} ${unreadBadge}</span>
                                </div>
                            </div>
                        </div>
                    `;
                    peopleList.append(personHtml);
                });
                
                if (!isSearch) {
                    peopleList.scrollTop(scrollPos); // Restore scroll position only for normal polling
                }
            },
            error: function(err) {
                console.error("Failed to load users", err);
            }
        });
    }

    // Load messages
    function loadMessages(userId, isPolling = false) {
        $.ajax({
            url: `/chat/messages/${userId}`,
            method: 'GET',
            success: function(response) {
                const chatMessagesContainer = $('#active-chat-messages');
                const user = response.target_user;
                
                // If not polling, clear and set header
                if (!isPolling) {
                    chatMessagesContainer.empty();
                    lastMessageCount = 0;
                    lastDateLabel = null;
                    
                    const avatar = user.user_profile_photo ? `/${user.user_profile_photo}` : '/img/profiles/blank.avif';
                    const name = `${user.user_firstname} ${user.user_lastname}`;
                    
                    $('#active-chat-img').attr('src', avatar);
                    const statusDot = user.is_online 
                        ? '<span class="online-pill">Online</span>' 
                        : '<span class="offline-pill">Offline</span>';

                    $('#active-chat-name').html(`${name} ${statusDot}`);
                    $('#active-chat-subtitle').text(user.department_name || 'User');

                    
                    $('#active-chat-header').show();
                    $('#chat-form').show();
                    chatMessagesContainer.show();
                    
                    // Hide non selected
                    $('.chat-not-selected').hide();
                    $('.chat-box-inner').addClass('chat-active');
                    $('.chat-meta-user').addClass('chat-active');
                    $('.chat-footer').addClass('chat-active');
                } else if (user) {
                    // Update online/offline status badge dynamically
                    const name = `${user.user_firstname} ${user.user_lastname}`;
                    const statusDot = user.is_online 
                        ? '<span class="online-pill">Online</span>' 
                        : '<span class="offline-pill">Offline</span>';
                    $('#active-chat-name').html(`${name} ${statusDot}`);
                }

                const newMessages = response.messages;
                
                // Re-render if message count has changed (messages added or deleted)
                if (newMessages.length !== lastMessageCount) {
                    chatMessagesContainer.empty();
                    lastDateLabel = null;
                    let html = '';
                    
                    newMessages.forEach(function(msg) {
                        const type = msg.sender_id == userId ? 'you' : 'me'; 
                        const timestamp = formatTime(msg.created_at);
                        const dateLabel = getMessageDateLabel(msg.created_at);
                        
                        if (dateLabel !== lastDateLabel) {
                            html += `
                                <div class="chat-date-divider-container">
                                    <div class="chat-date-divider">
                                        <span>${dateLabel}</span>
                                    </div>
                                </div>
                            `;
                            lastDateLabel = dateLabel;
                        }
                        html += `
                            <div class="bubble-wrapper ${type}" id="msg-wrapper-${msg.message_id}">
                                <div class="bubble ${type}">
                                    ${escapeHtml(msg.message)}
                                </div>
                                <div class="bubble-actions-container">
                                    <button class="chat-action-btn copy-msg" data-text="${escapeHtml(msg.message)}" title="Copy Message">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-copy"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path></svg>
                                    </button>
                                    <span class="chat-time-label">${timestamp}</span>
                                </div>
                            </div>
                        `;
                    });

                    const getScrollContainer = document.querySelector('.chat-conversation-box');
                    const isAtBottom = getScrollContainer.scrollHeight - getScrollContainer.scrollTop === getScrollContainer.clientHeight;
                    
                    chatMessagesContainer.append(html);
                    lastMessageCount = newMessages.length;

                    if (!isPolling || isAtBottom) {
                        scrollToBottom();
                    }
                }

                if (!isPolling) {
                    chatMessagesContainer.addClass('active-chat');
                }
            }
        });
    }

    function scrollToBottom() {
        const getScrollContainer = document.querySelector('.chat-conversation-box');
        if (getScrollContainer) {
            getScrollContainer.scrollTop = getScrollContainer.scrollHeight;
        }
    }

    // Init load
    loadUsers();
    
    // Poll users list every 3 seconds for real-time online status and previews
    usersPolling = setInterval(function() {
        loadUsers();
    }, 3000);

    // User click
    $(document).on('click', '.user-list-box .person', function() {
        if ($(this).hasClass('active')) return false;
        
        $('.user-list-box .person').removeClass('active');
        $(this).addClass('active');
        
        activeUserId = $(this).data('id');
        loadMessages(activeUserId);
        
        // Start polling for this specific conversation
        if (messagePolling) clearInterval(messagePolling);
        messagePolling = setInterval(function() {
            loadMessages(activeUserId, true);
        }, 3000); // 3 seconds poll for active chat
        
        if ($(window).width() <= 767) {
            $('.user-list-box').removeClass('user-list-box-show');
        }

        // If clicked during search, clear search and load regular users
        const searchInput = $('.search > input');
        if (searchInput.val().trim() !== '') {
            searchInput.val('');
            loadUsers();
        }
    });

    // Send Message
    $('#chat-form').on('submit', function(e) {
        e.preventDefault();
        const input = $('#chat-input-message');
        const message = input.val().trim();
        
        if (!message || !activeUserId) return;
        
        input.val('');

        // AJAX POST
        $.ajax({
            url: '/chat/messages',
            method: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                receiver_id: activeUserId,
                message: message
            },
            success: function(response) {
                loadMessages(activeUserId);
                loadUsers();
            },
            error: function(err) {
                console.error("Failed to send message", err);
            }
        });
    });
    
    $('#chat-send-btn').on('click', function() {
        $('#chat-form').submit();
    });

    // AJAX Search
    const handleSearch = debounce(function(e) {
        const query = $(this).val().trim();
        if (query === '') {
            loadUsers(); // Reset to default
        } else {
            loadUsers(true, query); // Perform search
        }
    }, 500); // 500ms debounce

    $('.search > input').on('keyup', handleSearch);

    // All / Unread filter tabs
    $(document).on('click', '.inbox-filter-btn', function() {
        if ($(this).hasClass('active')) return;
        $('.inbox-filter-btn').removeClass('active');
        $(this).addClass('active');
        activeFilter = $(this).data('filter');
        loadUsers();
    });

    $('.hamburger, .chat-system .chat-box .chat-not-selected p').on('click', function() {
        $(this).parents('.chat-system').find('.user-list-box').toggleClass('user-list-box-show')
    });

    // Event listener: Copy message text
    $(document).on('click', '.copy-msg', function() {
        const text = $(this).data('text');
        navigator.clipboard.writeText(text).then(() => {
            const btn = $(this);
            btn.css('background', '#00ab55').css('color', '#fff').css('border-color', '#00ab55');
            setTimeout(() => {
                btn.css('background', '').css('color', '').css('border-color', '');
            }, 1000);
        });
    });

    const ps = new PerfectScrollbar('.chat-conversation-box', { suppressScrollX : true });
    const ps2 = new PerfectScrollbar('.people', { suppressScrollX : true });
});