@extends('layouts.app')

@section('content')
    <div class="card">
        <h1>参加キャンセルの確認</h1>

        <p>
            「{{ $eventParticipant->event->title }}」の参加申し込みをキャンセルしますか？
        </p>

        <div style="margin-top: 24px;">
            <p>
                <strong>開催日時：</strong>
                {{ $eventParticipant->event->event_date->format('Y年m月d日 H:i') }}
            </p>

            <p>
                <strong>参加費：</strong>
                {{ number_format($eventParticipant->payment?->amount ?? $eventParticipant->event->price) }}円
            </p>
        </div>

        <div style="margin-top: 24px;">
            @if (
                $eventParticipant->status === 'confirmed' &&
                    $eventParticipant->payment &&
                    $eventParticipant->payment->status === 'paid')
                @if ($refundRate === 100)
                    <p>
                        このイベントを今キャンセルすると、
                        <strong>全額返金</strong>されます。
                    </p>

                    <p>
                        返金額：
                        <strong>{{ number_format($refundAmount) }}円</strong>
                    </p>
                @elseif ($refundRate > 0)
                    <p>
                        このイベントを今キャンセルすると、
                        <strong>{{ $refundRate }}%返金</strong>されます。
                    </p>

                    <p>
                        返金額：
                        <strong>{{ number_format($refundAmount) }}円</strong>
                    </p>
                @else
                    <p>
                        このイベントを今キャンセルした場合、
                        <strong>返金はありません。</strong>
                    </p>
                @endif
            @else
                <p>
                    この申し込みは決済済みではないため、返金はありません。
                </p>
            @endif
        </div>

        <div style="margin-top: 32px; display: flex; gap: 12px;">
            <form method="POST" action="{{ route('event-participants.destroy', $eventParticipant) }}">
                @csrf
                @method('DELETE')

                <x-button type="submit" variant="danger">
                    キャンセルを確定する
                </x-button>
            </form>

            <x-link-button href="{{ route('event-participants.index') }}" variant="secondary">
                戻る
            </x-link-button>
        </div>
    </div>
@endsection
