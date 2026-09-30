<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('admin_notices', function (Blueprint $table) {
            $table->foreignId('user_id')
                ->after('id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->foreignId('admin_id')
                ->nullable()
                ->after('user_id')
                ->constrained('users')
                ->nullOnDelete();

            $table->string('category')
                ->after('admin_id');

            $table->string('title')
                ->after('category');

            $table->text('body')
                ->after('title');

            $table->foreignId('event_id')
                ->nullable()
                ->after('body')
                ->constrained('events')
                ->nullOnDelete();

            $table->timestamp('read_at')
                ->nullable()
                ->after('event_id');

            $table->index([
                'user_id',
                'read_at',
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('admin_notices', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropForeign(['admin_id']);
            $table->dropForeign(['event_id']);

            $table->dropIndex([
                'user_id',
                'read_at',
            ]);

            $table->dropColumn([
                'user_id',
                'admin_id',
                'category',
                'title',
                'body',
                'event_id',
                'read_at',
            ]);
        });
    }
};