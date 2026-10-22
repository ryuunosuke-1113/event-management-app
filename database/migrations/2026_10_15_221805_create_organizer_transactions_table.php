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
        Schema::create('organizer_transactions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('event_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->string('type');

            $table->bigInteger('amount');

            $table->string('description')
                ->nullable();

            $table->timestamp('occurred_at');

            $table->timestamps();

            $table->index(['user_id', 'occurred_at']);
            $table->index('type');
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('organizer_transactions');
    }
};