<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('report_messages', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_report_id')
                ->constrained('user_reports')
                ->cascadeOnDelete();

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->text('body');

            $table->dateTime('read_at')
                ->nullable();

            $table->timestamps();

            $table->index([
                'user_report_id',
                'created_at',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('report_messages');
    }
};