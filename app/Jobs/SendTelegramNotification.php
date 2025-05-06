<?php

namespace App\Jobs;

use App\Models\User;
use App\Services\TelegramService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Throwable;

class SendTelegramNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $backoff = [60, 300, 600];

    public $user;
    public $message;

    public function __construct(User $user, string $message, string $locale = 'en')
    {

        $this->user = $user;
        $this->message = $message;
    }

    public function handle(TelegramService $telegram): void
    {
        try {
            $telegram->sendMessage($this->user->telegram_id, $this->message);
        } catch (\Exception $e) {
            Log::error('Failed to send Telegram message', [
                'user_id' => $this->user->id,
                'telegram_id' => $this->user->telegram_id,
                'error' => $e->getMessage(),
            ]);
            throw $e;  // Ошибка будет перехвачена методом failed
        }
    }

    public function failed(Throwable $exception): void
    {
       Log::error('❌ Failed to send Telegram message', [
            'user_id' => $this->user->id,
            'telegram_id' => $this->user->telegram_id,
            'error' => $exception->getMessage(),
        ]);


        $devChatId = config('services.telegram.dev_chat_id');
        if ($devChatId) {
            app(TelegramService::class)->sendMessage(
                $devChatId,
                "❌ Не удалось отправить сообщение пользователю {$this->user->id}.\nОшибка: {$exception->getMessage()}"
            );
        }
    }
}
