@extends('layout.app')
@section('title', 'Member Activity Report')
@section('content')

<div class="page-header-row mb-6">
    <div>
        <h1 class="page-title">Member Activity</h1>
        <p class="page-subtitle">Borrowing history per member</p>
    </div>
    <div class="flex gap-2">
        <a href="{{ route('reports.export', ['report' => 'members']) }}" class="btn btn-gold btn-sm">
            <i class="fas fa-file-pdf"></i> Export PDF
        </a>
        <a href="{{ route('reports.index') }}" class="btn btn-outline btn-sm">
            <i class="fas fa-arrow-left"></i> Back
        </a>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <span class="card-title">Member Borrowing Summary</span>
        <span class="badge badge-info">{{ $members->count() }} members</span>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Member ID</th>
                    <th>Name</th>
                    <th>Type</th>
                    <th>Total Transactions</th>
                    <th>Active Loans</th>
                    <th>Outstanding Fine</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($members as $member)
                @php
                    $outstandingFine = $member->transactions->where('fine_paid', false)->sum('fine');
                    $status = $member->transactions->where('status','overdue')->count() > 0 ? 'overdue' : 'active';
                @endphp
                <tr>
                    <td><code style="font-size:11px">{{ $member->member_id ?? 'MBR-'.str_pad($member->id,7,'0',STR_PAD_LEFT) }}</code></td>
                    <td style="font-weight:500">{{ $member->name }}</td>
                    <td><span class="badge badge-gray">{{ ucfirst($member->type ?? 'student') }}</span></td>
                    <td style="text-align:center;font-weight:600">{{ $member->total_txns }}</td>
                    <td style="text-align:center">
                        <span class="{{ $member->active_txns > 0 ? 'text-success font-bold' : 'text-muted' }}">
                            {{ $member->active_txns }}
                        </span>
                    </td>
                    <td>
                        @if($outstandingFine > 0)
                            <span style="color:var(--danger);font-weight:700">₱{{ number_format($outstandingFine,2) }}</span>
                        @else
                            <span class="text-success">₱0.00</span>
                        @endif
                    </td>
                    <td>
                        <span class="badge {{ $status==='overdue'?'badge-danger':'badge-success' }}">{{ ucfirst($status) }}</span>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" style="text-align:center;padding:24px;color:var(--text-light)">No members found</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
