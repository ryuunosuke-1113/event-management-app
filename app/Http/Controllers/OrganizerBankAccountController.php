<?php

namespace App\Http\Controllers;

use App\Models\OrganizerBankAccount;
use Illuminate\Http\Request;
use App\Models\WithdrawalRequest;

class OrganizerBankAccountController extends Controller
{
    public function edit(Request $request)
    {
        $bankAccount = OrganizerBankAccount::where('user_id', $request->user()->id)
            ->first();

        $returnTo = $request->query('return_to');

        return view('organizer.bank-account.edit', compact(
            'bankAccount',
            'returnTo'
        ));
    }
    public function update(Request $request)
    {
        $validated = $request->validate([
            'bank_name' => ['required', 'string', 'max:255'],
            'branch_name' => ['required', 'string', 'max:255'],
            'account_type' => ['required', 'in:ordinary,current'],
            'account_number' => ['required', 'string', 'max:20'],
            'account_holder_name' => ['required', 'string', 'max:255'],
        ]);

        OrganizerBankAccount::updateOrCreate(
            [
                'user_id' => $request->user()->id,
            ],
            $validated
        );

        $returnTo = $request->input('return_to');

        if ($returnTo) {
            return redirect($returnTo)
                ->with('success', '振込先口座を保存しました。');
        }

        return back()->with('success', '振込先口座を保存しました。');
    }
    public function destroy(Request $request)
    {
        $user = $request->user();

        $hasActiveWithdrawal = WithdrawalRequest::where('user_id', $user->id)
            ->whereIn('status', ['pending', 'approved'])
            ->exists();

        if ($hasActiveWithdrawal) {
            return back()->withErrors([
                'bank_account' => '申請中または承認済みの出金申請があるため、振込先口座を削除できません。',
            ]);
        }

        OrganizerBankAccount::where('user_id', $user->id)->delete();

        $returnTo = $request->input('return_to');

        if ($returnTo) {
            return redirect($returnTo)
                ->with('success', '振込先口座を削除しました。');
        }

        return back()->with('success', '振込先口座を削除しました。');
    }
}