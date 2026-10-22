@extends('layouts.app')

@section('content')
    <div class="card">
        <h1>出金申請一覧</h1>
        @php
            $statusLabels = [
                'pending' => '申請中',
                'approved' => '承認済み',
                'paid' => '振込済み',
                'rejected' => '却下',
            ];
        @endphp

        @forelse ($withdrawalRequests as $request)
            <div style="padding: 16px 0; border-bottom: 1px solid #ddd;">
                <div>
                    主催者：{{ $request->user->name }}
                </div>

                <div>
                    金額：{{ number_format($request->amount) }}円
                </div>

                <div>
                    状態：{{ $statusLabels[$request->status] ?? $request->status }} </div>
                @if ($request->transfer_reference)
                    <div>
                        振込管理番号：{{ $request->transfer_reference }}
                    </div>
                @endif

                @if ($request->transfer_note)
                    <div>
                        振込メモ：{{ $request->transfer_note }}
                    </div>
                @endif
                @if ($request->paid_at)
                    <div>
                        振込日時：{{ $request->paid_at->format('Y/m/d H:i') }}
                    </div>
                @endif
                @if ($request->bank_name)
                    <div style="margin-top: 12px;">
                        <strong>振込先口座</strong>

                        <div>銀行名：{{ $request->bank_name }}</div>
                        <div>支店名：{{ $request->branch_name }}</div>
                        <div>
                            口座種別：
                            {{ $request->account_type === 'ordinary' ? '普通' : '当座' }}
                        </div>
                        <div>口座番号：{{ $request->account_number }}</div>
                        <div>口座名義：{{ $request->account_holder_name }}</div>
                    </div>
                @else
                    <p style="margin-top: 12px;">
                        申請時点の振込先口座情報は保存されていません。
                    </p>
                @endif
                @if ($request->status === 'pending' && $request->bank_name)
                    <div
                        style="
        display: flex;
        gap: 8px;
        margin-top: 8px;
        align-items: center;
    ">
                        <form method="POST" action="{{ route('admin.withdrawal-requests.approve', $request) }}"
                            onsubmit="return confirm('この出金申請を承認しますか？');">
                            @csrf
                            @method('PATCH')

                            <x-button type="submit" variant="secondary">
                                承認
                            </x-button>
                        </form>

                        <form method="POST" action="{{ route('admin.withdrawal-requests.reject', $request) }}"
                            onsubmit="return confirm('この出金申請を却下しますか？');">
                            @csrf
                            @method('PATCH')

                            <input type="text" name="admin_note" placeholder="却下理由" required>

                            <x-button type="submit" variant="danger">
                                却下
                            </x-button>
                        </form>
                    </div>
                @endif
                @if ($request->status === 'approved' && $request->bank_name)
                    <form method="POST" action="{{ route('admin.withdrawal-requests.paid', $request) }}"
                        onsubmit="return confirm('実際の振込が完了していますか？\n振込済みにすると、主催者の売上台帳から出金額が差し引かれます。');"
                        style="margin-top: 8px;">
                        @csrf
                        @method('PATCH')

                        <div style="margin-bottom: 8px;">
                            <label>
                                振込管理番号
                                <input type="text" name="transfer_reference" maxlength="255">
                            </label>
                        </div>

                        <div style="margin-bottom: 8px;">
                            <label>
                                振込メモ
                                <input type="text" name="transfer_note">
                            </label>
                        </div>

                        <x-button type="submit" variant="primary">
                            振込済みにする
                        </x-button>
                    </form>
                @endif
                @if (in_array($request->status, ['pending', 'approved']) && !$request->bank_name)
                    <p>
                        申請時点の振込先口座情報が保存されていないため、処理できません。
                    </p>
                @endif
                <div>
                    申請日時：
                    {{ $request->requested_at?->format('Y/m/d H:i') }}
                </div>

                @if ($request->admin_note)
                    <div>
                        管理者メモ：{{ $request->admin_note }}
                    </div>
                @endif
            </div>
        @empty
            <p>出金申請はありません。</p>
        @endforelse
    </div>
@endsection
