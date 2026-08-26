@extends('layouts.app')

@section('title', 'ログイン')

@section('content')

    <div class="card">

        <h1>ログイン</h1>

        @if ($errors->any())
            <div class="error">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('login.store') }}">
            @csrf

            <div class="form-group">
                <label for="email">
                    メールアドレス
                </label>

                <input type="email" id="email" name="email" value="{{ old('email') }}" required>
            </div>

            <div class="form-group">
                <label for="password">
                    パスワード
                </label>

                <input type="password" id="password" name="password" required>
            </div>

            <x-button type="submit">
                ログイン
            </x-button>
        </form>

        <p>
            アカウントをお持ちでない方は
            <a href="{{ route('register') }}">
                新規登録
            </a>
        </p>
        <div style="margin-top: 12px;">
            <a href="{{ route('password.request') }}">
                パスワードを忘れた方はこちら
            </a>
        </div>

    </div>

@endsection
