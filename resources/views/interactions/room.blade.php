@extends('layouts.layout')

@section('title', 'Room Interactions')

@section('content')
<div class="mb-3">
    <h2><i class="bi bi-door-open me-2"></i>Room Interactions Settings</h2>
    <p class="text-muted">Configure webhook and room interaction settings</p>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-gear me-2"></i>Room Interaction Configuration
            </div>
            <div class="card-body">
                <form action="{{ route('interactions.room.update') }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label class="form-label">Webhook URL</label>
                        <input type="url" class="form-control" name="url" 
                               placeholder="https://your-webhook-url.com/webhook"
                               value="{{ old('url') }}">
                        <small class="text-muted">Optional: URL to receive room interaction webhooks</small>
                    </div>

                    <hr>

                    <div class="form-check form-switch mb-4">
                        <input class="form-check-input" type="checkbox" name="room_resolved" 
                               id="roomResolved" value="1" checked>
                        <label class="form-check-label" for="roomResolved">
                            <strong>Room Resolved</strong>
                            <br><small class="text-muted">Enable to receive webhooks when rooms are resolved</small>
                        </label>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save me-2"></i>Save Settings
                        </button>
                        <a href="{{ url()->previous() }}" class="btn btn-outline-secondary">
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-info-circle me-2"></i>Information
            </div>
            <div class="card-body">
                <h6>What are Room Interactions?</h6>
                <p class="small text-muted">
                    Room interactions allow you to monitor and respond to room status changes, 
                    particularly when rooms are resolved or closed.
                </p>

                <h6 class="mt-3">Webhook Payload</h6>
                <p class="small text-muted">
                    When a room is resolved, your webhook will receive information about the room, 
                    including its ID, status, resolution time, and associated data.
                </p>

                <div class="alert alert-info small">
                    <i class="bi bi-lightbulb me-2"></i>
                    <strong>Tip:</strong> Use this to trigger automated workflows, update external systems, 
                    or send notifications when conversations are completed.
                </div>
            </div>
        </div>
    </div>
</div>
@endsection