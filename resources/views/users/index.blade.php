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
        @php
            $usersList = $users['data'] ?? [];
            $total = $users['meta']['pagination']['total'] ?? 0;
        @endphp
        <span class="badge bg-primary">{{ $total }} total users</span>
    </div>
    <div class="card-body p-0">
        @if(count($usersList) > 0)
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>User</th>
                            <th>Email / Phone</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Assigned</th>
                            <th>Divisions</th>
                            <th>Last Sign In</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($usersList as $user)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        @if(isset($user['avatar']['url']))
                                            <img src="{{ $user['avatar']['url'] }}" alt="Avatar" 
                                                 class="rounded-circle me-3" width="40" height="40"
                                                 onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode($user['full_name'] ?? 'U') }}&background=random'">
                                        @else
                                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" 
                                                 style="width: 40px; height: 40px;">
                                                <i class="bi bi-person"></i>
                                            </div>
                                        @endif
                                        <div>
                                            <strong>{{ $user['full_name'] ?? 'N/A' }}</strong>
                                            <br><small class="text-muted">ID: {{ substr($user['id'] ?? '', 0, 8) }}...</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div>
                                        <small class="d-block">{{ $user['email'] ?? '-' }}</small>
                                        @if(isset($user['phone']) && $user['phone'])
                                            <small class="text-muted">{{ $user['phone'] }}</small>
                                        @endif
                                    </div>
                                </td>
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
                                    <span class="badge bg-info">{{ $user['assigned_count'] ?? 0 }}</span>
                                </td>
                                <td>
                                    @if(isset($user['divisions']) && count($user['divisions']) > 0)
                                        @foreach(array_slice($user['divisions'], 0, 2) as $division)
                                            <span class="badge bg-light text-dark">{{ $division['name'] }}</span>
                                        @endforeach
                                        @if(count($user['divisions']) > 2)
                                            <span class="badge bg-light text-dark">+{{ count($user['divisions']) - 2 }}</span>
                                        @endif
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if(isset($user['last_sign_in_at']))
                                        <small>{{ \Carbon\Carbon::parse($user['last_sign_in_at'])->format('d M Y') }}</small>
                                        <br><small class="text-muted">{{ \Carbon\Carbon::parse($user['last_sign_in_at'])->diffForHumans() }}</small>
                                    @else
                                        <small class="text-muted">Never</small>
                                    @endif
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

@if(isset($users['meta']['pagination']))
    @php
        $pagination = $users['meta']['pagination'];
        $currentOffset = $pagination['offset'] ?? 1;
        $limit = $pagination['limit'] ?? 10;
        $total = $pagination['total'] ?? 0;
    @endphp
    <div class="card mt-3">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center">
                <small class="text-muted">
                    Showing {{ $currentOffset }} of {{ $total }} users
                </small>
                <div>
                    @if(isset($pagination['cursor']['prev']))
                        <a href="{{ route('users.index', array_merge(request()->except('offset'), ['offset' => $currentOffset - 1])) }}" 
                           class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-chevron-left"></i> Previous
                        </a>
                    @endif
                    @if(isset($pagination['cursor']['next']))
                        <a href="{{ route('users.index', array_merge(request()->except('offset'), ['offset' => $currentOffset + 1])) }}" 
                           class="btn btn-sm btn-outline-secondary">
                            Next <i class="bi bi-chevron-right"></i>
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endif

@if(count($usersList) > 0)
<div class="row mt-4">
    @php
        $roleCounts = collect($usersList)->groupBy('role')->map->count();
        $onlineCount = collect($usersList)->where('is_online', true)->count();
    @endphp
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <h6 class="opacity-75">Total Users</h6>
                <h2 class="mb-0">{{ count($usersList) }}</h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-danger text-white">
            <div class="card-body">
                <h6 class="opacity-75">Admins</h6>
                <h2 class="mb-0">{{ $roleCounts['admin'] ?? 0 }}</h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body">
                <h6 class="opacity-75">Agents</h6>
                <h2 class="mb-0">{{ $roleCounts['agent'] ?? 0 }}</h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-info text-white">
            <div class="card-body">
                <h6 class="opacity-75">Online Now</h6>
                <h2 class="mb-0">{{ $onlineCount }}</h2>
            </div>
        </div>
    </div>
</div>
@endif
@endsection