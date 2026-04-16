@extends('layout.app')
@section('title', 'Overdue Report')
@section('content')

<div class="page-header-row mb-6">
    <div>
        <h1 class="page-title">Overdue Report</h1>
        <p class="page-subtitle">Members with outstanding loans past due date</p>
    </div>
    <a href="{{ route('reports.index') }}" class="btn btn-outline"><i class="fas fa-arrow-left"></i> Back</a>
</div>

<div class="card">
    <div class="card-header">
        <span class="card-title">Overdue Transactions</span>
        <span class="badge badge-danger">{{ $transactions->count() }} overdue</span>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Trans. ID</th><th>Member</th><th>Book</th>
                    <th>Issued</th><th>Due Date</th><th>Days Overdue</th><th>Fine (₱)</th>
                </tr>
            </thead>
            <tbody>
                @forelse($transactions as $txn)
                @php $days = now()->diffInDays($txn->due_date); @endphp
                <tr>
                    <td><code style="font-size:11px">TXN-{{ str_pad($txn->id,4,'0',STR_PAD_LEFT) }}</code></td>
                    <td>{{ $txn->member?->name ?? '—' }}</td>
                    <td>{{ $txn->book?->title ?? '—' }}</td>
                    <td>{{ $txn->issued_date?->format('M j, Y') }}</td>
                    <td style="color:var(--danger);font-weight:600">{{ $txn->due_date?->format('M j, Y') }}</td>
                    <td><span class="badge badge-danger">{{ $days }} days</span></td>
                    <td style="color:var(--danger);font-weight:700">₱{{ number_format($txn->fine,2) }}</td>
                </tr>
                @empty
                <tr><td colspan="7" style="text-align:center;padding:24px;color:var(--text-light)">No overdue books 🎉</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
