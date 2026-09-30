@extends('layouts.app')

@section('title', '運営からのお知らせ')

@section('content')

    <h1>運営からのお知らせ</h1>

    @if ($user->account_status === 'suspended')
        <div class="card"
            style="
                margin-bottom: 24px;
                border: 2px solid #dc3545;
            ">
            <h2>アカウントは現在停止されています</h2>

            <p>
                ログインや内容の確認はできますが、
                イベントの作成・参加には制限があります。
            </p>
        </div>
    @endif

    @forelse ($actions as $action)
        <div class="card" style="margin-bottom: 16px;">

            <h2>
                {{ $action->action_type_label }}
            </h2>

            <p style="white-space: pre-wrap;">
                {{ $action->comment }}
            </p>

            <p>
                日時：
                {{ $action->created_at->format('Y/m/d H:i') }}
            </p>

            <p>
                状態：
                {{ $action->is_active ? '有効' : '解除済み' }}
            </p>

            @if ($action->ended_at)
                <p>
                    解除日時：
                    {{ $action->ended_at->format('Y/m/d H:i') }}
                </p>
            @endif

        </div>

    @empty

        <div class="card">
            <p>現在、運営からのお知らせはありません。</p>
        </div>
    @endforelse

@endsection
