<?php

namespace Tests\Unit;

use App\Models\User;
use App\Services\TelegramCommandHandler;
use App\Services\TelegramService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class TelegramCommandHandlerTest extends TestCase
{
    use RefreshDatabase;

    public function testStartCommandCreatesOrUpdatesUserAndSendsMessage()
    {
        Http::fake(); // заглушка внешнего запроса

        $service = new TelegramService();
        $handler = new TelegramCommandHandler($service);

        $handler->handle('/start', [
            'chat' => ['id' => 123456],
            'from' => ['first_name' => 'TestUser']
        ]);

        $this->assertDatabaseHas('users', [
            'telegram_id' => 123456,
            'name' => 'TestUser',
            'subscribed' => true,
        ]);

        Http::assertSent(fn ($request) =>
            $request->url() === "https://api.telegram.org/bot" . config('services.telegram.bot_token') . "/sendMessage" &&
            $request['chat_id'] == 123456 &&
            str_contains($request['text'], 'подписаны')
        );
    }

    public function testStopCommandUnsubscribesUser()
    {
        Http::fake();

        User::factory()->create([
            'telegram_id' => 123456,
            'subscribed' => true
        ]);

        $service = new TelegramService();
        $handler = new TelegramCommandHandler($service);

        $handler->handle('/stop', [
            'chat' => ['id' => 123456]
        ]);

        $this->assertDatabaseHas('users', [
            'telegram_id' => 123456,
            'subscribed' => false
        ]);

        Http::assertSent(fn ($request) =>
        str_contains($request['text'], 'отписаны')
        );
    }
}