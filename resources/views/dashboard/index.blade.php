@extends('layouts.app')

@section('content')
<div class="row g-3 mb-4">
    <div class="col-md-3"><div class="card"><div class="card-body"><h6>Total Scripts</h6><h3>{{ $totalScripts }}</h3></div></div></div>
    <div class="col-md-3"><div class="card"><div class="card-body"><h6>Active Cronjobs</h6><h3>{{ $activeCronjobs }}</h3></div></div></div>
    <div class="col-md-3"><div class="card"><div class="card-body"><h6>Telegram Connections</h6><h3>{{ $totalTelegramConnections }}</h3></div></div></div>
    <div class="col-md-3"><div class="card"><div class="card-body"><h6>Redis Connections</h6><h3>{{ $totalRedisConnections }}</h3></div></div></div>
</div>
<div class="card">
    <div class="card-header">Recent Activity</div>
    <div class="table-responsive">
        <table class="table table-striped mb-0">
            <thead><tr><th>Action</th><th>User</th><th>IP</th><th>Timestamp</th></tr></thead>
            <tbody>
            @forelse($recentActivities as $activity)
                <tr>
                    <td>{{ $activity->action }}</td>
                    <td>{{ $activity->user?->username }}</td>
                    <td>{{ $activity->ip_address }}</td>
                    <td>{{ $activity->created_at }}</td>
                </tr>
            @empty
                <tr><td colspan="4">No activity yet.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
