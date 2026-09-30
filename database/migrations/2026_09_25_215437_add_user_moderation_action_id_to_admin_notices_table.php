<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('admin_notices', function (Blueprint $table) {
            $table->foreignId('user_moderation_action_id')
                ->nullable()
                ->after('event_id')
                ->constrained('user_moderation_actions')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('admin_notices', function (Blueprint $table) {
            $table->dropForeign([
                'user_moderation_action_id',
            ]);

            $table->dropColumn(
                'user_moderation_action_id'
            );
        });
    }
};