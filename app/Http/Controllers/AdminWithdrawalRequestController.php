<?php

namespace App\Http\Controllers;

use App\Models\WithdrawalRequest;
use App\Models\OrganizerTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class AdminWithdrawalRequestController extends Controller
{
    public function index()
    {
        $withdrawalRequests = WithdrawalRequest::with([
            'user.organizerBankAccount',
        ])
            ->latest('requested_at')
            ->get();
        return view('admin.withdrawal-requests.index', compact(
            'withdrawalRequests'
        ));
    }
    public function approve(WithdrawalRequest $withdrawalRequest)
    {
        if (!$withdrawalRequest->bank_name) {
            return back()->withErrors([
                'withdrawal' => '申請時点の振込先口座情報が保存されていないため、承認できません。',
            ]);
        }

        if ($withdrawalRequest->status !== 'pending') {
            return back()->withErrors([
                'withdrawal' => 'この申請は承認できる状態ではありません。',
            ]);
        }

        $withdrawalRequest->update([
            'status' => 'approved',
            'processed_at' => now(),
        ]);

        return back()->with('success', '出金申請を承認しました。');
    }
    public function markAsPaid(Request $request, WithdrawalRequest $withdrawalRequest)
    {
        $validated = $request->validate([
            'transfer_reference' => ['nullable', 'string', 'max:255'],
            'transfer_note' => ['nullable', 'string', 'max:2000'],
        ]);

        try {
            DB::transaction(function () use ($withdrawalRequest, $validated) {
                $lockedRequest = WithdrawalRequest::whereKey($withdrawalRequest->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if (!$lockedRequest->bank_name) {
                    throw new \RuntimeException(
                        '申請時点の振込先口座情報が保存されていないため、振込済みにできません。'
                    );
                }

                if ($lockedRequest->status !== 'approved') {
                    throw new \RuntimeException(
                        'この出金申請は、すでに処理済みか、振込済みにできる状態ではありません。'
                    );
                }

                OrganizerTransaction::create([
                    'user_id' => $lockedRequest->user_id,
                    'event_id' => null,
                    'type' => 'payout',
                    'amount' => -$lockedRequest->amount,
                    'description' => '出金',
                    'occurred_at' => now(),
                ]);

                $lockedRequest->update([
                    'status' => 'paid',
                    'processed_at' => now(),
                    'paid_at' => now(),
                    'transfer_reference' => $validated['transfer_reference'] ?? null,
                    'transfer_note' => $validated['transfer_note'] ?? null,
                ]);
            });
        } catch (\RuntimeException $e) {
            return back()->withErrors([
                'withdrawal' => $e->getMessage(),
            ]);
        }

        return back()->with('success', '出金申請を振込済みにしました。');
    }
    public function reject(Request $request, WithdrawalRequest $withdrawalRequest)
    {
        if ($withdrawalRequest->status !== 'pending') {
            return back()->withErrors([
                'withdrawal' => '申請中の出金申請のみ却下できます。',
            ]);
        }

        $validated = $request->validate([
            'admin_note' => ['required', 'string', 'max:1000'],
        ]);

        $withdrawalRequest->update([
            'status' => 'rejected',
            'admin_note' => $validated['admin_note'],
            'processed_at' => now(),
        ]);

        return back()->with('success', '出金申請を却下しました。');
    }
}