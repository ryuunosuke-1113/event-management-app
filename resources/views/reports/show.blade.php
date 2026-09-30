@extends('layouts.app')

@section('title', '通報内容')

@section('content')

    <h1>通報内容</h1>

    <div class="card" style="margin-bottom: 24px;">

        <p>
            通報対象：
            {{ $userReport->reportedUser->name }}
        </p>

        <p>
            通報理由：
            {{ $userReport->reason_label }}
        </p>

        <p>
            対応状況：
            {{ $userReport->status_label }}
        </p>

        <p>
            通報日時：
            {{ $userReport->created_at->format('Y/m/d H:i') }}
        </p>

        <hr>

        <h2>通報内容</h2>

        <p style="white-space: pre-wrap;">
            {{ $userReport->details }}
        </p>

    </div>

    <h2>管理者とのやり取り</h2>

    <div style="margin-bottom: 24px;">

        @forelse ($userReport->messages as $message)
            <div
                style="
                    margin-bottom: 12px;
                    padding: 12px;
                    border: 1px solid #ddd;
                    border-radius: 8px;
                ">
                <div style="margin-bottom: 6px;">

                    <strong>
                        @if ($message->user_id === auth()->id())
                            あなた
                        @else
                            管理者
                        @endif
                    </strong>

                    <span style="font-size: 0.9em; color: #666;">
                        {{ $message->created_at->format('Y/m/d H:i') }}
                    </span>

                    @if ($message->user_id === auth()->id())
                        <span style="font-size: 0.85em; color: #666; margin-left: 8px;">
                            @if ($message->read_at)
                                既読
                            @else
                                未読
                            @endif
                        </span>
                    @endif

                </div>

                <div style="white-space: pre-wrap;">
                    {{ $message->body }}
                </div>
                @if ($message->attachments->isNotEmpty())
                    <div
                        style="
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 10px;
        ">
                        @foreach ($message->attachments as $attachment)
                            <a href="{{ route('report-attachments.show', $attachment) }}" target="_blank"
                                rel="noopener noreferrer">
                                <img src="{{ route('report-attachments.show', $attachment) }}"
                                    alt="{{ $attachment->original_name }}"
                                    style="
                        width: 140px;
                        height: 140px;
                        object-fit: cover;
                        border-radius: 8px;
                        border: 1px solid #ddd;
                    ">
                            </a>
                        @endforeach
                    </div>
                @endif

            </div>

        @empty

            <p>
                まだ管理者とのメッセージはありません。
            </p>
        @endforelse

    </div>
    <div id="report-chat-bottom"></div>

    <form method="POST" action="{{ route('reports.messages.store', $userReport) }}" enctype="multipart/form-data">
        @csrf

        <div style="margin-bottom: 12px;">

            <label for="body">
                管理者へのメッセージ
            </label>

            <br>

            <textarea id="body" name="body" rows="5" maxlength="3000" required style="width: 100%; max-width: 700px;">{{ old('body') }}</textarea>

            <div style="margin-bottom: 12px;">
                <label for="images">
                    画像を添付（最大3枚・1枚5MBまで）
                </label>

                <br>

                <input type="file" id="images" name="images[]" accept="image/jpeg,image/png,image/webp" multiple>

                @error('images')
                    <div>{{ $message }}</div>
                @enderror

                @error('images.*')
                    <div>{{ $message }}</div>
                @enderror

                <div id="image-preview"
                    style="
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 12px;
        ">
                </div>
            </div>
            @error('body')
                <div>{{ $message }}</div>
            @enderror

        </div>

        <x-button type="submit" variant="primary">
            送信
        </x-button>

    </form>
    <script>
        document.addEventListener('DOMContentLoaded', function() {

            const input = document.getElementById('images');
            const preview = document.getElementById('image-preview');

            if (!input || !preview) {
                return;
            }

            input.addEventListener('change', function() {

                preview.innerHTML = '';

                const files = Array.from(this.files);

                if (files.length > 3) {
                    alert('画像は最大3枚まで選択できます。');

                    this.value = '';
                    return;
                }

                files.forEach(function(file) {

                    if (!file.type.startsWith('image/')) {
                        return;
                    }

                    const reader = new FileReader();

                    reader.onload = function(event) {

                        const img = document.createElement('img');

                        img.src = event.target.result;

                        img.style.width = '120px';
                        img.style.height = '120px';
                        img.style.objectFit = 'cover';
                        img.style.borderRadius = '8px';
                        img.style.border = '1px solid #ddd';

                        preview.appendChild(img);
                    };

                    reader.readAsDataURL(file);
                });
            });
        });
    </script>
    <script>
        window.addEventListener('load', function() {
            const bottom = document.getElementById('report-chat-bottom');

            if (bottom) {
                bottom.scrollIntoView({
                    behavior: 'auto',
                    block: 'end'
                });
            }
        });
    </script>
@endsection
