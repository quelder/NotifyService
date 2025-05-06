<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class TelegramControllerTest extends TestCase
{
    use RefreshDatabase;

    public function testWebhookHandlesStartCommand()
    {
        Http::fake();

        $response = $this->postJson('/api/webhook', [
            'message' => [
                'chat' => ['id' => 123456],
                'text' => '/start',
                'from' => ['first_name' => 'TestName']
            ]
        ]);

        $response->assertOk();

        $this->assertDatabaseHas('users', [
            'telegram_id' => 123456,
            'name' => 'TestName',
            'subscribed' => true
        ]);
    }

    public function testWebhookHandlesStopCommand()
    {
        Http::fake();

        User::factory()->create(['telegram_id' => 123456]);

        $response = $this->postJson('/api/webhook', [
            'message' => [
                'chat' => ['id' => 123456],
                'text' => '/stop'
            ]
        ]);

        $response->assertOk();

        $this->assertDatabaseHas('users', [
            'telegram_id' => 123456,
            'subscribed' => false
        ]);
    }

    public function testWebhookIgnoresUnknownCommand()
    {
        Http::fake();

        $response = $this->postJson('/api/webhook', [
            'message' => [
                'chat' => ['id' => 123456],
                'text' => '/unknown'
            ]
        ]);

        $response->assertOk();

        $this->assertDatabaseMissing('users', [
            'telegram_id' => 123456
        ]);
    }
}