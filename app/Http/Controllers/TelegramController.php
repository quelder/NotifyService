<?php

namespace App\Http\Controllers;

use App\Services\TelegramCommandHandler;
use Illuminate\Http\Request;

/**
 * @OA\Info(
 *     title="Notification Service API",
 *     version="1.0.0",
 *     description="This API handles Telegram webhook messages, supporting commands like /start and /stop."
 * )
 *
 * @OA\Server(
 *     url="http://127.0.0.1:8000",
 *     description="Local Development Server"
 * )
 */
class TelegramController extends Controller
{
    /**
     * Handle Telegram webhook POST request.
     *
     * @OA\Post(
     *     path="/api/webhook",
     *     summary="Receive Telegram webhook messages",
     *     description="Processes messages from Telegram and handles /start and /stop commands.",
     *     operationId="handleTelegramWebhook",
     *     tags={"Telegram Webhook"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"message"},
     *             @OA\Property(
     *                 property="message",
     *                 type="object",
     *                 @OA\Property(property="message_id", type="integer", example=12345),
     *                 @OA\Property(property="from", type="object",
     *                     @OA\Property(property="id", type="integer", example=67890),
     *                     @OA\Property(property="is_bot", type="boolean", example=false),
     *                     @OA\Property(property="first_name", type="string", example="John"),
     *                     @OA\Property(property="username", type="string", example="john_doe"),
     *                     @OA\Property(property="language_code", type="string", example="en")
     *                 ),
     *                 @OA\Property(property="chat", type="object",
     *                     @OA\Property(property="id", type="integer", example=12345678),
     *                     @OA\Property(property="type", type="string", example="private")
     *                 ),
     *                 @OA\Property(property="date", type="integer", example=1672531199),
     *                 @OA\Property(property="text", type="string", example="/start")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Webhook processed successfully"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error"
     *     )
     * )
     */
    protected TelegramCommandHandler $handler;

    public function __construct(TelegramCommandHandler $handler)
    {
        $this->handler = $handler;
    }

    public function webhook(Request $request)
    {
        $data = $request->input('message');

        if (!$data || !isset($data['text'])) {
            return response('ok', 200);
        }

        $this->handler->handle($data['text'], $data);

        return response('ok', 200);
    }
}
