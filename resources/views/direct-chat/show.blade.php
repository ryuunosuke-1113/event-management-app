@extends('layouts.app')

@section('content')

    @php
        $otherMember = $conversation->members->firstWhere('user_id', '!=', auth()->id());
    @endphp

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
            {{ $otherMember->user->name }} さんとのチャット
        </strong>
    </div>


    <div class="card">
        <h2>メッセージ</h2>

        <div id="message-list">
            @if ($conversation->messages->isEmpty())
                <p id="empty-message">
                    まだメッセージはありません。
                </p>
            @else
                @foreach ($conversation->messages as $message)
                    <div
                        style="
                        padding: 12px;
                        margin-bottom: 12px;
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
                            @else
                                <div
                                    style="
                width: 40px;
                height: 40px;
                border-radius: 50%;
                background: #e5e7eb;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 0.75rem;
            ">
                                    写真なし
                                </div>
                            @endif

                            <strong>{{ $message->user->name }}</strong>
                        </div>

                        <div style="margin-top: 6px;">
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
                                @if ($message->reads->count() > 0)
                                    既読
                                @endif
                            </div>
                        @endif
                    </div>
                @endforeach
            @endif
        </div>

        <div id="chat-bottom"></div>
        <div class="card">
            <h2>メッセージを送る</h2>

            <form id="message-form" method="POST" action="{{ route('direct-chat.messages.store', $conversation) }}"> @csrf

                <textarea name="body" rows="4" required style="width: 100%;">{{ old('body') }}</textarea>

                @error('body')
                    <p class="error">
                        {{ $message }}
                    </p>
                @enderror

                <div style="margin-top: 12px;">
                    <x-button type="submit" variant="primary">
                        送信
                    </x-button>
                </div>
            </form>
        </div>
        <script type="module">
            const conversationId = {{ $conversation->id }};
            const currentUserId = {{ auth()->id() }};
            const chatBottom = document.getElementById('chat-bottom');

            const messageForm = document.getElementById('message-form');
            const messageInput = messageForm?.querySelector('textarea[name="body"]');

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
                const isOwnMessage =
                    Number(event.user.id) === Number(currentUserId);

                const messageList = document.getElementById('message-list');
                const emptyMessage = document.getElementById('empty-message');

                // 途中のメッセージDOM作成処理はそのまま

                wrapper.appendChild(header);
                wrapper.appendChild(body);

                if (isOwnMessage) {
                    const readStatus = document.createElement('div');

                    readStatus.id = `read-count-${event.id}`;
                    readStatus.style.marginTop = '6px';
                    readStatus.style.fontSize = '0.8rem';
                    readStatus.style.color = '#6b7280';
                    readStatus.style.textAlign = 'right';

                    wrapper.appendChild(readStatus);
                }

                messageList.appendChild(wrapper);

                if (!isOwnMessage) {
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
                            console.log(
                                'read response body:',
                                await response.text()
                            );
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

                if (readCount && event.read_count > 0) {
                    readCount.textContent = '既読';
                }
            });
        </script>
    @endsection
