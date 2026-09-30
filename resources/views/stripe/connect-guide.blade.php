@extends('layouts.app')

@section('title', 'Stripe連携について')

@section('content')

    <div class="card">

        <h1>Stripe連携について</h1>

        <p>
            有料イベントを主催するには、
            参加費の受け取りのためにStripeとの連携が必要です。
        </p>

        <p>
            Stripeの登録画面では、主に以下の情報を入力します。
        </p>

        <ul>
            <li>業種</li>
            <li>ウェブサイト情報</li>
            <li>本人確認情報</li>
            <li>振込先の銀行口座情報</li>
        </ul>

        <p>
            一部の情報は、このサイトからあらかじめ入力されます。
            一度連携が完了すれば、イベントを作成するたびに
            同じ登録を繰り返す必要はありません。
        </p>

        <div
            style="
                margin-top: 24px;
                padding: 16px;
                background: #f5f5f5;
                border-radius: 8px;
            ">
            <strong>入力前にご確認ください</strong>

            <p style="margin-bottom: 0;">
                Stripeの画面へ移動します。
                登録内容はStripeの確認に使用されるため、
                実際の情報を入力してください。
            </p>
        </div>

        <div
            style="
                display: flex;
                gap: 12px;
                flex-wrap: wrap;
                margin-top: 24px;
            ">
            <x-link-button href="{{ route('stripe.connect.start') }}" variant="primary">
                Stripe連携を開始する
            </x-link-button>

            <x-link-button href="{{ route('organizer.events.index') }}" variant="secondary">
                戻る
            </x-link-button>
        </div>

    </div>

@endsection
