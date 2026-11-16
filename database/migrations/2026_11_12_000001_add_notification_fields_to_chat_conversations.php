<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('chat_conversations', function (Blueprint $table) {
            // Encrypted raw token — used to build the signed email resume link.
            // Never returned in any API response; decrypted only inside Jobs.
            $table->text('visitor_token_cipher')->nullable()->after('visitor_token');

            // Throttle: at most 1 email per conversation per hour.
            $table->timestamp('notification_email_sent_at')->nullable()->after('visitor_last_read_at');
        });
    }

    public function down(): void
    {
        Schema::table('chat_conversations', function (Blueprint $table) {
            $table->dropColumn(['visitor_token_cipher', 'notification_email_sent_at']);
        });
    }
};
