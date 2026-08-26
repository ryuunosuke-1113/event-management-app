@extends('layouts.app')

@section('title', 'パスワード再設定')

@section('content')

    <div class="card">
        <h1>パスワードを忘れた方</h1>

        <p>
            登録しているメールアドレスを入力してください。
            パスワード再設定用のリンクを送信します。
        </p>

        <form method="POST" action="{{ route('password.email') }}">
            @csrf

            <div class="form-group">
                <label for="email">
                    メールアドレス
                </label>

                <input type="email" id="email" name="email" value="{{ old('email') }}" required>
            </div>
            @error('email')
                <p class="error">
                    {{ $message }}
                </p>
            @enderror

            <x-button type="submit" variant="primary">
                再設定メールを送信
            </x-button>
        </form>
    </div>

@endsection
