<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('user_moderation_actions', function (Blueprint $table) {
            $table->id();

            // 警告・停止を受けたユーザー
            $table->foreignId('user_id')
                ->constrained('users')
                ->restrictOnDelete();

            // 処分を行った管理者
            $table->foreignId('admin_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            // warning / suspension
            $table->string('action_type');

            // 本人に表示する理由・コメント
            $table->text('comment');

            // 現在も有効な処分か
            $table->boolean('is_active')
                ->default(true);

            // 解除日時
            $table->timestamp('ended_at')
                ->nullable();

            // 解除した管理者
            $table->foreignId('ended_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();

            $table->index([
                'user_id',
                'action_type',
                'is_active',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_moderation_actions');
    }
};