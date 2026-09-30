@extends('layouts.app')

@section('content')
    <div class="card">
        <h1>フィードバック</h1>

        <p>
            サイトへのご意見・ご要望・不具合報告などをお送りください。
        </p>
        <div style="margin-top: 16px;">
            <x-link-button href="{{ route('feedback.index') }}" variant="secondary">
                自分が送ったフィードバック履歴を見る
            </x-link-button>
        </div>

        <form method="POST" action="{{ route('feedback.store') }}">
            @csrf

            <div style="margin-top: 24px;">
                <label for="title">
                    <strong>表題</strong>
                </label>

                <input type="text" id="title" name="title" value="{{ old('title') }}" maxlength="255" required
                    style="
                    width: 100%;
                    margin-top: 8px;
                ">

                @error('title')
                    <p style="color: #dc3545;">
                        {{ $message }}
                    </p>
                @enderror
            </div>

            <div style="margin-top: 24px;">
                <label for="body">
                    <strong>内容</strong>
                </label>

                <textarea id="body" name="body" rows="10" maxlength="5000" required
                    style="
                    width: 100%;
                    margin-top: 8px;
                    resize: vertical;
                ">{{ old('body') }}</textarea>

                @error('body')
                    <p style="color: #dc3545;">
                        {{ $message }}
                    </p>
                @enderror
            </div>

            <div style="margin-top: 24px;">
                <x-button type="submit" variant="primary">
                    フィードバックを送信
                </x-button>
            </div>
        </form>
    </div>
@endsection
