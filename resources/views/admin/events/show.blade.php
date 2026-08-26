@extends('layouts.app')

@section('title', $event->title)

@section('content')

    <h1>イベント詳細</h1>

    <div class="card">

        <h2>{{ $event->title }}</h2>

        <p>
            状態：
            <x-status-badge :status="$event->status" :label="$event->status_label" />
        </p>

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
            {{ $event->description }}
        </p>

        @if ($event->cancel_policy)
            <h3>キャンセルポリシー</h3>
            <p>{{ $event->cancel_policy }}</p>
        @endif

        <div style="display: flex; gap: 10px; flex-wrap: wrap;">

            <x-link-button href="{{ route('admin.events.edit', $event) }}">
                編集する
            </x-link-button>

            <x-link-button href="{{ route('admin.events.index') }}" variant="secondary">
                イベント管理へ戻る
            </x-link-button>

        </div>

    </div>

    <div class="card">

        <h2>参加状況</h2>

        <p>
            参加確定：
            {{ $event->participants->where('status', 'confirmed')->count() }}
            / {{ $event->capacity }}人
        </p>

        <p>
            決済待ち：
            {{ $event->participants->where('status', 'pending_payment')->count() }}人
        </p>

        <p>
            キャンセル済み：
            {{ $event->participants->where('status', 'cancelled')->count() }}人
        </p>

    </div>

    <div class="card">

        <h2>参加者一覧</h2>

        @if ($event->participants->isEmpty())

            <p>まだ参加者はいません。</p>
        @else
            <div style="overflow-x: auto;">

                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>名前</th>
                            <th>メール</th>
                            <th>参加状態</th>
                            <th>決済状態</th>
                            <th>申込日時</th>
                            <th>支払い期限</th>
                            <th>当日参加確認</th>
                            <th>操作</th>
                        </tr>
                    </thead>

                    <tbody>

                        @foreach ($event->participants as $participant)
                            <tr>
                                <td>{{ $participant->user_id }}</td>

                                <td>
                                    {{ $participant->user->name }}
                                </td>

                                <td>
                                    {{ $participant->user->email }}
                                </td>

                                <td>
                                    <x-status-badge :status="$participant->status" :label="$participant->status_label" />
                                </td>

                                <td>
                                    @if ($participant->payment)
                                        <x-status-badge :status="$participant->payment->status" :label="$participant->payment->display_status_label" />
                                    @else
                                        <x-status-badge status="none" label="決済情報なし" />
                                    @endif
                                </td>

                                <td>
                                    {{ $participant->created_at->format('Y/m/d H:i') }}
                                </td>

                                <td>
                                    @if ($participant->status === 'pending_payment' && $participant->payment_expires_at)
                                        {{ $participant->payment_expires_at->format('Y/m/d H:i') }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>
                                    @if ($participant->status === 'confirmed')
                                        <form method="POST"
                                            action="{{ route('admin.event-participants.attendance', $participant) }}">
                                            @csrf
                                            @method('PATCH')

                                            <input type="hidden" name="attended"
                                                value="{{ $participant->attended_at ? 0 : 1 }}">

                                            @if ($participant->attended_at)
                                                <button type="submit">
                                                    ✓ 参加確認済み
                                                </button>

                                                <div style="margin-top: 4px; font-size: 12px;">
                                                    {{ $participant->attended_at->format('Y/m/d H:i') }}
                                                </div>
                                            @else
                                                <button type="submit">
                                                    参加確認
                                                </button>
                                            @endif
                                        </form>
                                    @else
                                        -
                                    @endif
                                </td>

                                <td>
                                    @if ($participant->status === 'pending_payment')
                                        <form method="POST"
                                            action="{{ route('admin.event-participants.confirm-online-payment', $participant) }}">
                                            @csrf

                                            <x-button type="submit" variant="primary">
                                                オンライン決済済みとして確定
                                            </x-button>
                                        </form>
                                    @elseif (
                                        $participant->status === 'cancelled' &&
                                            $participant->payment &&
                                            $participant->payment->payment_method === 'online' &&
                                            $participant->payment->status === 'paid' &&
                                            is_null($participant->payment->refunded_at))
                                        @php
                                            $refundAmount = $event->refundAmountAt(
                                                $participant->cancelled_at,
                                                $participant->payment->amount,
                                            );
                                        @endphp

                                        @if ($refundAmount > 0)
                                            <div>
                                                <strong>
                                                    返金対応が必要：
                                                    {{ number_format($refundAmount) }}円
                                                </strong>
                                            </div>
                                        @else
                                            <div>
                                                返金対応は不要です。
                                            </div>
                                        @endif
                                    @else
                                        -
                                    @endif
                                </td>

                            </tr>
                        @endforeach

                    </tbody>
                </table>

            </div>

        @endif

    </div>

    <form method="POST" action="{{ route('admin.events.destroy', $event) }}"
        onsubmit="return confirm('本当にこのイベントを削除しますか？')">
        @csrf
        @method('DELETE')

        <x-button type="submit" variant="danger">
            イベントを削除する
        </x-button>
    </form>

@endsection
