@extends('layouts.app')

@section('title', 'イベント編集')

@section('content')

    <h1>イベント編集</h1>

    <div class="card">

        @if ($errors->any())
            <div class="error">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif


        {{-- 既存画像はイベント更新フォームの外に置く --}}
        @if ($event->images->isNotEmpty())
            <div style="margin-bottom: 24px;">
                <h2>現在のイベント画像</h2>

                <div
                    style="
                    display: flex;
                    flex-wrap: wrap;
                    gap: 16px;
                    align-items: flex-start;
                ">
                    @foreach ($event->images as $image)
                        <div
                            style="
                            width: 220px;
                            display: flex;
                            flex-direction: column;
                            gap: 8px;
                        ">
                            @if (!$loop->first)
                                <form method="POST"
                                    action="{{ route('admin.events.images.make-primary', [$event, $image]) }}"
                                    style="margin: 0;">
                                    @csrf
                                    @method('PATCH')

                                    <x-button type="submit" variant="secondary">
                                        1枚目にする
                                    </x-button>
                                </form>
                            @else
                                <div style="font-size: 13px; font-weight: bold;">
                                    現在の1枚目
                                </div>
                            @endif
                            <img src="{{ asset('storage/' . $image->image_path) }}" alt="{{ $event->title }}の画像"
                                style="
                                width: 220px;
                                height: 150px;
                                object-fit: cover;
                                border-radius: 10px;
                            ">

                            <form method="POST" action="{{ route('admin.events.images.destroy', [$event, $image]) }}"
                                onsubmit="return confirm('この画像を削除しますか？')" style="margin: 0;">
                                @csrf
                                @method('DELETE')

                                <x-button type="submit" variant="danger">
                                    この画像を削除
                                </x-button>
                            </form>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif


        {{-- イベント更新フォーム --}}
        <form method="POST" action="{{ route('admin.events.update', $event) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label for="title">イベント名</label>

                <input type="text" id="title" name="title" value="{{ old('title', $event->title) }}" required>
            </div>


            <div class="form-group">
                <label>
                    イベント画像を追加
                </label>

                <div id="image-inputs">
                    <div class="image-input-row">
                        <input type="file" name="images[]" accept="image/*" multiple>
                    </div>
                </div>
                <div id="image-preview"
                    style="
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        margin-top: 16px;
    ">
                </div>

                <button type="button" id="add-image-input" style="margin-top: 10px;">
                    ＋ 画像を追加
                </button>

                <p style="font-size: 0.9rem; color: #6b7280;">
                    複数枚まとめて選択することも、1枚ずつ追加することもできます。
                </p>

                @error('images')
                    <p>{{ $message }}</p>
                @enderror

                @error('images.*')
                    <p>{{ $message }}</p>
                @enderror
            </div>


            <div class="form-group">
                <label for="description">イベント説明</label>

                <textarea id="description" name="description" rows="6" required>{{ old('description', $event->description) }}</textarea>
            </div>


            <div class="form-group">
                <label for="event_date">開催日時</label>

                <input type="datetime-local" id="event_date" name="event_date"
                    value="{{ old('event_date', $event->event_date->format('Y-m-d\TH:i')) }}" required>
            </div>


            <div class="form-group">
                <label for="place">開催場所</label>

                <input type="text" id="place" name="place" value="{{ old('place', $event->place) }}" required>
            </div>


            <div class="form-group">
                <label for="capacity">定員</label>

                <input type="number" id="capacity" name="capacity" min="1"
                    value="{{ old('capacity', $event->capacity) }}" required>
            </div>


            <div class="form-group">
                <label for="price">参加費（円）</label>

                <input type="number" id="price" name="price" min="0"
                    value="{{ old('price', $event->price) }}" required>
            </div>


            <div class="form-group">
                <label for="status">状態</label>

                <select id="status" name="status" required>

                    <option value="draft" @selected(old('status', $event->status) === 'draft')>
                        下書き
                    </option>

                    <option value="published" @selected(old('status', $event->status) === 'published')>
                        公開中
                    </option>

                    <option value="closed" @selected(old('status', $event->status) === 'closed')>
                        募集終了
                    </option>

                    <option value="finished" @selected(old('status', $event->status) === 'finished')>
                        開催終了
                    </option>

                    <option value="cancelled" @selected(old('status', $event->status) === 'cancelled')>
                        イベント中止
                    </option>

                </select>
            </div>


            <div class="form-group">
                <label for="chat_url">参加者用チャットURL</label>

                <input type="url" id="chat_url" name="chat_url" value="{{ old('chat_url', $event->chat_url) }}">
            </div>


            <div class="form-group">
                <label for="cancel_policy">キャンセルポリシー</label>

                <textarea id="cancel_policy" name="cancel_policy" rows="4">{{ old('cancel_policy', $event->cancel_policy) }}</textarea>
            </div>


            <div style="display: flex; gap: 10px; flex-wrap: wrap;">

                <x-button type="submit">
                    更新する
                </x-button>

                <x-link-button href="{{ route('admin.events.show', $event) }}" variant="secondary">
                    詳細へ戻る
                </x-link-button>

            </div>

        </form>

    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const container = document.getElementById('image-inputs');
            const addButton = document.getElementById('add-image-input');
            const previewContainer = document.getElementById('image-preview');

            function updatePreview() {
                previewContainer.innerHTML = '';

                const inputs = container.querySelectorAll('input[type="file"]');

                let imageNumber = 1;

                inputs.forEach((input) => {
                    Array.from(input.files).forEach((file) => {
                        if (!file.type.startsWith('image/')) {
                            return;
                        }

                        const previewItem = document.createElement('div');

                        previewItem.style.display = 'flex';
                        previewItem.style.flexDirection = 'column';
                        previewItem.style.gap = '6px';

                        const img = document.createElement('img');

                        const imageUrl = URL.createObjectURL(file);

                        img.src = imageUrl;
                        img.style.width = '180px';
                        img.style.height = '125px';
                        img.style.objectFit = 'cover';
                        img.style.borderRadius = '8px';

                        img.onload = function() {
                            URL.revokeObjectURL(imageUrl);
                        };

                        const label = document.createElement('div');

                        label.textContent = `追加画像 ${imageNumber}`;

                        label.style.fontSize = '13px';
                        label.style.fontWeight = 'bold';

                        previewItem.appendChild(img);
                        previewItem.appendChild(label);

                        previewContainer.appendChild(previewItem);

                        imageNumber++;
                    });
                });
            }

            container.addEventListener('change', function(event) {
                if (event.target.matches('input[type="file"]')) {
                    updatePreview();
                }
            });

            addButton.addEventListener('click', function() {
                const row = document.createElement('div');

                row.className = 'image-input-row';
                row.style.marginTop = '10px';

                const input = document.createElement('input');

                input.type = 'file';
                input.name = 'images[]';
                input.accept = 'image/*';

                const removeButton = document.createElement('button');

                removeButton.type = 'button';
                removeButton.textContent = '削除';
                removeButton.style.marginLeft = '8px';

                removeButton.addEventListener('click', function() {
                    row.remove();
                    updatePreview();
                });

                row.appendChild(input);
                row.appendChild(removeButton);

                container.appendChild(row);
            });
        });
    </script>
@endsection
