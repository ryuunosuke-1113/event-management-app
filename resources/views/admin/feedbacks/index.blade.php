@extends('layouts.app')

@section('content')

    <div class="card">
        <h1>フィードバック一覧</h1>

        @if ($feedbacks->isEmpty())
            <p>フィードバックはまだありません。</p>
        @else
            <div style="margin-top: 20px;">
                @foreach ($feedbacks as $feedback)
                    <div
                        style="
                        padding: 16px 0;
                        border-bottom: 1px solid #ddd;
                    ">
                        <div>
                            @if (is_null($feedback->read_at))
                                <strong style="color: #dc3545;">
                                    未読
                                </strong>
                            @endif
                        </div>

                        <div style="margin-top: 6px;">
                            <strong>表題：</strong>
                            {{ $feedback->title }}
                        </div>

                        <div style="margin-top: 6px;">
                            <strong>送信者：</strong>
                            {{ $feedback->user->name }}
                        </div>

                        <div style="margin-top: 6px;">
                            <strong>送信日時：</strong>
                            {{ $feedback->created_at->format('Y年m月d日 H:i') }}
                        </div>

                        <div style="margin-top: 12px;">
                            <x-link-button href="{{ route('admin.feedbacks.show', $feedback) }}" variant="secondary">
                                詳細を見る
                            </x-link-button>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

@endsection
