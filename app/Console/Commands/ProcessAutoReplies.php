<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\AutoReplyService;
use Illuminate\Support\Facades\Log;

class ProcessAutoReplies extends Command
{
    protected $signature = 'qontak:auto-reply';
    protected $description = 'Process auto-replies for recent messages';

    public function handle(AutoReplyService $autoReplyService)
    {
        $start = microtime(true);

        $result = $autoReplyService->processNewMessages();

        $duration = round(microtime(true) - $start, 3);

        Log::info('[AUTO-REPLY]', [
            'processed' => $result['processed'],
            'duration' => $duration . 's',
        ]);

        return Command::SUCCESS;
    }
}
