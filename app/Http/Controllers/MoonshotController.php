<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use NotificationChannels\Telegram\TelegramMessage;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Models\CaHistory;
use App\Models\TokenBoost;

class MoonshotController extends Controller
{
    public function index()
    {
        $datas = $this->getMoonshot();
        if ($datas !== false)
        {
            foreach($datas as $data)
            {
                $created_at = Carbon::parse($data['createdAt']/1000);
                $message = TelegramMessage::create()
                    ->to(config('services.telegram-bot-api.chat_id'))
                    ->line($data['baseToken']['name'].' | *'.$data['baseToken']['symbol'].'* | 🌱'.$created_at->diffForHumans(now(), 1, true))
                    ->line('[DEX]('.$data['url'].')')
                    ->line('')
                    ->line($this->numberFormat($data['moonshot']['progress']).'% $'.$this->numberFormat($data['marketCap']))
                    ->line('')
                    ->line('`'.$data['baseToken']['address'].'`')
                    ->line('')
                    ->line('*Social:*');

                if (isset($data['profile']['links']) && count($data['profile']['links']))
                {
                    foreach($data['profile']['links'] as $link)
                    {
                        $message->line('['.$link.']('.$link.')');
                    }
                }
                $message->line('');

                $message->line($created_at->addHours(8)->format('Y-m-d H:i:s').' (GMT+8)')
                    ->options([
                        'disable_web_page_preview' => true,
                        'reply_to_message_id' => config('services.telegram-bot-api.topic_id_new_token'),
                    ])
                    ->button('BONKbot', 'https://t.me/bonkbot_bot?start=ref_lz8ym_ca_'.$data['baseToken']['address'])
                    ->send();

                $model = new CaHistory;
                $model->ca = $data['baseToken']['address'];
                $model->save();
            }
        }
    }

    public function token_boosts()
    {
        $datas = $this->getTokenBoosts();
        if ($datas !== false)
        {
            foreach($datas as $data)
            {
                $created_at = Carbon::parse($data['createdAt']/1000);
                $response = Http::timeout(3)->get('https://api.moonshot.cc/token/v1/solana/'.$data['tokenAddress']);
                if ($response->successful())
                {
                    $token = $response->json();
                    $message = TelegramMessage::create()
                        ->to(config('services.telegram-bot-api.chat_id'))
                        ->line('*TOKEN BOOST*')
                        ->line($token['baseToken']['name'].' | *'.$token['baseToken']['symbol'].'* | 🌱'.$created_at->diffForHumans(now(), 1, true))
                        ->line('[DEX]('.$token['url'].')')
                        ->line('')
                        ->line($this->numberFormat($token['moonshot']['progress']).'% $'.$this->numberFormat($token['marketCap']))
                        ->line('')
                        ->line('`'.$token['baseToken']['address'].'`')
                        ->line('')
                        ->line('Boots amount: '.$data['amount'])
                        ->line('Total amount: '.$data['totalAmount'])
                        ->line('')
                        ->line('*Social:*');
                    
                    if (isset($token['profile']['links']) && count($token['profile']['links']))
                    {
                        foreach($token['profile']['links'] as $link)
                        {
                            $message->line('['.$link.']('.$link.')');
                        }
                    }
                    $message->line('');
                    
                    $message->line(now()->format('Y-m-d H:i:s').' (GMT+8)')
                        ->options([
                            'disable_web_page_preview' => true,
                            'reply_to_message_id' => config('services.telegram-bot-api.topic_id_token_boosts'),
                        ])
                        ->button('BONKbot', 'https://t.me/bonkbot_bot?start=ref_lz8ym_ca_'.$token['baseToken']['address'])
                        ->send();
                    
                    TokenBoost::upsert([
                        'ca' => $data['tokenAddress'],
                        'total_amount' => $data['totalAmount'],
                        'amount' => $data['amount'],
                    ], uniqueBy: ['ca'], update: ['total_amount', 'amount']);
                }
            }
        }
    }

    function getMoonshot()
    {
        $now = Carbon::now()->getTimestampMs();
        $response = Http::timeout(3)->get('https://api.moonshot.cc/tokens/v1/new/solana');
        if ($response->successful())
        {
            $collection = collect($response->json())->filter(function($row){
                return now()->timestamp - $row['createdAt']/1000 <= 120;
            });
            return $collection->filter(function($row){
                return CaHistory::where('ca', $row['baseToken']['address'])->first() == null;
            });
        }
        return false;
    }

    function getTokenBoosts()
    {
        $response = Http::timeout(3)->get('https://api.dexscreener.com/token-boosts/latest/v1');
        if ($response->successful())
        {
            $collection = collect($response->json())->filter(function($row){
                return $row['chainId'] == 'solana' && Str::endsWith($row['tokenAddress'], 'moon');
            });
            return $collection->filter(function($row){
                $model = TokenBoost::where('ca', $row['tokenAddress'])->first();
                if ($model)
                {
                    if ($model->total_amount == $row['totalAmount'])
                    {
                        return false;
                    }
                }
                return true;
            })->unique('tokenAddress');
        }
        return false;
    }

    function numberFormat($value)
    {
        return number_format($value, 2, '.', ',');
    }
}
