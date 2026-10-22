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
            $table->string('transfer_reference')->nullable();
            $table->text('transfer_note')->nullable();
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('withdrawal_requests', function (Blueprint $table) {
            $table->dropColumn([
                'transfer_reference',
                'transfer_note',
            ]);
        });
    }
};