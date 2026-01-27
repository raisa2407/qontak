@extends('layouts.layout')

@section('title', 'Assign Agent')

@section('content')
    <div class="mb-3">
        <a href="{{ route('rooms.show', $id) }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-2"></i>Back to Room
        </a>
    </div>

    <div class="card">
        <div class="card-header">
            <i class="bi bi-person-plus me-2"></i>Available Agents
        </div>
        <div class="card-body p-0">
            @if (isset($agents['data']) && count($agents['data']) > 0)
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead>
                            <tr>
                                <th>Agent</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th>Channel Count</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($agents['data'] as $agent)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3"
                                                style="width: 40px; height: 40px;">
                                                <i class="bi bi-person"></i>
                                            </div>
                                            <div>
                                                <strong>{{ $agent['full_name'] ?? 'Unknown' }}</strong>
                                                <br><small class="text-muted">{{ $agent['email'] ?? '' }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td><span class="badge bg-info">{{ $agent['role'] ?? 'Agent' }}</span></td>
                                    <td>
                                        @php
                                            $isOnline = $agent['is_online'] ?? false;
                                            $statusText = $isOnline ? 'online' : 'offline';
                                            $statusColor = $isOnline ? 'success' : 'secondary';
                                        @endphp

                                        <span class="badge bg-{{ $statusColor }}">
                                            <i class="bi bi-circle-fill me-1" style="font-size: 0.5rem;"></i>
                                            {{ ucfirst($statusText) }}
                                        </span>
                                    </td>

                                    <td>
                                        <span class="badge bg-light text-dark">{{ $agent['channel_count'] ?? 0 }}</span>
                                    </td>
                                    <td>
                                        <form action="{{ route('rooms.assign-agent', [$id, $agent['id']]) }}" method="POST"
                                            style="display:inline;">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-primary"
                                                onclick="return confirm('Assign this agent?')">
                                                <i class="bi bi-check-circle me-1"></i>Assign
                                            </button>
                                        </form>
                                        <form action="{{ route('rooms.handover', [$id, $agent['id']]) }}" method="POST"
                                            style="display:inline;">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-warning"
                                                onclick="return confirm('Handover to this agent?')">
                                                <i class="bi bi-arrow-right-circle me-1"></i>Handover
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="p-5 text-center">
                    <i class="bi bi-person-x display-1 text-muted"></i>
                    <h5 class="mt-3 text-muted">No available agents</h5>
                </div>
            @endif
        </div>
    </div>
@endsection
