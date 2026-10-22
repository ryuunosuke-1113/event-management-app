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
        Schema::table('organizer_bank_accounts', function (Blueprint $table) {
            $table->text('bank_name')->change();
            $table->text('branch_name')->change();
            $table->text('account_type')->change();
            $table->text('account_number')->change();
            $table->text('account_holder_name')->change();
        });

        Schema::table('withdrawal_requests', function (Blueprint $table) {
            $table->text('bank_name')->nullable()->change();
            $table->text('branch_name')->nullable()->change();
            $table->text('account_type')->nullable()->change();
            $table->text('account_number')->nullable()->change();
            $table->text('account_holder_name')->nullable()->change();
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('organizer_bank_accounts', function (Blueprint $table) {
            $table->string('bank_name')->change();
            $table->string('branch_name')->change();
            $table->string('account_type')->change();
            $table->string('account_number', 20)->change();
            $table->string('account_holder_name')->change();
        });

        Schema::table('withdrawal_requests', function (Blueprint $table) {
            $table->string('bank_name')->nullable()->change();
            $table->string('branch_name')->nullable()->change();
            $table->string('account_type')->nullable()->change();
            $table->string('account_number', 20)->nullable()->change();
            $table->string('account_holder_name')->nullable()->change();
        });
    }
};