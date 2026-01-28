<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class UserController extends Controller
{
    private $api_token;
    private $base_url;
    private $organization_id;

    public function __construct()
    {
        $this->api_token = env('QONTAK_API_TOKEN');
        $this->organization_id = env('QONTAK_ORGANIZATION_ID');
        $this->base_url = 'https://chat-service.qontak.com/api/core/v1/' . $this->organization_id;
    }

    private function getHeaders()
    {
        return [
            'Authorization' => 'Bearer ' . $this->api_token,
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ];
    }

    public function index(Request $request)
    {
        $params = [
            'limit' => $request->input('limit', 10),
            'offset' => $request->input('offset', 1),
            'is_counted' => true
        ];

        if ($request->filled('query')) {
            $params['query'] = $request->input('query');
        }

        if ($request->filled('role')) {
            $params['role'] = $request->input('role');
        }

        if ($request->filled('order_by')) {
            $params['order_by'] = $request->input('order_by');
        }

        if ($request->filled('order_direction')) {
            $params['order_direction'] = $request->input('order_direction');
        }

        $response = Http::withHeaders($this->getHeaders())
            ->get($this->base_url . '/users', $params);

        /** @var Response $response */
        $users = $response->successful() ? $response->json() : ['status' => 'error', 'data' => []];

        return view('users.index', compact('users'));
    }

    public function show($id)
    {
        $response = Http::withHeaders($this->getHeaders())
            ->get($this->base_url . "/users/{$id}");

        /** @var Response $response */
        $user = $response->successful() ? $response->json() : null;

        return view('users.show', compact('user', 'id'));
    }
}