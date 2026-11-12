<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chat_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')->constrained('chat_conversations')->cascadeOnDelete();
            $table->enum('sender', ['visitor', 'agent', 'system']);
            $table->text('body');
            $table->string('attachment_path', 500)->nullable();
            $table->unsignedBigInteger('telegram_message_id')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            // Índice compuesto para polling eficiente (WHERE conversation_id = ? AND id > ?)
            $table->index(['conversation_id', 'id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_messages');
    }
};
