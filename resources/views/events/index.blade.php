@extends('layouts.app')

@section('title', 'イベント一覧')

@section('content')

    <h1>イベント一覧</h1>
    <form method="GET" action="{{ route('events.index') }}"
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
            <a href="{{ route('events.index') }}" style="margin-left: 12px;">
                検索条件をクリア
            </a>
        @endif
    </form>

    <div id="install-app-guide"
        style="
        display: none;
        margin: 16px 0 24px;
        padding: 16px;
        background: #e0f2fe;
        color: #0c4a6e;
        border-radius: 8px;
        font-size: 14px;
        line-height: 1.7;
    ">
        <strong>📱 このサイトをアプリのように使えます</strong>

        <p style="margin: 8px 0;">
            ホーム画面に追加すると、次回からアイコンをタップするだけで開けます。
        </p>

        <button type="button" id="install-app-button" style="display: none;">
            ホーム画面に追加
        </button>

        <button type="button" id="show-ios-install-guide" style="display: none;">
            iPhone / iPadでの追加方法
        </button>

        <div id="ios-install-guide" style="
            display: none;
            margin-top: 12px;
        ">
            Safariの「共有」ボタンを押して、
            「ホーム画面に追加」を選んでください。
        </div>
    </div>


    @if ($events->isEmpty())

        <div class="card">
            @if (request()->filled('keyword') || request()->filled('event_date'))
                <p>条件に一致するイベントはありません。</p>
            @else
                <p>現在募集中のイベントはありません。</p>
            @endif
        </div>
    @else
        @foreach ($events as $event)
            <div class="card">

                <h2>
                    <a href="{{ route('events.show', $event) }}">
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
                    開催日時：
                    {{ $event->event_date->format('Y/m/d H:i') }}
                </p>

                <p>
                    場所：
                    {{ $event->place }}
                </p>

                <p>
                    参加費：
                    {{ number_format($event->price) }}円
                </p>

                <p>
                    定員：
                    {{ $event->capacity }}人
                </p>

            </div>
        @endforeach

    @endif

@endsection
