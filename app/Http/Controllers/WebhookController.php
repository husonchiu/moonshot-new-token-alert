<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Telegram\Bot\Laravel\Facades\Telegram;
use App\Models\TokenTrade;
use NotificationChannels\Telegram\TelegramMessage;

class WebhookController extends Controller
{
    public function index($token)
    {
        $updates = Telegram::getWebhookUpdate();
        if ($updates['message']['chat']['id'] == config('services.telegram-bot-api.chat_id') && isset($updates['message']['reply_to_message']) && $updates['message']['reply_to_message']['message_id'] == config('services.telegram-bot-api.topic_id_token_trade'))
        {
            $message = TelegramMessage::create()
                ->to(config('services.telegram-bot-api.chat_id'))
                ->options([
                    'disable_web_page_preview' => true,
                    'reply_to_message_id' => config('services.telegram-bot-api.topic_id_token_trade'),
                ]);


            $text = $updates['message']['text'];
            if ($text === '/show')
            {
                $token = TokenTrade::first();
                if ($token)
                {

                }
                else
                {
                    $message->line('No Token.')->send();
                }
            }
            \Log::info($updates);
        }

        return 'ok';
    }
}
