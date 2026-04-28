@push('css')
    <link rel="stylesheet" href="{{ asset('css/account-setting/page-specific/chat.css') }}">
@endpush

@push('js')
    <script src="{{ asset('js/account-setting/page-specific/chat.js') }}"></script>
@endpush

{{-- Inbox Tab --}}
<div class="tab-pane fade" id="animated-underline-inbox" role="tabpanel"
    aria-labelledby="animated-underline-inbox-tab">
    <div class="chat-section layout-top-spacing">
        <div class="row">

            <div class="col-xl-12 col-lg-12 col-md-12">

                <div class="chat-system">
                    <div class="hamburger"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-menu mail-menu d-lg-none"><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg></div>
                    <div class="user-list-box">
                        <div class="search">
                            <input type="text" class="form-control" placeholder="Search User" />
                        </div>
                        <div class="people">

                            <div class="person" data-chat="person6">
                                <div class="user-info">
                                    <div class="f-head">
                                        <img src="{{ asset('img/profile-4.jpeg') }}" alt="avatar">
                                    </div>
                                    <div class="f-body">
                                        <div class="meta-info">
                                            <span class="user-name" data-name="Sophia Jane Ferrer">Sophia Jane Ferrer</span>
                                            <span class="user-meta-time">2:09 PM</span>
                                        </div>
                                        <span class="preview">Sige po, thank you!</span>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                    <div class="chat-box">

                        <div class="chat-not-selected">
                            <p> <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-message-square"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg> Click User To Chat</p>
                        </div>

                        <div class="chat-box-inner">
                            <div class="chat-meta-user">
                                <div class="current-chat-user-name">
                                    <span>
                                        <img src="{{ asset('img/90x90.jpg') }}" alt="dynamic-image">
                                        <div class="user-detail">
                                            <div class="d-flex align-items-center">
                                                <span class="name"></span>
                                            </div>
                                            <span class="subtitle">Assistant Director for Research and Extension Office</span>
                                        </div>
                                    </span>
                                </div>
                            </div>
                            <div class="chat-conversation-box">
                                <div id="chat-conversation-box-scroll" class="chat-conversation-box-scroll">
                                    <div class="chat" data-chat="person6">
                                        <div class="conversation-start">
                                            <span>Monday, 1:27 PM</span>
                                        </div>
                                        <div class="bubble-wrapper you">
                                            <div class="bubble you">
                                                Good morning, Sir!
                                                <div class="bubble-actions">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-trash-2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-copy"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path></svg>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="bubble-wrapper you">
                                            <div class="bubble you">
                                                Na-review niyo na po ba ang PR na sinend ko?
                                                <div class="bubble-actions">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-trash-2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-copy"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path></svg>
                                                </div>
                                            </div>
                                            
                                        </div>
                                        <div class="bubble-wrapper me">
                                            <div class="bubble me">
                                                Good morning!
                                                <div class="bubble-actions">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-trash-2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-copy"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path></svg>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="bubble-wrapper me">
                                            <div class="bubble me">
                                                Yes, I did.
                                                <div class="bubble-actions">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-trash-2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-copy"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path></svg>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="conversation-start today">
                                            <span>Today 5:38 PM</span>
                                        </div>
                                        <div class="bubble-wrapper you">
                                            <div class="bubble you">
                                                Sige po, thank you!
                                                <div class="bubble-actions">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-trash-2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-copy"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path></svg>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="chat-footer">
                                <div class="chat-input">
                                    <form class="chat-form d-flex align-items-center gap-3" action="javascript:void(0);">
                                        <input type="text" class="mail-write-box form-control flex-grow-1" placeholder="Type or add your message..."/>
                                        
                                        <button type="button" class="btn p-0 shadow-none bg-transparent border-0 d-flex align-items-center justify-content-center">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#bfc9d4" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-smile"><circle cx="12" cy="12" r="10"></circle><path d="M8 14s1.5 2 4 2 4-2 4-2"></path><line x1="9" y1="9" x2="9.01" y2="9"></line><line x1="15" y1="9" x2="15.01" y2="9"></line></svg>
                                        </button>
                                        
                                        <button type="button" class="btn p-0 shadow-none bg-transparent border-0 d-flex align-items-center justify-content-center" data-bs-toggle="modal" data-bs-target="#uploadFileModal">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#bfc9d4" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-paperclip"><path d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48"></path></svg>
                                        </button>
                                        
                                        <button type="submit" class="btn p-0 shadow-none bg-transparent border-0 d-flex align-items-center justify-content-center">
                                            <img src="{{ asset('img/chat-send-button.svg') }}" alt="Send" width="40" height="40">
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
{{-- End Inbox Tab --}}
