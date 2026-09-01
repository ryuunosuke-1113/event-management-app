@extends('layouts.app')

@section('title', 'キャンセルしたイベント')

@section('content')

    <h1>キャンセルしたイベント</h1>

    <div style="margin-bottom: 24px;">
        <x-link-button href="{{ route('event-participants.index') }}" variant="secondary">
            自分の参加予定へ戻る
        </x-link-button>
    </div>

    @if ($participants->isEmpty())

        <p>キャンセルしたイベントはありません。</p>
    @else
        @foreach ($participants as $participant)
            <div class="card">

                <h2>
                    {{ $participant->event->title }}
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
                    キャンセル日時：
                    @if ($participant->cancelled_at)
                        {{ $participant->cancelled_at->format('Y/m/d H:i') }}
                    @else
                        -
                    @endif
                </p>

                <p>
                    参加費：
                    {{ number_format($participant->event->price) }}円
                </p>

                <hr>

                <h3>決済・返金情報</h3>

                @if ($participant->payment)
                    <p>
                        決済方法：
                        @if ($participant->payment->payment_method === 'stripe')
                            Stripe
                        @elseif ($participant->payment->payment_method === 'online')
                            その他オンライン決済
                        @else
                            -
                        @endif
                    </p>

                    <p>
                        決済状態：
                        <x-status-badge :status="$participant->payment->status" :label="$participant->payment->display_status_label" />
                    </p>

                    <p>
                        返金状態：
                        {{ $participant->payment->refund_status_label }}
                    </p>

                    @if ($participant->payment->refund_due_amount !== null)
                        <p>
                            返金予定額：
                            {{ number_format($participant->payment->refund_due_amount) }}円
                        </p>
                    @endif

                    @if ($participant->payment->refunded_amount !== null)
                        <p>
                            返金済み額：
                            {{ number_format($participant->payment->refunded_amount) }}円
                        </p>
                    @endif

                    @if ($participant->payment->refunded_at)
                        <p>
                            返金日時：
                            {{ $participant->payment->refunded_at->format('Y/m/d H:i') }}
                        </p>
                    @endif
                @else
                    <p>決済情報はありません。</p>
                @endif

            </div>
        @endforeach

    @endif

@endsection
