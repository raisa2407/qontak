<?php

namespace App\Services;

use App\Models\AutoReplyTemplate;
use App\Models\AutoReplyLog;
use App\Models\QontakRoom;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class AutoReplyService
{
    private $base_url;
    private $core_url;
    private $api_token;
    private $organization_id;

    public function __construct()
    {
        $this->base_url = env('QONTAK_BASE_URL', 'https://service-chat.qontak.com/api/open');
        $this->core_url = env('QONTAK_CORE_URL', 'https://chat-service.qontak.com/api/core/v1');
        $this->api_token = env('QONTAK_API_TOKEN');
        $this->organization_id = env('QONTAK_ORGANIZATION_ID', 'bb315b54-030f-4e51-8583-8c4aec379964');

        Log::info('[AUTO REPLY SERVICE] Initialized', [
            'base_url' => $this->base_url,
            'core_url' => $this->core_url,
            'organization_id' => $this->organization_id
        ]);
    }

    private function getHeaders()
    {
        return [
            'Authorization' => 'Bearer ' . $this->api_token,
            'Accept' => 'application/json',
        ];
    }

    public function processMessage($roomId, $message, $messageId = null, $customerPhone = null)
    {
        if (empty($message)) {
            return false;
        }

        $cacheKey = "auto_reply_processed_{$roomId}_{$messageId}";

        if (Cache::has($cacheKey)) {
            return false;
        }

        if (AutoReplyLog::where('room_id', $roomId)->where('message_id', $messageId)->exists()) {
            Cache::put($cacheKey, true, now()->addHours(24));
            return false;
        }

        $room = QontakRoom::where('room_id', $roomId)->first() ?? $this->syncRoomFromApi($roomId);

        if (!$room) {
            return false;
        }

        $messageText = strtolower(trim($message));
        $isAssigned = (int) $room->is_assigned === 1;
        $replyType = $isAssigned ? 'assigned' : 'unassigned';

        $template = null;
        $matchedKeyword = null;

        if ($isAssigned) {
            $templates = AutoReplyTemplate::active()
                ->assigned()
                ->whereNotNull('keyword')
                ->get();

            foreach ($templates as $tmpl) {
                if (stripos($messageText, strtolower($tmpl->keyword)) !== false) {
                    $template = $tmpl;
                    $matchedKeyword = $tmpl->keyword;
                    break;
                }
            }
        }

        if (!$template) {
            $template = AutoReplyTemplate::active()
                ->where('type', $replyType)
                ->whereNull('keyword')
                ->first();
        }

        if (!$template) {
            return false;
        }

        $replyMessage = $template->message;

        $multipart = [
            ['name' => 'room_id', 'contents' => $roomId],
            ['name' => 'type', 'contents' => 'text'],
            ['name' => 'text', 'contents' => $replyMessage],
        ];

        $response = null;
        $isSuccessful = false;
        $errorMessage = null;

        try {
            $response = Http::withHeaders($this->getHeaders())
                ->timeout(2)
                ->asMultipart()
                ->post($this->base_url . '/v1/messages/whatsapp', $multipart);

            $isSuccessful = $response->successful();
            $errorMessage = $isSuccessful ? null : $response->body();
        } catch (\Throwable $e) {
            $errorMessage = $e->getMessage();
        }

        AutoReplyLog::create([
            'room_id' => $roomId,
            'message_id' => $messageId,
            'customer_phone' => $customerPhone,
            'customer_message' => $message,
            'matched_keyword' => $matchedKeyword,
            'reply_sent' => $replyMessage,
            'reply_type' => $replyType,
            'is_successful' => $isSuccessful,
            'error_message' => $errorMessage,
        ]);

        Cache::put($cacheKey, true, now()->addHours(24));

        return $isSuccessful;
    }


    private function syncRoomFromApi($roomId)
    {
        Log::info('[AUTO REPLY] Syncing room from API', ['room_id' => $roomId]);

        try {
            $response = Http::withHeaders($this->getHeaders())
                ->timeout(30)
                ->get($this->base_url . "/v1/rooms/{$roomId}");

            if (!$response->successful()) {
                Log::error('[AUTO REPLY] Failed to fetch room', [
                    'room_id' => $roomId,
                    'status' => $response->status()
                ]);
                return null;
            }

            $roomData = $response->json();
            $status = $roomData['status'] ?? 'unknown';
            $userId = $roomData['user_id'] ?? null;

            $isAssigned = ($status === 'assigned') ? 1 : 0;

            Log::info('[AUTO REPLY] Room data from API', [
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

            Log::info('[AUTO REPLY] Room synced to DB', [
                'room_id' => $room->room_id,
                'is_assigned' => $room->is_assigned,
                'agent_id' => $room->agent_id
            ]);

            return $room;
        } catch (\Exception $e) {
            Log::error('[AUTO REPLY] Exception syncing room', [
                'room_id' => $roomId,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    public function processNewMessages()
    {
        Log::info('[AUTO REPLY] Starting batch processing');

        $processedCount = 0;
        $errors = [];
        $startTime = microtime(true);

        try {
            $response = Http::withHeaders($this->getHeaders())
                ->timeout(30)
                ->get($this->base_url . '/v1/rooms', [
                    'limit' => 3,
                    'offset' => 0
                ]);

            if (!$response->successful()) {
                $error = 'Failed to fetch rooms';
                Log::error('[AUTO REPLY] ' . $error, [
                    'status' => $response->status()
                ]);
                return ['processed' => 0, 'errors' => [$error]];
            }

            $roomsData = $response->json();

            if (!isset($roomsData['data']) || !is_array($roomsData['data'])) {
                Log::warning('[AUTO REPLY] No rooms data');
                return ['processed' => 0, 'errors' => ['No rooms data']];
            }

            $roomCount = count($roomsData['data']);
            Log::info('[AUTO REPLY] Rooms fetched', ['total_rooms' => $roomCount]);

            foreach ($roomsData['data'] as $index => $roomData) {
                $roomId = $roomData['id'];
                $status = $roomData['status'] ?? 'unknown';
                $userId = $roomData['user_id'] ?? null;

                Log::info('[AUTO REPLY] Processing room', [
                    'index' => ($index + 1) . '/' . $roomCount,
                    'room_id' => $roomId,
                    'status' => $status
                ]);

                $isAssigned = ($status === 'assigned') ? 1 : 0;

                QontakRoom::updateOrCreate(
                    ['room_id' => $roomId],
                    [
                        'agent_id' => $userId,
                        'is_assigned' => $isAssigned,
                        'last_message_at' => isset($roomData['last_message_at'])
                            ? \Carbon\Carbon::parse($roomData['last_message_at'])
                            : now(),
                    ]
                );

                $url = $this->core_url . '/' . $this->organization_id . '/messages/rooms/' . $roomId;

                $msgResponse = Http::withHeaders($this->getHeaders())
                    ->timeout(30)
                    ->get($url, [
                        'limit' => 10,
                        'offset' => 1
                    ]);

                if (!$msgResponse->successful()) {
                    $error = "Failed to fetch messages for room {$roomId}";
                    $errors[] = $error;
                    Log::error('[AUTO REPLY] ' . $error);
                    continue;
                }

                $messages = $msgResponse->json();

                if (!isset($messages['data']) || !is_array($messages['data'])) {
                    continue;
                }

                Log::info('[AUTO REPLY] Messages fetched', [
                    'room_id' => $roomId,
                    'count' => count($messages['data'])
                ]);

                $fiveMinutesAgo = now()->subMinutes(5);

                foreach ($messages['data'] as $message) {
                    $participantType = $message['participant_type'] ?? 'unknown';

                    if (in_array($participantType, ['agent', 'bot', 'system'])) {
                        continue;
                    }

                    $messageCreatedAt = isset($message['created_at'])
                        ? \Carbon\Carbon::parse($message['created_at'])
                        : null;

                    if (!$messageCreatedAt || $messageCreatedAt->lessThan($fiveMinutesAgo)) {
                        continue;
                    }

                    Log::info('[AUTO REPLY] Recent customer message found', [
                        'room_id' => $roomId,
                        'message_id' => $message['id'] ?? null
                    ]);

                    $result = $this->processMessage(
                        $roomId,
                        $message['text'] ?? '',
                        $message['id'] ?? null,
                        $message['sender']['phone'] ?? null
                    );

                    if ($result) {
                        $processedCount++;
                    }
                }
            }

            $duration = round(microtime(true) - $startTime, 2);

            Log::info('[AUTO REPLY] Batch processing complete', [
                'processed' => $processedCount,
                'errors' => count($errors),
                'duration_seconds' => $duration
            ]);

            return [
                'processed' => $processedCount,
                'errors' => $errors,
            ];
        } catch (\Exception $e) {
            Log::error('[AUTO REPLY] Fatal exception', [
                'exception' => $e->getMessage()
            ]);

            return [
                'processed' => $processedCount,
                'errors' => array_merge($errors, [$e->getMessage()])
            ];
        }
    }
}
