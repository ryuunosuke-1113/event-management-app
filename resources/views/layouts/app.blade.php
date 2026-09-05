<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>@yield('title', 'イベント運営アプリ')</title>

    <link rel="manifest" href="{{ asset('manifest.webmanifest') }}">
    <link rel="apple-touch-icon" href="{{ asset('icons/icon-192.png') }}">
    <meta name="theme-color" content="#0ea5e9">

    @vite('resources/js/app.js')
    <style>
        body {
            font-family: sans-serif;
            margin: 0;
            background: #f5f5f5;
        }

        nav {
            background: white;
            padding: 16px 24px;
            border-bottom: 1px solid #ddd;
        }

        nav {
            background: white;
            padding: 14px 24px;
            border-bottom: 1px solid #ddd;
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
        }

        nav a,
        nav button {
            display: inline-block;
            padding: 10px 16px;
            border-radius: 8px;
            border: none;
            text-decoration: none;
            font-size: 14px;
            transition: 0.2s;
        }

        nav a {
            background: #f3f4f6;
            color: #333;
        }

        nav a:hover {
            background: #e5e7eb;
        }

        nav button {
            background: #333;
            color: white;
        }

        nav button:hover {
            opacity: 0.85;
        }

        main {
            max-width: 960px;
            margin: 32px auto;
            padding: 0 20px;
        }

        .card {
            background: white;
            padding: 24px;
            margin-bottom: 20px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        }

        .success {
            background: #e8f5e9;
            padding: 12px;
            margin-bottom: 16px;
        }

        .error {
            background: #ffebee;
            padding: 12px;
            margin-bottom: 16px;
        }

        button {
            cursor: pointer;
        }

        .badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 999px;
            font-size: 13px;
            font-weight: bold;
        }

        .badge-pending {
            background: #fff3cd;
            color: #856404;
        }

        .badge-confirmed {
            background: #d4edda;
            color: #155724;
        }

        .badge-cancelled {
            background: #f8d7da;
            color: #721c24;
        }

        .badge-paid {
            background: #d1ecf1;
            color: #0c5460;
        }

        .badge-failed {
            background: #f8d7da;
            color: #721c24;
        }

        .badge-refunded {
            background: #e2e3e5;
            color: #383d41;
        }

        .btn {
            display: inline-block;
            padding: 10px 16px;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            text-decoration: none;
            cursor: pointer;
            transition: 0.2s;
        }

        .btn:hover {
            opacity: 0.85;
        }

        .btn-primary {
            background: #333;
            color: white;
        }

        .btn-secondary {
            background: #e5e7eb;
            color: #333;
        }

        .btn-danger {
            background: #dc3545;
            color: white;
        }

        .form-group {
            margin-bottom: 18px;
        }

        .form-group label {
            display: block;
            margin-bottom: 6px;
            font-weight: bold;
        }

        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            box-sizing: border-box;
            padding: 10px 12px;
            border: 1px solid #ccc;
            border-radius: 8px;
            font-size: 14px;
        }

        .form-group input:focus,
        .form-group textarea:focus,
        .form-group select:focus {
            outline: none;
            border-color: #666;
        }

        .card h1 {
            margin-top: 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            padding: 12px;
            border-bottom: 1px solid #ddd;
            text-align: left;
        }

        th {
            background: #f3f4f6;
        }

        .hero-card {
            background: white;
            padding: 56px 48px;
            border-radius: 18px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            margin-bottom: 32px;
        }

        .hero-label {
            font-size: 13px;
            font-weight: bold;
            letter-spacing: 0.15em;
            color: #777;
            margin-bottom: 12px;
        }

        .hero-card h1 {
            margin: 0 0 20px;
            font-size: 42px;
            line-height: 1.35;
        }

        .hero-description {
            max-width: 650px;
            color: #555;
            line-height: 1.8;
            margin-bottom: 28px;
        }

        .hero-actions {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }

        .home-features {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
        }

        .home-features .card {
            margin-bottom: 0;
        }

        .home-features h2 {
            margin-top: 0;
            font-size: 20px;
        }

        .event-image-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 16px;
            width: 100%;
            max-width: 760px;
            margin: 20px auto 28px;
        }

        .event-image-grid>img {
            display: block;
            width: 100%;
            height: 240px;
            max-width: 100%;
            object-fit: cover;
            border-radius: 10px;
        }

        /* イベント一覧のメイン画像 */
        .event-list-image {
            display: block;
            width: 100%;
            max-width: 420px;
            height: 220px;
            object-fit: cover;
            border-radius: 10px;
            margin: 14px 0;
        }

        .btn-online-payment {
            background: #7dd3fc;
            color: #0c4a6e;
        }

        .btn-online-payment:hover {
            background: #38bdf8;
            color: #082f49;
        }


        @media (max-width: 700px) {
            .hero-card {
                padding: 32px 24px;
            }

            .hero-card h1 {
                font-size: 30px;
            }

            .home-features {
                grid-template-columns: 1fr;
            }

            .event-image-grid {
                grid-template-columns: 1fr;
                max-width: 420px;
            }

            .event-image-grid>img {
                height: 220px;
            }

            .event-list-image {
                max-width: 100%;
                height: 200px;
            }
        }
    </style>
</head>

<body>

    <header id="site-header"
        style="
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        z-index: 1000;
        background: white;
        border-bottom: 1px solid #e5e7eb;
    ">
        <div
            style="
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 12px 20px;
        ">
            <x-button type="button" variant="secondary" id="back-button" disabled>
                ← 戻る
            </x-button>

            @auth
                <div style="
                font-weight: 600;
                white-space: nowrap;
            ">
                    ログイン中：
                    {{ auth()->user()->name }}
                    （ID: {{ auth()->user()->id }}）
                </div>
            @endauth
            <button id="pwa-install-button" type="button"
                style="
        display: none;
        background: #7dd3fc;
        color: #0c4a6e;
        border: none;
        border-radius: 8px;
        padding: 10px 16px;
        font-weight: bold;
        cursor: pointer;
    ">
                アプリをインストール
            </button>

            <div id="ios-install-guide"
                style="
        display: none;
        margin-top: 10px;
        padding: 12px;
        background: #e0f2fe;
        color: #0c4a6e;
        border-radius: 8px;
        font-size: 14px;
    ">
                iPhoneでは、Safariの共有ボタンから
                「ホーム画面に追加」を選ぶと、
                このアプリをホーム画面から起動できます。
            </div>


            <x-button type="button" variant="secondary" id="navigation-toggle">
                ☰ メニュー
            </x-button>
        </div>

        <div id="navigation-area">
            <nav>
                <a href="{{ route('events.index') }}" class="{{ request()->routeIs('events.*') ? 'active' : '' }}">
                    イベント一覧
                </a>
                @auth
                    <a href="{{ route('event-participants.index') }}"
                        class="{{ request()->routeIs('event-participants.*') ? 'active' : '' }}">
                        自分の参加予定
                    </a>
                    <a href="{{ route('chats.index') }}" class="{{ request()->routeIs('chats.*') ? 'active' : '' }}">
                        チャット一覧
                    </a>
                    @if (Auth::user()->is_admin)
                        <a href="{{ route('admin.events.index') }}"
                            class="{{ request()->routeIs('admin.events.*') ? 'active' : '' }}">
                            イベント管理
                        </a>
                    @endif
                    <a href="{{ route('account.edit') }}" class="{{ request()->routeIs('account.*') ? 'active' : '' }}">
                        アカウント設定
                    </a>

                    <form method="POST" action="{{ route('logout') }}" style="display: inline;">
                        @csrf

                        <button type="submit">
                            ログアウト
                        </button>
                    </form>
                @else
                    <a href="{{ route('login') }}">
                        ログイン
                    </a>

                    <a href="{{ route('register') }}">
                        新規登録
                    </a>
                @endauth
            </nav>

        </div>
    </header>
    <main id="main-content">

        @if (session('success'))
            <div class="success">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="error">
                {{ session('error') }}
            </div>
        @endif

        @yield('content')

    </main>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const backButton = document.getElementById('back-button');

            if (!backButton) {
                return;
            }

            const referrer = document.referrer;

            if (!referrer) {
                return;
            }

            const previousUrl = new URL(referrer);

            const cameFromThisSite =
                previousUrl.origin === window.location.origin;

            if (!cameFromThisSite) {
                return;
            }

            backButton.disabled = false;

            backButton.addEventListener('click', function() {
                window.history.back();
            });
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const header = document.getElementById('site-header');
            const mainContent = document.getElementById('main-content');
            const toggleButton = document.getElementById('navigation-toggle');
            const navigationArea = document.getElementById('navigation-area');

            function updateMainPadding() {
                if (!header || !mainContent) {
                    return;
                }

                const headerHeight = header.offsetHeight;

                mainContent.style.paddingTop =
                    `${headerHeight + 20}px`;

                document.documentElement.style.setProperty(
                    '--site-header-height',
                    `${headerHeight}px`
                );
            }
            updateMainPadding();

            if (!toggleButton || !navigationArea) {
                return;
            }

            toggleButton.addEventListener('click', function() {
                const isHidden =
                    navigationArea.style.display === 'none';

                if (isHidden) {
                    navigationArea.style.display = '';
                    toggleButton.textContent = '✕ 閉じる';
                } else {
                    navigationArea.style.display = 'none';
                    toggleButton.textContent = '☰ メニュー';
                }

                updateMainPadding();
            });

            window.addEventListener('resize', updateMainPadding);
        });
    </script>
</body>

</html>
