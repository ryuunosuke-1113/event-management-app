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
        Schema::create('withdrawal_requests', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->bigInteger('amount');

            $table->string('status')
                ->default('pending');

            $table->text('admin_note')
                ->nullable();

            $table->timestamp('requested_at');

            $table->timestamp('processed_at')
                ->nullable();

            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index('requested_at');
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('withdrawal_requests');
    }
};