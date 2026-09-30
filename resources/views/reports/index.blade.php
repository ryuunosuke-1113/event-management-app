@extends('layouts.app')

@section('title', '自分の通報')

@section('content')

    <h1>自分の通報</h1>

    @forelse ($reports as $report)
        <div class="card" style="margin-bottom: 16px;">
            <p>
                通報対象：
                {{ $report->reportedUser->name }}
            </p>

            <p>
                通報理由：
                {{ $report->reason_label }}
            </p>

            <p>
                対応状況：
                {{ $report->status_label }}
            </p>
            @if ($report->unread_messages_count > 0)
                <p>
                    未読メッセージ：
                    <strong>{{ $report->unread_messages_count }}件</strong>
                </p>
            @endif

            <p>
                通報日時：
                {{ $report->created_at->format('Y/m/d H:i') }}
            </p>

            <x-link-button href="{{ route('reports.show', $report) }}" variant="secondary">
                詳細・メッセージを見る
            </x-link-button>
        </div>

    @empty

        <div class="card">
            <p>
                まだ通報履歴はありません。
            </p>
        </div>
    @endforelse

@endsection
