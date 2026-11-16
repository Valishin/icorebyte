<?php

namespace Tests\Feature\Chat;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateAgentCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_creates_agent_with_is_agent_true(): void
    {
        $this->artisan('chat:create-agent', [
            '--name'     => 'Ana López',
            '--email'    => 'ana@example.com',
            '--password' => 'secret123',
        ])->assertSuccessful();

        $this->assertDatabaseHas('users', [
            'email'    => 'ana@example.com',
            'is_agent' => true,
        ]);
    }

    public function test_rejects_duplicate_email(): void
    {
        User::create([
            'name'     => 'Existing',
            'email'    => 'existing@example.com',
            'password' => bcrypt('password'),
            'is_agent' => false,
        ]);

        $this->artisan('chat:create-agent', [
            '--name'     => 'Duplicate',
            '--email'    => 'existing@example.com',
            '--password' => 'secret123',
        ])->assertFailed();

        $this->assertDatabaseCount('users', 1);
    }

    public function test_rejects_short_password(): void
    {
        $this->artisan('chat:create-agent', [
            '--name'     => 'Bob',
            '--email'    => 'bob@example.com',
            '--password' => 'short',
        ])->assertFailed();

        $this->assertDatabaseMissing('users', ['email' => 'bob@example.com']);
    }
}
