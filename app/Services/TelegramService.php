<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

/**
 * Class TelegramService
 *
 * @OA\Schema(
 *     schema="TelegramService",
 *     title="Telegram Service",
 *     description="Service for sending messages to Telegram via Bot API"
 * )
 */
class TelegramService
{
    public function sendMessage(int|string $chatId, string $text): void
    {
        $token = config('services.telegram.bot_token');
        $response = Http::post("https://api.telegram.org/bot{$token}/sendMessage", [
            'chat_id' => $chatId,
            'text' => $text,
        ]);

        if (!$response->successful()) {
            throw new \Exception("Telegram API error: " . $response->body());
        }
    }
}