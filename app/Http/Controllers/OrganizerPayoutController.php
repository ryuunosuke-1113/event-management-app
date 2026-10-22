<?php

namespace App\Http\Controllers;

use App\Models\OrganizerTransaction;
use App\Models\WithdrawalRequest;
use Illuminate\Http\Request;
use App\Models\OrganizerBankAccount;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class OrganizerPayoutController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $bankAccount = OrganizerBankAccount::where('user_id', $user->id)->first();

        $ledgerBalance = OrganizerTransaction::where('user_id', $user->id)
            ->sum('amount');

        $reservedAmount = WithdrawalRequest::where('user_id', $user->id)
            ->whereIn('status', ['pending', 'approved'])
            ->sum('amount');

        $balance = $ledgerBalance - $reservedAmount;

        $transactions = OrganizerTransaction::where('user_id', $user->id)
            ->latest('occurred_at')
            ->get();

        $withdrawalRequests = WithdrawalRequest::where('user_id', $user->id)
            ->latest('requested_at')
            ->get();

        return view('organizer.payouts.index', compact(
            'balance',
            'transactions',
            'withdrawalRequests',
            'bankAccount'
        ));
    }
    public function store(Request $request)
    {
        $minimumWithdrawalAmount = 1000;

        $validated = $request->validate([
            'amount' => [
                'required',
                'integer',
                'min:' . $minimumWithdrawalAmount,
            ],
        ], [
            'amount.min' => '出金額は1,000円以上で入力してください。',
        ]);

        $user = $request->user();

        try {
            DB::transaction(function () use ($user, $validated, $minimumWithdrawalAmount) {
                // 同じ主催者からの出金申請を同時処理しないようにする
                $lockedUser = User::whereKey($user->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                $bankAccount = OrganizerBankAccount::where(
                    'user_id',
                    $lockedUser->id
                )->first();

                if (!$bankAccount) {
                    throw new \RuntimeException(
                        '出金申請をするには、先に振込先口座を登録してください。'
                    );
                }

                $ledgerBalance = OrganizerTransaction::where(
                    'user_id',
                    $lockedUser->id
                )->sum('amount');

                $reservedAmount = WithdrawalRequest::where(
                    'user_id',
                    $lockedUser->id
                )
                    ->whereIn('status', ['pending', 'approved'])
                    ->sum('amount');

                $availableBalance = $ledgerBalance - $reservedAmount;

                if ($validated['amount'] > $availableBalance) {
                    throw new \RuntimeException(
                        '出金可能残高を超える金額は申請できません。'
                    );
                }

                WithdrawalRequest::create([
                    'user_id' => $lockedUser->id,
                    'amount' => $validated['amount'],
                    'status' => 'pending',
                    'requested_at' => now(),

                    // 申請時点の口座情報を保存
                    'bank_name' => $bankAccount->bank_name,
                    'branch_name' => $bankAccount->branch_name,
                    'account_type' => $bankAccount->account_type,
                    'account_number' => $bankAccount->account_number,
                    'account_holder_name' => $bankAccount->account_holder_name,
                ]);
            });
        } catch (\RuntimeException $e) {
            return back()->withErrors([
                'amount' => $e->getMessage(),
            ]);
        }

        return back()->with(
            'success',
            '出金申請を受け付けました。'
        );
    }
}