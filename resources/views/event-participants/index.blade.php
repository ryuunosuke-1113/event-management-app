@extends('layouts.app')

@section('title', '自分の参加予定')

@section('content')

    <h1>自分の参加予定</h1>

    <div style="margin-bottom: 24px;">
        <x-link-button href="{{ route('event-participants.cancelled') }}" variant="secondary">
            キャンセルしたイベントを見る
        </x-link-button>
    </div>

    {{-- これから参加するイベント --}}
    <h2 style="margin-top: 24px;">これから参加するイベント</h2>

    @if ($upcomingParticipants->isEmpty())
        <p>これから参加するイベントはありません。</p>
    @else
        @foreach ($upcomingParticipants as $participant)
            <div class="card">

                <h2>
                    <a href="{{ route('events.show', $participant->event) }}">
                        {{ $participant->event->title }}
                    </a>
                </h2>

                <p>
                    開催日時：
                    {{ $participant->event->event_date->format('Y/m/d H:i') }}
                </p>

                <p>
                    開催場所：
                    {{ $participant->event->place }}
                </p>

                <p>
                    参加状態：
                    <x-status-badge :status="$participant->status" :label="$participant->status_label" />
                </p>

                @if ($participant->status === 'pending_payment' && $participant->payment_expires_at)
                    <p>
                        支払い期限：
                        {{ $participant->payment_expires_at->format('Y/m/d H:i') }}
                    </p>
                @endif

                <p>
                    決済状態：

                    @if ($participant->payment)
                        <x-status-badge :status="$participant->payment->status" :label="$participant->payment->status_label" />
                    @else
                        <x-status-badge status="none" label="決済情報なし" />
                    @endif
                </p>

                @if ($participant->cancellation_reason === 'capacity_reached_after_payment')
                    <div
                        style="
                            margin-top: 12px;
                            padding: 12px;
                            border-radius: 8px;
                            background: #fff3cd;
                        ">
                        <strong>定員到達によるキャンセル</strong>

                        <p style="margin: 8px 0 0;">
                            支払処理中に定員に達したため、
                            参加申し込みをキャンセルしました。
                        </p>

                        <p style="margin: 8px 0 0;">
                            お支払いいただいた参加費は全額返金されます。
                        </p>
                    </div>
                @endif

                @if ($participant->status === 'pending_payment' && $participant->payment?->status === 'pending')
                    <form method="POST" action="{{ route('checkout.store', $participant) }}">
                        @csrf

                        <x-button type="submit">
                            支払いへ進む
                        </x-button>
                    </form>
                @endif
                @if (in_array($participant->status, ['pending_payment', 'confirmed'], true) &&
                        auth()->id() !== $participant->event->organizer_id)
                    <div style="margin-top: 10px;">
                        <form method="POST" action="{{ route('direct-chat.start', $participant->event->organizer) }}">
                            @csrf

                            <input type="hidden" name="event_id" value="{{ $participant->event->id }}">

                            <button type="submit" class="btn btn-online-payment">
                                オンライン決済へ
                            </button>
                        </form>

                        <p
                            style="
                margin: 6px 0 0;
                font-size: 13px;
                color: #6b7280;
            ">
                            PayPayなどのオンライン決済について、
                            主催者とチャットで確認できます。
                        </p>
                    </div>
                @endif

                @if ($participant->status !== 'cancelled')
                    <x-link-button href="{{ route('event-participants.cancel-confirm', $participant) }}" variant="danger">
                        キャンセル
                    </x-link-button>
                @endif

                <p>
                    参加費：
                    {{ number_format($participant->event->price) }}円
                </p>

                @if ($participant->status === 'confirmed' && $participant->event->chat_url)
                    <p>
                        参加者用チャット：
                        <a href="{{ $participant->event->chat_url }}" target="_blank" rel="noopener noreferrer">
                            チャットを開く
                        </a>
                    </p>
                @endif

            </div>
        @endforeach
    @endif


    {{-- 終了・中止したイベント --}}
    <h2 style="margin-top: 40px;">終了・中止したイベント</h2>

    @if ($pastParticipants->isEmpty())
        <p>終了・中止したイベントはありません。</p>
    @else
        @foreach ($pastParticipants as $participant)
            <div class="card">

                <h2>
                    <a href="{{ route('events.show', $participant->event) }}">
                        {{ $participant->event->title }}
                    </a>
                </h2>

                <p>
                    開催日時：
                    {{ $participant->event->event_date->format('Y/m/d H:i') }}
                </p>

                <p>
                    開催場所：
                    {{ $participant->event->place }}
                </p>

                <p>
                    イベント状態：
                    <x-status-badge :status="$participant->event->status" :label="$participant->event->status_label" />
                </p>

                <p>
                    参加状態：
                    <x-status-badge :status="$participant->status" :label="$participant->status_label" />
                </p>

                <p>
                    決済状態：

                    @if ($participant->payment)
                        <x-status-badge :status="$participant->payment->status" :label="$participant->payment->status_label" />
                    @else
                        <x-status-badge status="none" label="決済情報なし" />
                    @endif
                </p>

                <p>
                    参加費：
                    {{ number_format($participant->event->price) }}円
                </p>

            </div>
        @endforeach
    @endif

@endsection
