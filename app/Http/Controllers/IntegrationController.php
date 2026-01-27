<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class IntegrationController extends Controller
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
            'cursor',
            'cursor_direction',
            'limit',
            'offset',
            'order_by',
            'order_direction',
            'target_channel'
        ]));

        $response = Http::withHeaders($this->getHeaders())
            ->get($this->base_url . '/v1/integrations', $params);

            /** @var Response $response */
        $integrations = $response->json();
        return view('integrations.index', compact('integrations'));
    }

    public function show($channelId)
    {
        $response = Http::withHeaders($this->getHeaders())
            ->get($this->base_url . "/v1/integrations/{$channelId}");

            /** @var Response $response */
        $channel = $response->json();
        return view('integrations.show', compact('channel', 'channelId'));
    }
}