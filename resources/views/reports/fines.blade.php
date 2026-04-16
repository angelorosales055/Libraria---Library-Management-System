@extends('layout.app')
@section('title', 'Fines & Collection Report')
@section('content')

<div class="page-header-row mb-6">
    <div>
        <h1 class="page-title">Fines &amp; Collection</h1>
        <p class="page-subtitle">Fine ledger and payment records</p>
    </div>
    <a href="{{ route('reports.index') }}" class="btn btn-outline btn-sm">
        <i class="fas fa-arrow-left"></i> Back
    </a>
</div>

@php
    $totalFines  = $transactions->sum('fine');
    $totalPaid   = $transactions->where('fine_paid', true)->sum('fine');
    $totalUnpaid = $transactions->where('fine_paid', false)->sum('fine');
@endphp

<div style="display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin-bottom:24px">
    <div class="stat-card gold">
        <div class="stat-label">Total Fines</div>
        <div class="stat-value">₱{{ number_format($totalFines,2) }}</div>
    </div>
    <div class="stat-card green">
        <div class="stat-label">Collected</div>
        <div class="stat-value" style="color:var(--success)">₱{{ number_format($totalPaid,2) }}</div>
    </div>
    <div class="stat-card red">
        <div class="stat-label">Outstanding</div>
        <div class="stat-value" style="color:var(--danger)">₱{{ number_format($totalUnpaid,2) }}</div>
    </div>
</div>

<div class="card">
    <div class="card-header"><span class="card-title">Fine Records</span></div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Trans. ID</th>
                    <th>Member</th>
                    <th>Book</th>
                    <th>Due Date</th>
                    <th>Returned</th>
                    <th>Days Late</th>
                    <th>Fine</th>
                    <th>Payment</th>
                </tr>
            </thead>
            <tbody>
                @forelse($transactions as $txn)
                @php
                    $daysLate = $txn->returned_date && $txn->due_date
                        ? max(0, $txn->due_date->diffInDays($txn->returned_date, false) * -1)
                        : ($txn->due_date ? max(0, today()->diffInDays($txn->due_date, false) * -1) : 0);
                @endphp
                <tr>
                    <td><code style="font-size:11px">TXN-{{ str_pad($txn->id,4,'0',STR_PAD_LEFT) }}</code></td>
                    <td>{{ $txn->member?->name ?? '—' }}</td>
                    <td style="max-width:140px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">{{ $txn->book?->title ?? '—' }}</td>
                    <td>{{ $txn->due_date?->format('M j, Y') }}</td>
                    <td>{{ $txn->returned_date?->format('M j, Y') ?? '<span style="color:var(--danger)">Not returned</span>' }}</td>
                    <td>
                        @if($daysLate > 0)
                            <span class="badge badge-danger">{{ $daysLate }}d</span>
                        @else —
                        @endif
                    </td>
                    <td style="font-weight:700;color:var(--danger)">₱{{ number_format($txn->fine,2) }}</td>
                    <td>
                        @if($txn->fine_paid)
                            <span class="badge badge-success"><i class="fas fa-check" style="font-size:9px"></i> PAID</span>
                        @else
                            <span class="badge badge-danger">UNPAID</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" style="text-align:center;padding:24px;color:var(--text-light)">No fines recorded</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
