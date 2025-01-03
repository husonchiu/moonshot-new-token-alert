<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Telegram\Bot\Laravel\Facades\Telegram;
use App\Models\TokenTrade;
use NotificationChannels\Telegram\TelegramMessage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;

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

            if (Str::startsWith($text, '/set '))
            {
                $token = Str::of($text)->after('/set ')->trim();
                if ($token)
                {
                    $response = Http::timeout(3)->get('https://api.moonshot.cc/token/v1/solana/'.$token);
                    if ($response->successful())
                    {
                        $data = $response->json();
                        if (!isset($data['error']))
                        {
                            $model = new TokenTrade;
                            $model->ca = $data['baseToken']['address'];
                            $model->creator = $data['moonshot']['creator'];
                            $model->name = $data['baseToken']['name'];
                            $model->symbol = $data['baseToken']['symbol'];
                            $model->save();
                            $message->line('Token set successfully.')->send();
                        }
                        else
                        {
                            $message->line('Token not found.')->send();
                        }
                    }
                    else
                    {
                        $message->line('Token not found.')->send();
                    }
                }
            }

            \Log::info($updates);
        }

        return 'ok';
    }
}
