<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('report_message_attachments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('report_message_id')
                ->constrained('report_messages')
                ->cascadeOnDelete();

            $table->string('file_path');

            $table->string('original_name');

            $table->string('mime_type', 100);

            $table->unsignedBigInteger('file_size');

            $table->timestamps();

            $table->index([
                'report_message_id',
                'created_at',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('report_message_attachments');
    }
};