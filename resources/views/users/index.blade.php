@extends('layouts.layout')

@section('title', 'Users Management')

@section('content')
<div class="mb-3 d-flex justify-content-between align-items-center">
    <div>
        <h2 class="mb-0">Users</h2>
        <p class="text-muted">Manage your team members and their roles</p>
    </div>
</div>

<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('users.index') }}" class="row g-3">
            <div class="col-md-4">
                <input type="text" class="form-control" name="query" 
                       placeholder="Search users..." 
                       value="{{ request('query') }}">
            </div>
            <div class="col-md-3">
                <select class="form-select" name="role">
                    <option value="">All Roles</option>
                    <option value="admin" {{ request('role') == 'admin' ? 'selected' : '' }}>Admin</option>
                    <option value="agent" {{ request('role') == 'agent' ? 'selected' : '' }}>Agent</option>
                    <option value="supervisor" {{ request('role') == 'supervisor' ? 'selected' : '' }}>Supervisor</option>
                </select>
            </div>
            <div class="col-md-2">
                <select class="form-select" name="order_by">
                    <option value="created_at" {{ request('order_by') == 'created_at' ? 'selected' : '' }}>Created Date</option>
                    <option value="name" {{ request('order_by') == 'name' ? 'selected' : '' }}>Name</option>
                </select>
            </div>
            <div class="col-md-1">
                <select class="form-select" name="order_direction">
                    <option value="desc" {{ request('order_direction') == 'desc' ? 'selected' : '' }}>DESC</option>
                    <option value="asc" {{ request('order_direction') == 'asc' ? 'selected' : '' }}>ASC</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-search me-2"></i>Search
                </button>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-people me-2"></i>All Users</span>
        @if(isset($users['data']) && is_array($users['data']))
            <span class="badge bg-primary">{{ count($users['data']) }} users</span>
        @endif
    </div>
    <div class="card-body p-0">
        @if(isset($users['data']) && count($users['data']) > 0)
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Channels</th>
                            <th>Created At</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($users['data'] as $user)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        @if(isset($user['avatar']['url']))
                                            <img src="{{ $user['avatar']['url'] }}" alt="Avatar" 
                                                 class="rounded-circle me-3" width="40" height="40">
                                        @else
                                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" 
                                                 style="width: 40px; height: 40px;">
                                                <i class="bi bi-person"></i>
                                            </div>
                                        @endif
                                        <div>
                                            <strong>{{ $user['full_name'] ?? 'N/A' }}</strong>
                                            @if(isset($user['username']))
                                                <br><small class="text-muted">@{{ $user['username'] }}</small>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $user['email'] ?? '-' }}</td>
                                <td>
                                    @php
                                        $role = $user['role'] ?? 'user';
                                        $roleColor = match($role) {
                                            'admin' => 'danger',
                                            'supervisor' => 'warning',
                                            'agent' => 'success',
                                            default => 'secondary'
                                        };
                                    @endphp
                                    <span class="badge bg-{{ $roleColor }}">{{ ucfirst($role) }}</span>
                                </td>
                                <td>
                                    @if(isset($user['is_online']) && $user['is_online'])
                                        <span class="badge bg-success">
                                            <i class="bi bi-circle-fill" style="font-size: 0.5rem;"></i> Online
                                        </span>
                                    @else
                                        <span class="badge bg-secondary">
                                            <i class="bi bi-circle-fill" style="font-size: 0.5rem;"></i> Offline
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    @if(isset($user['channels']) && is_array($user['channels']))
                                        <span class="badge bg-info">{{ count($user['channels']) }} channels</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    <small>{{ isset($user['created_at']) ? \Carbon\Carbon::parse($user['created_at'])->format('d M Y') : '-' }}</small>
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('users.show', $user['id']) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="p-5 text-center">
                <i class="bi bi-people display-1 text-muted"></i>
                <h5 class="mt-3 text-muted">No users found</h5>
                <p class="text-muted">Try adjusting your search filters</p>
            </div>
        @endif
    </div>
</div>

@if(isset($users['pagination']))
    <div class="card mt-3">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center">
                <small class="text-muted">
                    Showing {{ $users['pagination']['offset'] ?? 0 }} of {{ $users['pagination']['total'] ?? 0 }}
                </small>
                <div>
                    @if(request('offset', 0) > 0)
                        <a href="{{ route('users.index', array_merge(request()->all(), ['offset' => max(0, request('offset', 0) - request('limit', 50))])) }}" 
                           class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-chevron-left"></i> Previous
                        </a>
                    @endif
                    @if(isset($users['pagination']['total']) && (request('offset', 0) + request('limit', 50)) < $users['pagination']['total'])
                        <a href="{{ route('users.index', array_merge(request()->all(), ['offset' => request('offset', 0) + request('limit', 50)])) }}" 
                           class="btn btn-sm btn-outline-secondary">
                            Next <i class="bi bi-chevron-right"></i>
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endif
@endsection