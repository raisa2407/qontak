@extends('layouts.layout')

@section('title', 'Dashboard')

@section('content')
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h6 class="text-muted mb-2">Total Rooms</h6>
                        <h2 class="mb-0">{{ number_format($stats['total']) }}</h2>
                        <small class="text-success"><i class="bi bi-arrow-up"></i> Active System</small>
                    </div>
                    <div class="bg-primary bg-opacity-10 p-3 rounded">
                        <i class="bi bi-chat-left-text fs-4 text-primary"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h6 class="text-muted mb-2">Active Rooms</h6>
                        <h2 class="mb-0">{{ number_format($stats['active']) }}</h2>
                        <small class="text-info"><i class="bi bi-chat-dots"></i> In Progress</small>
                    </div>
                    <div class="bg-success bg-opacity-10 p-3 rounded">
                        <i class="bi bi-check-circle fs-4 text-success"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h6 class="text-muted mb-2">Unassigned</h6>
                        <h2 class="mb-0">{{ number_format($stats['unassigned']) }}</h2>
                        <small class="text-warning"><i class="bi bi-exclamation-circle"></i> Need Attention</small>
                    </div>
                    <div class="bg-warning bg-opacity-10 p-3 rounded">
                        <i class="bi bi-person-x fs-4 text-warning"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h6 class="text-muted mb-2">Resolved</h6>
                        <h2 class="mb-0">{{ number_format($stats['resolved']) }}</h2>
                        <small class="text-muted"><i class="bi bi-check-all"></i> Completed</small>
                    </div>
                    <div class="bg-secondary bg-opacity-10 p-3 rounded">
                        <i class="bi bi-check2-all fs-4 text-secondary"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm border-start border-danger border-4">
            <div class="card-body">
                <h6 class="text-muted mb-2">Expired Rooms</h6>
                <h3 class="mb-0 text-danger">{{ number_format($stats['expired']) }}</h3>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card border-0 shadow-sm border-start border-warning border-4">
            <div class="card-body">
                <h6 class="text-muted mb-2">Expiring Today</h6>
                <h3 class="mb-0 text-warning">{{ number_format($stats['expiring_today']) }}</h3>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card border-0 shadow-sm border-start border-info border-4">
            <div class="card-body">
                <h6 class="text-muted mb-2">Pending Rooms</h6>
                <h3 class="mb-0 text-info">{{ number_format($stats['pending']) }}</h3>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card border-0 shadow-sm border-start border-primary border-4">
            <div class="card-body">
                <h6 class="text-muted mb-2">Unread Messages</h6>
                <h3 class="mb-0 text-primary">{{ number_format($stats['unread']) }}</h3>
            </div>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-graph-up me-2"></i>Room Activity (Last 7 Days)</h5>
            </div>
            <div class="card-body">
                <canvas id="roomActivityChart" height="80"></canvas>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-people me-2"></i>User Statistics</h5>
                <a href="{{ route('users.index') }}" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="card-body">
                <div class="row text-center mb-3">
                    <div class="col-4">
                        <h3 class="text-primary mb-0">{{ $userStats['total'] }}</h3>
                        <small class="text-muted">Total Users</small>
                    </div>
                    <div class="col-4">
                        <h3 class="text-success mb-0">{{ $userStats['online'] }}</h3>
                        <small class="text-muted">Online</small>
                    </div>
                    <div class="col-4">
                        <h3 class="text-secondary mb-0">{{ $userStats['offline'] }}</h3>
                        <small class="text-muted">Offline</small>
                    </div>
                </div>
                <hr>
                <h6 class="mb-3">Users by Role</h6>
                @forelse($userStats['by_role'] as $role => $count)
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-capitalize">
                            <span class="badge bg-primary">{{ ucfirst($role) }}</span>
                        </span>
                        <span class="fw-bold">{{ $count }} users</span>
                    </div>
                @empty
                    <p class="text-muted text-center">No role data available</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const dailyData = @json($chartData['daily']);

    const labels = Object.keys(dailyData).map(date => {
        const d = new Date(date);
        return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
    });
    const createdData = Object.values(dailyData).map(d => d.created);
    const resolvedData = Object.values(dailyData).map(d => d.resolved);

    new Chart(document.getElementById('roomActivityChart'), {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Created',
                data: createdData,
                borderColor: 'rgb(79, 70, 229)',
                backgroundColor: 'rgba(79, 70, 229, 0.1)',
                tension: 0.4,
                fill: true
            }, {
                label: 'Resolved',
                data: resolvedData,
                borderColor: 'rgb(16, 185, 129)',
                backgroundColor: 'rgba(16, 185, 129, 0.1)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    position: 'top',
                },
                tooltip: {
                    mode: 'index',
                    intersect: false,
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            },
            interaction: {
                mode: 'nearest',
                axis: 'x',
                intersect: false
            }
        }
    });
});
</script>
@endpush