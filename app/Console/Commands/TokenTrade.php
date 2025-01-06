<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Models\TokenTrade as Model;
use App\Models\TokenTradeHistory;
use NotificationChannels\Telegram\TelegramMessage;

class TokenTrade extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:token-trade';

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
        $token = Model::first();
        if ($token)
        {
            $response = Http::timeout(3)->get('https://api.moonshot.cc/trades/v1/latest/solana/'.$token->ca);
            if ($response->successful())
            {
                $now = now()->timestamp;
                $trades = collect($response->json())->filter(function($row)use($now){
                    return $now - $row['blockTimestamp'] <= 60;
                })->filter(function($row){
                    return TokenTradeHistory::where('txn_id', $row['txnId'])->first() == null;
                })->reverse();

                foreach($trades as $trade)
                {
                    if (TokenTradeHistory::where('txn_id', $trade['txnId'])->first() == null)
                    {
                        $message = TelegramMessage::create()
                            ->to(config('services.telegram-bot-api.chat_id'))
                            ->options([
                                'disable_web_page_preview' => true,
                                'reply_to_message_id' => config('services.telegram-bot-api.topic_id_token_trade'),
                            ])
                            ->lineIf($token['creator'] == $trade['maker'],'🚨🚨🚨 Creator 🚨🚨🚨')
                            ->lineIf($trade['type'] == 'buy', '💚'.strtoupper($trade['type']))
                            ->lineIf($trade['type'] == 'sell', '❤️'.strtoupper($trade['type']))
                            ->line('')
                            ->line($this->escapeMarkdown($token->name).' | *'.$token->symbol.'*')
                            ->line('')
                            ->line('SOL: '.$trade['amount1'])
                            ->line('USD: '.$trade['volumeUsd'])
                            ->line('')
                            ->line('MC: '.(number_format(1000000000 * $trade['priceUsd'], 2, '.', ',')));
    
                        if (isset($trade['metadata']))
                        {
                            $message->line('Progress: '.$trade['metadata']['progress'].'%');
                        }
                            
                            $message->send();
    
                        $model = new TokenTradeHistory;
                        $model->txn_id = $trade['txnId'];
                        $model->save();
                    }
                }
            }
        }
    }

    function escapeMarkdown($string)
    {
        return addcslashes($string, '*,_,\\,#,~,|,[,],(,),{,},?,/,`,<,>,/');
    }
}
