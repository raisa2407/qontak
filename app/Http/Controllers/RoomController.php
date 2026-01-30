<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Services\RoomService;
use App\Services\AutoReplyService;
use App\Models\QontakRoom;

class RoomController extends Controller
{
    private $base_url;
    private $core_url;
    private $api_token;
    private $organization_id;
    private $roomService;
    private $autoReplyService;

    public function __construct(RoomService $roomService, AutoReplyService $autoReplyService)
    {
        $this->base_url = env('QONTAK_BASE_URL', 'https://service-chat.qontak.com/api/open');
        $this->core_url = env('QONTAK_CORE_URL', 'https://chat-service.qontak.com/api/core/v1');
        $this->api_token = env('QONTAK_API_TOKEN');
        $this->organization_id = env('QONTAK_ORGANIZATION_ID', 'bb315b54-030f-4e51-8583-8c4aec379964');
        $this->roomService = $roomService;
        $this->autoReplyService = $autoReplyService;
    }

    private function getHeaders()
    {
        return [
            'Authorization' => 'Bearer ' . $this->api_token,
            'Accept' => 'application/json',
        ];
    }

    public function index(Request $request)
    {
        $params = array_filter($request->only([
            'query',
            'status',
            'sessions',
            'tags',
            'user_ids',
            'target_channel',
            'untagged',
            'response_status',
            'type',
            'start_date',
            'end_date',
            'time_offsets',
            'offset',
            'limit'
        ]));

        /** @var \Illuminate\Http\Client\Response $response */
        $response = Http::withHeaders($this->getHeaders())
            ->get($this->base_url . '/v1/rooms', $params);

        $rooms = $response->json();

        $autoTakeoverResponse = Http::withHeaders($this->getHeaders())
            ->get($this->base_url . '/v1/rooms/auto_takeover/agents/assignable');
        $autoTakeoverAgents = $autoTakeoverResponse->json();

        $agentId = env('QONTAK_AGENT_ID', '7180a56b-27d6-4cbc-85d9-55b0edc9c0c6');

        Log::info('[INDEX] Starting auto-assign check', [
            'agent_id' => $agentId,
            'timestamp' => now()->toDateTimeString()
        ]);

        $result = $this->roomService->autoAssignRooms($agentId);

        Log::info('[INDEX] Auto-assign result', [
            'assigned' => $result['assigned'],
            'errors' => count($result['errors'])
        ]);

        if ($result['assigned'] > 0) {
            session()->flash('success', "Successfully assigned {$result['assigned']} rooms to agent.");
        }

        if (!empty($result['errors'])) {
            session()->flash('warning', 'Some rooms failed to assign: ' . implode(', ', $result['errors']));
        }

        return view('rooms.index', compact('rooms', 'autoTakeoverAgents'));
    }

    public function messages($id, Request $request)
    {
        Log::info('[ROOM MESSAGES] Page loaded', [
            'room_id' => $id,
            'timestamp' => now()->toDateTimeString()
        ]);

        $params = [
            'limit' => $request->input('limit', 15),
            'offset' => $request->input('offset', 1),
            'cursor' => $request->input('cursor', '')
        ];

        $url = $this->core_url . '/' . $this->organization_id . '/messages/rooms/' . $id;

        try {
            $response = Http::withHeaders($this->getHeaders())
                ->get($url, $params);

            Log::info('[ROOM MESSAGES] Messages API Response', [
                'status' => $response->status(),
                'url' => $url,
                'params' => $params
            ]);

            if (!$response->successful()) {
                Log::error('[ROOM MESSAGES] Failed to fetch messages', [
                    'status' => $response->status(),
                    'response' => $response->body()
                ]);

                return view('rooms.messages', [
                    'messages' => ['data' => []],
                    'id' => $id
                ]);
            }

            $messages = $response->json();

            if (isset($messages['data']) && is_array($messages['data'])) {
                $messages['data'] = array_reverse($messages['data']);
            }

            $this->roomService->syncRoomFromApi($id);

            Log::info('[ROOM MESSAGES] Starting auto-reply check for recent messages');
            $this->processAutoReplyForRoom($id, $messages);

            return view('rooms.messages', compact('messages', 'id'));
        } catch (\Exception $e) {
            Log::error('[ROOM MESSAGES] Exception in messages', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return view('rooms.messages', [
                'messages' => ['data' => []],
                'id' => $id
            ]);
        }
    }

    private function processAutoReplyForRoom($roomId, $messages)
    {
        Log::info('[ROOM MESSAGES] Processing auto-reply for room', [
            'room_id' => $roomId,
            'total_messages' => isset($messages['data']) ? count($messages['data']) : 0
        ]);

        if (!isset($messages['data']) || !is_array($messages['data'])) {
            Log::info('[ROOM MESSAGES] No messages to process');
            return;
        }

        $processedCount = 0;
        $fiveMinutesAgo = now()->subMinutes(5);

        foreach ($messages['data'] as $message) {
            $participantType = $message['participant_type'] ?? 'unknown';
            $messageText = $message['text'] ?? '';
            $messageId = $message['id'] ?? null;

            if (in_array($participantType, ['agent', 'bot', 'system'])) {
                continue;
            }

            $messageCreatedAt = isset($message['created_at'])
                ? \Carbon\Carbon::parse($message['created_at'])
                : null;

            if (!$messageCreatedAt || $messageCreatedAt->lessThan($fiveMinutesAgo)) {
                continue;
            }

            Log::info('[ROOM MESSAGES] Recent customer message found', [
                'room_id' => $roomId,
                'message_id' => $messageId,
                'text' => substr($messageText, 0, 50)
            ]);

            $result = $this->autoReplyService->processMessage(
                $roomId,
                $messageText,
                $messageId,
                $message['sender']['phone'] ?? null
            );

            if ($result) {
                $processedCount++;
                Log::info('[ROOM MESSAGES] Auto-reply sent', [
                    'message_id' => $messageId,
                    'total_processed' => $processedCount
                ]);
            }
        }

        Log::info('[ROOM MESSAGES] Auto-reply processing complete', [
            'room_id' => $roomId,
            'total_processed' => $processedCount
        ]);
    }

    public function show($id)
    {
        /** @var \Illuminate\Http\Client\Response $response */
        $response = Http::withHeaders($this->getHeaders())
            ->get($this->base_url . "/v1/rooms/{$id}");

        $room = $response->json();
        $this->roomService->syncRoomFromApi($id);

        return view('rooms.show', compact('room', 'id'));
    }

    public function assignAgent(Request $request, $id, $userId)
    {
        Log::info('[ASSIGN AGENT] Manual assignment request', [
            'room_id' => $id,
            'user_id' => $userId
        ]);

        /** @var \Illuminate\Http\Client\Response $response */
        $response = Http::withHeaders($this->getHeaders())
            ->post($this->base_url . "/v1/rooms/{$id}/agents/{$userId}");

        if ($response->successful()) {
            QontakRoom::updateOrCreate(
                ['room_id' => $id],
                [
                    'agent_id' => $userId,
                    'is_assigned' => true,
                    'assigned_at' => now(),
                ]
            );

            Log::info('[ASSIGN AGENT] Agent assigned successfully', [
                'room_id' => $id,
                'agent_id' => $userId
            ]);

            return back()->with('success', 'Agent assigned successfully!');
        }

        Log::error('[ASSIGN AGENT] Failed to assign agent', [
            'room_id' => $id,
            'status' => $response->status(),
            'response' => $response->body()
        ]);

        return back()->with('error', 'Failed to assign agent: ' . $response->body());
    }

    public function rename(Request $request, $id)
    {
        $request->validate(['name' => 'required|string']);

        /** @var \Illuminate\Http\Client\Response $response */
        $response = Http::withHeaders($this->getHeaders())
            ->asMultipart()
            ->put($this->base_url . "/v1/rooms/{$id}", [
                ['name' => 'name', 'contents' => $request->name]
            ]);

        return back()->with('success', 'Room renamed successfully!');
    }

    public function histories($id)
    {
        /** @var \Illuminate\Http\Client\Response $response */
        $response = Http::withHeaders($this->getHeaders())
            ->get($this->base_url . "/v1/rooms/{$id}/histories");

        $histories = $response->json();
        return view('rooms.histories', compact('histories', 'id'));
    }

    public function participants($id, Request $request)
    {
        $url = $this->base_url . "/v1/rooms/{$id}/participants";

        try {
            /** @var \Illuminate\Http\Client\Response $response */
            $response = Http::withHeaders($this->getHeaders())->get($url);

            Log::info('Participants API Response', [
                'status' => $response->status(),
                'body' => $response->body(),
                'url' => $url
            ]);

            if (!$response->successful()) {
                return view('rooms.participants', [
                    'participants' => [
                        'status' => 'error',
                        'data' => [],
                        'http_status' => $response->status(),
                        'error_body' => $response->body()
                    ],
                    'id' => $id
                ]);
            }

            $participants = $response->json();
            return view('rooms.participants', compact('participants', 'id'));
        } catch (\Exception $e) {
            Log::error('Exception in participants', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return view('rooms.participants', [
                'participants' => [
                    'status' => 'error',
                    'data' => [],
                    'exception' => $e->getMessage()
                ],
                'id' => $id
            ]);
        }
    }

    public function specificInfo()
    {
        /** @var \Illuminate\Http\Client\Response $response */
        $response = Http::withHeaders($this->getHeaders())
            ->get($this->base_url . '/v1/rooms/specific/info');

        $info = $response->json();
        return response()->json($info);
    }

    public function assignableAgents($id)
    {
        /** @var \Illuminate\Http\Client\Response $response */
        $response = Http::withHeaders($this->getHeaders())
            ->get($this->base_url . "/v1/rooms/{$id}/agents/assignable");

        $agents = $response->json();

        if (isset($agents['data']) && count($agents['data']) > 0) {
            $agents['data'] = collect($agents['data'])->map(function ($agent) {
                $agent['channel_count'] = count($agent['channels'] ?? []);
                $agent['user_count'] = count($agent['users'] ?? []);
                return $agent;
            })->sortBy('channel_count')->values()->all();
        }

        return view('rooms.assignable-agents', compact('agents', 'id'));
    }

    // public function assignAgent(Request $request, $id, $userId)
    // {
    //     /** @var \Illuminate\Http\Client\Response $response */
    //     $response = Http::withHeaders($this->getHeaders())
    //         ->post($this->base_url . "/v1/rooms/{$id}/agents/{$userId}");

    //     return back()->with('success', 'Agent assigned successfully!');
    // }


    // public function autoTakeover(Request $request)
    // {
    //     $agentId = $request->input('agent_id');
    //
    //     if ($agentId) {
    //         $response = Http::withHeaders($this->getHeaders())
    //             ->post($this->base_url . '/v1/rooms/auto_takeover', [
    //                 'agent_id' => $agentId
    //             ]);
    //
    //         $agentName = $request->input('agent_name', 'selected agent');
    //         return back()->with('success', 'Room auto takeover successful to ' . $agentName . '!');
    //     }
    //
    //     $response = Http::withHeaders($this->getHeaders())
    //         ->get($this->base_url . '/v1/rooms/auto_takeover/agents/assignable');
    //
    //     $agents = $response->json();
    //
    //     if (isset($agents['data']) && count($agents['data']) > 0) {
    //         $bestAgent = collect($agents['data'])
    //             ->where('is_online', true)
    //             ->map(function ($agent) {
    //                 $agent['channel_count'] = count($agent['channels'] ?? []);
    //                 return $agent;
    //             })
    //             ->sortBy('channel_count')
    //             ->first();
    //
    //         if ($bestAgent) {
    //             Http::withHeaders($this->getHeaders())
    //                 ->post($this->base_url . '/v1/rooms/auto_takeover', [
    //                     'agent_id' => $bestAgent['id']
    //                 ]);
    //
    //             return back()->with('success', 'Room auto takeover successful to ' . $bestAgent['full_name'] . '!');
    //         }
    //     }
    //
    //     $response = Http::withHeaders($this->getHeaders())
    //         ->post($this->base_url . '/v1/rooms/auto_takeover');
    //
    //     return back()->with('success', 'Room auto takeover successful!');
    // }

    public function listExpired(Request $request)
    {
        $params = array_filter($request->only([
            'query',
            'status',
            'sessions',
            'tags',
            'user_ids',
            'target_channel',
            'untagged',
            'response_status',
            'type',
            'start_date',
            'end_date',
            'time_offsets',
            'offset',
            'limit'
        ]));

        $params['status'] = 'expired';

        /** @var \Illuminate\Http\Client\Response $response */
        $response = Http::withHeaders($this->getHeaders())
            ->get($this->base_url . '/v1/rooms/list/expired', $params);

        $rooms = $response->json();
        return view('rooms.expired', compact('rooms'));
    }

    public function takeover($id)
    {
        /** @var \Illuminate\Http\Client\Response $response */
        $response = Http::withHeaders($this->getHeaders())
            ->get($this->base_url . "/v1/rooms/{$id}/agents/assignable");

        $agents = $response->json();

        if (isset($agents['data']) && count($agents['data']) > 0) {
            $bestAgent = collect($agents['data'])
                ->where('is_online', true)
                ->map(function ($agent) {
                    $agent['channel_count'] = count($agent['channels'] ?? []);
                    return $agent;
                })
                ->sortBy('channel_count')
                ->first();

            if ($bestAgent) {
                Http::withHeaders($this->getHeaders())
                    ->post($this->base_url . "/v1/rooms/{$id}/agents/{$bestAgent['id']}");

                return back()->with('success', 'Room takeover successful to ' . $bestAgent['full_name'] . '!');
            }
        }

        /** @var \Illuminate\Http\Client\Response $response */
        $response = Http::withHeaders($this->getHeaders())
            ->post($this->base_url . "/v1/rooms/{$id}/takeover");

        return back()->with('success', 'Room takeover successful!');
    }

    public function markAllAsRead($id)
    {
        /** @var \Illuminate\Http\Client\Response $response */
        $response = Http::withHeaders($this->getHeaders())
            ->put($this->base_url . "/v1/rooms/{$id}/mark_all_as_read");

        return back()->with('success', 'All messages marked as read!');
    }

    public function handover($id, $userId)
    {
        /** @var \Illuminate\Http\Client\Response $response */
        $response = Http::withHeaders($this->getHeaders())
            ->post($this->base_url . "/v1/rooms/{$id}/handover/{$userId}");

        return back()->with('success', 'Room handed over successfully!');
    }

    public function resolveExpired()
    {
        /** @var \Illuminate\Http\Client\Response $response */
        $response = Http::withHeaders($this->getHeaders())
            ->put($this->base_url . '/v1/rooms/resolve_expired');

        return back()->with('success', 'Expired rooms resolved!');
    }

    public function resolve($id)
    {
        /** @var \Illuminate\Http\Client\Response $response */
        $response = Http::withHeaders($this->getHeaders())
            ->put($this->base_url . "/v1/rooms/{$id}/resolve");

        return back()->with('success', 'Room resolved successfully!');
    }

    public function addTag(Request $request, $id)
    {
        $request->validate(['tags' => 'required']);

        /** @var \Illuminate\Http\Client\Response $response */
        $response = Http::withHeaders($this->getHeaders())
            ->post($this->base_url . "/v1/rooms/{$id}/tags", [
                'tags' => $request->tags
            ]);

        return back()->with('success', 'Tags added successfully!');
    }

    public function removeTag(Request $request, $id)
    {
        /** @var \Illuminate\Http\Client\Response $response */
        $response = Http::withHeaders($this->getHeaders())
            ->delete($this->base_url . "/v1/rooms/{$id}/tags", [
                'tags' => $request->tags
            ]);

        return back()->with('success', 'Tags removed successfully!');
    }

    // public function messages($id, Request $request)
    // {
    //     $params = [
    //         'limit' => $request->input('limit', 15),
    //         'offset' => $request->input('offset', 1),
    //         'cursor' => $request->input('cursor', '')
    //     ];

    //     $url = $this->core_url . '/' . $this->organization_id . '/messages/rooms/' . $id;

    //     try {
    //         /** @var \Illuminate\Http\Client\Response $response */
    //         $response = Http::withHeaders($this->getHeaders())
    //             ->get($url, $params);

    //         Log::info('Messages API Response', [
    //             'status' => $response->status(),
    //             'url' => $url,
    //             'params' => $params
    //         ]);

    //         if (!$response->successful()) {
    //             return view('rooms.messages', [
    //                 'messages' => ['data' => []],
    //                 'id' => $id
    //             ]);
    //         }

    //         $messages = $response->json();

    //         if (isset($messages['data']) && is_array($messages['data'])) {
    //             $messages['data'] = array_reverse($messages['data']);
    //         }

    //         return view('rooms.messages', compact('messages', 'id'));
    //     } catch (\Exception $e) {
    //         Log::error('Exception in messages', [
    //             'message' => $e->getMessage(),
    //             'trace' => $e->getTraceAsString()
    //         ]);

    //         return view('rooms.messages', [
    //             'messages' => ['data' => []],
    //             'id' => $id
    //         ]);
    //     }
    // }

    public function sendMessage(Request $request, $id)
    {
        $request->validate([
            'message' => 'required|string',
            'type' => 'nullable|string|in:text,image,video,audio,document,voice'
        ]);

        $response = Http::withHeaders($this->getHeaders())
            ->post($this->base_url . "/v1/rooms/{$id}/messages", [
                'message' => $request->message,
                'type' => $request->type ?? 'text',
            ]);

        return back()->with('success', 'Message sent successfully!');
    }

    public function sendWhatsAppMessage(Request $request, $id)
    {
        $request->validate([
            'type' => 'required|string|in:audio,contact,document,image,location,story,system,text,video,voice',
            'text' => 'required_if:type,text',
            'file' => 'required_if:type,audio,document,image,video,voice'
        ]);

        $multipart = [
            ['name' => 'room_id', 'contents' => $id],
            ['name' => 'type', 'contents' => $request->type],
        ];

        if ($request->local_id) {
            $multipart[] = ['name' => 'local_id', 'contents' => $request->local_id];
        }

        if ($request->created_at) {
            $multipart[] = ['name' => 'created_at', 'contents' => $request->created_at];
        }

        if ($request->type === 'text' && $request->text) {
            $multipart[] = ['name' => 'text', 'contents' => $request->text];
        }

        if ($request->hasFile('file')) {
            $multipart[] = [
                'name' => 'file',
                'contents' => fopen($request->file('file')->getRealPath(), 'r'),
                'filename' => $request->file('file')->getClientOriginalName()
            ];
        }

        $response = Http::withHeaders($this->getHeaders())
            ->asMultipart()
            ->post($this->base_url . '/v1/messages/whatsapp', $multipart);

        /** @var \Illuminate\Http\Client\Response $response */
        if ($response->successful()) {
            return back()->with('success', 'WhatsApp message sent successfully!');
        }

        return back()->with('error', 'Failed to send message: ' . $response->body());
    }

    public function sendWhatsAppBotMessage(Request $request, $id)
    {
        $request->validate([
            'type' => 'required|string|in:audio,contact,document,image,location,story,system,text,video,voice',
            'text' => 'required_if:type,text',
            'file' => 'required_if:type,audio,document,image,video,voice'
        ]);

        $multipart = [
            ['name' => 'room_id', 'contents' => $id],
            ['name' => 'type', 'contents' => $request->type],
        ];

        if ($request->created_at) {
            $multipart[] = ['name' => 'created_at', 'contents' => $request->created_at];
        }

        if ($request->type === 'text' && $request->text) {
            $multipart[] = ['name' => 'text', 'contents' => $request->text];
        }

        if ($request->hasFile('file')) {
            $multipart[] = [
                'name' => 'file',
                'contents' => fopen($request->file('file')->getRealPath(), 'r'),
                'filename' => $request->file('file')->getClientOriginalName()
            ];
        }

        /** @var \Illuminate\Http\Client\Response $response */
        $response = Http::withHeaders($this->getHeaders())
            ->asMultipart()
            ->post($this->base_url . '/v1/messages/whatsapp/bot', $multipart);

        if ($response->successful()) {
            return back()->with('success', 'WhatsApp bot message sent successfully!');
        }

        return back()->with('error', 'Failed to send bot message: ' . $response->body());
    }

    public function sendInteractiveMessage(Request $request, $id)
    {
        $request->validate([
            'type' => 'required|string',
            'interactive' => 'required|array',
            'interactive.body' => 'required|string',
        ]);

        $payload = $request->all();
        $payload['room_id'] = $id;

        /** @var \Illuminate\Http\Client\Response $response */
        $response = Http::withHeaders($this->getHeaders())
            ->post($this->base_url . '/v1/messages/whatsapp/interactive_message/bot', $payload);

        if ($response->successful()) {
            return back()->with('success', 'Interactive message sent successfully!');
        }

        return back()->with('error', 'Failed to send interactive message: ' . $response->body());
    }

    public function sendHsmMessage(Request $request, $id)
    {
        $request->validate([
            'message_template_id' => 'required|string',
        ]);

        $multipart = [
            ['name' => 'room_id', 'contents' => $id],
            ['name' => 'message_template_id', 'contents' => $request->message_template_id],
        ];

        /** @var \Illuminate\Http\Client\Response $response */
        $response = Http::withHeaders($this->getHeaders())
            ->asMultipart()
            ->post($this->base_url . '/v1/messages/whatsapp/hsm', $multipart);

        if ($response->successful()) {
            return back()->with('success', 'HSM message sent successfully!');
        }

        return back()->with('error', 'Failed to send HSM message: ' . $response->body());
    }

    public function showMessageInteractions()
    {
        /** @var \Illuminate\Http\Client\Response $response */
        $response = Http::withHeaders($this->getHeaders())
            ->get($this->base_url . '/v1/message_interactions');

        $settings = $response->successful() ? $response->json() : null;

        return view('interactions.message', compact('settings'));
    }

    public function updateMessageInteractions(Request $request)
    {
        $request->validate([
            'receive_message_from_agent' => 'required|boolean',
            'receive_message_from_customer' => 'required|boolean',
            'broadcast_log_status' => 'required|boolean',
            'status_message' => 'required|boolean',
            'url' => 'nullable|string|url',
        ]);

        $multipart = [
            ['name' => 'receive_message_from_agent', 'contents' => $request->receive_message_from_agent ? '1' : '0'],
            ['name' => 'receive_message_from_customer', 'contents' => $request->receive_message_from_customer ? '1' : '0'],
            ['name' => 'broadcast_log_status', 'contents' => $request->broadcast_log_status ? '1' : '0'],
            ['name' => 'status_message', 'contents' => $request->status_message ? '1' : '0'],
        ];

        if ($request->url) {
            $multipart[] = ['name' => 'url', 'contents' => $request->url];
        }

        /** @var \Illuminate\Http\Client\Response $response */
        $response = Http::withHeaders($this->getHeaders())
            ->asMultipart()
            ->put($this->base_url . '/v1/message_interactions', $multipart);

        if ($response->successful()) {
            return back()->with('success', 'Message interactions updated successfully!');
        }

        return back()->with('error', 'Failed to update message interactions: ' . $response->body());
    }

    public function showRoomInteractions()
    {
        /** @var \Illuminate\Http\Client\Response $response */
        $response = Http::withHeaders($this->getHeaders())
            ->get($this->base_url . '/v1/room_interactions');

        $settings = $response->successful() ? $response->json() : null;

        return view('interactions.room', compact('settings'));
    }

    public function updateRoomInteractions(Request $request)
    {
        $request->validate([
            'room_resolved' => 'required|boolean',
            'url' => 'nullable|string|url',
        ]);

        $multipart = [
            ['name' => 'room_resolved', 'contents' => $request->room_resolved ? '1' : '0'],
        ];

        if ($request->url) {
            $multipart[] = ['name' => 'url', 'contents' => $request->url];
        }

        /** @var \Illuminate\Http\Client\Response $response */
        $response = Http::withHeaders($this->getHeaders())
            ->asMultipart()
            ->put($this->base_url . '/v1/room_interactions', $multipart);

        if ($response->successful()) {
            return back()->with('success', 'Room interactions updated successfully!');
        }

        return back()->with('error', 'Failed to update room interactions: ' . $response->body());
    }

    public function getMessageInteractions()
    {
        /** @var \Illuminate\Http\Client\Response $response */
        $response = Http::withHeaders($this->getHeaders())
            ->get($this->base_url . '/v1/message_interactions');

        if ($response->successful()) {
            return response()->json($response->json());
        }

        return response()->json(['error' => 'Failed to get message interactions'], 500);
    }

    public function getRoomInteractions()
    {
        /** @var \Illuminate\Http\Client\Response $response */
        $response = Http::withHeaders($this->getHeaders())
            ->get($this->base_url . '/v1/room_interactions');

        if ($response->successful()) {
            return response()->json($response->json());
        }

        return response()->json(['error' => 'Failed to get room interactions'], 500);
    }
}
