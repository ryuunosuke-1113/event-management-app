@extends('layouts.app')

@section('content')

    <div class="card">
        <div
            style="
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 20px;
    ">
            <h1 style="margin: 0;">
                チャット一覧
            </h1>

            <x-link-button href="{{ route('chats.archived') }}" variant="secondary">
                アーカイブ済みチャット
            </x-link-button>
        </div>
        @if ($memberships->isEmpty())
            <p>利用できるチャットはありません。</p>
        @else
            <div id="chat-list">
                @foreach ($memberships as $membership)
                    @php
                        $conversation = $membership->conversation;

                        $latestMessage = $conversation->messages->sortByDesc('created_at')->first();

                        $unreadCount = $conversation->messages
                            ->where('user_id', '!=', auth()->id())
                            ->filter(function ($message) {
                                return !$message->reads->contains('user_id', auth()->id());
                            })
                            ->count();
                    @endphp
                    <div id="chat-item-{{ $conversation->id }}"
                        style="
        padding: 16px 0;
        border-bottom: 1px solid #e5e7eb;
    ">
                        @if ($conversation->type === 'event')
                            <h2>
                                {{ $conversation->event->title }}
                            </h2>
                            @if ($conversation->event?->status === 'finished')
                                <span
                                    style="
            margin-left: 8px;
            color: #6b7280;
            font-weight: 700;
        ">
                                    【開催終了】
                                </span>
                            @elseif ($conversation->event?->status === 'cancelled')
                                <span
                                    style="
            margin-left: 8px;
            color: #dc2626;
            font-weight: 700;
        ">
                                    【イベント中止】
                                </span>
                            @endif
                            <span id="unread-count-{{ $conversation->id }}"
                                style="
        display: {{ $unreadCount > 0 ? 'inline-block' : 'none' }};
        color: #dc2626;
        font-weight: 700;
        margin-left: 8px;
    ">
                                未読 {{ $unreadCount }}
                            </span>

                            <p>イベントチャット</p>
                            <div id="latest-message-{{ $conversation->id }}" style="margin-top: 10px; margin-bottom: 12px;">
                                @if ($latestMessage)
                                    <strong id="latest-message-user-{{ $conversation->id }}">
                                        {{ $latestMessage->user->name }}
                                    </strong>

                                    <span id="latest-message-body-{{ $conversation->id }}">
                                        {{ \Illuminate\Support\Str::limit($latestMessage->body, 50) }}
                                    </span>

                                    <div id="latest-message-time-{{ $conversation->id }}"
                                        style="
                margin-top: 4px;
                font-size: 0.85rem;
                color: #6b7280;
            ">
                                        {{ $latestMessage->created_at->format('Y年m月d日 H:i') }}
                                    </div>
                                @else
                                    <span id="latest-message-user-{{ $conversation->id }}"></span>

                                    <span id="latest-message-body-{{ $conversation->id }}">
                                        まだメッセージはありません。
                                    </span>

                                    <div id="latest-message-time-{{ $conversation->id }}"
                                        style="
                margin-top: 4px;
                font-size: 0.85rem;
                color: #6b7280;
            ">
                                    </div>
                                @endif
                            </div>
                            <x-link-button href="{{ route('event-chat.show', $conversation->event) }}" variant="secondary">
                                チャットを開く
                            </x-link-button>
                            @php
                                $isFinishedEvent =
                                    $conversation->type === 'event' &&
                                    in_array($conversation->event?->status, ['finished', 'cancelled'], true);
                            @endphp
                            <form method="POST" action="{{ route('chats.archive', $conversation) }}"
                                style="margin-top: 10px;">
                                @csrf

                                @if ($isFinishedEvent)
                                    <p
                                        style="
            margin: 10px 0 6px;
            color: #6b7280;
            font-size: 14px;
        ">
                                        このイベントは終了しています。不要になった場合はアーカイブできます。
                                    </p>
                                @endif

                                <form method="POST" action="{{ route('chats.archive', $conversation) }}"
                                    style="margin-top: 10px;">
                                    @csrf

                                    @if ($isFinishedEvent)
                                        <x-button type="submit" variant="danger">
                                            このチャットをアーカイブ
                                        </x-button>
                                    @else
                                        <x-button type="submit" variant="secondary">
                                            アーカイブ
                                        </x-button>
                                    @endif
                                </form>
                            @elseif ($conversation->type === 'direct')
                                @php
                                    $otherMember = $conversation->members->firstWhere('user_id', '!=', auth()->id());
                                @endphp

                                <h2>
                                    {{ $otherMember->user->name }} さん
                                </h2>
                                <span id="unread-count-{{ $conversation->id }}"
                                    style="
        display: {{ $unreadCount > 0 ? 'inline-block' : 'none' }};
        color: #dc2626;
        font-weight: 700;
        margin-left: 8px;
    ">
                                    未読 {{ $unreadCount }}
                                </span>
                                <p>ダイレクトチャット</p>
                                <div id="latest-message-{{ $conversation->id }}"
                                    style="margin-top: 10px; margin-bottom: 12px;">
                                    @if ($latestMessage)
                                        <strong id="latest-message-user-{{ $conversation->id }}">
                                            {{ $latestMessage->user->name }}
                                        </strong>

                                        <span id="latest-message-body-{{ $conversation->id }}">
                                            {{ \Illuminate\Support\Str::limit($latestMessage->body, 50) }}
                                        </span>

                                        <div id="latest-message-time-{{ $conversation->id }}"
                                            style="
                margin-top: 4px;
                font-size: 0.85rem;
                color: #6b7280;
            ">
                                            {{ $latestMessage->created_at->format('Y年m月d日 H:i') }}
                                        </div>
                                    @else
                                        <span id="latest-message-user-{{ $conversation->id }}"></span>

                                        <span id="latest-message-body-{{ $conversation->id }}">
                                            まだメッセージはありません。
                                        </span>

                                        <div id="latest-message-time-{{ $conversation->id }}"
                                            style="
                margin-top: 4px;
                font-size: 0.85rem;
                color: #6b7280;
            ">
                                        </div>
                                    @endif
                                </div>
                                <x-link-button href="{{ route('direct-chat.show', $conversation) }}" variant="secondary">
                                    チャットを開く
                                </x-link-button>
                                <form method="POST" action="{{ route('chats.archive', $conversation) }}"
                                    style="margin-top: 10px;">
                                    @csrf

                                    <x-button type="submit" variant="secondary">
                                        アーカイブ
                                    </x-button>
                                </form>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif
    </div>
    @php
        $chats = $memberships
            ->map(function ($membership) {
                $conversation = $membership->conversation;

                $unreadCount = $conversation->messages
                    ->where('user_id', '!=', auth()->id())
                    ->filter(function ($message) {
                        return !$message->reads->contains('user_id', auth()->id());
                    })
                    ->count();

                return [
                    'conversation_id' => $conversation->id,
                    'unread_count' => $unreadCount,
                ];
            })
            ->values();
    @endphp
    @auth
        <script type="module">
            window.addEventListener('pageshow', (event) => {
                if (event.persisted) {
                    window.location.reload();
                }
            });
            const currentUserId = {{ auth()->id() }};

            const chats = @json($chats);

            chats.forEach((chat) => {
                let unreadCount = chat.unread_count;

                const channel = window.Echo.private(
                    `conversation.${chat.conversation_id}`
                );

                channel.listen('.message.sent', (event) => {
                    if (event.user.id !== currentUserId) {
                        unreadCount++;

                        const unreadElement = document.getElementById(
                            `unread-count-${chat.conversation_id}`
                        );

                        if (unreadElement) {
                            unreadElement.textContent = `未読 ${unreadCount}`;
                            unreadElement.style.display = '';
                        }
                    }

                    const userElement = document.getElementById(
                        `latest-message-user-${chat.conversation_id}`
                    );

                    const bodyElement = document.getElementById(
                        `latest-message-body-${chat.conversation_id}`
                    );

                    const timeElement = document.getElementById(
                        `latest-message-time-${chat.conversation_id}`
                    );

                    if (userElement) {
                        userElement.textContent = event.user.name;
                    }

                    if (bodyElement) {
                        const maxLength = 50;

                        bodyElement.textContent =
                            event.body.length > maxLength ?
                            event.body.slice(0, maxLength) + '...' :
                            event.body;
                    }

                    if (timeElement) {
                        timeElement.textContent = event.created_at;
                    }
                    const chatItem = document.getElementById(
                        `chat-item-${chat.conversation_id}`
                    );

                    const chatList = document.getElementById('chat-list');

                    if (chatItem && chatList) {
                        chatList.prepend(chatItem);
                    }
                });
            });
        </script>
    @endauth
@endsection
