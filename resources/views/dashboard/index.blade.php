@extends('layout.app')
@section('title', 'Dashboard')
@section('content')

<div class="page-header-row mb-6">
    <div>
        <h1 class="page-title">Library Dashboard</h1>
    </div>
    <a href="{{ route('circulation.create') }}" class="btn btn-gold">
        <i class="fas fa-plus"></i> New Transaction
    </a>
</div>

{{-- STAT CARDS --}}
<div class="stat-cards">
    <a href="{{ route('books.index') }}" class="stat-card blue stat-card-link">
        <div class="stat-label">Total Books</div>
        <div class="stat-value">{{ $totalBooks ?? 0 }}</div>
        <div class="stat-sub">+{{ $newBooksMonth ?? 0 }} this month</div>
    </a>
    <a href="{{ route('members.index') }}" class="stat-card green stat-card-link">
        <div class="stat-label">Active Members</div>
        <div class="stat-value">{{ $activeMembers ?? 0 }}</div>
        <div class="stat-sub">+{{ $newMembersWeek ?? 0 }} new registrations</div>
    </a>
    <a href="{{ route('circulation.index', ['status' => 'overdue']) }}" class="stat-card red stat-card-link">
        <div class="stat-label">Overdue</div>
        <div class="stat-value" style="color:var(--danger)">{{ $overdueCount ?? 0 }}</div>
        <div class="stat-sub">{{ $overdueToday ?? 0 }} overdue yesterday</div>
    </a>
    <a href="{{ route('circulation.index', ['status' => 'active']) }}" class="stat-card gold stat-card-link">
        <div class="stat-label">Checked Out</div>
        <div class="stat-value">{{ $checkedOut ?? 0 }}</div>
        <div class="stat-sub">{{ $utilizationRate ?? 0 }}% utilization rate</div>
    </a>
</div>

{{-- RECENT TRANSACTIONS --}}
<div class="card mb-6">
    <div class="card-header">
        <span class="card-title">Recent Transactions</span>
        <a href="{{ route('circulation.index') }}" class="btn btn-outline btn-sm">View All</a>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Member</th>
                    <th>Book</th>
                    <th>Action</th>
                    <th>Date</th>
                    <th>Due</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentTransactions ?? [] as $txn)
                <tr>
                    <td>{{ $txn->member->name ?? '—' }}</td>
                    <td>{{ $txn->book->title ?? '—' }}</td>
                    <td>{{ ucfirst($txn->action) }}</td>
                    <td>{{ $txn->created_at?->format('M j') }}</td>
                    <td>{{ $txn->due_date?->format('M j') ?? '—' }}</td>
                    <td>
                        @php $s = $txn->status; @endphp
                        <span class="badge {{ $s==='active'?'badge-success':($s==='overdue'?'badge-danger':($s==='returned'?'badge-info':'badge-warning')) }}">
                            {{ ucfirst($s) }}
                        </span>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" style="text-align:center;color:var(--text-light);padding:24px">No recent transactions</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
