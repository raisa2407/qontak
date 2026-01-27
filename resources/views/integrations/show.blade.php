@extends('layouts.layout')

@section('title', 'Channel Details')

@section('subtitle')
    <p class="page-subtitle">View detailed information about this integration</p>
@endsection

@section('content')
<div class="row mb-3">
    <div class="col-12">
        <a href="{{ route('integrations.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Back to Integrations
        </a>
       
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="bi bi-info-circle"></i> Channel Information
                </h5>
            </div>
            <div class="card-body">
                @if(isset($channel['data']))
                    <table class="table table-borderless">
                        <tbody>
                            <tr>
                                <th width="200">Channel ID:</th>
                                <td><code>{{ $channel['data']['id'] ?? $channelId }}</code></td>
                            </tr>
                            <tr>
                                <th>Channel Type:</th>
                                <td>
                                    @php
                                        $channelIcons = [
                                            'wa' => 'whatsapp',
                                            'wa_cloud' => 'whatsapp',
                                            'fb' => 'facebook',
                                            'email' => 'envelope',
                                            'telegram' => 'telegram',
                                            'ig' => 'instagram',
                                            'line' => 'chat-dots',
                                            'twitter' => 'twitter',
                                            'web_chat' => 'chat-left-dots',
                                            'app_chat' => 'phone',
                                        ];
                                        $channelColors = [
                                            'wa' => 'success',
                                            'wa_cloud' => 'success',
                                            'fb' => 'primary',
                                            'email' => 'danger',
                                            'telegram' => 'info',
                                            'ig' => 'danger',
                                            'line' => 'success',
                                            'twitter' => 'info',
                                            'web_chat' => 'secondary',
                                            'app_chat' => 'dark',
                                        ];
                                        $icon = $channelIcons[$channel['data']['target_channel'] ?? 'web_chat'] ?? 'chat';
                                        $color = $channelColors[$channel['data']['target_channel'] ?? 'web_chat'] ?? 'info';
                                    @endphp
                                    <span class="badge bg-{{ $color }}">
                                        <i class="bi bi-{{ $icon }}"></i>
                                        {{ strtoupper(str_replace('_', ' ', $channel['data']['target_channel'] ?? 'N/A')) }}
                                    </span>
                                </td>
                            </tr>
                            @if(isset($channel['data']['settings']['account_name']))
                                <tr>
                                    <th>Account Name:</th>
                                    <td>{{ $channel['data']['settings']['account_name'] }}</td>
                                </tr>
                            @endif
                            @if(isset($channel['data']['settings']['title']))
                                <tr>
                                    <th>Title:</th>
                                    <td>{{ $channel['data']['settings']['title'] }}</td>
                                </tr>
                            @endif
                            <tr>
                                <th>Status:</th>
                                <td>
                                    @if(isset($channel['data']['is_active']) && $channel['data']['is_active'])
                                        <span class="badge bg-success">
                                            <i class="bi bi-check-circle"></i> Active
                                        </span>
                                    @else
                                        <span class="badge bg-secondary">
                                            <i class="bi bi-x-circle"></i> Inactive
                                        </span>
                                    @endif
                                </td>
                            </tr>
                            @if(isset($channel['data']['settings']['phone_number']))
                                <tr>
                                    <th>Phone Number:</th>
                                    <td>{{ $channel['data']['settings']['phone_number'] }}</td>
                                </tr>
                            @endif
                            @if(isset($channel['data']['settings']['domain']))
                                <tr>
                                    <th>Domain:</th>
                                    <td>{{ $channel['data']['settings']['domain'] }}</td>
                                </tr>
                            @endif
                            @if(isset($channel['data']['webhook']))
                                <tr>
                                    <th>Webhook:</th>
                                    <td><code>{{ $channel['data']['webhook'] }}</code></td>
                                </tr>
                            @endif
                            @if(isset($channel['data']['settings']['waba_id']))
                                <tr>
                                    <th>WABA ID:</th>
                                    <td><code>{{ $channel['data']['settings']['waba_id'] }}</code></td>
                                </tr>
                            @endif
                            @if(isset($channel['data']['settings']['phone_number_id']))
                                <tr>
                                    <th>Phone Number ID:</th>
                                    <td><code>{{ $channel['data']['settings']['phone_number_id'] }}</code></td>
                                </tr>
                            @endif
                            @if(isset($channel['data']['settings']['smtp_host']))
                                <tr>
                                    <th>SMTP Host:</th>
                                    <td>{{ $channel['data']['settings']['smtp_host'] }}</td>
                                </tr>
                            @endif
                            @if(isset($channel['data']['settings']['smtp_port']))
                                <tr>
                                    <th>SMTP Port:</th>
                                    <td>{{ $channel['data']['settings']['smtp_port'] }}</td>
                                </tr>
                            @endif
                            <tr>
                                <th>Created At:</th>
                                <td>{{ isset($channel['data']['created_at']) ? \Carbon\Carbon::parse($channel['data']['created_at'])->format('F d, Y H:i:s') : '-' }}</td>
                            </tr>
                            @if(isset($channel['data']['updated_at']))
                                <tr>
                                    <th>Updated At:</th>
                                    <td>{{ \Carbon\Carbon::parse($channel['data']['updated_at'])->format('F d, Y H:i:s') }}</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                @else
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle"></i>
                        No channel data available
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="bi bi-gear"></i> Quick Actions
                </h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                  
                    <button class="btn btn-outline-info" data-bs-toggle="modal" data-bs-target="#configModal">
                        <i class="bi bi-sliders"></i> Configuration
                    </button>
                    <button class="btn btn-outline-success">
                        <i class="bi bi-arrow-repeat"></i> Sync Channel
                    </button>
                    @if(isset($channel['data']['is_active']) && $channel['data']['is_active'])
                        <button class="btn btn-outline-warning">
                            <i class="bi bi-pause-circle"></i> Deactivate
                        </button>
                    @else
                        <button class="btn btn-outline-success">
                            <i class="bi bi-play-circle"></i> Activate
                        </button>
                    @endif
                </div>
            </div>
        </div>

        @if(isset($channel['data']['stats']))
            <div class="card mt-3">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-graph-up"></i> Statistics
                    </h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <small class="text-muted">Messages Sent</small>
                        <h4 class="mb-0">{{ $channel['data']['stats']['messages_sent'] ?? 0 }}</h4>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted">Messages Received</small>
                        <h4 class="mb-0">{{ $channel['data']['stats']['messages_received'] ?? 0 }}</h4>
                    </div>
                    <div>
                        <small class="text-muted">Active Rooms</small>
                        <h4 class="mb-0">{{ $channel['data']['stats']['active_rooms'] ?? 0 }}</h4>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>

@if(isset($channel['data']['settings']))
    <div class="row mt-3">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-gear"></i> Channel Settings
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        @foreach($channel['data']['settings'] as $key => $value)
                            @if(!in_array($key, ['password', 'pin', 'authorization', 'secret_key', 'fcm_server_key']))
                                <div class="col-md-6 mb-3">
                                    <strong class="text-muted text-uppercase" style="font-size: 0.75rem;">{{ str_replace('_', ' ', $key) }}:</strong>
                                    <div class="mt-1">
                                        @if(is_array($value))
                                            <pre class="bg-light p-2 rounded"><code>{{ json_encode($value, JSON_PRETTY_PRINT) }}</code></pre>
                                        @elseif(is_bool($value))
                                            <span class="badge bg-{{ $value ? 'success' : 'secondary' }}">
                                                {{ $value ? 'Yes' : 'No' }}
                                            </span>
                                        @else
                                            <span>{{ $value }}</span>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif

<div class="modal fade" id="configModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Channel Configuration</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i>
                    Channel configuration settings will be available here.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endsection