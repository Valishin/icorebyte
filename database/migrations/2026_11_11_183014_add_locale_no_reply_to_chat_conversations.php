<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('chat_conversations', function (Blueprint $table) {
            $table->string('locale', 10)->default('es')->after('status');
            $table->timestamp('no_reply_warned_at')->nullable()->after('visitor_last_read_at');
        });
    }

    public function down(): void
    {
        Schema::table('chat_conversations', function (Blueprint $table) {
            $table->dropColumn(['locale', 'no_reply_warned_at']);
        });
    }
};
