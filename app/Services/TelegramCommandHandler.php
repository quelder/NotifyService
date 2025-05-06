<?php
namespace App\Services;

use App\Models\User;

/**
 * Class TelegramCommandHandler
 *
 * @OA\Schema(
 *     schema="TelegramCommandHandler",
 *     title="Telegram Command Handler",
 *     description="Handles Telegram commands from users, such as /start and /stop. Updates user information and sends appropriate notifications.",
 * )
 */
class TelegramCommandHandler
{
    protected TelegramService $telegram;

    public function __construct(TelegramService $telegram)
    {
        $this->telegram = $telegram;
    }

    /**
     * Processing of the Telegram text command.
     *
     * @param string $text Command from the user
     * @param array $message
     *
     * @return void
     *
     * @OA\Parameter(
     *     name="text",
     *     in="query",
     *     required=true,
     *     @OA\Schema(type="string")
     * )
     * @OA\Parameter(
     *     name="message",
     *     in="query",
     *     required=true,
     *     @OA\Schema(type="object")
     * )
     */
    public function handle(string $text, array $message): void
    {
        $chatId = $message['chat']['id'];
        $languageCode = $message['from']['language_code'] ?? 'en';
        $name = $message['from']['first_name'] ?? 'Telegram User';

        if ($text === '/start') {
            $user = User::updateOrCreate(
                ['telegram_id' => $chatId],
                [
                    'name' => $name,
                    'subscribed' => true,
                    'language_code' => $languageCode,
                ]
            );
            if ($user->wasRecentlyCreated) {
                $this->sendLocalizedMessage($chatId, 'welcome', $languageCode, $name);
                $this->sendLocalizedMessage($chatId, 'subscribed', $languageCode, $name);
            } else {
                $this->sendLocalizedMessage($chatId, 'subscribed', $languageCode, $name);
            }
        } elseif ($text === '/stop') {
            User::where('telegram_id', $chatId)->update(['subscribed' => false]);
            $this->sendLocalizedMessage($chatId, 'unsubscribed', $languageCode, $name);
        } else {
            $this->sendLocalizedMessage($chatId, 'did_not_understand', $languageCode, $name);
        }
    }

    /**
     * Sending a localized message to a Telegram user.
     *
     * @param int|string $chatId
     * @param string $status Key
     * @param string $languageCode
     * @param string $name
     *
     * @return void
     */
    private function sendLocalizedMessage($chatId, $status, $languageCode, $name): void
    {
        app()->setLocale($languageCode);

        $message = __('general.' . $status, ['name' => $name]);
        $this->telegram->sendMessage($chatId, $message);
    }
}