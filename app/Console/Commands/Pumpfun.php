<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Amp\Websocket\Client\WebsocketHandshake;
use Amp\Websocket\WebsocketCloseCode;
use function Amp\Websocket\Client\connect;
use Illuminate\Support\Facades\Http;
use NotificationChannels\Telegram\TelegramMessage;

class Pumpfun extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:pumpfun';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $handshake = (new WebsocketHandshake('wss://pumpportal.fun/api/data'))
            ->withHeader('Sec-WebSocket-Protocol', 'dumb-increment-protocol');

        $connection = connect($handshake);
        $connection->sendText('{"method":"subscribeNewToken"}');

        foreach($connection as $message)
        {
            $add = null;
            $string = $message->buffer();
            $payload = json_decode($string, true);
            if (isset($payload['txType']) && $payload['txType'] == 'create')
            {
                try
                {
                    $more = Http::timeout(1)->get($payload['uri']);
                    if ($more->successful())
                    {
                        $add = $more->json();
                    }
                }
                catch(\Exception $e)
                {
                    \Log::info('GET '.$payload['uri'].' timeout');
                }

                if ($add && (isset($add['twitter']) || isset($add['website']) || isset($add['telegram'])))
                {
                    $message = TelegramMessage::create()
                        ->to(config('services.telegram-bot-api.chat_id'));
    
                    $message->escapedLine($payload['name'].' | '.$payload['symbol'])
                        ->line('')
                        ->line('`'.$payload['mint'].'`')
                        ->line('')
                        ->line('SolAmount: '.$payload['solAmount'])
                        ->line('')
                        ->line('*Social:*');
    
                    if ($add)
                    {
                        if (isset($add['twitter']))
                        {
                            $message->line('[Twitter]('.$add['twitter'].')');
                        }
                        if (isset($add['telegram']))
                        {
                            $message->line('[Telegram]('.$add['telegram'].')');
                        }
                        if (isset($add['website']))
                        {
                            $message->line('[Website]('.$add['website'].')');
                        }
                    }
    
                    $message->options([
                            'disable_web_page_preview' => true,
                            'reply_to_message_id' => config('services.telegram-bot-api.topic_id_new_token_pumpfun'),
                        ])
                        ->button('BONKbot', 'https://t.me/bonkbot_bot?start=ref_lz8ym_ca_'.$payload['mint'])
                        ->send();
                }
            }
        }
    }
}
