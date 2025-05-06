<?php

namespace Tests\Unit;

use App\Services\TelegramService;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class TelegramServiceTest extends TestCase
{
    public function testSendMessageSendsCorrectPayload()
    {
        Http::fake();

        $service = new TelegramService();
        $service->sendMessage(123456, 'Test message');

        Http::assertSent(function ($request) {
            return
                $request->url() === "https://api.telegram.org/bot" . config('services.telegram.bot_token') . "/sendMessage" &&
                $request['chat_id'] === 123456 &&
                $request['text'] === 'Test message';
        });
    }

    public function testSendMessageHandlesErrorGracefully()
    {
        Http::fake([
            '*' => Http::response(['ok' => false, 'description' => 'Bad Request'], 400)
        ]);

        $service = new TelegramService();

        // Мы просто проверим, что исключения не выбрасываются
        $this->expectNotToPerformAssertions();

        $service->sendMessage(123456, 'Invalid request');
    }
}