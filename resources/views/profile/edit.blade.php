@extends('layouts.app')

@section('content')
    <div class="card">
        <h1>プロフィール編集</h1>

        <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label for="name">
                    名前
                </label>

                <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" required>

                @error('name')
                    <p>{{ $message }}</p>
                @enderror
            </div>

            <div class="form-group">
                <label for="photo">
                    プロフィール写真
                </label>

                @if ($profile->photo_path)
                    <div style="margin-bottom: 12px;">
                        <img src="{{ asset('storage/' . $profile->photo_path) }}" alt="プロフィール写真"
                            style="
                                width: 120px;
                                height: 120px;
                                object-fit: cover;
                                border-radius: 50%;
                            ">
                    </div>
                @endif

                <input type="file" id="photo" name="photo" accept="image/jpeg,image/png,image/webp">

                @error('photo')
                    <p>{{ $message }}</p>
                @enderror
            </div>

            <div class="form-group">
                <label>
                    <input type="checkbox" name="show_participation_history" value="1"
                        {{ old('show_participation_history', $profile->show_participation_history) ? 'checked' : '' }}>

                    過去に参加したイベントをプロフィールに公開する
                </label>

                @error('show_participation_history')
                    <p>{{ $message }}</p>
                @enderror
            </div>
            <x-button type="submit" variant="primary">
                プロフィールを保存
            </x-button>
        </form>
    </div>
@endsection
