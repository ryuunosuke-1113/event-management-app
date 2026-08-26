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

        <form method="POST" action="{{ route('admin.events.update', $event) }}">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label for="title">イベント名</label>

                <input type="text" id="title" name="title" value="{{ old('title', $event->title) }}" required>
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

                <input type="number" id="price" name="price" min="0" value="{{ old('price', $event->price) }}"
                    required>
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

@endsection
