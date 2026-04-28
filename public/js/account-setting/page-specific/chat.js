$('.search > input').on('keyup', function() {
  var rex = new RegExp($(this).val(), 'i');
    $('.people .person').hide();
    $('.people .person').filter(function() {
        return rex.test($(this).text());
    }).show();
});

$('.user-list-box .person').on('click', function(event) {
    if ($(this).hasClass('.active')) {
        return false;
    } else {
        var findChat = $(this).attr('data-chat');
        var personName = $(this).find('.user-name').text();
        var personImage = $(this).find('img').attr('src');
        var hideTheNonSelectedContent = $(this).parents('.chat-system').find('.chat-box .chat-not-selected').hide();
        var showChatInnerContent = $(this).parents('.chat-system').find('.chat-box .chat-box-inner').addClass('chat-active');

        if (window.innerWidth <= 767) {
          $('.chat-box .current-chat-user-name .name').html(personName.split(' ')[0]);
        } else if (window.innerWidth > 767) {
          $('.chat-box .current-chat-user-name .name').html(personName);
        }
        $('.chat-box .current-chat-user-name img').attr('src', personImage);
        $('.chat').removeClass('active-chat');
        $('.user-list-box .person').removeClass('active');
        $('.chat-box .overlay-phone-call').css('display', 'block');
        $('.chat-box .overlay-video-call').css('display', 'block');
        $(this).addClass('active');
        $('.chat[data-chat = '+findChat+']').addClass('active-chat');
    }
    if ($(this).parents('.user-list-box').hasClass('user-list-box-show')) {
      $(this).parents('.user-list-box').removeClass('user-list-box-show');
    }
    $('.chat-meta-user').addClass('chat-active');
    $('.chat-footer').addClass('chat-active');

  const ps = new PerfectScrollbar('.chat-conversation-box', {
    suppressScrollX : true
  });

  const getScrollContainer = document.querySelector('.chat-conversation-box');
  getScrollContainer.scrollTop = 0;
});

const ps = new PerfectScrollbar('.people', {
  suppressScrollX : true
});

$('.mail-write-box').on('keydown', function(event) {
    if(event.key === 'Enter') {
        var chatInput = $(this);
        var chatMessageValue = chatInput.val();
        if (chatMessageValue === '') { return; }
        var now = new Date();
        var timestamp = now.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
        $messageHtml = '<div class="bubble-wrapper me">' +
                          '<div class="bubble me">' + 
                              chatMessageValue + 
                              '<div class="bubble-actions">' +
                                  '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-trash-2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>' +
                                  '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-copy"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path></svg>' +
                              '</div>' +
                          '</div>' +
                          '<div class="message-info">' + timestamp + '</div>' +
                       '</div>';
        var appendMessage = $(this).parents('.chat-system').find('.active-chat').append($messageHtml);
        const getScrollContainer = document.querySelector('.chat-conversation-box');
        getScrollContainer.scrollTop = getScrollContainer.scrollHeight;
        var clearChatInput = chatInput.val('');
    }
})

$('.hamburger, .chat-system .chat-box .chat-not-selected p').on('click', function(event) {
  $(this).parents('.chat-system').find('.user-list-box').toggleClass('user-list-box-show')
})

// File Upload Modal Interactivity
$(document).on('click', '#browseFiles', function() {
    $('#fileInput').trigger('click');
});

$(document).on('dragover', '#dropZone', function(e) {
    e.preventDefault();
    $(this).css('background', 'rgba(209, 77, 77, 0.08)');
});

$(document).on('dragleave', '#dropZone', function(e) {
    e.preventDefault();
    $(this).css('background', 'rgba(209, 77, 77, 0.02)');
});

$(document).on('drop', '#dropZone', function(e) {
    e.preventDefault();
    $(this).css('background', 'rgba(209, 77, 77, 0.02)');
    var files = e.originalEvent.dataTransfer.files;
    console.log('Files dropped:', files);
    // You can add logic here to handle the dropped files (e.g., show filenames)
});

$('#fileInput').on('change', function() {
    var files = this.files;
    console.log('Files selected:', files);
    // You can add logic here to handle the selected files
});