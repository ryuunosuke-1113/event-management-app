@extends('layouts.app')

@section('title', '制御管理')

@section('content')

    <h1>制御管理</h1>

    @forelse ($actions as $action)
        @php
            $unreadCount = $action->messages
                ->where('user_id', '!=', auth()->id())
                ->whereNull('read_at')
                ->count();
        @endphp

        <div class="card" style="margin-bottom: 16px;">

            <h2>
                {{ $action->user->name }}
            </h2>

            <p>
                制御：
                <strong>
                    {{ $action->action_type_label }}
                </strong>
            </p>

            @if ($action->action_type === 'suspension')
                <p>
                    現在の利用制限：
                    @switch($action->user->account_status)
                        @case('creation_suspended')
                            イベント作成禁止
                        @break

                        @case('full_suspended')
                            イベント作成・参加禁止
                        @break

                        @default
                            通常利用
                    @endswitch
                </p>
            @endif

            <p>
                開始日時：
                {{ $action->created_at->format('Y/m/d H:i') }}
            </p>

            @if ($unreadCount > 0)
                <p style="color: #dc3545; font-weight: bold;">
                    新着メッセージあり（{{ $unreadCount }}）
                </p>
            @endif

            <x-link-button href="{{ route('admin.users.moderation.edit', $action->user) }}" variant="secondary">
                詳細を見る
            </x-link-button>

        </div>

        @empty

            <div class="card">
                <p>
                    現在有効なアカウント制御はありません。
                </p>
            </div>
        @endforelse

    @endsection
