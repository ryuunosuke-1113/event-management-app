@extends('layouts.app')

@section('title', '運営とのチャット')

@section('content')

    <h1>運営とのチャット</h1>

    <div style="margin-top: 24px;">
        <x-link-button href="{{ route('admin-notices.index') }}" variant="secondary">
            運営からのメッセージ一覧に戻る
        </x-link-button>
    </div>

    <div class="card" style="margin-bottom: 24px;">
        <p>
            制御内容：
            <strong>
                {{ $action->action_type_label }}
            </strong>
        </p>

        <p style="white-space: pre-wrap;">
            {{ $action->comment }}
        </p>

        <p>
            開始日時：
            {{ $action->created_at->format('Y/m/d H:i') }}
        </p>
    </div>

    <div style="margin-bottom: 24px;">

        @forelse ($action->messages as $message)
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
                            あなた
                        @else
                            運営
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
                まだ運営とのメッセージはありません。
            </p>
        @endforelse

    </div>

    @if ($action->is_active)
        <form method="POST" action="{{ route('moderation-messages.store', $action) }}">
            @csrf

            <div style="margin-bottom: 12px;">
                <label for="body">
                    運営へのメッセージ
                </label>

                <br>

                <textarea id="body" name="body" rows="5" maxlength="3000" required style="width: 100%; max-width: 700px;">{{ old('body') }}</textarea>

                @error('body')
                    <div>{{ $message }}</div>
                @enderror
            </div>

            <x-button type="submit" variant="primary">
                送信
            </x-button>
        </form>
    @else
        <p style="color: #666;">
            この制御はすでに終了しているため、
            新しいメッセージは送信できません。
        </p>
    @endif

@endsection
