@extends('layout.app')
@section('title', 'Circulation Report')
@section('content')

<div class="page-header-row mb-6">
    <div>
        <h1 class="page-title">Circulation Report</h1>
        <p class="page-subtitle">All checkouts and returns</p>
    </div>
    <div class="flex gap-2">
        <a href="{{ route('reports.export', ['report' => 'circulation']) }}" class="btn btn-gold btn-sm">
            <i class="fas fa-file-pdf"></i> Export PDF
        </a>
        <a href="{{ route('reports.index') }}" class="btn btn-outline btn-sm">
            <i class="fas fa-arrow-left"></i> Back
        </a>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <span class="card-title">All Transactions</span>
        <span class="badge badge-info">{{ $transactions->total() }} records</span>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Trans. ID</th>
                    <th>Member</th>
                    <th>Book</th>
                    <th>Action</th>
                    <th>Issued</th>
                    <th>Due</th>
                    <th>Returned</th>
                    <th>Fine</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($transactions as $txn)
                <tr>
                    <td><code style="font-size:11px;color:var(--text-mid)">TXN-{{ str_pad($txn->id,4,'0',STR_PAD_LEFT) }}</code></td>
                    <td>{{ $txn->member?->name ?? '—' }}</td>
                    <td style="max-width:160px;">
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
                    <td><span class="badge badge-gray">{{ ucfirst($txn->action) }}</span></td>
                    <td>{{ $txn->issued_date?->format('M j, Y') }}</td>
                    <td>{{ $txn->due_date?->format('M j, Y') }}</td>
                    <td>{{ $txn->returned_date?->format('M j, Y') ?? '—' }}</td>
                    <td>
                        @if($txn->fine > 0)
                            <span class="{{ $txn->fine_paid ? 'text-success' : 'text-danger' }} font-bold" style="font-size:13px">
                                ₱{{ number_format($txn->fine,2) }}
                                @if($txn->fine_paid) <span class="badge badge-success" style="font-size:10px">PAID</span>@endif
                            </span>
                        @else
                            <span style="color:var(--text-light)">—</span>
                        @endif
                    </td>
                    <td>
                        @php $s = $txn->status; @endphp
                        <span class="badge {{ $s==='active'?'badge-success':($s==='overdue'?'badge-danger':($s==='returned'?'badge-info':'badge-gray')) }}">
                            {{ ucfirst($s) }}
                        </span>
                    </td>
                </tr>
                @empty
                <tr><td colspan="9" style="text-align:center;padding:24px;color:var(--text-light)">No transactions found</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div style="padding:16px 20px">{{ $transactions->links() }}</div>
</div>
@endsection
