@extends('layouts.app')

@section('content')
    <div class="card">
        <h1>{{ $user->name }}さんのプロフィール</h1>

        @if ($user->profile?->photo_path)
            <div style="margin-bottom: 20px;">
                <img src="{{ asset('storage/' . $user->profile->photo_path) }}" alt="{{ $user->name }}"
                    style="
                        width: 160px;
                        height: 160px;
                        object-fit: cover;
                        border-radius: 50%;
                    ">
            </div>
        @else
            <p>プロフィール写真は登録されていません。</p>
        @endif

        <h2>自己紹介</h2>

        @if ($user->profile?->bio)
            <p style="white-space: pre-wrap;">
                {{ $user->profile->bio }}
            </p>
        @else
            <p>自己紹介はまだ登録されていません。</p>
        @endif
        @auth
            @if ($canDirectChat)
                <form method="POST" action="{{ route('direct-chat.start', $user) }}" style="margin-top: 20px;">
                    @csrf

                    <x-button type="submit" variant="primary">
                        この人とチャット
                    </x-button>
                </form>
            @endif
        @endauth
    </div>
@endsection
