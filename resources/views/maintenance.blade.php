<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>メンテナンス中</title>
</head>

<body>
    <main>
        <h1>ただいまメンテナンス中です</h1>

        <p>
            {{ $setting->message ?? 'サービス改善のため、ただいまメンテナンスを行っています。' }}
        </p>

        @if ($setting->ends_at)
            <p>
                メンテナンス終了予定：
                {{ $setting->ends_at->format('Y年m月d日 H:i') }}頃
            </p>
        @endif

        <p>
            ご不便をおかけしますが、しばらくお待ちください。
        </p>
    </main>
</body>

</html>
