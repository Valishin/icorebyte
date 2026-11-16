<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Security fix: remove visitor_token_cipher (raw token was recoverable via Crypt).
 * Introduce one-time-use resume_nonce_hash and resume_token_hash instead.
 *
 * Raw tokens NEVER stored. Only SHA-256 hashes. Resume flow uses a signed URL
 * with a one-time nonce; the new token is returned only in the URL fragment
 * (never in a query string, so it stays out of server logs and the Referer header).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('chat_conversations', function (Blueprint $table) {
            // Remove the encrypted cipher that allowed raw-token recovery
            $table->dropColumn('visitor_token_cipher');

            // One-time nonce for email resume links (hashed, invalidated on first use)
            $table->string('resume_nonce_hash', 64)->nullable()->after('notification_email_sent_at');

            // Hash of the token generated on resume (device B); original visitor_token stays
            // valid so the original device is not logged out
            $table->string('resume_token_hash', 64)->nullable()->after('resume_nonce_hash');
        });
    }

    public function down(): void
    {
        Schema::table('chat_conversations', function (Blueprint $table) {
            $table->dropColumn(['resume_nonce_hash', 'resume_token_hash']);
            $table->text('visitor_token_cipher')->nullable()->after('visitor_token');
        });
    }
};
