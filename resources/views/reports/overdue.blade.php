@extends('layout.app')
@section('title', 'Overdue Report')
@section('content')

<div class="page-header-row mb-6">
    <div>
        <h1 class="page-title">Overdue Report</h1>
        <p class="page-subtitle">Members with outstanding loans past due date</p>
    </div>
    <div class="flex gap-2">
        <a href="{{ route('reports.export', ['report' => 'overdue']) }}" class="btn btn-gold btn-sm">
            <i class="fas fa-file-pdf"></i> Export PDF
        </a>
        <a href="{{ route('reports.index') }}" class="btn btn-outline"><i class="fas fa-arrow-left"></i> Back</a>
    </div>
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
                @php
                    $days = $txn->due_date
                        ? intval(max(0, now()->diffInDays($txn->due_date, false) * -1))
                        : 0;
                @endphp
                <tr>
                    <td><code style="font-size:11px">TXN-{{ str_pad($txn->id,4,'0',STR_PAD_LEFT) }}</code></td>
                    <td>{{ $txn->member?->name ?? '—' }}</td>
                    <td style="display:flex;align-items:center;gap:8px;">
  @if($txn->book?->cover_image)
    <img src="{{ asset('storage/' . $txn->book->cover_image) }}" alt="Book cover" style="width:28px;height:40px;flex-shrink:0;object-fit:cover;border-radius:4px;">
  @else
    <div style="width:28px;height:40px;background:rgba(0,0,0,0.08);border-radius:4px;display:flex;align-items:center;justify-content:center;font-size:14px;color:#999;">📖</div>
  @endif
  <div style="min-width:0;flex:1;">
    <div style="font-weight:500;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;" title="{{ $txn->book?->title ?? '—' }}">{{ $txn->book?->title ?? '—' }}</div>
  </div>
                    </td>
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
