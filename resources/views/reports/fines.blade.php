@extends('layout.app')
@section('title', 'Fines & Collection Report')
@section('content')

<div class="page-header-row mb-6">
    <div>
        <h1 class="page-title">Fines &amp; Collection</h1>
        <p class="page-subtitle">Fine ledger and payment records</p>
    </div>
    <div class="flex gap-2">
        <a href="{{ route('reports.export', ['report' => 'fines']) }}" class="btn btn-gold btn-sm">
            <i class="fas fa-file-pdf"></i> Export PDF
        </a>
        <a href="{{ route('reports.index') }}" class="btn btn-outline btn-sm">
            <i class="fas fa-arrow-left"></i> Back
        </a>
    </div>
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
                    <td style="max-width:140px;">
  <div style="display:flex;align-items:center;gap:8px;">
    @if($txn->book?->cover_image)
      <img src="{{ asset('storage/' . $txn->book->cover_image) }}" alt="Book cover" style="width:28px;height:40px;flex-shrink:0;object-fit:cover;border-radius:4px;">
    @else
      <div style="width:28px;height:40px;background:rgba(0,0,0,0.08);border-radius:4px;display:flex;align-items:center;justify-content:center;font-size:14px;color:#999;">📖</div>
    @endif
    <div style="min-width:0;flex:1;">
      <div style="font-weight:500;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;" title="{{ $txn->book?->title ?? '—' }}">{{ $txn->book?->title ?? '—' }}</div>
    </div>
  </div>
                    </td>
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
