@extends('layouts.app')

@section('title', '自分の参加予定')

@section('content')

    <h1>自分の参加予定</h1>

    @if ($participants->isEmpty())

        <p>現在、申し込んでいるイベントはありません。</p>
    @else
        @foreach ($participants as $participant)
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

@endsection
