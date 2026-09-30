@extends('layouts.app')

@section('title', '運営からのメッセージ')

@section('content')

    <h1>運営からのメッセージ</h1>

    @forelse ($notices as $notice)
        <div class="card"
            style="
                margin-bottom: 16px;
                {{ !$notice->read_at ? 'border: 2px solid #dc3545;' : '' }}
            ">
            <p>
                <strong>
                    {{ $notice->category_label }}
                </strong>

                @if (!$notice->read_at)
                    <span
                        style="
                            margin-left: 8px;
                            color: #dc3545;
                            font-weight: bold;
                        ">
                        未読
                    </span>
                @endif
            </p>

            <h2>
                {{ $notice->title }}
            </h2>

            <p>
                {{ $notice->created_at->format('Y/m/d H:i') }}
            </p>

            <x-link-button href="{{ route('admin-notices.show', $notice) }}">
                詳細を見る
            </x-link-button>
        </div>

    @empty

        <div class="card">
            <p>
                運営からのメッセージはありません。
            </p>
        </div>
    @endforelse

@endsection
