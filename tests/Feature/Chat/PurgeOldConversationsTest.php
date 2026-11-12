<?php

namespace Tests\Feature\Chat;

use App\Models\ChatConversation;
use App\Models\TelegramOutbound;
use App\Models\TelegramUpdate;
use App\Services\Chat\TelegramService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Mockery;
use Tests\TestCase;

class PurgeOldConversationsTest extends TestCase
{
    use RefreshDatabase;

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function conv(array $attrs = []): ChatConversation
    {
        $createdAt              = $attrs['created_at'] ?? null;
        $lastVisitorMessageAt   = $attrs['last_visitor_message_at'] ?? null;
        $lastAgentMessageAt     = $attrs['last_agent_message_at'] ?? null;
        unset($attrs['created_at'], $attrs['last_visitor_message_at'], $attrs['last_agent_message_at']);

        $conv = ChatConversation::create(array_merge([
            'uuid'          => \Illuminate\Support\Str::uuid(),
            'visitor_token' => hash('sha256', bin2hex(random_bytes(16))),
            'status'        => 'open',
            'locale'        => 'es',
            'visitor_name'  => 'User',
            'ip_hash'       => hash('sha256', '127.0.0.1'),
            'consent_at'    => now(),
        ], $attrs));

        // $fillable doesn't include timestamp overrides — write directly
        $overrides = array_filter([
            'created_at'              => $createdAt,
            'last_visitor_message_at' => $lastVisitorMessageAt,
            'last_agent_message_at'   => $lastAgentMessageAt,
        ], fn ($v) => $v !== null);

        if ($overrides) {
            DB::table('chat_conversations')->where('id', $conv->id)->update($overrides);
            $conv->refresh();
        }

        return $conv;
    }

    // ── Cutoff: last activity, not created_at ─────────────────────────────────

    public function test_purge_deletes_conversations_with_no_activity_in_90_days(): void
    {
        $old = $this->conv(['created_at' => now()->subDays(91)]);

        $this->artisan('chat:purge')->assertExitCode(0);

        $this->assertNull(ChatConversation::find($old->id));
    }

    public function test_purge_keeps_conversation_with_recent_visitor_activity(): void
    {
        // Created 100 days ago but visitor messaged 2 days ago
        $conv = $this->conv([
            'created_at'            => now()->subDays(100),
            'last_visitor_message_at' => now()->subDays(2),
        ]);

        $this->artisan('chat:purge')->assertExitCode(0);

        $this->assertNotNull(ChatConversation::find($conv->id), 'recent activity must keep conversation');
    }

    public function test_purge_keeps_conversation_with_recent_agent_activity(): void
    {
        $conv = $this->conv([
            'created_at'           => now()->subDays(100),
            'last_agent_message_at' => now()->subDays(2),
        ]);

        $this->artisan('chat:purge')->assertExitCode(0);

        $this->assertNotNull(ChatConversation::find($conv->id), 'recent agent activity must keep conversation');
    }

    public function test_purge_keeps_recent_conversation(): void
    {
        $recent = $this->conv(['created_at' => now()->subDays(30)]);

        $this->artisan('chat:purge')->assertExitCode(0);

        $this->assertNotNull(ChatConversation::find($recent->id));
    }

    // ── Cascade deletes ───────────────────────────────────────────────────────

    public function test_purge_deletes_messages_of_old_conversations(): void
    {
        $old = $this->conv(['created_at' => now()->subDays(91)]);
        $old->messages()->create(['sender' => 'visitor', 'body' => 'hello']);

        $this->artisan('chat:purge')->assertExitCode(0);

        $this->assertDatabaseEmpty('chat_messages');
    }

    public function test_purge_deletes_telegram_outbound_records(): void
    {
        $this->mock(TelegramService::class, function ($mock) {
            $mock->shouldReceive('deleteMessage')->andReturn([]); // Telegram accepts
        });

        $old = $this->conv(['created_at' => now()->subDays(91)]);
        TelegramOutbound::create([
            'conversation_id'    => $old->id,
            'telegram_message_id' => 999,
            'tipo'               => 'message',
        ]);

        $this->artisan('chat:purge')->assertExitCode(0);

        $this->assertDatabaseEmpty('telegram_outbound');
    }

    public function test_purge_removes_attachment_files(): void
    {
        Storage::fake('private');

        $this->mock(TelegramService::class, fn ($m) => $m->shouldReceive('deleteMessage')->andReturn([]));

        $old = $this->conv(['created_at' => now()->subDays(91)]);
        Storage::disk('private')->put('chat/attachments/test.jpg', 'fake-image-data');
        $old->messages()->create([
            'sender'          => 'visitor',
            'body'            => '',
            'attachment_path' => 'chat/attachments/test.jpg',
        ]);

        $this->artisan('chat:purge')->assertExitCode(0);

        Storage::disk('private')->assertMissing('chat/attachments/test.jpg');
    }

    public function test_no_orphan_files_remain_after_purge(): void
    {
        Storage::fake('private');

        $this->mock(TelegramService::class, fn ($m) => $m->shouldReceive('deleteMessage')->andReturn([]));

        $old = $this->conv(['created_at' => now()->subDays(91)]);
        Storage::disk('private')->put('chat/attachments/img1.jpg', 'data1');
        Storage::disk('private')->put('chat/attachments/img2.jpg', 'data2');
        $old->messages()->create(['sender' => 'visitor', 'body' => '', 'attachment_path' => 'chat/attachments/img1.jpg']);
        $old->messages()->create(['sender' => 'agent',   'body' => '', 'attachment_path' => 'chat/attachments/img2.jpg']);

        $this->artisan('chat:purge')->assertExitCode(0);

        Storage::disk('private')->assertMissing('chat/attachments/img1.jpg');
        Storage::disk('private')->assertMissing('chat/attachments/img2.jpg');
    }

    public function test_purge_deletes_visitor_token_and_nonces(): void
    {
        $old = $this->conv([
            'created_at'        => now()->subDays(91),
            'resume_nonce_hash' => hash('sha256', 'nonce'),
            'resume_token_hash' => hash('sha256', 'token'),
        ]);

        $this->artisan('chat:purge')->assertExitCode(0);

        $this->assertNull(ChatConversation::find($old->id));
        $this->assertDatabaseEmpty('chat_conversations');
    }

    // ── Telegram deleteMessage ────────────────────────────────────────────────

    public function test_purge_calls_telegram_delete_message_for_outbound_records(): void
    {
        $deletedIds = [];

        $this->mock(TelegramService::class, function ($mock) use (&$deletedIds) {
            $mock->shouldReceive('deleteMessage')
                ->andReturnUsing(function (int $id) use (&$deletedIds) {
                    $deletedIds[] = $id;
                    return [];
                });
        });

        $old = $this->conv(['created_at' => now()->subDays(91)]);
        TelegramOutbound::create(['conversation_id' => $old->id, 'telegram_message_id' => 101, 'tipo' => 'message']);
        TelegramOutbound::create(['conversation_id' => $old->id, 'telegram_message_id' => 102, 'tipo' => 'photo']);

        $this->artisan('chat:purge')->assertExitCode(0);

        $this->assertEqualsCanonicalizing([101, 102], $deletedIds);
    }

    public function test_purge_continues_when_telegram_rejects_delete(): void
    {
        $this->mock(TelegramService::class, function ($mock) {
            $mock->shouldReceive('deleteMessage')->andReturn(null); // Telegram rejects
        });

        Log::shouldReceive('info')->withArgs(fn ($msg) => str_contains($msg, 'deleteMessage'))->once();
        Log::shouldReceive('info')->andReturn(null); // allow other log calls

        $old = $this->conv(['created_at' => now()->subDays(91)]);
        TelegramOutbound::create(['conversation_id' => $old->id, 'telegram_message_id' => 999, 'tipo' => 'message']);

        // Should still delete the conversation (exit 0, not failure)
        $this->artisan('chat:purge')->assertExitCode(0);

        $this->assertNull(ChatConversation::find($old->id), 'conversation must be purged even if Telegram rejects');
    }

    // ── Dry-run ───────────────────────────────────────────────────────────────

    public function test_dry_run_does_not_delete(): void
    {
        $old = $this->conv(['created_at' => now()->subDays(91)]);

        $this->artisan('chat:purge', ['--dry-run' => true])->assertExitCode(0);

        $this->assertNotNull(ChatConversation::find($old->id), 'dry-run must not delete');
    }

    public function test_purge_reports_nothing_when_nothing_to_delete(): void
    {
        $this->conv(['created_at' => now()->subDays(30)]);

        $this->artisan('chat:purge')
            ->expectsOutput('Nothing to purge.')
            ->assertExitCode(0);
    }
}
