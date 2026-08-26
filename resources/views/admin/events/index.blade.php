@extends('layouts.app')

@section('title', 'イベント管理')

@section('content')

    <h1>イベント管理</h1>

    <div style="margin-bottom: 24px;">
        <x-link-button href="{{ route('admin.events.create') }}">
            新しいイベントを作成
        </x-link-button>
    </div>

    @if ($events->isEmpty())

        <div class="card">
            <p>まだイベントはありません。</p>
        </div>
    @else
        @foreach ($events as $event)
            <div class="card">

                <h2>
                    <a href="{{ route('admin.events.show', $event) }}">
                        {{ $event->title }}
                    </a>
                </h2>

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

                <div style="display: flex; gap: 10px; flex-wrap: wrap;">

                    <x-link-button href="{{ route('admin.events.show', $event) }}" variant="secondary">
                        詳細を見る
                    </x-link-button>

                    <x-link-button href="{{ route('admin.events.edit', $event) }}">
                        編集する
                    </x-link-button>

                </div>

            </div>
        @endforeach

    @endif

@endsection
