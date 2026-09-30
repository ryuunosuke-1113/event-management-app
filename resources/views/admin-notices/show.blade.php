@extends('layouts.app')

@section('title', '運営からのメッセージ')

@section('content')

    <h1>{{ $adminNotice->title }}</h1>

    <div class="card">

        <p>
            種類：
            {{ $adminNotice->category_label }}
        </p>

        <p>
            日時：
            {{ $adminNotice->created_at->format('Y/m/d H:i') }}
        </p>

        @if ($adminNotice->event)
            <p>
                関連イベント：
                {{ $adminNotice->event->title }}
            </p>
        @endif

        <hr>

        <p style="white-space: pre-wrap;">
            {{ $adminNotice->body }}
        </p>
        @if (
            $adminNotice->moderationAction &&
                $adminNotice->moderationAction->is_active &&
                in_array($adminNotice->category, ['moderation_applied', 'moderation_updated'], true))
            <div style="margin-top: 24px;">
                <x-link-button href="{{ route('moderation-messages.show', $adminNotice->moderationAction) }}"
                    variant="primary">
                    この制御について運営に問い合わせる
                </x-link-button>
            </div>
        @endif
    </div>

    <div style="margin-top: 20px;">
        <x-link-button href="{{ route('admin-notices.index') }}">
            一覧に戻る
        </x-link-button>
    </div>

@endsection
