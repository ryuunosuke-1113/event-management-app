@extends('layouts.app')

@section('title', '主催イベント管理')

@section('content')

    <h1>主催イベント管理</h1>

    <div style="margin-bottom: 24px;">
        <x-link-button href="{{ route('organizer.events.archived') }}" variant="secondary">
            アーカイブイベントを見る
        </x-link-button>
    </div>
    @if (auth()->check() && in_array(auth()->user()->account_status, ['creation_suspended', 'full_suspended'], true))
        <p style="color: #dc3545; font-weight: bold;">
            現在、運営によりイベント作成が制限されています。
        </p>
        <x-link-button href="{{ route('moderation.status') }}" variant="secondary">
            制限内容を確認する
        </x-link-button>
    @endif
    <div style="margin-bottom: 24px;">
        @if (!in_array(auth()->user()->account_status, ['creation_suspended', 'full_suspended'], true))
            <x-link-button href="{{ route('organizer.events.create') }}">
                新しいイベントを作成
            </x-link-button>
        @endif
    </div>

    <div style="margin-bottom: 24px;">
        @if ($stripeConnectStatus === 'active')
            <p>✅ Stripe連携済み</p>
        @elseif ($stripeConnectStatus === 'incomplete')
            <p>⚠️ Stripeの設定がまだ完了していません。</p>

            <x-link-button href="{{ route('stripe.connect.start') }}">
                Stripe設定を続ける
            </x-link-button>
        @elseif ($stripeConnectStatus === 'error')
            <p>⚠️ Stripeの連携状態を確認できませんでした。</p>

            <x-link-button href="{{ route('stripe.connect.start') }}">
                Stripe設定を確認する
            </x-link-button>
        @else
            <x-link-button href="{{ route('stripe.connect.guide') }}">
                Stripeを連携する
            </x-link-button>
        @endif
    </div>

    @if ($events->isEmpty())

        <div class="card">
            <p>まだ主催しているイベントはありません。</p>
        </div>
    @else
        @foreach ($events as $event)
            <div class="card">

                <h2>
                    <a href="{{ route('organizer.events.show', $event) }}">
                        {{ $event->title }}
                    </a>
                </h2>

                @if ($event->images->isNotEmpty())
                    @php
                        $mainImage = $event->images->sortBy('sort_order')->first();
                    @endphp

                    <img src="{{ asset('storage/' . $mainImage->image_path) }}" alt="{{ $event->title }}の画像"
                        class="event-list-image">
                @endif

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

                    <x-link-button href="{{ route('organizer.events.show', $event) }}" variant="secondary">
                        詳細を見る
                    </x-link-button>

                    <x-link-button href="{{ route('organizer.events.edit', $event) }}">
                        編集する
                    </x-link-button>

                </div>

            </div>
        @endforeach

    @endif

@endsection
