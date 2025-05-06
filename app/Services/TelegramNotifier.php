<?php
namespace App\Services;

use App\Jobs\SendTelegramNotification;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class TelegramNotifier implements NotifierInterface
{
    public function send(User $user, string $message): void
    {
        if (!$user->telegram_id) {
            Log::warning('User has no telegram_id', ['user_id' => $user->id]);
            return;
        }

        SendTelegramNotification::dispatch($user, $message);
    }
}