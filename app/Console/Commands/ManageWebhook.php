<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class ManageWebhook extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:manage-webhook';

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
        // set webhook
        $response = Http::post('https://api.telegram.org/bot'.config('services.telegram-bot-api.token').'/setWebhook?url='.route('webhook', ['token' => config('services.telegram-bot-api.token')]));
        dd($response);
    }
}
