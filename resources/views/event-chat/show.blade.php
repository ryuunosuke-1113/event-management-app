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

                        @if ($message->body)
                            <div style="white-space: pre-wrap;">
                                {!! $message->body_html !!}
                            </div>
                        @endif

                        @if ($message->image_path)
                            <div style="margin-top: 10px;">
                                <img src="{{ asset('storage/' . $message->image_path) }}" alt="チャット画像"
                                    style="
                display: block;
                max-width: 100%;
                width: 420px;
                max-height: 500px;
                object-fit: contain;
                border-radius: 10px;
            ">
                            </div>
                        @endif
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

        <form id="message-form" method="POST" action="{{ route('event-chat.messages.store', $event) }}"
            enctype="multipart/form-data">
            @csrf

            <div class="form-group">
                <label for="body">
                    メッセージ
                </label>

                <textarea id="body" name="body" rows="5">{{ old('body') }}</textarea>

                @error('body')
                    <p>{{ $message }}</p>
                @enderror
            </div>

            <div class="form-group">
                <label for="image">
                    写真
                </label>

                <input type="file" id="image" name="image" accept="image/jpeg,image/png,image/webp">
                <div id="image-preview" style="margin-top: 10px;"></div>

                @error('image')
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
        const messageInput = messageForm?.querySelector('textarea[name="body"]');
        const imageInput = messageForm?.querySelector('input[name="image"]');

        const imagePreview = document.getElementById('image-preview');

        if (imageInput && imagePreview) {
            imageInput.addEventListener('change', () => {
                imagePreview.innerHTML = '';

                const file = imageInput.files?.[0];

                if (!file) {
                    return;
                }

                const imageUrl = URL.createObjectURL(file);

                const previewImage = document.createElement('img');

                previewImage.src = imageUrl;
                previewImage.alt = '送信前プレビュー';

                previewImage.style.display = 'block';
                previewImage.style.maxWidth = '100%';
                previewImage.style.width = '300px';
                previewImage.style.maxHeight = '300px';
                previewImage.style.objectFit = 'contain';
                previewImage.style.borderRadius = '10px';

                previewImage.onload = () => {
                    URL.revokeObjectURL(imageUrl);
                };

                imagePreview.appendChild(previewImage);
            });
        }

        if (messageForm && messageInput) {
            messageForm.addEventListener('submit', async (event) => {
                event.preventDefault();

                const body = messageInput.value.trim();
                const hasImage =
                    imageInput &&
                    imageInput.files &&
                    imageInput.files.length > 0;

                console.log('body:', body);
                console.log('imageInput:', imageInput);
                console.log('files:', imageInput?.files);
                console.log('hasImage:', hasImage);

                if (!body && !hasImage) {
                    console.log('本文も画像もないので送信中止');
                    return;
                }
                const submitButton = messageForm.querySelector(
                    'button[type="submit"]'
                );

                if (submitButton) {
                    submitButton.disabled = true;
                }

                try {
                    const formData = new FormData(messageForm);
                    const response = await fetch(messageForm.action, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector(
                                'meta[name="csrf-token"]'
                            ).content,
                            'Accept': 'application/json',
                        },
                        body: formData,
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

                    if (imageInput) {
                        imageInput.value = '';
                    }
                    if (imagePreview) {
                        imagePreview.innerHTML = '';
                    }
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

            wrapper.appendChild(header);

            if (event.body) {
                const body = document.createElement('div');

                body.style.whiteSpace = 'pre-wrap';

                appendMessageBody(body, event.body);

                wrapper.appendChild(body);
            }

            if (event.image_url) {
                const chatImageContainer = document.createElement('div');

                chatImageContainer.style.marginTop = '10px';

                const chatImage = document.createElement('img');

                chatImage.src = event.image_url;
                chatImage.alt = 'チャット画像';

                chatImage.style.display = 'block';
                chatImage.style.maxWidth = '100%';
                chatImage.style.width = '420px';
                chatImage.style.maxHeight = '500px';
                chatImage.style.objectFit = 'contain';
                chatImage.style.borderRadius = '10px';

                chatImageContainer.appendChild(chatImage);

                wrapper.appendChild(chatImageContainer);
            }
            if (event.user.id === currentUserId) {
                const readCount = document.createElement('div');

                readCount.id = `read-count-${event.id}`;
                readCount.style.marginTop = '6px';
                readCount.style.fontSize = '0.8rem';
                readCount.style.color = '#6b7280';
                readCount.style.textAlign = 'right';
                readCount.textContent = '既読 0';

                wrapper.appendChild(readCount);
            }

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
                    .catch((error) => {
                        console.error('read request failed:', error);
                    });
            }
        });

        channel.listen('.message.read-updated', (event) => {

            const readCount = document.getElementById(
                `read-count-${event.message_id}`
            );


            if (readCount) {
                readCount.textContent = `既読 ${event.read_count}`;
            }
        });
    </script>

@endsection
