<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\RoomService;
use Illuminate\Support\Facades\Log;

class AutoAssignRooms extends Command
{
    protected $signature = 'qontak:auto-assign {agent_id?}';
    protected $description = 'Automatically assign unassigned rooms to an agent';

    public function handle(RoomService $roomService)
    {
        $agentId = $this->argument('agent_id') ?? env('QONTAK_AGENT_ID', '7180a56b-27d6-4cbc-85d9-55b0edc9c0c6');

        $this->info('==================================================');
        $this->info('Starting auto-assign process...');
        $this->info("Agent ID: {$agentId}");
        $this->info("Time: " . now()->toDateTimeString());
        $this->info('==================================================');

        Log::info('[COMMAND] Auto-assign command started', [
            'agent_id' => $agentId,
            'timestamp' => now()->toDateTimeString()
        ]);

        $result = $roomService->autoAssignRooms($agentId);

        $this->info("✅ Assigned rooms: {$result['assigned']}");

        if (!empty($result['errors'])) {
            $this->warn('⚠️  Errors occurred:');
            foreach ($result['errors'] as $error) {
                $this->error("   - {$error}");
            }
        }

        $this->info('==================================================');
        $this->info('Auto-assign process completed!');
        $this->info('==================================================');

        Log::info('[COMMAND] Auto-assign command completed', [
            'assigned' => $result['assigned'],
            'errors' => count($result['errors'])
        ]);

        return 0;
    }
}