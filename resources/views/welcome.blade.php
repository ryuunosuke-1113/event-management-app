@extends('layouts.app')

@section('title', 'イベント運営アプリ')

@section('content')

    <div class="hero-card">

        <p class="hero-label">
            EVENT MANAGEMENT
        </p>

        <h1>
            イベントを見つけて、<br>
            気軽に参加しよう
        </h1>

        <p class="hero-description">
            勉強会・交流会・サークルイベントなど、
            公開中のイベントを探して参加できます。
        </p>

        <div class="hero-actions">

            <x-link-button href="{{ route('events.index') }}">
                イベント一覧を見る
            </x-link-button>

            @guest
                <x-link-button href="{{ route('login') }}" variant="secondary">
                    ログイン
                </x-link-button>

                <x-link-button href="{{ route('register') }}" variant="secondary">
                    新規登録
                </x-link-button>
            @endguest

            @auth
                <x-link-button href="{{ route('event-participants.index') }}" variant="secondary">
                    自分の参加予定
                </x-link-button>
            @endauth

        </div>

    </div>

    <div class="home-features">

        <div class="card">
            <h2>イベントを探す</h2>
            <p>
                公開中のイベントから、
                気になるイベントを探せます。
            </p>
        </div>

        <div class="card">
            <h2>かんたん申し込み</h2>
            <p>
                アカウントを作成すれば、
                イベント詳細からそのまま参加申し込みできます。
            </p>
        </div>

        <div class="card">
            <h2>オンライン決済</h2>
            <p>
                Stripe Checkoutを利用して、
                参加費を事前に支払えます。
            </p>
        </div>

    </div>

@endsection
