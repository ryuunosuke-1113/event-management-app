@extends('layouts.app')

@section('content')
    <div class="chat-title-bar"
        style="
        position: sticky;
        top: var(--site-header-height, 70px);
        z-index: 900;
        background: white;
        padding: 12px 16px;
        border-bottom: 1px solid #e5e7eb;
        margin-bottom: 16px;
    ">
        <strong>
            {{ $event->title }}
        </strong>
    </div>

    <div class="card">
        <h1>{{ $event->title }} のイベントチャット</h1>

        <p>
            主催者と参加確定者だけが利用できるチャットです。
        </p>
        <form action="{{ route('event-chat.archive', $event) }}" method="POST" style="margin-top: 12px;">
            @csrf

            <x-button type="submit" variant="secondary">
                チャットをアーカイブ
            </x-button>
        </form>
    </div>

    <div class="card">
        <h2>メッセージ</h2>

        <div id="message-list"
            style="
                display: flex;
                flex-direction: column;
                gap: 16px;
            ">
            @if ($conversation->messages->isEmpty())
                <p id="empty-message">
                    まだメッセージはありません。
                </p>
            @else
                @foreach ($conversation->messages as $message)
                    <div
                        style="
                            padding: 12px;
                            border-radius: 10px;
                            background: #f8fafc;
                        ">
                        <div
                            style="
                                display: flex;
                                align-items: center;
                                gap: 10px;
                                margin-bottom: 8px;
                            ">
                            @if ($message->user->profile?->photo_path)
                                <img src="{{ asset('storage/' . $message->user->profile->photo_path) }}"
                                    alt="{{ $message->user->name }}"
                                    style="
                                        width: 40px;
                                        height: 40px;
                                        object-fit: cover;
                                        border-radius: 50%;
                                    ">
                            @endif

                            <strong>
                                {{ $message->user->name }}
                            </strong>

                            <span style="font-size: 0.85rem;">
                                {{ $message->created_at->format('Y/m/d H:i') }}
                            </span>
                        </div>

                        <div style="white-space: pre-wrap;">
                            {!! $message->body_html !!}
                        </div>

                        @if ($message->user_id === auth()->id())
                            <div id="read-count-{{ $message->id }}"
                                style="
                                    margin-top: 6px;
                                    font-size: 0.8rem;
                                    color: #6b7280;
                                    text-align: right;
                                ">
                                既読 {{ $message->reads->count() }}
                            </div>
                        @endif
                    </div>
                @endforeach
            @endif
        </div>
    </div>
    <div id="chat-bottom"></div>

    <div class="card">
        <h2>メッセージを送る</h2>

        <form id="message-form" method="POST" action="{{ route('event-chat.messages.store', $event) }}"> @csrf

            <div class="form-group">
                <label for="body">
                    メッセージ
                </label>

                <textarea id="body" name="body" rows="5" required>{{ old('body') }}</textarea>

                @error('body')
                    <p>{{ $message }}</p>
                @enderror
            </div>

            <x-button type="submit" variant="primary">
                送信
            </x-button>
        </form>
    </div>

    <div class="card">
        <x-link-button href="{{ route('events.show', $event) }}" variant="secondary">
            イベント詳細へ戻る
        </x-link-button>
    </div>

    <script type="module">
        const conversationId = {{ $conversation->id }};
        const currentUserId = {{ auth()->id() }};
        const messageForm = document.getElementById('message-form');
        const messageInput = document.getElementById('body');

        if (messageForm && messageInput) {
            messageForm.addEventListener('submit', async (event) => {
                event.preventDefault();

                const body = messageInput.value.trim();

                if (!body) {
                    return;
                }

                const submitButton = messageForm.querySelector(
                    'button[type="submit"]'
                );

                if (submitButton) {
                    submitButton.disabled = true;
                }

                try {
                    const response = await fetch(messageForm.action, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector(
                                'meta[name="csrf-token"]'
                            ).content,
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            body: body,
                        }),
                    });

                    if (!response.ok) {
                        console.error(
                            'message send failed:',
                            response.status,
                            await response.text()
                        );

                        return;
                    }

                    messageInput.value = '';
                } catch (error) {
                    console.error('message send failed:', error);
                } finally {
                    if (submitButton) {
                        submitButton.disabled = false;
                    }
                }
            });
        }
        const chatBottom = document.getElementById('chat-bottom');

        if (chatBottom) {
            chatBottom.scrollIntoView();
        }

        function appendMessageBody(container, text) {
            const urlPattern = /(https?:\/\/[^\s]+)/g;

            let lastIndex = 0;

            for (const match of text.matchAll(urlPattern)) {
                const url = match[0];
                const index = match.index;

                if (index > lastIndex) {
                    container.appendChild(
                        document.createTextNode(
                            text.slice(lastIndex, index)
                        )
                    );
                }

                const link = document.createElement('a');

                link.href = url;
                link.textContent = url;
                link.target = '_blank';
                link.rel = 'noopener noreferrer';

                container.appendChild(link);

                lastIndex = index + url.length;
            }

            if (lastIndex < text.length) {
                container.appendChild(
                    document.createTextNode(
                        text.slice(lastIndex)
                    )
                );
            }
        }

        const channel = window.Echo.private(
            `conversation.${conversationId}`
        );

        channel.listen('.message.sent', (event) => {
            const messageList = document.getElementById('message-list');
            const emptyMessage = document.getElementById('empty-message');

            if (emptyMessage) {
                emptyMessage.remove();
            }

            const wrapper = document.createElement('div');

            wrapper.style.padding = '12px';
            wrapper.style.borderRadius = '10px';
            wrapper.style.background = '#f8fafc';

            const header = document.createElement('div');

            header.style.display = 'flex';
            header.style.alignItems = 'center';
            header.style.gap = '10px';
            header.style.marginBottom = '8px';

            if (event.user.photo_url) {
                const image = document.createElement('img');

                image.src = event.user.photo_url;
                image.alt = event.user.name;

                image.style.width = '40px';
                image.style.height = '40px';
                image.style.objectFit = 'cover';
                image.style.borderRadius = '50%';

                header.appendChild(image);
            }

            const name = document.createElement('strong');
            name.textContent = event.user.name;

            const time = document.createElement('span');
            time.textContent = event.created_at;
            time.style.fontSize = '0.85rem';

            header.appendChild(name);
            header.appendChild(time);

            const body = document.createElement('div');
            body.style.whiteSpace = 'pre-wrap';

            appendMessageBody(body, event.body);

            wrapper.appendChild(header);
            wrapper.appendChild(body);

            messageList.appendChild(wrapper);
            if (event.user.id !== currentUserId) {
                fetch(`/messages/${event.id}/read`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector(
                                'meta[name="csrf-token"]'
                            ).content,
                            'Accept': 'application/json',
                        },
                    })
                    .then(async (response) => {
                        console.log('read response status:', response.status);
                        console.log('read response body:', await response.text());
                    })
                    .catch((error) => {
                        console.error('read request failed:', error);
                    });
            }
        });

        channel.listen('.message.read-updated', (event) => {
            console.log('read-updated received:', event);

            const readCount = document.getElementById(
                `read-count-${event.message_id}`
            );

            console.log('read count element:', readCount);

            if (readCount) {
                readCount.textContent = `既読 ${event.read_count}`;
            }
        });
    </script>

@endsection
