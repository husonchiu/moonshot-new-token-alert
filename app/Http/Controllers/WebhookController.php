<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Telegram\Bot\Laravel\Facades\Telegram;

class WebhookController extends Controller
{
    public function index($token)
    {
        $updates = Telegram::getWebhookUpdate();

        return 'ok';
    }
}
