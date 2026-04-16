@extends('layout.app')
@section('title', $member->name)
@section('content')

<div class="page-header-row mb-6">
    <div>
        <h1 class="page-title">{{ $member->name }}</h1>
        <p class="page-subtitle">{{ $member->member_id ?? 'MBR-'.str_pad($member->id,7,'0',STR_PAD_LEFT) }} · {{ ucfirst($member->type ?? 'student') }}</p>
    </div>
    <div class="flex gap-2">
        <a href="{{ route('members.edit', $member) }}" class="btn btn-outline">
            <i class="fas fa-edit"></i> Edit
        </a>
        <a href="{{ route('members.index') }}" class="btn btn-outline">
            <i class="fas fa-arrow-left"></i> Back
        </a>
    </div>
</div>

<div class="grid-2" style="gap:20px;align-items:start">
    {{-- Member Info Card --}}
    <div class="card">
        <div class="card-header"><span class="card-title">Member Details</span></div>
        <div class="card-body">
            @php
                $avatarColors = ['#1a3a3a','#c9a84c','#5a8a6a','#8a4a2a','#2a4a7a'];
                $bg = $avatarColors[$member->id % count($avatarColors)];
                $initials = strtoupper(substr($member->name,0,1).(strpos($member->name,' ')!==false?substr($member->name,strpos($member->name,' ')+1,1):''));
                $outstanding = $member->transactions->where('fine_paid',false)->sum('fine');
            @endphp
            <div style="display:flex;align-items:center;gap:16px;margin-bottom:20px">
                <div style="width:56px;height:56px;border-radius:50%;background:{{ $bg }};color:#fff;display:flex;align-items:center;justify-content:center;font-size:22px;font-weight:700;flex-shrink:0">
                    {{ $initials }}
                </div>
                <div>
                    <div style="font-size:18px;font-weight:700">{{ $member->name }}</div>
                    <div style="font-size:12px;color:var(--text-light)">
                        @php $status = $member->transactions->where('status','overdue')->count()>0?'overdue':'active'; @endphp
                        <span class="badge {{ $status==='overdue'?'badge-danger':'badge-success' }}">{{ ucfirst($status) }}</span>
                        · {{ ucfirst($member->type ?? 'student') }}
                    </div>
                </div>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
                <div>
                    <div style="font-size:11px;color:var(--text-light);text-transform:uppercase;font-weight:600;margin-bottom:2px">Email</div>
                    <div style="font-size:13px">{{ $member->email ?? '—' }}</div>
                </div>
                <div>
                    <div style="font-size:11px;color:var(--text-light);text-transform:uppercase;font-weight:600;margin-bottom:2px">Contact</div>
                    <div style="font-size:13px">{{ $member->contact ?? '—' }}</div>
                </div>
                <div>
                    <div style="font-size:11px;color:var(--text-light);text-transform:uppercase;font-weight:600;margin-bottom:2px">Books Borrowed</div>
                    <div style="font-size:16px;font-weight:700">{{ $member->transactions->whereIn('status',['active','overdue'])->count() }} active</div>
                </div>
                <div>
                    <div style="font-size:11px;color:var(--text-light);text-transform:uppercase;font-weight:600;margin-bottom:2px">Total Borrowed</div>
                    <div style="font-size:16px;font-weight:700">{{ $member->transactions->count() }} books</div>
                </div>
                <div>
                    <div style="font-size:11px;color:var(--text-light);text-transform:uppercase;font-weight:600;margin-bottom:2px">Outstanding Fine</div>
                    <div style="font-size:16px;font-weight:700;color:{{ $pendingFineTotal>0?'var(--danger)':'var(--success)' }}">
                        ₱{{ number_format($pendingFineTotal,2) }}
                    </div>
                </div>
                <div>
                    <div style="font-size:11px;color:var(--text-light);text-transform:uppercase;font-weight:600;margin-bottom:2px">Fines Pending</div>
                    <div style="font-size:16px;font-weight:700;color:var(--muted)">{{ $pendingFineCount }} transaction{{ $pendingFineCount === 1 ? '' : 's' }}</div>
                </div>
                <div>
                    <div style="font-size:11px;color:var(--text-light);text-transform:uppercase;font-weight:600;margin-bottom:2px">Member Since</div>
                    <div style="font-size:13px">{{ $member->created_at?->format('M j, Y') }}</div>
                </div>
            </div>

            @if($member->address)
            <div style="margin-top:12px;padding-top:12px;border-top:1px solid rgba(0,0,0,0.06)">
                <div style="font-size:11px;color:var(--text-light);text-transform:uppercase;font-weight:600;margin-bottom:2px">Address</div>
                <div style="font-size:13px">{{ $member->address }}</div>
            </div>
            @endif

            <div style="margin-top:20px;display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px">
                <div style="background:var(--surface);border:1px solid rgba(0,0,0,0.06);border-radius:18px;padding:16px;">
                    <div style="font-size:12px;color:var(--text-light);text-transform:uppercase;font-weight:700;margin-bottom:10px">Pending Overdue Fines</div>
                    <div style="font-size:14px;color:var(--danger);font-weight:700">₱{{ number_format($pendingFineByStatus['overdue']['total'] ?? 0,2) }}</div>
                    <div style="font-size:12px;color:var(--muted);margin-top:6px">{{ $pendingFineByStatus['overdue']['count'] ?? 0 }} transaction{{ ($pendingFineByStatus['overdue']['count'] ?? 0) === 1 ? '' : 's' }}</div>
                </div>
                <div style="background:var(--surface);border:1px solid rgba(0,0,0,0.06);border-radius:18px;padding:16px;">
                    <div style="font-size:12px;color:var(--text-light);text-transform:uppercase;font-weight:700;margin-bottom:10px">Pending Returned Fines</div>
                    <div style="font-size:14px;color:var(--gold);font-weight:700">₱{{ number_format($pendingFineByStatus['returned']['total'] ?? 0,2) }}</div>
                    <div style="font-size:12px;color:var(--muted);margin-top:6px">{{ $pendingFineByStatus['returned']['count'] ?? 0 }} transaction{{ ($pendingFineByStatus['returned']['count'] ?? 0) === 1 ? '' : 's' }}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Transaction History --}}
    <div class="card">
        <div class="card-header"><span class="card-title">Borrowing History</span></div>
        <div class="table-wrap" style="max-height:420px;overflow-y:auto">
            <table>
                <thead>
                    <tr><th>Book</th><th>Issued</th><th>Due</th><th>Status</th><th>Fine</th></tr>
                </thead>
                <tbody>
                    @forelse($member->transactions as $txn)
                    <tr>
                        <td style="max-width:140px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap" title="{{ $txn->book?->title }}">
                            {{ $txn->book?->title ?? '—' }}
                        </td>
                        <td style="font-size:12px">{{ $txn->issued_date?->format('M j, Y') }}</td>
                        <td style="font-size:12px">{{ $txn->due_date?->format('M j, Y') }}</td>
                        <td>
                            @php $s=$txn->status; @endphp
                            <span class="badge {{ $s==='active'?'badge-success':($s==='overdue'?'badge-danger':($s==='returned'?'badge-info':'badge-gray')) }}">
                                {{ ucfirst($s) }}
                            </span>
                        </td>
                        <td>
                            @if($txn->fine > 0)
                                <span style="font-size:12px;font-weight:600;color:{{ $txn->fine_paid?'var(--success)':'var(--danger)' }}">
                                    ₱{{ number_format($txn->fine,2) }}
                                </span>
                            @else
                                <span style="color:var(--text-light)">—</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" style="text-align:center;padding:20px;color:var(--text-light)">No borrowing history</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
