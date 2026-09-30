@extends('layouts.app')

@section('title', '通報管理')

@section('content')

    <h1>通報管理</h1>

    <form method="GET" action="{{ route('admin.reports.index') }}" style="margin-bottom: 24px;">
        <label for="status">対応状況</label>

        <select id="status" name="status">
            <option value="">すべて</option>

            <option value="open" @selected(request('status') === 'open')>
                未対応
            </option>

            <option value="in_progress" @selected(request('status') === 'in_progress')>
                対応中
            </option>

            <option value="resolved" @selected(request('status') === 'resolved')>
                解決済み
            </option>
        </select>

        <x-button type="submit" variant="primary">
            絞り込む
        </x-button>
    </form>

    @if ($reports->isEmpty())

        <div class="card">
            <p>通報はありません。</p>
        </div>
    @else
        @foreach ($reports as $report)
            <div class="card" style="margin-bottom: 16px;">

                <h2>
                    通報 #{{ $report->id }}
                </h2>

                <p>
                    通報者：
                    {{ $report->reporter->name }}
                </p>

                <p>
                    通報されたユーザー：
                    {{ $report->reportedUser->name }}
                </p>

                <p>
                    理由：
                    {{ $report->reason_label }}
                </p>

                <p>
                    状態：
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

                <x-link-button href="{{ route('admin.reports.show', $report) }}" variant="secondary">
                    詳細を見る
                </x-link-button>

            </div>
        @endforeach

    @endif

@endsection
