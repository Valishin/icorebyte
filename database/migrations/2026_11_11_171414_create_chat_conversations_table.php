<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chat_conversations', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('visitor_token', 64)->unique(); // sha-256 del token en claro
            $table->enum('status', ['open', 'closed'])->default('open');
            $table->string('visitor_name', 100);
            $table->string('visitor_phone', 30)->nullable();
            $table->string('visitor_email', 150)->nullable();
            $table->string('page_url', 500)->nullable();
            $table->string('ip_hash', 64);
            $table->timestamp('consent_at');
            $table->timestamp('last_visitor_message_at')->nullable();
            $table->timestamp('last_agent_message_at')->nullable();
            $table->timestamp('visitor_last_seen_at')->nullable();
            $table->timestamp('agent_last_read_at')->nullable();
            $table->timestamp('visitor_last_read_at')->nullable();
            $table->timestamps();

            // Índices para consultas frecuentes del panel de agente
            $table->index('status');
            $table->index('last_visitor_message_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_conversations');
    }
};
