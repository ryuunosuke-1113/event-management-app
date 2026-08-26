@extends('layouts.app')

@section('content')

    <div class="card">
        <h1>{{ $event->title }}</h1>

        <div style="margin-top: 24px;">
            <p>
                <strong>開催日時：</strong>
                {{ $event->event_date->format('Y年m月d日 H:i') }}
            </p>

            <p>
                <strong>場所：</strong>
                {{ $event->place }}
            </p>

            <p>
                <strong>参加費：</strong>
                {{ number_format($event->price) }}円
            </p>

            <p>
                <strong>申込状況：</strong>
                {{ $occupiedCount }} / {{ $event->capacity }}人
            </p>
        </div>

        <div style="margin-top: 24px;">
            <h2>イベント内容</h2>

            <p style="white-space: pre-wrap;">{{ $event->description }}</p>
        </div>

        @if ($event->cancel_policy)
            <div style="margin-top: 24px;">
                <h2>キャンセルポリシー</h2>

                <p style="white-space: pre-wrap;">
                    {{ $event->cancel_policy }}
                </p>
            </div>
        @endif

        <div style="margin-top: 32px;">

            @if ($isFull)
                <p>
                    <strong>このイベントは現在満員です。</strong>
                </p>
            @elseif (auth()->check())
                <form method="POST" action="{{ route('event-participants.store', $event) }}">
                    @csrf

                    <x-button type="submit" variant="primary">
                        このイベントに参加申し込み
                    </x-button>
                </form>
            @else
                <p>
                    参加申し込みにはログインが必要です。
                </p>

                <x-link-button href="{{ route('login') }}" variant="primary">
                    ログイン
                </x-link-button>
            @endif

        </div>

        @auth
            @php
                $eventConversation = $event->conversations->firstWhere('type', 'event');

                $myMembership = $eventConversation
                    ? $eventConversation->members->firstWhere('user_id', auth()->id())
                    : null;

                $canUseEventChat = $myMembership && $myMembership->archived_at === null;

                $unreadCount = 0;

                if ($canUseEventChat) {
                    $unreadCount = $eventConversation->messages
                        ->where('user_id', '!=', auth()->id())
                        ->filter(function ($message) {
                            return !$message->reads->contains('user_id', auth()->id());
                        })
                        ->count();
                }
            @endphp
            @if ($canUseEventChat)
                <div
                    style="
            margin-top: 16px;
            display: flex;
            align-items: center;
            gap: 10px;
        ">
                    <x-link-button href="{{ route('event-chat.show', $event) }}" variant="secondary">
                        イベントチャット
                    </x-link-button>

                    <span id="event-chat-unread-count" @if ($unreadCount === 0) style="display: none;" @endif>
                        未読 {{ $unreadCount }}
                    </span>
                </div>
            @endif
            @if (auth()->id() !== $event->organizer_id)
                <div style="margin-top: 16px;">
                    <form method="POST" action="{{ route('direct-chat.start', $event->organizer) }}">
                        @csrf

                        <input type="hidden" name="event_id" value="{{ $event->id }}">

                        <x-button type="submit" variant="primary">
                            主催者に問い合わせ
                        </x-button>
                    </form>
                </div>
            @endif

        @endauth

    </div>

    <div class="card">
        <h2>参加者</h2>

        @if ($event->participants->isEmpty())
            <p>まだ参加確定者はいません。</p>
        @else
            <div
                style="
                    display: flex;
                    flex-wrap: wrap;
                    gap: 20px;
                    margin-top: 20px;
                ">
                @foreach ($event->participants as $participant)
                    <a href="{{ route('profile.show', $participant->user) }}"
                        style="
                            width: 120px;
                            text-align: center;
                            text-decoration: none;
                            color: inherit;
                        ">
                        @if ($participant->user->profile?->photo_path)
                            <img src="{{ asset('storage/' . $participant->user->profile->photo_path) }}"
                                alt="{{ $participant->user->name }}"
                                style="
                                    width: 90px;
                                    height: 90px;
                                    object-fit: cover;
                                    border-radius: 50%;
                                ">
                        @else
                            <div
                                style="
                                    width: 90px;
                                    height: 90px;
                                    margin: 0 auto;
                                    border-radius: 50%;
                                    background: #e5e7eb;
                                    display: flex;
                                    align-items: center;
                                    justify-content: center;
                                ">
                                写真なし
                            </div>
                        @endif

                        <div style="margin-top: 8px;">
                            {{ $participant->user->name }}
                        </div>
                    </a>
                @endforeach
            </div>
        @endif
    </div>
    @auth
        @if ($canUseEventChat)
            <script type="module">
                const conversationId = {{ $eventConversation->id }};
                const currentUserId = {{ auth()->id() }};

                let unreadCount = {{ $unreadCount }};

                const channel = window.Echo.private(
                    `conversation.${conversationId}`
                );

                channel.listen('.message.sent', (event) => {
                    if (event.user.id === currentUserId) {
                        return;
                    }

                    unreadCount++;

                    const unreadElement = document.getElementById(
                        'event-chat-unread-count'
                    );

                    if (unreadElement) {
                        unreadElement.textContent = `未読 ${unreadCount}`;
                        unreadElement.style.display = '';
                    }
                });
            </script>
        @endif
    @endauth

@endsection
