<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('admin_notices', function (Blueprint $table) {
            $table->id();

            // 通知を受け取るユーザー
            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            // 通知を作成した管理者
            $table->foreignId('admin_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            // event_cancelled / warning_lifted /
            // suspension_lifted / other
            $table->string('category');

            $table->string('title');

            $table->text('body');

            // 関連イベントがある場合
            $table->foreignId('event_id')
                ->nullable()
                ->constrained('events')
                ->nullOnDelete();

            $table->timestamp('read_at')
                ->nullable();

            $table->timestamps();

            $table->index([
                'user_id',
                'read_at',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_notices');
    }
};