@extends('layouts.app')

@section('content')

    <div class="card">
        <div
            style="
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 20px;
    ">
            <h1 style="margin: 0;">
                アーカイブ済みチャット
            </h1>

            <x-link-button href="{{ route('chats.index') }}" variant="secondary">
                チャット一覧へ戻る
            </x-link-button>
        </div>
        @if ($memberships->isEmpty())
            <p>アーカイブ済みのチャットはありません。</p>
        @else
            @foreach ($memberships as $membership)
                @php
                    $conversation = $membership->conversation;
                @endphp

                <div
                    style="
                        padding: 16px 0;
                        border-bottom: 1px solid #e5e7eb;
                    ">
                    @if ($conversation->type === 'event')
                        <h2>
                            {{ $conversation->event->title }}
                        </h2>

                        <p>イベントチャット</p>
                    @elseif ($conversation->type === 'direct')
                        @php
                            $otherMember = $conversation->members->firstWhere('user_id', '!=', auth()->id());
                        @endphp

                        <h2>
                            {{ $otherMember->user->name }} さん
                        </h2>

                        <p>ダイレクトチャット</p>
                    @endif

                    <p>
                        アーカイブ日時：
                        {{ $membership->archived_at->format('Y年m月d日 H:i') }}
                    </p>
                    <form method="POST" action="{{ route('chats.restore', $conversation) }}" style="margin-top: 12px;">
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
