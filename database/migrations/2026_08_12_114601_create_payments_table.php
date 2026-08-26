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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('event_participant_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->unsignedInteger('amount');

            $table->string('stripe_checkout_session_id')
                ->nullable()
                ->unique();

            $table->string('stripe_payment_intent_id')
                ->nullable()
                ->unique();

            $table->string('status')
                ->default('pending');

            $table->timestamp('paid_at')
                ->nullable();

            $table->timestamp('refunded_at')
                ->nullable();

            $table->timestamps();

            $table->unique('event_participant_id');
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};