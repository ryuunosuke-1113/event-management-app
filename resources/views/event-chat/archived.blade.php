@extends('layouts.app')

@section('content')

    <div class="card">
        <h1>アーカイブ済みチャット</h1>

        @if ($memberships->isEmpty())
            <p>アーカイブ済みのチャットはありません。</p>
        @else
            @foreach ($memberships as $membership)
                @php
                    $conversation = $membership->conversation;
                    $event = $conversation->event;
                @endphp

                <div style="
        padding: 16px 0;
        border-bottom: 1px solid #e5e7eb;
    ">
                    <h2>
                        {{ $event->title }}
                    </h2>

                    <p>
                        アーカイブ日時：
                        {{ $membership->archived_at->format('Y年m月d日 H:i') }}
                    </p>

                    <form method="POST" action="{{ route('event-chat.restore', $event) }}" style="margin-top: 12px;">
                        @csrf

                        <x-button type="submit" variant="secondary">
                            再表示する
                        </x-button>
                    </form>
                </div>
            @endforeach
        @endif
    </div>

@endsection
