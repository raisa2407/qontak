<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class UserController extends Controller
{
    private $api_token;

    public function __construct()
    {
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
            'role',
            'order_by',
            'order_direction',
            'limit',
            'offset',
            'is_counted'
        ]));

        if (empty($params['limit'])) {
            $params['limit'] = 50;
        }

        if (empty($params['offset'])) {
            $params['offset'] = 0;
        }

        $response = Http::withHeaders($this->getHeaders())
            ->get('https://service-chat.qontak.com/api/open/users', $params);

        /** @var Response $response */
        $users = $response->successful() ? $response->json() : ['data' => []];

        return view('users.index', compact('users'));
    }

    public function show($id)
    {
        $response = Http::withHeaders($this->getHeaders())
            ->get("https://service-chat.qontak.com/api/open/users/{$id}");

        /** @var Response $response */
        $user = $response->successful() ? $response->json() : null;

        return view('users.show', compact('user', 'id'));
    }
}
