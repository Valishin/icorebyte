<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('telegram_outbound', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('telegram_message_id')->unique();
            $table->foreignId('conversation_id')->constrained('chat_conversations')->cascadeOnDelete();
            $table->foreignId('chat_message_id')->nullable()->constrained('chat_messages')->nullOnDelete();
            $table->string('tipo', 50); // new_conversation | visitor_message | visitor_photo | system
            $table->timestamps();

            $table->index('conversation_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('telegram_outbound');
    }
};
