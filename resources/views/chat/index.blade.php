@extends('layouts.main')

@section('title', 'Chat')

@section('content')
<div class="chat-sidebar-content">
    <div class="tab-content" id="pills-tabContent">
        <div class="tab-pane fade show active" id="pills-Chats">
            <div class="p-3">
                <div class="meeting-button d-flex justify-content-between">
                    <!-- ...meeting and new chat buttons... -->
                </div>
                <div class="dropdown mt-3">
                    <!-- ...recent chats dropdown... -->
                </div>
            </div>
            <div class="chat-list">
                <div class="list-group list-group-flush">
                    @foreach($users as $user)
                        <a href="javascript:;" class="list-group-item" data-user-id="{{ $user->id }}">
                            <div class="d-flex">
                                <div class="chat-user-online">
                                    <img src="{{ asset('assets/images/avatars/avatar-2.png') }}" width="42" height="42" class="rounded-circle" alt="" />
                                </div>
                                <div class="flex-grow-1 ms-2">
                                    <h6 class="mb-0 chat-title">{{ $user->name }}</h6>
                                    <p class="mb-0 chat-msg">Click to chat</p>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Chat window and footer would go here, JS needed for dynamic chat -->
@endsection
