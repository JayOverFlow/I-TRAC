$(document).ready(function() {
    let activeUserId = null;
    let messagePolling = null;
    let usersPolling = null;
    let lastMessageCount = 0;

    // Helper: format time
    function formatTime(dateString) {
        if (!dateString) return '';
        const date = new Date(dateString);
        return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
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
                    peopleList.append('<div class="p-3 text-center text-muted"><small>No users found</small></div>');
                    return;
                }

                response.users.forEach(function(user) {
                    const avatar = user.user_profile_photo ? `/img/profiles/${user.user_profile_photo}` : '/img/profiles/blank.avif';
                    const name = `${user.user_firstname} ${user.user_lastname}`;
                    const time = formatTime(user.latest_message_date);
                    const preview = user.latest_message ? user.latest_message : 'No messages yet';
                    
                    let unreadBadge = '';
                    if (user.unread_count > 0) {
                        unreadBadge = `<span class="badge bg-danger rounded-circle float-end" style="width: 20px; height: 20px; line-height: 20px; text-align: center; padding: 0; font-size: 10px;">${user.unread_count}</span>`;
                    }

                    const isActive = activeUserId === user.user_id ? 'active' : '';

                    const personHtml = `
                        <div class="person ${isActive}" data-chat="person-${user.user_id}" data-id="${user.user_id}">
                            <div class="user-info">
                                <div class="f-head">
                                    <img src="${avatar}" alt="avatar">
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
                
                // If not polling, clear and set header
                if (!isPolling) {
                    chatMessagesContainer.empty();
                    lastMessageCount = 0;
                    
                    const user = response.target_user;
                    const avatar = user.user_profile_photo ? `/img/profiles/${user.user_profile_photo}` : '/img/profiles/blank.avif';
                    const name = `${user.user_firstname} ${user.user_lastname}`;
                    
                    $('#active-chat-img').attr('src', avatar);
                    $('#active-chat-name').text(name);
                    $('#active-chat-subtitle').text(user.user_type || 'User');
                    
                    $('#active-chat-header').show();
                    $('#chat-form').show();
                    chatMessagesContainer.show();
                    
                    // Hide non selected
                    $('.chat-not-selected').hide();
                    $('.chat-box-inner').addClass('chat-active');
                    $('.chat-meta-user').addClass('chat-active');
                    $('.chat-footer').addClass('chat-active');
                }

                const newMessages = response.messages;
                
                // Only append if there are new messages
                if (newMessages.length > lastMessageCount) {
                    const messagesToAppend = newMessages.slice(lastMessageCount);
                    let html = '';
                    
                    messagesToAppend.forEach(function(msg) {
                        const type = msg.sender_id == userId ? 'you' : 'me'; 
                        const timestamp = formatTime(msg.created_at);
                        
                        // We put message-info AFTER the bubble in HTML
                        // and use flex-direction: row-reverse in CSS for 'me' to flip them visually
                        html += `
                            <div class="bubble-wrapper ${type}">
                                <div class="bubble ${type}">
                                    ${msg.message}
                                </div>
                                <div class="message-info">
                                    ${timestamp}
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
    
    // Poll users list every 10 seconds
    usersPolling = setInterval(function() {
        loadUsers();
    }, 10000);

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
        
        // optimistic updates
        lastMessageCount++; 
        
        const timestamp = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
        const tempHtml = `
            <div class="bubble-wrapper me">
                <div class="bubble me">
                    ${message}
                    <div class="bubble-actions">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-trash-2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-copy"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path></svg>
                    </div>
                </div>
                <div class="message-info">
                    ${timestamp}
                </div>
            </div>
        `;
        $('#active-chat-messages').append(tempHtml);
        input.val('');
        scrollToBottom();

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

    $('.hamburger, .chat-system .chat-box .chat-not-selected p').on('click', function() {
        $(this).parents('.chat-system').find('.user-list-box').toggleClass('user-list-box-show')
    });

    const ps = new PerfectScrollbar('.chat-conversation-box', { suppressScrollX : true });
    const ps2 = new PerfectScrollbar('.people', { suppressScrollX : true });
});