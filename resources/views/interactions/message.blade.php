@extends('layouts.layout')

@section('title', 'Message Interactions')

@section('content')
<div class="mb-3">
    <h2><i class="bi bi-chat-dots me-2"></i>Message Interactions Settings</h2>
    <p class="text-muted">Configure webhook and message interaction settings</p>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-gear me-2"></i>Message Interaction Configuration
            </div>
            <div class="card-body">
                <form action="{{ route('interactions.message.update') }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label class="form-label">Webhook URL</label>
                        <input type="url" class="form-control" name="url" 
                               placeholder="https://your-webhook-url.com/webhook"
                               value="{{ old('url') }}">
                        <small class="text-muted">Optional: URL to receive message interaction webhooks</small>
                    </div>

                    <hr>

                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" name="receive_message_from_agent" 
                               id="receiveAgent" value="1" checked>
                        <label class="form-check-label" for="receiveAgent">
                            <strong>Receive Message from Agent</strong>
                            <br><small class="text-muted">Enable to receive webhooks when agents send messages</small>
                        </label>
                    </div>

                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" name="receive_message_from_customer" 
                               id="receiveCustomer" value="1" checked>
                        <label class="form-check-label" for="receiveCustomer">
                            <strong>Receive Message from Customer</strong>
                            <br><small class="text-muted">Enable to receive webhooks when customers send messages</small>
                        </label>
                    </div>

                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" name="broadcast_log_status" 
                               id="broadcastLog" value="1" checked>
                        <label class="form-check-label" for="broadcastLog">
                            <strong>Broadcast Log Status</strong>
                            <br><small class="text-muted">Enable to broadcast message status updates</small>
                        </label>
                    </div>

                    <div class="form-check form-switch mb-4">
                        <input class="form-check-input" type="checkbox" name="status_message" 
                               id="statusMessage" value="1" checked>
                        <label class="form-check-label" for="statusMessage">
                            <strong>Status Message</strong>
                            <br><small class="text-muted">Enable status message notifications</small>
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
                <h6>What are Message Interactions?</h6>
                <p class="small text-muted">
                    Message interactions allow you to configure how your system responds to and processes messages 
                    from agents and customers.
                </p>

                <h6 class="mt-3">Webhook Configuration</h6>
                <p class="small text-muted">
                    If you provide a webhook URL, the system will send HTTP POST requests to your endpoint 
                    whenever the selected events occur.
                </p>

                <div class="alert alert-info small">
                    <i class="bi bi-lightbulb me-2"></i>
                    <strong>Tip:</strong> Make sure your webhook endpoint can handle POST requests and 
                    responds with a 200 status code.
                </div>
            </div>
        </div>
    </div>
</div>
@endsection