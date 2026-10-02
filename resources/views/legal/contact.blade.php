@extends('layouts.app')

@section('content')
    <div class="card">
        <h1>お問い合わせ</h1>

        <p>
            本サービスに関するお問い合わせは、
            以下のメールアドレスまでご連絡ください。
        </p>

        <div style="margin-top: 24px;">
            <p>
                <strong>お問い合わせ先：</strong><br>

                <a href="mailto:holidayevent1113@gmail.com">
                    holidayevent1113@gmail.com
                </a>
            </p>
        </div>

        <div style="margin-top: 24px;">
            <p>
                お問い合わせの際は、必要に応じて以下の内容をご記載ください。
            </p>

            <ul>
                <li>お名前またはユーザー名</li>
                <li>お問い合わせ内容</li>
                <li>対象となるイベント名</li>
                <li>発生している問題の状況</li>
            </ul>
        </div>

        <div style="margin-top: 24px;">
            <p>
                内容を確認のうえ、必要に応じて返信いたします。
            </p>
        </div>
    </div>
@endsection
