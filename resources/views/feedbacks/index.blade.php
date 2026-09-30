@extends('layouts.app')

@section('content')

    <div class="card">
        <h1>送信済みフィードバック</h1>

        @if ($feedbacks->isEmpty())
            <p>まだフィードバックを送信していません。</p>
        @else
            <div style="margin-top: 20px;">
                @foreach ($feedbacks as $feedback)
                    <div
                        style="
                        padding: 16px 0;
                        border-bottom: 1px solid #ddd;
                    ">
                        <div>
                            <strong>表題：</strong>
                            {{ $feedback->title }}
                        </div>

                        <div style="margin-top: 6px;">
                            <strong>送信日時：</strong>
                            {{ $feedback->created_at->format('Y年m月d日 H:i') }}
                        </div>

                        <div style="margin-top: 6px;">
                            <strong>確認状況：</strong>

                            @if ($feedback->read_at)
                                <span>運営が確認済み</span>
                            @else
                                <span>未確認</span>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        <div style="margin-top: 24px;">
            <x-link-button href="{{ route('feedback.create') }}" variant="primary">
                新しいフィードバックを送る
            </x-link-button>
        </div>
    </div>

@endsection
