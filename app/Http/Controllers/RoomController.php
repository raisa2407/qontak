<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class RoomController extends Controller
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

        $response = Http::withHeaders($this->getHeaders())
            ->get($this->base_url . '/v1/rooms', $params);

        $rooms = $response->json();
        return view('rooms.index', compact('rooms'));
    }

    public function show($id)
    {
        $response = Http::withHeaders($this->getHeaders())
            ->get($this->base_url . "/v1/rooms/{$id}");

        $room = $response->json();
        return view('rooms.show', compact('room', 'id'));
    }

    public function rename(Request $request, $id)
    {
        $request->validate(['name' => 'required|string']);

        $response = Http::withHeaders($this->getHeaders())
            ->asMultipart()
            ->put($this->base_url . "/v1/rooms/{$id}", [
                ['name' => 'name', 'contents' => $request->name]
            ]);

        return back()->with('success', 'Room renamed successfully!');
    }

    public function histories($id)
    {
        $response = Http::withHeaders($this->getHeaders())
            ->get($this->base_url . "/v1/rooms/{$id}/histories");

        $histories = $response->json();
        return view('rooms.histories', compact('histories', 'id'));
    }

    public function participants($id, Request $request)
    {
        $params = array_filter($request->only(['offset', 'limit']));

        if (empty($params['limit'])) {
            $params['limit'] = 100;
        }

        $response = Http::withHeaders($this->getHeaders())
            ->get($this->base_url . "/v1/rooms/{$id}/participants", $params);

        $participants = $response->json();

        return view('rooms.participants', compact('participants', 'id'));
    }

    public function specificInfo()
    {
        $response = Http::withHeaders($this->getHeaders())
            ->get($this->base_url . '/v1/rooms/specific/info');

        $info = $response->json();
        return response()->json($info);
    }

    public function assignableAgents($id)
    {
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

    public function assignAgent(Request $request, $id, $userId)
    {
        $response = Http::withHeaders($this->getHeaders())
            ->post($this->base_url . "/v1/rooms/{$id}/agents/{$userId}");

        return back()->with('success', 'Agent assigned successfully!');
    }

    public function autoTakeover()
    {
        $response = Http::withHeaders($this->getHeaders())
            ->get($this->base_url . '/v1/rooms/auto_takeover/agents/assignable');

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
                    ->post($this->base_url . '/v1/rooms/auto_takeover', [
                        'agent_id' => $bestAgent['id']
                    ]);

                return back()->with('success', 'Room auto takeover successful to ' . $bestAgent['full_name'] . '!');
            }
        }

        $response = Http::withHeaders($this->getHeaders())
            ->post($this->base_url . '/v1/rooms/auto_takeover');

        return back()->with('success', 'Room auto takeover successful!');
    }

    public function takeover($id)
    {
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

        $response = Http::withHeaders($this->getHeaders())
            ->post($this->base_url . "/v1/rooms/{$id}/takeover");

        return back()->with('success', 'Room takeover successful!');
    }

    public function listExpired()
    {
        $response = Http::withHeaders($this->getHeaders())
            ->get($this->base_url . '/v1/rooms/list/expired');

        $rooms = $response->json();
        return view('rooms.expired', compact('rooms'));
    }

    public function markAllAsRead($id)
    {
        $response = Http::withHeaders($this->getHeaders())
            ->put($this->base_url . "/v1/rooms/{$id}/mark_all_as_read");

        return back()->with('success', 'All messages marked as read!');
    }

    public function handover($id, $userId)
    {
        $response = Http::withHeaders($this->getHeaders())
            ->post($this->base_url . "/v1/rooms/{$id}/handover/{$userId}");

        return back()->with('success', 'Room handed over successfully!');
    }

    public function resolveExpired()
    {
        $response = Http::withHeaders($this->getHeaders())
            ->put($this->base_url . '/v1/rooms/resolve_expired');

        return back()->with('success', 'Expired rooms resolved!');
    }

    public function resolve($id)
    {
        $response = Http::withHeaders($this->getHeaders())
            ->put($this->base_url . "/v1/rooms/{$id}/resolve");

        return back()->with('success', 'Room resolved successfully!');
    }

    public function addTag(Request $request, $id)
    {
        $request->validate(['tags' => 'required']);

        $response = Http::withHeaders($this->getHeaders())
            ->post($this->base_url . "/v1/rooms/{$id}/tags", [
                'tags' => $request->tags
            ]);

        return back()->with('success', 'Tags added successfully!');
    }

    public function removeTag(Request $request, $id)
    {
        $response = Http::withHeaders($this->getHeaders())
            ->delete($this->base_url . "/v1/rooms/{$id}/tags", [
                'tags' => $request->tags
            ]);

        return back()->with('success', 'Tags removed successfully!');
    }

    public function messages($id, Request $request)
    {
        $params = [
            'limit' => $request->input('limit', 50),
            'offset' => $request->input('offset', 0),
            'order_by' => 'created_at',
            'order_direction' => 'asc'
        ];

        $response = Http::withHeaders($this->getHeaders())
            ->get($this->base_url . "/v1/rooms/{$id}/messages", $params);

        if (!$response->successful()) {
            return view('rooms.messages', [
                'messages' => ['data' => []],
                'id' => $id
            ]);
        }

        $messages = $response->json();

        return view('rooms.messages', compact('messages', 'id'));
    }

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
        $response = Http::withHeaders($this->getHeaders())
            ->get($this->base_url . '/v1/message_interactions');

        if ($response->successful()) {
            return response()->json($response->json());
        }

        return response()->json(['error' => 'Failed to get message interactions'], 500);
    }

    public function getRoomInteractions()
    {
        $response = Http::withHeaders($this->getHeaders())
            ->get($this->base_url . '/v1/room_interactions');

        if ($response->successful()) {
            return response()->json($response->json());
        }

        return response()->json(['error' => 'Failed to get room interactions'], 500);
    }
}
