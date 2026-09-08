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


    <div id="chat-bottom"></div>

    <div class="card">
        <h2>メッセージを送る</h2>

        <form id="message-form" method="POST" action="{{ route('direct-chat.messages.store', $conversation) }}"
            enctype="multipart/form-data">
            @csrf

            <textarea name="body" rows="4" style="width: 100%;">{{ old('body') }}</textarea>

            @error('body')
                <p class="error">
                    {{ $message }}
                </p>
            @enderror

            <div style="margin-top: 12px;">
                <label for="image">
                    写真
                </label>

                <input type="file" id="image" name="image" accept="image/jpeg,image/png,image/webp">

                @error('image')
                    <p class="error">
                        {{ $message }}
                    </p>
                @enderror
            </div>

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
        const imageInput = messageForm?.querySelector('input[name="image"]');
        if (messageForm && messageInput) {
            messageForm.addEventListener('submit', async (event) => {
                event.preventDefault();

                const body = messageInput.value.trim();
                const image = imageInput?.files?.[0] ?? null;

                console.log('body:', body);
                console.log('image:', image);

                if (!body && !image) {
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

            const messageList = document.getElementById('message-list');
            const emptyMessage = document.getElementById('empty-message');

            if (!messageList) {
                return;
            }

            if (emptyMessage) {
                emptyMessage.remove();
            }

            const wrapper = document.createElement('div');

            wrapper.style.padding = '12px';
            wrapper.style.marginBottom = '12px';
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

            header.appendChild(name);

            wrapper.appendChild(header);

            if (event.body) {
                const body = document.createElement('div');

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
                const readStatus = document.createElement('div');

                readStatus.id = `read-count-${event.id}`;
                readStatus.style.marginTop = '6px';
                readStatus.style.fontSize = '0.8rem';
                readStatus.style.color = '#6b7280';
                readStatus.style.textAlign = 'right';

                wrapper.appendChild(readStatus);
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


            if (readCount && event.read_count > 0) {
                readCount.textContent = '既読';
            }
        });
    </script>
@endsection
