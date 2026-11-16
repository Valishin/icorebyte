<?php

namespace App\Console\Commands\Chat;

use App\Models\ChatConversation;
use App\Services\Chat\TelegramService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * RGPD / GDPR — data minimisation.
 *
 * Deletes chat conversations whose last activity (max of last_visitor_message_at,
 * last_agent_message_at, created_at) is older than RETENTION_DAYS.
 * Also attempts to delete the corresponding Telegram messages from the chat.
 *
 * Runs daily via the scheduler (routes/console.php).
 */
class PurgeOldConversations extends Command
{
    protected $signature   = 'chat:purge {--dry-run : Print what would be deleted without deleting}';
    protected $description = 'Delete chat conversations with no activity in 90 days (RGPD/GDPR)';

    private const RETENTION_DAYS = 90;

    public function handle(): int
    {
        $cutoff   = now()->subDays(self::RETENTION_DAYS);
        $isDryRun = (bool) $this->option('dry-run');

        /*
         * Last-activity cutoff: a conversation is eligible only when ALL of
         * last_visitor_message_at, last_agent_message_at and created_at are
         * before $cutoff (or NULL, which counts as inactive).
         */
        $query = ChatConversation::where(function ($q) use ($cutoff) {
            $q->where(function ($inner) use ($cutoff) {
                $inner->where('last_visitor_message_at', '<', $cutoff)
                      ->orWhereNull('last_visitor_message_at');
            })->where(function ($inner) use ($cutoff) {
                $inner->where('last_agent_message_at', '<', $cutoff)
                      ->orWhereNull('last_agent_message_at');
            })->where('created_at', '<', $cutoff);
        });

        $count = $query->count();

        if ($count === 0) {
            $this->info('Nothing to purge.');
            return self::SUCCESS;
        }

        if ($isDryRun) {
            $this->line("Would delete {$count} conversation(s) with no activity since {$cutoff->toDateString()}.");
            return self::SUCCESS;
        }

        $telegram = app(TelegramService::class);
        $deleted  = 0;
        $files    = 0;
        $tgDeleted = 0;
        $errors   = 0;

        $query->with(['messages', 'telegramOutbound'])->chunkById(100, function ($conversations) use (
            &$deleted, &$files, &$tgDeleted, &$errors, $telegram
        ) {
            foreach ($conversations as $conv) {
                // 1. Delete Telegram messages (best-effort, non-blocking)
                foreach ($conv->telegramOutbound as $outbound) {
                    $result = $telegram->deleteMessage((int) $outbound->telegram_message_id);
                    if ($result !== null) {
                        $tgDeleted++;
                    } else {
                        Log::info('chat:purge — Telegram deleteMessage skipped/failed', [
                            'conversation_uuid'  => $conv->uuid,
                            'telegram_message_id' => $outbound->telegram_message_id,
                        ]);
                    }
                }

                // 2. Delete attachment files from private disk
                foreach ($conv->messages as $msg) {
                    if ($msg->attachment_path) {
                        try {
                            Storage::disk('private')->delete('chat/attachments/' . $msg->attachment_path);
                            $files++;
                        } catch (\Throwable $e) {
                            Log::warning('chat:purge — failed to delete attachment', [
                                'path'  => $msg->attachment_path,
                                'error' => $e->getMessage(),
                            ]);
                            $errors++;
                        }
                    }
                }

                // 3. Delete conversation (cascades: messages, telegram_outbound, telegram_updates via FK)
                try {
                    $conv->delete();
                    $deleted++;
                } catch (\Throwable $e) {
                    $this->error("Failed to delete conversation {$conv->uuid}: {$e->getMessage()}");
                    $errors++;
                }
            }
        });

        $this->info(
            "Purged {$deleted} conversation(s), {$files} file(s), {$tgDeleted} Telegram message(s)." .
            ($errors > 0 ? " Errors: {$errors}." : ''),
        );

        return $errors > 0 ? self::FAILURE : self::SUCCESS;
    }
}
