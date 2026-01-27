@extends('layouts.layout')

@section('title', 'Channel Integrations')

@section('subtitle')
    <p class="page-subtitle">Manage all your integrated communication channels</p>
@endsection

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Filter Channels</h5>
                <button class="btn btn-sm btn-outline-primary" type="button" data-bs-toggle="collapse" data-bs-target="#filterCollapse">
                    <i class="bi bi-funnel"></i> Filters
                </button>
            </div>
            <div class="collapse" id="filterCollapse">
                <div class="card-body">
                    <form method="GET" action="{{ route('integrations.index') }}">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">Target Channel</label>
                                <select name="target_channel" class="form-select">
                                    <option value="">All Channels</option>
                                    <option value="wa" {{ request('target_channel') == 'wa' ? 'selected' : '' }}>WhatsApp</option>
                                    <option value="fb" {{ request('target_channel') == 'fb' ? 'selected' : '' }}>Facebook</option>
                                    <option value="email" {{ request('target_channel') == 'email' ? 'selected' : '' }}>Email</option>
                                    <option value="telegram" {{ request('target_channel') == 'telegram' ? 'selected' : '' }}>Telegram</option>
                                    <option value="qontaklivechat_dot_com" {{ request('target_channel') == 'qontaklivechat_dot_com' ? 'selected' : '' }}>Qontak Live Chat</option>
                                    <option value="ig" {{ request('target_channel') == 'ig' ? 'selected' : '' }}>Instagram</option>
                                    <option value="line" {{ request('target_channel') == 'line' ? 'selected' : '' }}>Line</option>
                                    <option value="twitter" {{ request('target_channel') == 'twitter' ? 'selected' : '' }}>Twitter</option>
                                    <option value="web_chat" {{ request('target_channel') == 'web_chat' ? 'selected' : '' }}>Web Chat</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Order By</label>
                                <select name="order_by" class="form-select">
                                    <option value="created_at" {{ request('order_by') == 'created_at' ? 'selected' : '' }}>Created At</option>
                                    <option value="updated_at" {{ request('order_by') == 'updated_at' ? 'selected' : '' }}>Updated At</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Direction</label>
                                <select name="order_direction" class="form-select">
                                    <option value="desc" {{ request('order_direction') == 'desc' ? 'selected' : '' }}>Descending</option>
                                    <option value="asc" {{ request('order_direction') == 'asc' ? 'selected' : '' }}>Ascending</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Limit</label>
                                <input type="number" name="limit" class="form-control" placeholder="20" value="{{ request('limit') }}">
                            </div>
                            <div class="col-md-3 d-flex align-items-end gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-search"></i> Apply Filters
                                </button>
                                <a href="{{ route('integrations.index') }}" class="btn btn-outline-secondary">
                                    <i class="bi bi-x-circle"></i> Clear
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="bi bi-plug"></i> Integrated Channels
                </h5>
                <span class="badge bg-primary">{{ isset($integrations['data']) ? count($integrations['data']) : 0 }} channels</span>
            </div>
            <div class="card-body p-0">
                @if(isset($integrations['data']) && count($integrations['data']) > 0)
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Channel Type</th>
                                    <th>Channel Name</th>
                                    <th>Status</th>
                                    <th>Created At</th>
                                    <th>Updated At</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($integrations['data'] as $integration)
                                    <tr>
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
                                                $icon = $channelIcons[$integration['target_channel'] ?? 'web_chat'] ?? 'chat';
                                                $color = $channelColors[$integration['target_channel'] ?? 'web_chat'] ?? 'info';
                                            @endphp
                                            <span class="badge bg-{{ $color }}">
                                                <i class="bi bi-{{ $icon }}"></i>
                                                {{ strtoupper(str_replace('_', ' ', $integration['target_channel'] ?? 'N/A')) }}
                                            </span>
                                        </td>
                                        <td>
                                            <strong>{{ $integration['settings']['account_name'] ?? $integration['settings']['title'] ?? $integration['webhook'] ?? 'Unnamed Channel' }}</strong>
                                            @if(isset($integration['settings']['phone_number']))
                                                <br><small class="text-muted">{{ $integration['settings']['phone_number'] }}</small>
                                            @endif
                                            @if(isset($integration['settings']['domain']))
                                                <br><small class="text-muted">{{ $integration['settings']['domain'] }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            @if(isset($integration['is_active']) && $integration['is_active'])
                                                <span class="badge bg-success">
                                                    <i class="bi bi-check-circle"></i> Active
                                                </span>
                                            @else
                                                <span class="badge bg-secondary">
                                                    <i class="bi bi-x-circle"></i> Inactive
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            <small class="text-muted">
                                                {{ isset($integration['created_at']) ? \Carbon\Carbon::parse($integration['created_at'])->format('M d, Y H:i') : '-' }}
                                            </small>
                                        </td>
                                        <td>
                                            <small class="text-muted">
                                                {{ isset($integration['updated_at']) ? \Carbon\Carbon::parse($integration['updated_at'])->format('M d, Y H:i') : '-' }}
                                            </small>
                                        </td>
                                        <td class="text-end">
                                            <div class="btn-group btn-group-sm">
                                                <a href="{{ route('integrations.show', $integration['id']) }}" class="btn btn-outline-primary" title="View Details">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                               
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="bi bi-inbox text-muted" style="font-size: 3rem;"></i>
                        <p class="text-muted mt-3">No integrations found</p>
                    </div>
                @endif
            </div>
            @if(isset($integrations['meta']['pagination']))
                <div class="card-footer">
                    <div class="d-flex justify-content-between align-items-center">
                        <small class="text-muted">
                            Showing {{ $integrations['meta']['pagination']['offset'] ?? 1 }} to {{ min($integrations['meta']['pagination']['offset'] + $integrations['meta']['pagination']['limit'] - 1, $integrations['meta']['pagination']['total']) ?? 0 }} of {{ $integrations['meta']['pagination']['total'] ?? 0 }} results
                        </small>
                        <div>
                            @if(($integrations['meta']['pagination']['offset'] ?? 1) > 1)
                                <a href="{{ route('integrations.index', array_merge(request()->all(), ['offset' => max(1, request('offset', 1) - request('limit', 100))])) }}" class="btn btn-sm btn-outline-secondary">
                                    <i class="bi bi-chevron-left"></i> Previous
                                </a>
                            @endif
                            @if(isset($integrations['meta']['pagination']) && ($integrations['meta']['pagination']['offset'] + $integrations['meta']['pagination']['limit'] - 1) < $integrations['meta']['pagination']['total'])
                                <a href="{{ route('integrations.index', array_merge(request()->all(), ['offset' => request('offset', 1) + request('limit', 100)])) }}" class="btn btn-sm btn-outline-secondary">
                                    Next <i class="bi bi-chevron-right"></i>
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection