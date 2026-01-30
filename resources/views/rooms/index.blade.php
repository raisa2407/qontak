@extends('layouts.layout')

@section('title', 'All Rooms')

@section('content')
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-label">Total Rooms</div>
                <div class="stat-value">{{ $rooms['meta']['total'] ?? 0 }}</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
                <div class="stat-label">Assigned</div>
                <div class="stat-value">{{ collect($rooms['data'] ?? [])->where('status', 'assigned')->count() }}</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);">
                <div class="stat-label">Unassigned</div>
                <div class="stat-value">{{ collect($rooms['data'] ?? [])->where('status', 'unassigned')->count() }}</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card" style="background: linear-gradient(135deg, #64748b 0%, #475569 100%);">
                <div class="stat-label">Resolved</div>
                <div class="stat-value">{{ collect($rooms['data'] ?? [])->where('status', 'resolved')->count() }}</div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span><i class="bi bi-funnel me-2"></i>Search & Filter</span>
            <button class="btn btn-sm btn-primary" type="button" data-bs-toggle="collapse" data-bs-target="#filterForm">
                <i class="bi bi-sliders"></i>
            </button>
        </div>
        <div class="collapse show" id="filterForm">
            <div class="card-body">
                <form method="GET" action="{{ route('rooms.index') }}">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Search</label>
                            <input type="text" class="form-control" name="query" value="{{ request('query') }}"
                                placeholder="Search rooms...">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Status</label>
                            <select class="form-select" name="status">
                                <option value="">All Status</option>
                                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending
                                </option>
                                <option value="resolved" {{ request('status') == 'resolved' ? 'selected' : '' }}>Resolved
                                </option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Type</label>
                            <select class="form-select" name="type">
                                <option value="">All Types</option>
                                <option value="customer" {{ request('type') == 'customer' ? 'selected' : '' }}>Customer
                                </option>
                                <option value="group" {{ request('type') == 'group' ? 'selected' : '' }}>Group</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Channel</label>
                            <select name="channels" class="form-control">
                                <option value="">-- Semua Channel --</option>

                                <option value="wa" {{ request('channels') == 'wa' ? 'selected' : '' }}>
                                    WhatsApp
                                </option>
                                <option value="wa_cloud" {{ request('channels') == 'wa_cloud' ? 'selected' : '' }}>
                                    WhatsApp Cloud
                                </option>
                                <option value="ig" {{ request('channels') == 'ig' ? 'selected' : '' }}>
                                    Instagram
                                </option>
                                <option value="telegram" {{ request('channels') == 'telegram' ? 'selected' : '' }}>
                                    Telegram
                                </option>
                                <option value="fb" {{ request('channels') == 'fb' ? 'selected' : '' }}>
                                    Facebook
                                </option>
                                <option value="email" {{ request('channels') == 'email' ? 'selected' : '' }}>
                                    Email
                                </option>
                                <option value="line" {{ request('channels') == 'line' ? 'selected' : '' }}>
                                    Line
                                </option>
                                <option value="twitter" {{ request('channels') == 'twitter' ? 'selected' : '' }}>
                                    Twitter
                                </option>
                                <option value="web_chat" {{ request('channels') == 'web_chat' ? 'selected' : '' }}>
                                    Web Chat
                                </option>
                                <option value="qontaklivechat_dot_com"
                                    {{ request('channels') == 'qontaklivechat_dot_com' ? 'selected' : '' }}>
                                    Qontak Live Chat
                                </option>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Start Date</label>
                            <input type="date" class="form-control" name="start_date"
                                value="{{ request('start_date') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">End Date</label>
                            <input type="date" class="form-control" name="end_date" value="{{ request('end_date') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Limit</label>
                            <select class="form-select" name="limit">
                                <option value="20" {{ request('limit', 20) == 20 ? 'selected' : '' }}>20</option>
                                <option value="50" {{ request('limit') == 50 ? 'selected' : '' }}>50</option>
                                <option value="100" {{ request('limit') == 100 ? 'selected' : '' }}>100</option>
                            </select>
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="bi bi-search me-2"></i>Search
                            </button>
                        </div>
                        <div class="col-12">
                            <a href="{{ route('rooms.index') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-clockwise me-2"></i>Reset
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span><i class="bi bi-list-ul me-2"></i>Rooms List</span>
            <button class="btn btn-sm btn-outline-primary" onclick="location.reload()">
                <i class="bi bi-arrow-clockwise"></i> Refresh
            </button>
        </div>
        <div class="card-body p-0">
            @if (isset($rooms['data']) && count($rooms['data']) > 0)
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead>
                            <tr>
                                <th>Room</th>
                                <th>Status</th>
                                <th>Type</th>
                                <th>Channel</th>
                                <th>Last Message</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($rooms['data'] as $room)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3"
                                                style="width: 40px; height: 40px;">
                                                <i class="bi bi-person"></i>
                                            </div>
                                            <div>
                                                <strong>{{ $room['name'] ?? 'Unknown' }}</strong>
                                                <br><small
                                                    class="text-muted">{{ Str::limit($room['id'] ?? '', 20) }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        @php
                                            $status = $room['status'] ?? 'unknown';
                                            $badgeClass = match ($status) {
                                                'assigned' => 'success',
                                                'unassigned' => 'warning',
                                                'resolved' => 'secondary',
                                                default => 'info',
                                            };
                                        @endphp
                                        <span class="badge bg-{{ $badgeClass }}">
                                            <i class="bi bi-circle-fill me-1" style="font-size: 0.5rem;"></i>
                                            {{ ucfirst($status) }}
                                        </span>
                                    </td>
                                    <td><span class="badge bg-light text-dark">{{ $room['type'] ?? '-' }}</span></td>
                                    <td>
                                        @php
                                            $channel = $room['channel'] ?? 'unknown';
                                            $channelIcon = match ($channel) {
                                                'whatsapp' => 'whatsapp',
                                                'instagram' => 'instagram',
                                                'telegram' => 'telegram',
                                                default => 'chat-dots',
                                            };
                                        @endphp
                                        <i class="bi bi-{{ $channelIcon }} me-1"></i>{{ ucfirst($channel) }}
                                    </td>
                                    <td>
                                        @if (isset($room['last_message']))
                                            <small
                                                class="text-muted">{{ Str::limit(is_array($room['last_message']) ? $room['last_message']['text'] ?? 'Media' : $room['last_message'], 35) }}</small>
                                        @else
                                            <small class="text-muted">-</small>
                                        @endif
                                    </td>
                                    <td><small
                                            class="text-muted">{{ isset($room['created_at']) ? \Carbon\Carbon::parse($room['created_at'])->diffForHumans() : '-' }}</small>
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="{{ route('rooms.show', $room['id']) }}"
                                                class="btn btn-sm btn-outline-primary" title="View Details">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a href="{{ route('rooms.messages', $room['id']) }}"
                                                class="btn btn-sm btn-outline-success" title="Messages">
                                                <i class="bi bi-chat"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if (isset($rooms['meta']))
                    <div class="card-footer bg-white d-flex justify-content-between align-items-center">
                        <div class="text-muted">
                            <small>Showing {{ ($rooms['meta']['offset'] ?? 0) + 1 }} to
                                {{ min(($rooms['meta']['offset'] ?? 0) + count($rooms['data']), $rooms['meta']['total'] ?? 0) }}
                                of {{ $rooms['meta']['total'] ?? 0 }} entries</small>
                        </div>
                        <div>
                            @if (request('offset', 0) > 0)
                                <a href="{{ route('rooms.index', array_merge(request()->all(), ['offset' => max(0, request('offset', 0) - request('limit', 20))])) }}"
                                    class="btn btn-sm btn-outline-secondary">
                                    <i class="bi bi-chevron-left"></i> Previous
                                </a>
                            @endif
                            @if (($rooms['meta']['offset'] ?? 0) + count($rooms['data']) < ($rooms['meta']['total'] ?? 0))
                                <a href="{{ route('rooms.index', array_merge(request()->all(), ['offset' => request('offset', 0) + request('limit', 20)])) }}"
                                    class="btn btn-sm btn-outline-secondary">
                                    Next <i class="bi bi-chevron-right"></i>
                                </a>
                            @endif
                        </div>
                    </div>
                @endif
            @else
                <div class="p-5 text-center">
                    <i class="bi bi-inbox display-1 text-muted"></i>
                    <h5 class="mt-3 text-muted">No rooms found</h5>
                    <p class="text-muted">Try adjusting your search filters</p>
                </div>
            @endif
        </div>
    </div>
@endsection
