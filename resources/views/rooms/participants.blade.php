@extends('layouts.layout')

@section('title', 'Room Participants')

@section('content')
<div class="mb-3">
    <a href="{{ route('rooms.show', $id) }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-2"></i>Back to Room
    </a>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-people me-2"></i>Participants List</span>
        @if(isset($participants['data']['response']))
            <span class="badge bg-primary">{{ count($participants['data']['response']) }} participant(s)</span>
        @elseif(isset($participants['data']) && is_array($participants['data']))
            <span class="badge bg-primary">{{ count($participants['data']) }} participant(s)</span>
        @else
            <span class="badge bg-secondary">0 participants</span>
        @endif
    </div>
    
    <div class="card-body p-0">
        @php
            $participantsList = [];
            if (isset($participants['data']['response'])) {
                $participantsList = $participants['data']['response'];
            } elseif (isset($participants['data']) && is_array($participants['data'])) {
                $participantsList = $participants['data'];
            }
        @endphp

        @if(count($participantsList) > 0)
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Participant</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th>Joined At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($participantsList as $participant)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    @if(isset($participant['profile']['avatar']['url']))
                                        <img src="{{ $participant['profile']['avatar']['url'] }}" alt="Avatar" class="rounded-circle me-3" width="40" height="40">
                                    @else
                                        <div class="avatar bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                                            <i class="bi bi-person"></i>
                                        </div>
                                    @endif
                                    <div>
                                        <strong>{{ $participant['profile']['name'] ?? 'Unknown' }}</strong>
                                        <br><small class="text-muted">ID: {{ substr($participant['id'] ?? 'N/A', 0, 18) }}...</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                @php
                                    $type = str_replace('Models::', '', $participant['type'] ?? 'Unknown');
                                    $typeColor = match($type) {
                                        'CustomerParticipant' => 'primary',
                                        'AgentParticipant' => 'success',
                                        'BotParticipant' => 'info',
                                        'SystemParticipant' => 'secondary',
                                        default => 'dark'
                                    };
                                    $typeIcon = match($type) {
                                        'CustomerParticipant' => 'person',
                                        'AgentParticipant' => 'person-badge',
                                        'BotParticipant' => 'robot',
                                        'SystemParticipant' => 'gear',
                                        default => 'question-circle'
                                    };
                                @endphp
                                <span class="badge bg-{{ $typeColor }}">
                                    <i class="bi bi-{{ $typeIcon }} me-1"></i>
                                    {{ str_replace('Participant', '', $type) }}
                                </span>
                            </td>
                            <td>
                                @php
                                    $status = $participant['active_status'] ?? 'inactive';
                                    $statusColor = $status == 'active' ? 'success' : 'secondary';
                                @endphp
                                <span class="badge bg-{{ $statusColor }}">
                                    <i class="bi bi-circle-fill me-1" style="font-size: 0.5rem;"></i>
                                    {{ ucfirst($status) }}
                                </span>
                            </td>
                            <td>
                                <small class="text-muted">
                                    {{ isset($participant['created_at']) ? \Carbon\Carbon::parse($participant['created_at'])->format('d M Y, H:i') : '-' }}
                                </small>
                                <br>
                                <small class="text-muted">
                                    {{ isset($participant['created_at']) ? \Carbon\Carbon::parse($participant['created_at'])->diffForHumans() : '' }}
                                </small>
                            </td>
                            <td>
                                @if(isset($participant['contact_able_type']) && $participant['contact_able_type'] == 'Models::Contact')
                                    <button class="btn btn-sm btn-outline-primary" title="View Contact">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="p-5 text-center">
                <i class="bi bi-people display-1 text-muted"></i>
                <h5 class="mt-3 text-muted">No participants found</h5>
                <p class="text-muted">This room has no participants yet</p>
            </div>
        @endif
    </div>
</div>

@if(count($participantsList) > 0)
<div class="row mt-4">
    @php
        $typeCounts = collect($participantsList)->groupBy(function($p) {
            return str_replace(['Models::', 'Participant'], '', $p['type'] ?? 'Unknown');
        })->map->count();
    @endphp
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <h6 class="opacity-75">Total Participants</h6>
                <h2 class="mb-0">{{ count($participantsList) }}</h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body">
                <h6 class="opacity-75">Customers</h6>
                <h2 class="mb-0">{{ $typeCounts['Customer'] ?? 0 }}</h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-info text-white">
            <div class="card-body">
                <h6 class="opacity-75">Agents</h6>
                <h2 class="mb-0">{{ $typeCounts['Agent'] ?? 0 }}</h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-secondary text-white">
            <div class="card-body">
                <h6 class="opacity-75">Bots/System</h6>
                <h2 class="mb-0">{{ ($typeCounts['Bot'] ?? 0) + ($typeCounts['System'] ?? 0) }}</h2>
            </div>
        </div>
    </div>
</div>
@endif

@if(isset($participants['data']['pagination']))
<div class="card mt-3">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <small class="text-muted">
                    Showing {{ $participants['data']['pagination']['offset'] ?? 0 }} 
                    of {{ $participants['data']['pagination']['total'] ?? 0 }} total
                </small>
            </div>
            <div>
                @if(isset($participants['data']['pagination']['cursor']['prev']))
                    <a href="{{ route('rooms.participants', ['id' => $id, 'cursor' => $participants['data']['pagination']['cursor']['prev']]) }}" 
                       class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-chevron-left"></i> Previous
                    </a>
                @endif
                @if(isset($participants['data']['pagination']['cursor']['next']))
                    <a href="{{ route('rooms.participants', ['id' => $id, 'cursor' => $participants['data']['pagination']['cursor']['next']]) }}" 
                       class="btn btn-sm btn-outline-secondary">
                        Next <i class="bi bi-chevron-right"></i>
                    </a>
                @endif
            </div>
        </div>
    </div>
</div>
@endif

@if(isset($participants) && $participants['status'] !== 'success')
<div class="alert alert-warning mt-3">
    <i class="bi bi-exclamation-triangle me-2"></i>
    Failed to load participants or invalid response
    <pre class="mt-2">{{ json_encode($participants, JSON_PRETTY_PRINT) }}</pre>
</div>
@endif
@endsection