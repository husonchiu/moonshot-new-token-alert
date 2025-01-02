<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use NotificationChannels\Telegram\TelegramMessage;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Models\CaHistory;

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
                    ->line($data['baseToken']['name'].' | *'.$data['baseToken']['symbol'].'* | '.abs(round(now()->diffInSeconds($created_at),0)).'s')
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
                    ])
                    ->send();

                $model = new CaHistory;
                $model->ca = $data['baseToken']['address'];
                $model->save();
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

    function numberFormat($value)
    {
        return number_format($value, 2, '.', ',');
    }
}
