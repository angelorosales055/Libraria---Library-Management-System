@extends('layout.app')
@section('title', 'Activity Log')
@section('content')

<div class="page-header-row mb-6">
    <div>
        <h1 class="page-title">Activity Log</h1>
        <p class="page-subtitle">Track all system activities and user actions</p>
    </div>
</div>

{{-- STAT CARDS --}}
<div class="stat-cards mb-6">
    <div class="stat-card blue">
        <div class="stat-label">Total Activities</div>
        <div class="stat-value">{{ number_format($totalActivities) }}</div>
        <div class="stat-sub">All recorded events</div>
    </div>
    <div class="stat-card green">
        <div class="stat-label">User Logins</div>
        <div class="stat-value">{{ number_format($userLogins) }}</div>
        <div class="stat-sub">Authentication events</div>
    </div>
    <div class="stat-card gold">
        <div class="stat-label">Book Actions</div>
        <div class="stat-value">{{ number_format($bookActions) }}</div>
        <div class="stat-sub">Checkout, return, renew</div>
    </div>
    <div class="stat-card red">
        <div class="stat-label">Today's Activity</div>
        <div class="stat-value">{{ number_format($todayActivity) }}</div>
        <div class="stat-sub">Events today</div>
    </div>
</div>

<div class="grid-2" style="gap:20px;align-items:start">

    {{-- RECENT ACTIVITIES TABLE --}}
    <div class="card" style="grid-column:1/-1">
    <div class="card-header">
            <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;width:100%;">
                <div>
                    <span class="card-title">Recent Activities</span>
                    <span class="badge badge-info">{{ $logs->total() }} records</span>
                </div>
                <div style="display:flex;gap:8px;flex-wrap:wrap;">
                    <input type="text" id="activitySearch" class="form-control" style="width:auto;min-width:220px;padding:8px 12px;font-size:12px;" placeholder="Search activities...">
                    <select id="activityFilter" class="form-control" style="width:auto;min-width:140px;padding:8px 12px;font-size:12px;">
                        <option value="">All Types</option>
                        <option value="login">Login</option>
                        <option value="logout">Logout</option>
                        <option value="checkout">Checkout</option>
                        <option value="return">Return</option>
                        <option value="payment">Payment</option>
                        <option value="approval">Approval</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="table-wrap">
<table id="activityLogTable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>User</th>
                        <th>Type</th>
                        <th>Details</th>
                        <th>Timestamp</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                    <tr>
                        <td><span style="font-weight:600;font-size:12px">#{{ $log->id }}</span></td>
                        <td>
                            <div style="display:flex;align-items:center;gap:10px">
                                <div class="member-avatar avatar-teal" style="width:28px;height:28px;font-size:11px">
                                    {{ strtoupper(substr($log->user?->name ?? 'S', 0, 1)) }}
                                </div>
                                <span style="font-size:13px">{{ $log->user?->name ?? 'System' }}</span>
                            </div>
                        </td>
                        <td>
                            <span class="badge badge-{{ $log->type === 'login' ? 'info' : ($log->type === 'logout' ? 'gray' : ($log->type === 'payment' ? 'success' : ($log->type === 'approval' ? 'warning' : 'info'))) }}">
                                {{ ucfirst($log->type) }}
                            </span>
                        </td>
                        <td style="max-width:320px">
                            <span style="font-size:13px">{{ $log->details }}</span>
                        </td>
                        <td>
                            <span style="font-size:12px;color:var(--text-mid)">{{ $log->created_at->format('M j, Y g:i A') }}</span>
                        </td>
                        <td>
                            <span class="badge badge-{{ $log->status === 'success' ? 'success' : ($log->status === 'warning' ? 'warning' : 'info') }}">
                                {{ ucfirst($log->status) }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" style="text-align:center;color:var(--text-light);padding:32px">
                            <i class="fas fa-inbox" style="font-size:24px;margin-bottom:8px;display:block;color:var(--text-light)"></i>
                            No activities recorded yet.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div style="padding:12px 20px;border-top:1px solid rgba(0,0,0,0.06)">
            {{ $logs->links('vendor.pagination.simple-custom') }}
        </div>
    </div>

    {{-- ACTIVITY BREAKDOWN --}}
    <div class="card">
        <div class="card-header">
            <span class="card-title">Activity Breakdown</span>
        </div>
        <div class="card-body">
            <div style="display:flex;flex-direction:column;gap:10px">
                @forelse($typeBreakdown as $item)
                <div style="display:flex;align-items:center;justify-content:space-between;padding:10px 14px;background:var(--cream);border-radius:var(--radius)">
                    <div style="display:flex;align-items:center;gap:10px">
                        <div style="width:8px;height:8px;border-radius:50%;background:var(--teal-accent)"></div>
                        <span style="font-size:13px;font-weight:600;color:var(--text-dark)">{{ ucfirst($item->type) }}</span>
                    </div>
                    <span style="font-size:16px;font-weight:700;color:var(--teal-dark)">{{ $item->count }}</span>
                </div>
                @empty
                <div style="text-align:center;color:var(--text-light);padding:24px">
                    No activity data available.
                </div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- ACTIVITY TYPES LEGEND --}}
    <div class="card">
        <div class="card-header">
            <span class="card-title">Tracked Events</span>
        </div>
        <div class="card-body">
            <div style="display:flex;flex-direction:column;gap:8px">
                @php
                $types = [
                    ['icon' => 'fa-sign-in-alt', 'label' => 'Logins', 'desc' => 'User authentication events'],
                    ['icon' => 'fa-sign-out-alt', 'label' => 'Logouts', 'desc' => 'Session termination events'],
                    ['icon' => 'fa-money-bill-wave', 'label' => 'Payments', 'desc' => 'Fine payment processing'],
                    ['icon' => 'fa-check-circle', 'label' => 'Approvals', 'desc' => 'Staff approval actions'],
                    ['icon' => 'fa-book', 'label' => 'Book Actions', 'desc' => 'Checkout, return, renew'],
                ];
                @endphp
                @foreach($types as $t)
                <div style="display:flex;align-items:center;gap:12px;padding:10px 0;border-bottom:1px solid rgba(0,0,0,0.04)">
                    <div style="width:30px;height:30px;border-radius:var(--radius);background:var(--cream-dark);display:flex;align-items:center;justify-content:center">
                        <i class="fas {{ $t['icon'] }}" style="font-size:12px;color:var(--teal-dark)"></i>
                    </div>
                    <div style="flex:1">
                        <div style="font-size:13px;font-weight:600">{{ $t['label'] }}</div>
                        <div style="font-size:11px;color:var(--text-light)">{{ $t['desc'] }}</div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('activitySearch');
    const filterSelect = document.getElementById('activityFilter');
    const rows = document.querySelectorAll('#activityLogTable tbody tr');

    function filterTable() {
        const searchTerm = searchInput.value.toLowerCase();
        const filterType = filterSelect.value.toLowerCase();

        rows.forEach(row => {
            const userText = row.cells[1]?.textContent.toLowerCase() || '';
            const typeText = row.cells[2]?.textContent.toLowerCase() || '';
            const detailsText = row.cells[3]?.textContent.toLowerCase() || '';
            const timestampText = row.cells[4]?.textContent.toLowerCase() || '';

            const matchesSearch = userText.includes(searchTerm) || detailsText.includes(searchTerm) || timestampText.includes(searchTerm);
            const matchesType = !filterType || typeText.includes(filterType);

            row.style.display = matchesSearch && matchesType ? '' : 'none';
        });
    }

    if (searchInput) searchInput.addEventListener('input', filterTable);
    if (filterSelect) filterSelect.addEventListener('change', filterTable);
});
</script>
@endpush

@endsection

