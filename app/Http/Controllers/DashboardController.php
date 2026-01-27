<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

class DashboardController extends Controller
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

    public function index()
    {
        $allRooms = $this->fetchAllRooms();
        $expiredRooms = $this->fetchExpiredRooms();
        $users = $this->fetchUsers();

        $stats = $this->calculateStats($allRooms, $expiredRooms);
        $userStats = $this->calculateUserStats($users);
        $chartData = $this->prepareChartData($allRooms);

        return view('dashboard.index', compact('stats', 'userStats', 'chartData'));
    }

    private function fetchAllRooms()
    {
        $response = Http::withHeaders($this->getHeaders())
            ->get($this->base_url . '/v1/rooms', ['limit' => 1000]);

    /** @var Response $response */
        if ($response->successful()) {
            $data = $response->json();
            if (isset($data['data']['response']['data'])) {
                return $data['data']['response']['data'];
            }
            if (isset($data['data']['response'])) {
                return $data['data']['response'];
            }
            if (isset($data['data'])) {
                return $data['data'];
            }
        }

        return [];
    }

    private function fetchExpiredRooms()
    {
        $response = Http::withHeaders($this->getHeaders())
            ->get($this->base_url . '/v1/rooms/list/expired');

            /** @var Response $response */
        if ($response->successful()) {
            $data = $response->json();
            if (isset($data['data']['data'])) {
                return $data['data']['data'];
            }
            if (isset($data['data'])) {
                return $data['data'];
            }
        }

        return [];
    }

    private function fetchUsers()
    {
        $response = Http::withHeaders($this->getHeaders())
            ->get('https://api.mekari.com/qontak/chat/v1/users', ['limit' => 1000]);

            /** @var Response $response */
        if ($response->successful()) {
            $data = $response->json();
            return $data['data'] ?? [];
        }

        return [];
    }

    private function calculateStats($rooms, $expiredRooms)
    {
        $roomsCollection = collect($rooms);
        
        $totalRooms = $roomsCollection->count();
        $resolved = $roomsCollection->where('status', 'resolved')->count();
        $active = $roomsCollection->where('is_active', true)->count();
        $pending = $roomsCollection->where('status', 'pending')->count();
        
        $unassigned = $roomsCollection->filter(function($room) {
            return empty($room['agent_ids']) || count($room['agent_ids']) === 0;
        })->count();

        $expired = is_array($expiredRooms) ? count($expiredRooms) : 0;
        
        $expiringToday = $roomsCollection->filter(function($room) {
            if (!isset($room['session_at'])) return false;
            try {
                $sessionDate = Carbon::parse($room['session_at']);
                $expireDate = $sessionDate->copy()->addHours(24);
                $now = Carbon::now();
                return $expireDate->isToday() && $expireDate->isFuture();
            } catch (\Exception $e) {
                return false;
            }
        })->count();

        $unreadCount = $roomsCollection->sum(function($room) {
            return $room['unread_count'] ?? 0;
        });

        return [
            'total' => $totalRooms,
            'resolved' => $resolved,
            'active' => $active,
            'pending' => $pending,
            'unassigned' => $unassigned,
            'expired' => $expired,
            'expiring_today' => $expiringToday,
            'unread' => $unreadCount,
        ];
    }

    private function calculateUserStats($users)
    {
        $usersList = is_array($users) ? collect($users) : collect([]);
        
        $totalUsers = $usersList->count();
        $roles = $usersList->groupBy('role')->map->count();
        
        $onlineUsers = $usersList->where('is_online', true)->count();
        $offlineUsers = $totalUsers - $onlineUsers;

        return [
            'total' => $totalUsers,
            'online' => $onlineUsers,
            'offline' => $offlineUsers,
            'by_role' => $roles->toArray(),
        ];
    }

    private function prepareChartData($rooms)
    {
        $roomsCollection = collect($rooms);
        
        $last7Days = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i)->format('Y-m-d');
            $last7Days[$date] = [
                'created' => 0,
                'resolved' => 0,
            ];
        }

        foreach ($roomsCollection as $room) {
            if (isset($room['created_at'])) {
                try {
                    $createdDate = Carbon::parse($room['created_at'])->format('Y-m-d');
                    if (isset($last7Days[$createdDate])) {
                        $last7Days[$createdDate]['created']++;
                        
                        if (isset($room['status']) && $room['status'] === 'resolved') {
                            $last7Days[$createdDate]['resolved']++;
                        }
                    }
                } catch (\Exception $e) {
                }
            }

            if (isset($room['resolved_at']) && isset($room['status']) && $room['status'] === 'resolved') {
                try {
                    $resolvedDate = Carbon::parse($room['resolved_at'])->format('Y-m-d');
                    if (isset($last7Days[$resolvedDate])) {
                        $createdDate = isset($room['created_at']) ? Carbon::parse($room['created_at'])->format('Y-m-d') : null;
                        
                        if ($resolvedDate !== $createdDate) {
                            $last7Days[$resolvedDate]['resolved']++;
                        }
                    }
                } catch (\Exception $e) {
                }
            }
        }

        return [
            'daily' => $last7Days,
        ];
    }
}