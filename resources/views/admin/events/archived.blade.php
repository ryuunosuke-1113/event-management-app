@extends('layouts.app')

@section('title', 'アーカイブイベント一覧')

@section('content')

    <h1>アーカイブイベント一覧</h1>

    <div style="margin-bottom: 24px;">
        <x-link-button href="{{ route('admin.events.index') }}" variant="secondary">
            イベント管理へ戻る
        </x-link-button>
    </div>

    @if ($events->isEmpty())

        <p>アーカイブされたイベントはありません。</p>
    @else
        @foreach ($events as $event)
            <div class="card">

                <h2>{{ $event->title }}</h2>

                <p>
                    開催日時：
                    {{ $event->event_date->format('Y/m/d H:i') }}
                </p>

                <p>
                    開催場所：
                    {{ $event->place }}
                </p>

                <p>
                    状態：
                    <x-status-badge :status="$event->status" :label="$event->status_label" />
                </p>

                <p>
                    アーカイブ日時：
                    {{ $event->archived_at->format('Y/m/d H:i') }}
                </p>

                <x-link-button href="{{ route('admin.events.show', $event) }}" variant="primary">
                    詳細を見る
                </x-link-button>
                <form method="POST" action="{{ route('admin.events.restore-archive', $event) }}"
                    onsubmit="return confirm('このイベントのアーカイブを解除しますか？')" style="margin-top: 12px;">
                    @csrf
                    @method('PATCH')

                    <x-button type="submit" variant="secondary">
                        アーカイブを解除する
                    </x-button>
                </form>

            </div>
        @endforeach

    @endif

@endsection
