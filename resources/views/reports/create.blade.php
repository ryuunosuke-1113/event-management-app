@extends('layouts.app')

@section('title', 'ユーザーを通報')

@section('content')

    <h1>{{ $user->name }}さんを通報</h1>

    <p>
        通報内容は管理者が確認します。
    </p>

    <form method="POST" action="{{ route('reports.store', $user) }}">
        @csrf

        <div style="margin-bottom: 20px;">
            <label for="reason">
                通報理由
            </label>

            <br>

            <select id="reason" name="reason" required>
                <option value="">選択してください</option>

                <option value="nuisance" @selected(old('reason') === 'nuisance')>
                    迷惑行為
                </option>

                <option value="inappropriate" @selected(old('reason') === 'inappropriate')>
                    不適切な発言・行動
                </option>

                <option value="impersonation" @selected(old('reason') === 'impersonation')>
                    虚偽・なりすまし
                </option>

                <option value="event_trouble" @selected(old('reason') === 'event_trouble')>
                    イベント上のトラブル
                </option>

                <option value="other" @selected(old('reason') === 'other')>
                    その他
                </option>
            </select>

            @error('reason')
                <div>{{ $message }}</div>
            @enderror
        </div>

        <div style="margin-bottom: 20px;">
            <label for="details">
                詳細
            </label>

            <br>

            <textarea id="details" name="details" rows="8" maxlength="3000" required style="width: 100%; max-width: 700px;">{{ old('details') }}</textarea>

            @error('details')
                <div>{{ $message }}</div>
            @enderror
        </div>

        <x-button type="submit" variant="danger">
            通報する
        </x-button>
    </form>

@endsection
