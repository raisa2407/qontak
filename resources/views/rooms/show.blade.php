@extends('layouts.layout')

@section('title', 'Room Details')

@section('content')
    <div class="mb-3">
        <a href="{{ route('rooms.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-2"></i>Back to Rooms
        </a>
    </div>

    @if (isset($room['data']))
        @php
            $roomData = $room['data'];
        @endphp
        
        <div class="row">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span><i class="bi bi-info-circle me-2"></i>Room Information</span>
                        <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#renameModal">
                            <i class="bi bi-pencil"></i> Rename
                        </button>
                    </div>
                    
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-4 p-3 bg-light rounded">
                            @if (isset($roomData['avatar']['url']))
                                <img src="{{ $roomData['avatar']['url'] }}" alt="Avatar" class="rounded-circle me-3" width="60" height="60">
                            @else
                                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 60px; height: 60px;">
                                    <i class="bi bi-person fs-3"></i>
                                </div>
                            @endif
                            
                            <div>
                                <h4 class="mb-1">{{ $roomData['name'] ?? '-' }}</h4>
                                <span class="badge bg-{{ $roomData['channel'] == 'wa_cloud' ? 'success' : 'info' }}">
                                    <i class="bi bi-whatsapp"></i> {{ strtoupper($roomData['channel_account'] ?? 'N/A') }}
                                </span>
                            </div>
                        </div>

                        <table class="table table-borderless">
                            <tr>
                                <th width="200">Room ID</th>
                                <td><code>{{ $roomData['id'] ?? '-' }}</code></td>
                            </tr>
                            <tr>
                                <th>Name</th>
                                <td><strong>{{ $roomData['name'] ?? '-' }}</strong></td>
                            </tr>
                            <tr>
                                <th>Status</th>
                                <td>
                                    @php
                                        $status = $roomData['status'] ?? 'unknown';
                                        $badgeClass = match ($status) {
                                            'active' => 'success',
                                            'pending' => 'warning',
                                            'resolved' => 'secondary',
                                            default => 'info',
                                        };
                                    @endphp
                                    <span class="badge bg-{{ $badgeClass }}">{{ ucfirst($status) }}</span>
                                </td>
                            </tr>
                            <tr>
                                <th>Type</th>
                                <td>{{ str_replace('Models::', '', $roomData['type'] ?? '-') }}</td>
                            </tr>
                            <tr>
                                <th>Channel</th>
                                <td>
                                    @php
                                        $channel = $roomData['channel'] ?? 'unknown';
                                        $channelIcon = match ($channel) {
                                            'wa', 'wa_cloud' => 'whatsapp',
                                            'ig' => 'instagram',
                                            'telegram' => 'telegram',
                                            'fb' => 'facebook',
                                            default => 'chat-dots',
                                        };
                                    @endphp
                                    <i class="bi bi-{{ $channelIcon }} me-2"></i>{{ ucfirst(str_replace('_', ' ', $channel)) }}
                                </td>
                            </tr>
                            <tr>
                                <th>Account ID</th>
                                <td><code>{{ $roomData['account_uniq_id'] ?? '-' }}</code></td>
                            </tr>
                            <tr>
                                <th>Unread Count</th>
                                <td>
                                    @if ($roomData['unread_count'] > 0)
                                        <span class="badge bg-danger">{{ $roomData['unread_count'] }}</span>
                                    @else
                                        <span class="badge bg-success">0</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>Is Blocked</th>
                                <td>
                                    @if (data_get($roomData, 'is_blocked', false))
                                        <span class="badge bg-danger">Blocked</span>
                                    @else
                                        <span class="badge bg-success">Not Blocked</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>Created At</th>
                                <td>{{ isset($roomData['created_at']) ? \Carbon\Carbon::parse($roomData['created_at'])->format('d M Y, H:i') : '-' }}</td>
                            </tr>
                            <tr>
                                <th>Last Message At</th>
                                <td>{{ isset($roomData['last_message_at']) ? \Carbon\Carbon::parse($roomData['last_message_at'])->format('d M Y, H:i') : '-' }}</td>
                            </tr>
                            <tr>
                                <th>Last Activity</th>
                                <td>{{ isset($roomData['last_activity_at']) ? \Carbon\Carbon::parse($roomData['last_activity_at'])->diffForHumans() : '-' }}</td>
                            </tr>
                        </table>

                        @if (!empty($roomData['tags']))
                            <hr>
                            <h6 class="mb-3"><i class="bi bi-tags me-2"></i>Tags</h6>
                            <div>
                                @foreach ($roomData['tags'] as $tag)
                                    <span class="badge bg-primary me-1 mb-1">{{ $tag }}</span>
                                @endforeach
                            </div>
                        @endif

                        @if (isset($roomData['last_message']))
                            <hr>
                            <h6 class="mb-3"><i class="bi bi-chat-left-quote me-2"></i>Last Message</h6>
                            <div class="bg-light p-3 rounded">
                                <div class="mb-2">
                                    <small class="text-muted">
                                        Type: <span class="badge bg-info">{{ $roomData['last_message']['type'] }}</span>
                                        From: <span class="badge bg-secondary">{{ $roomData['last_message']['participant_type'] }}</span>
                                    </small>
                                </div>
                                <p class="mb-1">{{ $roomData['last_message']['text'] ?? 'No text content' }}</p>
                                <small class="text-muted">
                                    {{ \Carbon\Carbon::parse($roomData['last_message']['created_at'])->diffForHumans() }}
                                </small>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <i class="bi bi-lightning me-2"></i>Quick Actions
                    </div>
                    
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="{{ route('rooms.messages', $id) }}" class="btn btn-success">
                                <i class="bi bi-chat-left-text me-2"></i>View Messages
                            </a>
                            <a href="{{ route('rooms.histories', $id) }}" class="btn btn-info text-white">
                                <i class="bi bi-clock-history me-2"></i>View History
                            </a>
                            <a href="{{ route('rooms.participants', $id) }}" class="btn btn-primary">
                                <i class="bi bi-people me-2"></i>View Participants
                            </a>
                            <a href="{{ route('rooms.assignable-agents', $id) }}" class="btn btn-outline-primary">
                                <i class="bi bi-person-plus me-2"></i>Assign Agent
                            </a>
                            
                            <hr>
                            
                            <form action="{{ route('rooms.takeover', $id) }}" method="POST" onsubmit="return confirm('Take over this room?')">
                                @csrf
                                <button type="submit" class="btn btn-warning w-100">
                                    <i class="bi bi-hand-index me-2"></i>Takeover Room
                                </button>
                            </form>
                            
                            <form action="{{ route('rooms.mark-read', $id) }}" method="POST">
                                @csrf
                                @method('PUT')
                                <button type="submit" class="btn btn-outline-secondary w-100">
                                    <i class="bi bi-check-all me-2"></i>Mark as Read
                                </button>
                            </form>
                            
                            <button class="btn btn-outline-primary w-100" data-bs-toggle="modal" data-bs-target="#tagModal">
                                <i class="bi bi-tags me-2"></i>Manage Tags
                            </button>
                            
                            <hr>
                            
                            <form action="{{ route('rooms.resolve', $id) }}" method="POST" onsubmit="return confirm('Resolve this room?')">
                                @csrf
                                @method('PUT')
                                <button type="submit" class="btn btn-outline-success w-100">
                                    <i class="bi bi-check-circle me-2"></i>Resolve Room
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                @if (!empty($roomData['agent_ids']))
                    <div class="card mt-3">
                        <div class="card-header">
                            <i class="bi bi-people-fill me-2"></i>Assigned Agents
                        </div>
                        <div class="card-body">
                            <p class="mb-0">
                                <span class="badge bg-primary">{{ count($roomData['agent_ids']) }}</span>
                                agent(s) assigned
                            </p>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    @else
        <div class="alert alert-warning">
            <i class="bi bi-exclamation-triangle me-2"></i>Room not found or invalid response
            <pre class="mt-2">{{ json_encode($room, JSON_PRETTY_PRINT) }}</pre>
        </div>
    @endif

    <div class="modal fade" id="renameModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Rename Room</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                
                <form action="{{ route('rooms.rename', $id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">New Name</label>
                            <input type="text" class="form-control" name="name" value="{{ $roomData['name'] ?? '' }}" required>
                        </div>
                    </div>
                    
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="tagModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Manage Tags</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                
                <div class="modal-body">
                    <form action="{{ route('rooms.add-tag', $id) }}" method="POST" class="mb-3">
                        @csrf
                        <label class="form-label">Add Tags</label>
                        <div class="input-group">
                            <input type="text" class="form-control" name="tags" placeholder="tag1,tag2,tag3" required>
                            <button type="submit" class="btn btn-primary">Add</button>
                        </div>
                        <small class="text-muted">Separate multiple tags with commas</small>
                    </form>

                    <form action="{{ route('rooms.remove-tag', $id) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <label class="form-label">Remove Tags</label>
                        <div class="input-group">
                            <input type="text" class="form-control" name="tags" placeholder="tag1,tag2,tag3" required>
                            <button type="submit" class="btn btn-danger">Remove</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection