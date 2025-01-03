<?php

namespace App\Telegram\Commands\TokenTrade;

use Telegram\Bot\Commands\Command;

class Show extends Command
{
    protected string $name = 'show';
    protected string $description = 'Show current monitor token';

    public function handle()
    {
        $this->replyWithMessage([
            'text' => 'Hey man!',
        ]);
    }
}
