<?php

namespace Tests\Feature\Chat;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Tests for the chat_enabled Inertia shared prop (HandleInertiaRequests).
 *
 * Changing CHAT_ENABLED in .env + php artisan optimize must hide the widget
 * without recompiling the frontend.
 */
class ChatEnabledSharedPropTest extends TestCase
{
    use RefreshDatabase;

    public function test_chat_enabled_true_is_shared_as_inertia_prop(): void
    {
        config(['chat.enabled' => true]);

        $this->get('/')
            ->assertInertia(fn ($page) => $page->where('chat_enabled', true));
    }

    public function test_chat_enabled_false_is_shared_as_inertia_prop(): void
    {
        config(['chat.enabled' => false]);

        $this->get('/')
            ->assertInertia(fn ($page) => $page->where('chat_enabled', false));
    }
}
