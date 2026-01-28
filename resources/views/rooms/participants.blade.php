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
        @if(isset($participants['data']) && is_array($participants['data']))
            <span class="badge bg-primary">{{ count($participants['data']) }} participant(s)</span>
        @else
            <span class="badge bg-secondary">0 participants</span>
        @endif
    </div>
    
    <div class="card-body p-0">
        @php
            $participantsList = $participants['data'] ?? [];
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
                            <th>Contact ID</th>
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
                                        <br><small class="text-muted">{{ $participant['contact_able_type'] ?? 'N/A' }}</small>
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
                                    $statusColor = match($status) {
                                        'active' => 'success',
                                        'kicked' => 'danger',
                                        default => 'secondary'
                                    };
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
                                <small class="text-muted font-monospace">
                                    {{ $participant['contact_able_id'] ? substr($participant['contact_able_id'], 0, 18) . '...' : '-' }}
                                </small>
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
        
        $statusCounts = collect($participantsList)->groupBy('active_status')->map->count();
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

<div class="row mt-3">
    <div class="col-md-6">
        <div class="card">
            <div class="card-body">
                <h6 class="card-title">Active Participants</h6>
                <h3 class="text-success mb-0">{{ $statusCounts['active'] ?? 0 }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-body">
                <h6 class="card-title">Kicked Participants</h6>
                <h3 class="text-danger mb-0">{{ $statusCounts['kicked'] ?? 0 }}</h3>
            </div>
        </div>
    </div>
</div>
@endif

@if(isset($participants) && $participants['status'] !== 'success')
<div class="alert alert-warning mt-3">
    <i class="bi bi-exclamation-triangle me-2"></i>
    Failed to load participants or invalid response
</div>
@endif
@endsection