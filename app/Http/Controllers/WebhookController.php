<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Telegram\Bot\Laravel\Facades\Telegram;

class WebhookController extends Controller
{
    public function index($token)
    {
        $updates = Telegram::getWebhookUpdate();
        if ($updates['message']['chat']['id'] == config('services.telegram-bot-api.chat_id') && isset($updates['message']['reply_to_message']) && $updates['message']['reply_to_message']['message_id'] == config('services.telegram-bot-api.topic_id_token_trade'))
        {
            \Log::info($updates);
        }

        return 'ok';
    }
}
