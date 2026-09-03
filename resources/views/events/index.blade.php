@extends('layouts.app')

@section('title', 'イベント一覧')

@section('content')

    <h1>イベント一覧</h1>

    @if ($events->isEmpty())

        <div class="card">
            <p>現在募集中のイベントはありません。</p>
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
