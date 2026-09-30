@extends('layouts.app')

@section('title', '過去のイベント閲覧')

@section('content')

    <h1>過去のイベント閲覧</h1>

    <p>
        過去10日以内に開催されたイベントを確認できます。
    </p>

    <form method="GET" action="{{ route('admin.events.index') }}"
        style="
            margin: 16px 0 24px;
            padding: 16px;
            border: 1px solid #ddd;
            border-radius: 8px;
        ">
        <div style="margin-bottom: 12px;">
            <label for="keyword">
                イベント名
            </label>

            <br>

            <input type="text" id="keyword" name="keyword" value="{{ request('keyword') }}" placeholder="例：将棋">
        </div>

        <div style="margin-bottom: 12px;">
            <label for="event_date">
                開催日
            </label>

            <br>

            <input type="date" id="event_date" name="event_date" value="{{ request('event_date') }}">
        </div>

        <x-button type="submit" variant="primary">
            検索
        </x-button>

        @if (request()->filled('keyword') || request()->filled('event_date'))
            <a href="{{ route('admin.events.index') }}" style="margin-left: 12px;">
                検索条件をクリア
            </a>
        @endif
    </form>

    @if ($events->isEmpty())

        <div class="card">
            @if (request()->filled('keyword') || request()->filled('event_date'))
                <p>条件に一致するイベントはありません。</p>
            @else
                <p>過去10日以内のイベントはありません。</p>
            @endif
        </div>
    @else
        @foreach ($events as $event)
            <div class="card">

                <h2>
                    <a href="{{ route('admin.events.show', $event) }}">
                        {{ $event->title }}
                    </a>
                </h2>

                @if ($event->images->isNotEmpty())
                    @php
                        $mainImage = $event->images->first();
                    @endphp

                    <img src="{{ asset('storage/' . $mainImage->image_path) }}" alt="{{ $event->title }}の画像"
                        class="event-list-image">
                @endif

                <p>
                    主催者：
                    {{ $event->organizer?->name ?? '不明' }}
                </p>

                <p>
                    開催日時：
                    {{ $event->event_date->format('Y/m/d H:i') }}
                </p>

                <p>
                    開催場所：
                    {{ $event->place }}
                </p>

                <p>
                    定員：
                    {{ $event->capacity }}人
                </p>

                <p>
                    参加費：
                    {{ number_format($event->price) }}円
                </p>

                <p>
                    状態：
                    <x-status-badge :status="$event->status" :label="$event->status_label" />
                </p>

                <x-link-button href="{{ route('admin.events.show', $event) }}" variant="secondary">
                    詳細を見る
                </x-link-button>

            </div>
        @endforeach

    @endif

@endsection
