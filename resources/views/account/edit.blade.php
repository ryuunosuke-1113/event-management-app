@extends('layouts.app')

@section('title', 'アカウント設定')

@section('content')

    <h1>アカウント設定</h1>

    <div class="card">

        @if ($errors->any())
            <div class="error">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('account.update') }}" enctype="multipart/form-data"> @csrf
            @method('PUT')

            <div class="form-group">
                <label for="name">
                    名前
                </label>

                <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" required>
            </div>

            <div class="form-group">
                <label for="email">
                    メールアドレス
                </label>

                <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}" required>
            </div>
            <hr>

            <h2>プロフィール</h2>

            <div class="form-group">
                <label for="photo">
                    プロフィール画像
                </label>

                @if ($user->profile?->photo_path)
                    <div style="margin-bottom: 12px;">
                        <img src="{{ asset('storage/' . $user->profile->photo_path) }}" alt="{{ $user->name }}"
                            style="
                    width: 100px;
                    height: 100px;
                    object-fit: cover;
                    border-radius: 50%;
                ">
                    </div>
                @endif

                <input type="file" id="photo" name="photo" accept="image/*">
            </div>

            <div class="form-group">
                <label for="bio">
                    自己紹介
                </label>

                <textarea id="bio" name="bio" rows="5">{{ old('bio', $user->profile?->bio) }}</textarea>
            </div>

            <hr>

            <h2>パスワード変更</h2>

            <p>
                パスワードを変更しない場合は、
                以下の3項目は空欄のままで大丈夫です。
            </p>

            <div class="form-group">
                <label for="current_password">
                    現在のパスワード
                </label>

                <input type="password" id="current_password" name="current_password">
            </div>

            <div class="form-group">
                <label for="password">
                    新しいパスワード
                </label>

                <input type="password" id="password" name="password">
            </div>

            <div class="form-group">
                <label for="password_confirmation">
                    新しいパスワード確認
                </label>

                <input type="password" id="password_confirmation" name="password_confirmation">
            </div>
            <x-button type="submit">
                アカウント情報を更新
            </x-button>

        </form>

    </div>

@endsection
