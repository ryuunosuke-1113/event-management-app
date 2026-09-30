@extends('layouts.app')

@section('title', $user->name . 'さんが参加したイベント')

@section('content')
    <div class="container">
        <h1>{{ $user->name }}さんが過去に参加したイベント</h1>

        <p>
            <a href="{{ route('profile.show', $user) }}">
                ← プロフィールに戻る
            </a>
        </p>

        @if ($events->isEmpty())
            <div class="card">
                <p>過去に参加したイベントはありません。</p>
            </div>
        @else
            @foreach ($events as $event)
                <div class="card" style="margin-bottom: 16px;">
                    <h2>
                        <a href="{{ route('events.show', $event) }}">
                            {{ $event->title }}
                        </a>
                    </h2>

                    <p>
                        開催日時：
                        {{ $event->event_date->format('Y/m/d H:i') }}
                    </p>

                    <p>
                        場所：{{ $event->place }}
                    </p>
                </div>
            @endforeach
        @endif
    </div>
@endsection
