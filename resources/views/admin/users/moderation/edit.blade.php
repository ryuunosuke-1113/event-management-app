@extends('layouts.app')

@section('title', 'アカウント制御')

@section('content')

    <h1>{{ $user->name }}さんのアカウント制御</h1>

    <div style="margin-top: 24px;">
        <x-link-button href="{{ route('admin.moderation.index') }}" variant="secondary">
            制御管理に戻る
        </x-link-button>
    </div>

    <div class="card" style="margin-bottom: 24px;">
        <h2>現在のアカウント制御</h2>

        @if ($currentAction)

            <p>
                <strong>
                    {{ $currentAction->action_type_label }}
                </strong>
            </p>

            <p style="white-space: pre-wrap;">
                {{ $currentAction->comment }}
            </p>

            @if ($currentAction->action_type === 'suspension')
                <p>
                    制限内容：
                    @switch($user->account_status)
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
                {{ $currentAction->created_at->format('Y/m/d H:i') }}
            </p>

            <form method="POST" action="{{ route('admin.users.moderation.clear', $user) }}" style="margin-top: 16px;">
                @csrf

                <x-button type="submit" variant="primary">
                    アカウント制御を解除する
                </x-button>
            </form>
            <hr style="margin: 32px 0;">

            <h2>ユーザーとのチャット</h2>

            <div style="margin-bottom: 24px;">

                @forelse ($currentAction->messages as $message)
                    <div
                        style="
                margin-bottom: 12px;
                padding: 12px;
                border: 1px solid #ddd;
                border-radius: 8px;
            ">
                        <div style="margin-bottom: 6px;">

                            <strong>
                                @if ($message->user_id === auth()->id())
                                    運営
                                @else
                                    {{ $user->name }}
                                @endif
                            </strong>

                            <span style="font-size: 0.9em; color: #666;">
                                {{ $message->created_at->format('Y/m/d H:i') }}
                            </span>

                        </div>

                        <div style="white-space: pre-wrap;">
                            {{ $message->body }}
                        </div>
                    </div>

                @empty

                    <p>
                        まだメッセージはありません。
                    </p>
                @endforelse

            </div>

            <form method="POST" action="{{ route('admin.moderation.messages.store', $currentAction) }}">
                @csrf

                <div style="margin-bottom: 12px;">
                    <label for="moderation-message-body">
                        ユーザーへのメッセージ
                    </label>

                    <textarea id="moderation-message-body" name="body" rows="5" maxlength="3000" required
                        style="width: 100%; max-width: 700px;">{{ old('body') }}</textarea>

                    @error('body')
                        <div>{{ $message }}</div>
                    @enderror
                </div>

                <x-button type="submit" variant="primary">
                    送信
                </x-button>
            </form>
        @else
            <p>現在、有効なアカウント制御はありません。</p>

        @endif
    </div>
    <div class="card" style="margin-bottom: 24px;">
        <h2>警告</h2>

        <form method="POST" action="{{ route('admin.users.warning', $user) }}">
            @csrf

            <label for="warning-comment">
                警告内容
            </label>

            <textarea id="warning-comment" name="comment" rows="5" maxlength="2000" required
                style="width: 100%; max-width: 700px;">{{ old('comment') }}</textarea>

            <div style="margin-top: 12px;">
                <x-button type="submit" variant="warning">
                    警告する
                </x-button>
            </div>
        </form>
    </div>

    <div class="card" style="margin-bottom: 24px;">
        <h2>アカウント制限</h2>

        <form method="POST" action="{{ route('admin.users.suspend', $user) }}">
            @csrf

            <div style="margin-bottom: 16px;">
                <label for="suspension_type">
                    制限内容
                </label>

                <select id="suspension_type" name="suspension_type" required>
                    <option value="">
                        選択してください
                    </option>

                    <option value="creation_suspended">
                        イベント作成のみ禁止
                    </option>

                    <option value="full_suspended">
                        イベント作成・参加を禁止
                    </option>
                </select>
            </div>

            <div style="margin-bottom: 16px;">
                <label for="suspension-comment">
                    制限理由
                </label>

                <textarea id="suspension-comment" name="comment" rows="5" maxlength="2000" required
                    style="width: 100%; max-width: 700px;"></textarea>
            </div>

            <div style="margin-bottom: 16px;">
                <label>
                    <input type="checkbox" name="cancel_existing_events" value="1">

                    現在開催待ちの主催イベントも強制中止する
                </label>
            </div>

            <x-button type="submit" variant="danger">
                アカウント制限を実行する
            </x-button>
        </form>
    </div>

    <div class="card">
        <h2>処分履歴</h2>

        @forelse ($user->moderationActions as $action)
            <div style="
                padding: 12px 0;
                border-bottom: 1px solid #ddd;
            ">
                <p>
                    <strong>
                        {{ $action->action_type_label }}
                    </strong>
                </p>

                <p style="white-space: pre-wrap;">
                    {{ $action->comment }}
                </p>

                <p>
                    {{ $action->created_at->format('Y/m/d H:i') }}
                </p>

                <p>
                    {{ $action->is_active ? '有効' : '解除済み' }}
                </p>
            </div>

        @empty

            <p>
                処分履歴はありません。
            </p>
        @endforelse
    </div>

@endsection
