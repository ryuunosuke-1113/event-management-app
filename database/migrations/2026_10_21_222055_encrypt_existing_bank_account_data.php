<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        $fields = [
            'bank_name',
            'branch_name',
            'account_type',
            'account_number',
            'account_holder_name',
        ];

        DB::table('organizer_bank_accounts')
            ->orderBy('id')
            ->chunkById(100, function ($accounts) use ($fields) {
                foreach ($accounts as $account) {
                    $updates = [];

                    foreach ($fields as $field) {
                        if ($account->{$field} !== null) {
                            $updates[$field] = Crypt::encryptString(
                                $account->{$field}
                            );
                        }
                    }

                    DB::table('organizer_bank_accounts')
                        ->where('id', $account->id)
                        ->update($updates);
                }
            });

        DB::table('withdrawal_requests')
            ->orderBy('id')
            ->chunkById(100, function ($requests) use ($fields) {
                foreach ($requests as $request) {
                    $updates = [];

                    foreach ($fields as $field) {
                        if ($request->{$field} !== null) {
                            $updates[$field] = Crypt::encryptString(
                                $request->{$field}
                            );
                        }
                    }

                    if ($updates) {
                        DB::table('withdrawal_requests')
                            ->where('id', $request->id)
                            ->update($updates);
                    }
                }
            });
    }

    public function down(): void
    {
        //
    }
};