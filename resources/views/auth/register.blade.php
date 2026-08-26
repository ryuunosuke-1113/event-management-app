@extends('layouts.app')

@section('title', '新規登録')

@section('content')

    <div class="card">

        <h1>新規登録</h1>

        @if ($errors->any())
            <div class="error">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('register.store') }}">
            @csrf

            <div class="form-group">
                <label for="name">
                    名前
                </label>

                <input type="text" id="name" name="name" value="{{ old('name') }}" required>
            </div>

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

            <div class="form-group">
                <label for="password_confirmation">
                    パスワード確認
                </label>

                <input type="password" id="password_confirmation" name="password_confirmation" required>
            </div>

            <x-button type="submit">
                新規登録
            </x-button>
        </form>

        <p>
            すでにアカウントをお持ちの方は
            <a href="{{ route('login') }}">
                ログイン
            </a>
        </p>

    </div>

@endsection
