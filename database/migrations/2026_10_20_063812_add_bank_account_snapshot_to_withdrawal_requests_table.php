<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('withdrawal_requests', function (Blueprint $table) {
            $table->string('bank_name')->nullable();
            $table->string('branch_name')->nullable();
            $table->string('account_type')->nullable();
            $table->string('account_number')->nullable();
            $table->string('account_holder_name')->nullable();
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('withdrawal_requests', function (Blueprint $table) {
            $table->dropColumn([
                'bank_name',
                'branch_name',
                'account_type',
                'account_number',
                'account_holder_name',
            ]);
        });
    }
};