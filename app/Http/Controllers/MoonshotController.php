<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use NotificationChannels\Telegram\TelegramMessage;
use Illuminate\Support\Str;
use Carbon\Carbon;

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
                    ->to('-1002294440653')
                    // ->line($data['baseToken']['name'].' | *'.$data['baseToken']['symbol'].'* | '.abs(round(now()->diffInSeconds($created_at),0)).'s')
                    // ->line('[DEX]('.$data['url'].')')
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
                        $message->line($link);
                    }
                }
                $message->line('');

                $message->line($created_at->addHours(8)->format('Y-m-d H:i:s'). ' GMT+8')
                    ->send();
            }

            dd($data);
        }
        else
        {
            dd('false');
        }
    }

    function getMoonshot()
    {
        $now = Carbon::now()->getTimestampMs();
        $response = Http::timeout(3)->get('https://api.moonshot.cc/tokens/v1/new/solana');
        if ($response->successful())
        {
            $collection = collect($response->json());
            return $collection->filter(fn($row, $i) => $i =='0');
        }
        return false;
    }

    function numberFormat($value)
    {
        return number_format($value, 2, '.', ',');
    }
}
