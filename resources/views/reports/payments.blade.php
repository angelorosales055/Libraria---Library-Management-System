@extends('layout.app')
@section('title', 'Payments Report')
@section('content')

<div class="page-header-row mb-6">
    <div>
        <h1 class="page-title">Payments Report</h1>
        <p class="page-subtitle">All collected fine payments with receipts</p>
    </div>
    <div class="flex gap-2">
        <a href="{{ route('reports.export', array_merge(request()->query(), ['report' => 'payments'])) }}" class="btn btn-gold btn-sm">
            <i class="fas fa-file-pdf"></i> Export PDF
        </a>
        <a href="{{ route('reports.index') }}" class="btn btn-outline btn-sm">
            <i class="fas fa-arrow-left"></i> Back
        </a>
    </div>
</div>

<div class="card mb-6 p-4">
    <form method="GET" action="{{ route('reports.payments') }}" style="display: flex; gap: 16px; flex-wrap: wrap; align-items: flex-end;">
        <div>
            <label class="form-label text-sm">Start Date</label>
            <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}">
        </div>
        <div>
            <label class="form-label text-sm">End Date</label>
            <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}">
        </div>
        <div>
            <label class="form-label text-sm">Method</label>
            <select name="method" class="form-control">
                <option value="">All Methods</option>
                <option value="cash" {{ request('method') == 'cash' ? 'selected' : '' }}>Cash</option>
                <option value="gcash" {{ request('method') == 'gcash' ? 'selected' : '' }}>GCash</option>
                <option value="paymaya" {{ request('method') == 'paymaya' ? 'selected' : '' }}>PayMaya</option>
            </select>
        </div>
        <div style="display:flex;gap:8px;align-items:center;">
            <button type="submit" class="btn btn-primary"><i class="fas fa-filter"></i> Filter</button>
            <a href="{{ route('reports.payments') }}" class="btn btn-outline">Clear</a>
        </div>
    </form>
</div>

<div class="stat-cards" style="grid-template-columns: repeat(3, 1fr); margin-bottom:24px">
    <div class="stat-card gold">
        <div class="stat-label">Total Collected</div>
        <div class="stat-value">₱{{ number_format($totalCollected,2) }}</div>
    </div>
    <div class="stat-card blue">
        <div class="stat-label">Cash Payments</div>
        <div class="stat-value">₱{{ number_format($cashTotal, 2) }}</div>
    </div>
    <div class="stat-card green">
        <div class="stat-label">Digital Payments</div>
        <div class="stat-value">₱{{ number_format($digitalTotal, 2) }}</div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <span class="card-title">Payment Records</span>
        <span class="badge badge-info">{{ $payments->total() }} records</span>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Receipt No</th>
                    <th>Member</th>
                    <th>Book</th>
                    <th>Amount</th>
                    <th>Method</th>
                    <th>Paid At</th>
                    <th>Collected By</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($payments as $txn)
                <tr>
                    <td><code style="font-size:11px;color:var(--text-mid)">{{ $txn->receipt_no }}</code></td>
                    <td>{{ $txn->member?->name ?? '—' }}</td>
                    <td style="max-width:160px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap" title="{{ $txn->book?->title }}">
                        {{ $txn->book?->title ?? '—' }}
                    </td>
                    <td style="font-weight:700;color:var(--success)">₱{{ number_format($txn->fine,2) }}</td>
                    <td>
                        <span class="badge {{ $txn->payment_method==='cash'?'badge-gray':($txn->payment_method==='gcash'?'badge-info':'badge-warning') }}">
                            <i class="fas {{ $txn->payment_method==='cash'?'fa-money-bill':($txn->payment_method==='gcash'?'fa-mobile-alt':'fa-credit-card') }}" style="font-size:10px;margin-right:4px"></i>
                            {{ ucfirst($txn->payment_method) }}
                        </span>
                    </td>
                    <td>{{ $txn->paid_at?->format('M j, Y g:i A') }}</td>
                    <td>{{ $txn->collectedBy?->name ?? '—' }}</td>
                    <td>
                        <a href="{{ route('receipt.show', $txn) }}" class="btn btn-outline btn-xs">
                            <i class="fas fa-receipt"></i> Receipt
                        </a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" style="text-align:center;padding:24px;color:var(--text-light)">No payments recorded yet</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($payments->hasPages())
        <div class="card-footer" style="padding: 16px; border-top: 1px solid var(--border-color);">
            {{ $payments->links() }}
        </div>
    @endif
</div>
@endsection
