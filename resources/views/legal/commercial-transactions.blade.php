@extends('layouts.app')

@section('content')
    <div class="card">
        <h1>特定商取引法に基づく表記</h1>

        <div style="margin-top: 24px;">
            <p>
                <strong>販売事業者：</strong><br>
                内藤　竜飛
            </p>

            <p>
                <strong>運営責任者：</strong><br>
                内藤　竜飛
            </p>

            <p>
                <strong>所在地：</strong><br>
                請求があった場合は遅滞なく開示いたします。
            </p>

            <p>
                <strong>電話番号：</strong><br>
                請求があった場合は遅滞なく開示いたします。
            </p>


            <p>
                <strong>メールアドレス：</strong><br>

                <a href="mailto:holidayevent1113@gmail.com">
                    holidayevent1113@gmail.com
                </a>
            </p>
            <p>
                <strong>商品代金以外に必要な料金：</strong><br>
                インターネット接続に必要な通信料等は、
                利用者の負担となります。
            </p>

            <p>
                <strong>支払方法：</strong><br>
                クレジットカード決済
            </p>

            <p>
                <strong>支払時期：</strong><br>
                イベント参加申込時に決済されます。
            </p>

            <p>
                <strong>サービス提供時期：</strong><br>
                各イベントページに記載された開催日時
            </p>

            <p>
                <strong>キャンセル・返金について：</strong><br>
                参加者都合によるキャンセルは、
                当サイト共通のキャンセルポリシーに従います。
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
                主催者都合でイベントが中止された場合は、
                参加費の
                {{ config('event.cancel_policy.organizer_cancelled') }}%
                を返金します。
            </p>
        </div>
    </div>
@endsection
