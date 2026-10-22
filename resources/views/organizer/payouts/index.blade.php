@extends('layouts.app')

@section('content')
    <div class="card">
        <h1>売上・出金</h1>
        @error('bank_account')
            <p>{{ $message }}</p>
        @enderror

        <p style="font-size: 24px; font-weight: bold;">
            出金可能残高：{{ number_format($balance) }}円
        </p>
        @if (!$bankAccount)
            <p>
                出金申請をするには、先に振込先口座を登録してください。
            </p>

            <a href="{{ route('organizer.bank-account.edit') }}" class="btn btn-secondary">
                振込先口座を登録
            </a>
        @elseif ($balance >= 1000)
            <form method="POST" action="{{ route('organizer.payouts.store') }}">
                @csrf

                <label for="amount">出金額</label>

                <input type="number" name="amount" id="amount" min="1000" max="{{ $balance }}" required>

                <p style="font-size: 14px;">
                    最低出金額は1,000円です。
                </p>

                <div style="display: flex; gap: 8px; align-items: center;">
                    <x-button type="submit" variant="secondary">
                        出金申請
                    </x-button>

                    <a href="{{ route('organizer.bank-account.edit', ['return_to' => request()->fullUrl()]) }}"
                        class="btn btn-secondary">
                        振込先口座を確認・変更
                    </a>
                </div>
            </form>
        @else
            <p>
                出金可能残高が1,000円以上になると、出金申請できます。
            </p>

            <a href="{{ route('organizer.bank-account.edit', ['return_to' => request()->fullUrl()]) }}"
                class="btn btn-secondary">
                振込先口座を確認・変更
            </a>
        @endif
    </div>

    <div class="card">
        <h2>取引履歴</h2>

        @forelse ($transactions as $transaction)
            <div style="padding: 12px 0; border-bottom: 1px solid #ddd;">
                <div>
                    {{ $transaction->occurred_at?->format('Y/m/d H:i') }}
                </div>

                <div>
                    種別：{{ $transaction->type }}
                </div>

                <div>
                    金額：
                    @if ($transaction->amount >= 0)
                        +
                    @endif
                    {{ number_format($transaction->amount) }}円
                </div>

                @if ($transaction->description)
                    <div>
                        {{ $transaction->description }}
                    </div>
                @endif
            </div>
        @empty
            <p>まだ取引履歴はありません。</p>
        @endforelse
    </div>

    <div class="card">
        <h2>出金申請履歴</h2>
        @php
            $statusLabels = [
                'pending' => '申請中',
                'approved' => '承認済み',
                'paid' => '振込済み',
                'rejected' => '却下',
            ];
        @endphp

        @forelse ($withdrawalRequests as $request)
            <div style="padding: 12px 0; border-bottom: 1px solid #ddd;">
                <div>
                    申請日時：{{ $request->requested_at?->format('Y/m/d H:i') }}
                </div>

                <div>
                    金額：{{ number_format($request->amount) }}円
                </div>

                <div>
                    状態：{{ $statusLabels[$request->status] ?? $request->status }} </div>
                @if ($request->paid_at)
                    <div>
                        振込日時：{{ $request->paid_at->format('Y/m/d H:i') }}
                    </div>
                @endif

                @if ($request->admin_note)
                    <div>
                        管理者メモ：{{ $request->admin_note }}
                    </div>
                @endif
            </div>
        @empty
            <p>まだ出金申請はありません。</p>
        @endforelse
    </div>
@endsection
