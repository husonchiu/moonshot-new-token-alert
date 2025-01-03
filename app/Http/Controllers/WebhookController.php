<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use NotificationChannels\Telegram\TelegramUpdates;

class WebhookController extends Controller
{
    public function index($token)
    {
        $updates = TelegramUpdates::create()->limit(2)->get();
        \Log::info($updates);

        return 'ok';
    }
}
