<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('user_reports', function (Blueprint $table) {
            $table->id();

            // 通報したユーザー
            $table->foreignId('reporter_id')
                ->constrained('users')
                ->cascadeOnDelete();

            // 通報されたユーザー
            $table->foreignId('reported_user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            // 通報理由
            $table->string('reason');

            // 詳細
            $table->text('details');

            // 対応状況
            $table->string('status')
                ->default('open');

            // 担当管理者
            $table->foreignId('handled_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();

            $table->index([
                'status',
                'created_at',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_reports');
    }
};