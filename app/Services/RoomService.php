<?php

namespace App\Services;

use App\Models\QontakRoom;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RoomService
{
    private $base_url;
    private $api_token;

    public function __construct()
    {
        $this->base_url = env('QONTAK_BASE_URL', 'https://service-chat.qontak.com/api/open');
        $this->api_token = env('QONTAK_API_TOKEN');
    }

    private function getHeaders()
    {
        return [
            'Authorization' => 'Bearer ' . $this->api_token,
            'Accept' => 'application/json',
        ];
    }

    public function autoAssignRooms($userId)
    {
        $twentyFourHoursAgo = now()->subHours(24);
        $assignedCount = 0;
        $errors = [];

        Log::info('[AUTO ASSIGN] ===== START AUTO ASSIGN =====');
        Log::info('[AUTO ASSIGN] Configuration', [
            'agent_id' => $userId,
            'cutoff_time' => $twentyFourHoursAgo->toDateTimeString(),
            'base_url' => $this->base_url
        ]);

        Log::info('[AUTO ASSIGN] Fetching rooms from API...');
        
        $response = Http::withHeaders($this->getHeaders())
            ->get($this->base_url . '/v1/rooms', [
                'limit' => 100,
                'offset' => 0
            ]);

        if (!$response->successful()) {
            Log::error('[AUTO ASSIGN] Failed to fetch rooms from API', [
                'status' => $response->status(),
                'response' => $response->body()
            ]);
            return ['assigned' => 0, 'errors' => ['Failed to fetch rooms']];
        }

        $rooms = $response->json();

        if (!isset($rooms['data']) || !is_array($rooms['data'])) {
            Log::warning('[AUTO ASSIGN] No rooms data in API response', [
                'response_keys' => array_keys($rooms)
            ]);
            return ['assigned' => 0, 'errors' => ['No rooms data']];
        }

        $totalRooms = count($rooms['data']);
        Log::info('[AUTO ASSIGN] Rooms fetched from API', [
            'total_rooms' => $totalRooms
        ]);

        $unassignedRooms = 0;
        $recentRooms = 0;
        $alreadyAssignedInDB = 0;
        $eligibleForAssign = 0;

        foreach ($rooms['data'] as $index => $room) {
            $roomId = $room['id'];
            $roomStatus = $room['status'] ?? 'unknown';
            $roomUserId = $room['user_id'] ?? null;
            $roomCreatedAt = isset($room['created_at']) ? \Carbon\Carbon::parse($room['created_at']) : null;

            Log::info('[AUTO ASSIGN] Checking room ' . ($index + 1) . '/' . $totalRooms, [
                'room_id' => $roomId,
                'status' => $roomStatus,
                'user_id' => $roomUserId,
                'created_at' => $roomCreatedAt ? $roomCreatedAt->toDateTimeString() : 'null'
            ]);

            if ($roomStatus !== 'unassigned') {
                Log::debug('[AUTO ASSIGN] ❌ Skipping - Status is not unassigned', [
                    'room_id' => $roomId,
                    'status' => $roomStatus
                ]);
                continue;
            }

            $unassignedRooms++;

            if (!$roomCreatedAt || $roomCreatedAt->lessThan($twentyFourHoursAgo)) {
                Log::debug('[AUTO ASSIGN] ❌ Skipping - Room too old', [
                    'room_id' => $roomId,
                    'created_at' => $roomCreatedAt ? $roomCreatedAt->toDateTimeString() : 'null',
                    'age_hours' => $roomCreatedAt ? $roomCreatedAt->diffInHours(now()) : 'unknown'
                ]);
                continue;
            }

            $recentRooms++;

            $existingRoom = QontakRoom::where('room_id', $roomId)->first();

            if ($existingRoom) {
                Log::debug('[AUTO ASSIGN] Room found in database', [
                    'room_id' => $roomId,
                    'db_is_assigned' => $existingRoom->is_assigned,
                    'db_is_assigned_type' => gettype($existingRoom->is_assigned),
                    'db_is_assigned_raw' => var_export($existingRoom->is_assigned, true),
                    'db_agent_id' => $existingRoom->agent_id
                ]);

                if ($existingRoom->is_assigned == 1 || $existingRoom->is_assigned === true || $existingRoom->is_assigned === 1) {
                    $alreadyAssignedInDB++;
                    Log::debug('[AUTO ASSIGN] ❌ Skipping - Already assigned in DB', [
                        'room_id' => $roomId,
                        'agent_id' => $existingRoom->agent_id
                    ]);
                    continue;
                }
            } else {
                Log::debug('[AUTO ASSIGN] Room not in database yet', [
                    'room_id' => $roomId
                ]);
            }

            $eligibleForAssign++;

            Log::info('[AUTO ASSIGN] ✅ Room eligible for assignment', [
                'room_id' => $roomId,
                'agent_id' => $userId,
                'eligible_count' => $eligibleForAssign
            ]);

            Log::info('[AUTO ASSIGN] Calling API to assign room...', [
                'url' => $this->base_url . "/v1/rooms/{$roomId}/agents/{$userId}",
                'room_id' => $roomId,
                'user_id' => $userId
            ]);

            $assignResponse = Http::withHeaders($this->getHeaders())
                ->post($this->base_url . "/v1/rooms/{$roomId}/agents/{$userId}");

            Log::info('[AUTO ASSIGN] API assignment response', [
                'room_id' => $roomId,
                'status' => $assignResponse->status(),
                'is_successful' => $assignResponse->successful(),
                'response_body' => $assignResponse->body()
            ]);

            if ($assignResponse->successful()) {
                $savedRoom = QontakRoom::updateOrCreate(
                    ['room_id' => $roomId],
                    [
                        'agent_id' => $userId,
                        'is_assigned' => 1,
                        'assigned_at' => now(),
                        'last_message_at' => $roomCreatedAt,
                    ]
                );

                Log::info('[AUTO ASSIGN] Room saved to database', [
                    'room_id' => $savedRoom->room_id,
                    'agent_id' => $savedRoom->agent_id,
                    'is_assigned' => $savedRoom->is_assigned,
                    'is_assigned_type' => gettype($savedRoom->is_assigned),
                    'is_assigned_raw' => var_export($savedRoom->is_assigned, true)
                ]);

                $assignedCount++;

                Log::info('[AUTO ASSIGN] ✅ Room assigned successfully', [
                    'room_id' => $roomId,
                    'agent_id' => $userId,
                    'total_assigned' => $assignedCount
                ]);
            } else {
                $error = "Failed to assign room {$roomId}";
                $errors[] = $error;

                Log::error('[AUTO ASSIGN] ❌ Room assignment failed', [
                    'room_id' => $roomId,
                    'status' => $assignResponse->status(),
                    'response' => $assignResponse->body()
                ]);
            }
        }

        Log::info('[AUTO ASSIGN] ===== AUTO ASSIGN SUMMARY =====', [
            'total_rooms_fetched' => $totalRooms,
            'unassigned_status' => $unassignedRooms,
            'recent_rooms' => $recentRooms,
            'already_assigned_in_db' => $alreadyAssignedInDB,
            'eligible_for_assign' => $eligibleForAssign,
            'successfully_assigned' => $assignedCount,
            'errors' => count($errors)
        ]);

        return [
            'assigned' => $assignedCount,
            'errors' => $errors,
        ];
    }

    public function syncRoomFromApi($roomId)
    {
        Log::info('[ROOM SERVICE] Syncing room from API', ['room_id' => $roomId]);

        $response = Http::withHeaders($this->getHeaders())
            ->get($this->base_url . "/v1/rooms/{$roomId}");

        if (!$response->successful()) {
            Log::error('[ROOM SERVICE] Failed to fetch room', [
                'room_id' => $roomId,
                'status' => $response->status()
            ]);
            return null;
        }

        $roomData = $response->json();
        $status = $roomData['status'] ?? 'unknown';
        $userId = $roomData['user_id'] ?? null;
        
        $isAssigned = ($status === 'assigned') ? 1 : 0;

        Log::info('[ROOM SERVICE] Room data from API', [
            'room_id' => $roomId,
            'status' => $status,
            'user_id' => $userId,
            'is_assigned_calculated' => $isAssigned
        ]);

        $room = QontakRoom::updateOrCreate(
            ['room_id' => $roomId],
            [
                'agent_id' => $userId,
                'is_assigned' => $isAssigned,
                'last_message_at' => isset($roomData['last_message_at']) 
                    ? \Carbon\Carbon::parse($roomData['last_message_at']) 
                    : now(),
            ]
        );

        Log::info('[ROOM SERVICE] Room synced to DB', [
            'room_id' => $room->room_id,
            'is_assigned' => $room->is_assigned,
            'is_assigned_type' => gettype($room->is_assigned),
            'agent_id' => $room->agent_id
        ]);

        return $room;
    }
}