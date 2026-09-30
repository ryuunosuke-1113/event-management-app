@extends('layouts.app')

@section('content')
    <div class="card">
        <h1>イベント作成時の確認事項</h1>

        <p>
            イベントを作成する前に、以下の内容をご確認ください。
        </p>

        <h2>参加費と手数料について</h2>

        <p>
            イベント参加費から、
            プラットフォーム利用手数料として
            {{ config('services.stripe.platform_fee_percent') }}%
            を差し引きます。
        </p>
        @php
            $platformFeePercent = (float) config('services.stripe.platform_fee_percent');

            $examplePrice = 1000;

            $examplePlatformFee = (int) round($examplePrice * ($platformFeePercent / 100));

            $exampleOrganizerAmount = $examplePrice - $examplePlatformFee;
        @endphp

        <p>
            例：参加費が{{ number_format($examplePrice) }}円の場合、
            プラットフォーム利用手数料は
            {{ number_format($examplePlatformFee) }}円、
            主催者への受取額は
            {{ number_format($exampleOrganizerAmount) }}円です。
        </p>

        <p>
            Stripeのカード決済手数料は、
            現在の決済方式ではプラットフォーム側が負担します。
        </p>
        <h2>イベント開催について</h2>

        <p>
            イベントの内容、開催日時、場所、参加費などを正確に入力してください。
        </p>

        <h2>禁止事項</h2>

        <p>
            法令または公序良俗に反するイベント、
            虚偽の内容を含むイベントなどは禁止します。
        </p>

        <h2>キャンセルポリシー</h2>

        <p>
            参加者都合でキャンセルする場合は、
            以下の共通ルールが適用されます。
        </p>

        <ul>
            <li>
                開催日の3日前まで：
                参加費の
                {{ config('event.cancel_policy.participant.three_days_before') }}%
                を返金
            </li>

            <li>
                開催日の2日前から前日まで：
                参加費の
                {{ config('event.cancel_policy.participant.two_days_to_day_before') }}%
                を返金
            </li>

            <li>
                開催当日：
                返金なし
            </li>
        </ul>

        <p>
            主催者都合でイベントを中止した場合は、
            参加費の
            {{ config('event.cancel_policy.organizer_cancelled') }}%
            を返金します。
        </p>
        <div style="margin-top: 32px;">
            <x-link-button href="{{ route('organizer.events.create') }}" variant="secondary">
                イベント作成画面に戻る
            </x-link-button>
        </div>
    </div>
@endsection
