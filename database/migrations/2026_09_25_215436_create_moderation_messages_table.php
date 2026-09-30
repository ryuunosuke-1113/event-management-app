<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('moderation_messages', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_moderation_action_id')
                ->constrained('user_moderation_actions')
                ->cascadeOnDelete();

            // 発言者（制御対象ユーザー or 管理者）
            $table->foreignId('user_id')
                ->constrained('users')
                ->restrictOnDelete();

            $table->text('body');

            $table->timestamp('read_at')
                ->nullable();

            $table->timestamps();

            $table->index([
                'user_moderation_action_id',
                'created_at',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('moderation_messages');
    }
};