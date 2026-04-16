@extends('layout.portal')
@section('title', 'Transactions')
@section('content')
<div class="summary-grid">
    <div class="summary-card">
        <span>Active Borrowings</span>
        <h2>{{ $activeBorrowings->count() }}</h2>
    </div>
    <div class="summary-card">
        <span>Overdue Loans</span>
        <h2>{{ $overdueCount }}</h2>
    </div>
    <div class="summary-card">
        <span>Pending Fees</span>
        <h2>₱{{ number_format($outstandingFees, 2) }}</h2>
    </div>
</div>
<div class="section">
    <div class="section-header">
        <div>
            <h2>Active Borrowings</h2>
            <p style="color:var(--muted);margin-top:8px">Manage your current loans and return books on time.</p>
        </div>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Book</th>
                    <th>Issued</th>
                    <th>Due</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($activeBorrowings as $txn)
                <tr>
                    <td>{{ $txn->book?->title ?? '—' }}</td>
                    <td>{{ $txn->issued_date?->format('M j, Y') }}</td>
                    <td>{{ $txn->due_date?->format('M j, Y') }}</td>
                    <td><span class="status-chip {{ $txn->status === 'overdue' ? 'status-overdue' : 'status-active' }}">{{ ucfirst($txn->status) }}</span></td>
                    <td>
                        @if($txn->canRenew())
                            <form method="POST" action="{{ route('portal.renew', $txn) }}" style="display:inline;margin-right:8px">
                                @csrf @method('PATCH')
                                <button class="btn-sm btn-outline">Renew</button>
                            </form>
                        @endif
                        <form method="POST" action="{{ route('portal.return', $txn) }}">
                            @csrf
                            @method('PATCH')
                            <button class="btn-sm">Return</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" style="color:var(--muted);padding:24px;text-align:center;">No active borrowings found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="section">
    <div class="section-header">
        <div>
            <h2>Return History</h2>
            <p style="color:var(--muted);margin-top:8px">See the books you have already returned.</p>
        </div>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Book</th>
                    <th>Returned</th>
                    <th>Fine</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($history as $txn)
                <tr>
                    <td>{{ $txn->book?->title ?? '—' }}</td>
                    <td>{{ $txn->returned_date?->format('M j, Y') }}</td>
                    <td>{{ $txn->fine > 0 ? '₱'.number_format($txn->fine,2) : '₱0.00' }}</td>
                    <td><span class="status-chip status-returned">Returned</span></td>
                </tr>
                @empty
                <tr><td colspan="4" style="color:var(--muted);padding:24px;text-align:center;">No return history yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="section">
    <div class="section-header">
        <div>
            <h2>Recent Notifications</h2>
            <p style="color:var(--muted);margin-top:8px">Important reminders from your borrowing activity.</p>
        </div>
    </div>
    <div class="featured-grid">
        @forelse($notifications as $note)
            <div class="book-card" style="padding:22px;">
                <div class="book-category">{{ ucfirst(str_replace('_', ' ', $note['type'])) }}</div>
                <div class="book-title" style="margin-top:10px; margin-bottom:10px;">{{ $note['title'] }}</div>
                <div class="book-author" style="font-size:13px; color:var(--muted);">{{ $note['message'] }}</div>
                <div style="margin-top:16px; color:var(--muted); font-size:12px;">{{ $note['date'] }}</div>
            </div>
        @empty
            <div style="color:var(--muted);padding:32px;background:var(--surface);border-radius:20px">No notifications yet. Your latest borrow activity will appear here.</div>
        @endforelse
    </div>
</div>
@endsection
