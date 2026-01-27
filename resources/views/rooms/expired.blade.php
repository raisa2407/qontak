@extends('layouts.layout')

@section('title', 'Expired Rooms')

@section('content')
<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-clock-history me-2"></i>Expired Rooms Management</span>
        <form action="{{ route('rooms.resolve-expired') }}" method="POST" style="display:inline;">
            @csrf
            @method('PUT')
            <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('Resolve all expired rooms?')">
                <i class="bi bi-check-all me-2"></i>Resolve All
            </button>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <i class="bi bi-list-ul me-2"></i>Expired Rooms List
    </div>
    <div class="card-body p-0">
        @if(isset($rooms['data']) && count($rooms['data']) > 0)
            <div class="table-responsive">
                <table class="table mb-0">
                    <thead>
                        <tr>
                            <th>Room</th>
                            <th>Channel</th>
                            <th>Expired At</th>
                            <th>Duration</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($rooms['data'] as $room)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar bg-danger text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                                        <i class="bi bi-exclamation-triangle"></i>
                                    </div>
                                    <div>
                                        <strong>{{ $room['name'] ?? 'Unknown' }}</strong>
                                        <br><small class="text-muted">{{ Str::limit($room['id'] ?? 'N/A', 30) }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                @php
                                    $channel = $room['target_channel'] ?? 'unknown';
                                    $channelIcon = match($channel) {
                                        'whatsapp' => 'whatsapp',
                                        'instagram' => 'instagram',
                                        'telegram' => 'telegram',
                                        default => 'chat-dots'
                                    };
                                @endphp
                                <i class="bi bi-{{ $channelIcon }} me-1"></i>{{ ucfirst($channel) }}
                            </td>
                            <td>
                                <small class="text-danger">
                                    <i class="bi bi-clock me-1"></i>
                                    {{ isset($room['expired_at']) ? \Carbon\Carbon::parse($room['expired_at'])->format('d M Y, H:i') : '-' }}
                                </small>
                            </td>
                            <td>
                                <span class="badge bg-danger">
                                    {{ isset($room['expired_at']) ? \Carbon\Carbon::parse($room['expired_at'])->diffForHumans() : '-' }}
                                </span>
                            </td>
                            <td>
                                @if(isset($room['id']))
                                    <div class="btn-group">
                                        <a href="{{ route('rooms.show', $room['id']) }}" class="btn btn-sm btn-outline-info">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <form action="{{ route('rooms.resolve', $room['id']) }}" method="POST" style="display:inline;">
                                            @csrf
                                            @method('PUT')
                                            <button type="submit" class="btn btn-sm btn-outline-success" onclick="return confirm('Resolve this room?')">
                                                <i class="bi bi-check-circle"></i>
                                            </button>
                                        </form>
                                    </div>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="p-5 text-center">
                <i class="bi bi-check-circle display-1 text-success"></i>
                <h5 class="mt-3 text-muted">No expired rooms</h5>
                <p class="text-muted">All rooms are up to date!</p>
            </div>
        @endif
    </div>
</div>
@endsection