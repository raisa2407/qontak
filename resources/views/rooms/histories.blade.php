@extends('layouts.layout')

@section('title', 'Room History')

@section('content')
<div class="mb-3">
    <a href="{{ route('rooms.show', $id) }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-2"></i>Back to Room
    </a>
</div>

<div class="card">
    <div class="card-header">
        <i class="bi bi-clock-history me-2"></i>Room Activity History
    </div>
    <div class="card-body p-0">
        @if(isset($histories['data']) && count($histories['data']) > 0)
            <div class="timeline p-4">
                @foreach($histories['data'] as $history)
                    <div class="timeline-item mb-4">
                        <div class="d-flex">
                            <div class="timeline-marker me-3">
                                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                    <i class="bi bi-circle-fill" style="font-size: 0.5rem;"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1">
                                <div class="card">
                                    <div class="card-body">
                                        <h6 class="mb-2">{{ $history['action'] ?? 'Activity' }}</h6>
                                        <p class="mb-2 text-muted">{{ $history['description'] ?? 'No description' }}</p>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <small class="text-muted">
                                                <i class="bi bi-person me-1"></i>{{ $history['user_name'] ?? 'System' }}
                                            </small>
                                            <small class="text-muted">
                                                <i class="bi bi-clock me-1"></i>{{ isset($history['created_at']) ? \Carbon\Carbon::parse($history['created_at'])->diffForHumans() : '' }}
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="p-5 text-center">
                <i class="bi bi-clock-history display-1 text-muted"></i>
                <h5 class="mt-3 text-muted">No history available</h5>
            </div>
        @endif
    </div>
</div>
@endsection