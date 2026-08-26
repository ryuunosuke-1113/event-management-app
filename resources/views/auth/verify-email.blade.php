@extends('layouts.app')

@section('title', 'メールアドレスの確認')

@section('content')

    <div class="card">
        <h1>メールアドレスの確認</h1>

        <p>
            登録したメールアドレス宛に確認メールを送信しました。
        </p>

        <p>
            メール内のリンクをクリックして、
            メールアドレスの確認を完了してください。
        </p>

        <form method="POST" action="{{ route('verification.send') }}">
            @csrf

            <x-button type="submit">
                確認メールを再送する
            </x-button>
        </form>
    </div>

@endsection
