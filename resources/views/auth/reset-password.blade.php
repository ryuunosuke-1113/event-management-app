@extends('layouts.app')

@section('title', 'パスワード再設定')

@section('content')

    <div class="card">
        <h1>新しいパスワードを設定</h1>

        <form method="POST" action="{{ route('password.update') }}">
            @csrf

            <input type="hidden" name="token" value="{{ $token }}">

            <div class="form-group">
                <label for="email">
                    メールアドレス
                </label>

                <input type="email" id="email" name="email" value="{{ old('email', $email) }}" required>
            </div>

            <div class="form-group">
                <label for="password">
                    新しいパスワード
                </label>

                <input type="password" id="password" name="password" required>
            </div>

            <div class="form-group">
                <label for="password_confirmation">
                    新しいパスワード確認
                </label>

                <input type="password" id="password_confirmation" name="password_confirmation" required>
            </div>

            <x-button type="submit" variant="primary">
                パスワードを再設定
            </x-button>
        </form>
    </div>

@endsection
