@extends('layouts.app')

@section('content')
    <div class="card">
        <h1>フィードバック詳細</h1>

        <div style="margin-top: 24px;">
            <p>
                <strong>送信者：</strong>
                {{ $feedback->user->name }}
            </p>

            <p>
                <strong>メールアドレス：</strong>
                {{ $feedback->user->email }}
            </p>

            <p>
                <strong>送信日時：</strong>
                {{ $feedback->created_at->format('Y年m月d日 H:i') }}
            </p>
        </div>

        <div style="margin-top: 24px;">
            <h2>{{ $feedback->title }}</h2>

            <p style="white-space: pre-wrap;">
                {{ $feedback->body }}
            </p>
        </div>

        <div style="margin-top: 32px;">
            <x-link-button href="{{ route('admin.feedbacks.index') }}" variant="secondary">
                フィードバック一覧に戻る
            </x-link-button>
        </div>
    </div>
@endsection
