@extends('layout.app')
@section('title', $book->title)
@section('content')

<div class="page-header-row mb-6">
    <div>
        <h1 class="page-title">{{ $book->title }}</h1>
        <p class="page-subtitle">{{ $book->author }} · {{ $book->category?->name }}</p>
    </div>
    <div class="flex gap-2">
        <a href="{{ route('books.edit', $book) }}" class="btn btn-outline">
            <i class="fas fa-edit"></i> Edit
        </a>
        <a href="{{ route('books.index') }}" class="btn btn-outline">
            <i class="fas fa-arrow-left"></i> Back
        </a>
    </div>
</div>

<div class="grid-2" style="gap:20px;align-items:start">
    <div class="card">
        <div class="card-body" style="display:flex;gap:20px">
            <div style="width:100px;height:140px;border-radius:8px;overflow:hidden;flex-shrink:0;background:var(--cream-dark);display:flex;align-items:center;justify-content:center;font-size:40px">
                @if($book->cover_image)
                    <img src="{{ asset('storage/'.$book->cover_image) }}" style="width:100%;height:100%;object-fit:cover">
                @else 📚
                @endif
            </div>
            <div>
                <div style="margin-bottom:8px">
                    <div style="font-size:11px;color:var(--text-light);text-transform:uppercase;font-weight:600">ISBN</div>
                    <div style="font-size:14px;font-family:monospace">{{ $book->isbn }}</div>
                </div>
                <div style="margin-bottom:8px">
                    <div style="font-size:11px;color:var(--text-light);text-transform:uppercase;font-weight:600">Accession No.</div>
                    <div style="font-size:14px;font-family:monospace">{{ $book->accession_no ?? '—' }}</div>
                </div>
                <div style="margin-bottom:8px">
                    <div style="font-size:11px;color:var(--text-light);text-transform:uppercase;font-weight:600">Shelf</div>
                    <div style="font-size:14px">{{ $book->shelf ?? '—' }}</div>
                </div>
                <div class="flex gap-2" style="margin-top:12px">
                    @php $avail = $book->available_copies ?? $book->copies; @endphp
                    <span class="badge {{ $avail>2?'badge-success':($avail>0?'badge-warning':'badge-danger') }}">
                        {{ $avail }} / {{ $book->copies }} available
                    </span>
                </div>
            </div>
        </div>
        @if($book->description)
        <div style="padding:0 20px 16px;font-size:13px;color:var(--text-mid);line-height:1.6">
            {{ $book->description }}
        </div>
        @endif
    </div>

    <div class="card">
        <div class="card-header"><span class="card-title">Recent Checkouts</span></div>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Member</th><th>Issued</th><th>Due</th><th>Status</th></tr></thead>
                <tbody>
                    @forelse($book->transactions->take(8) as $txn)
                    <tr>
                        <td>{{ $txn->member?->name ?? '—' }}</td>
                        <td>{{ $txn->issued_date?->format('M j, Y') }}</td>
                        <td>{{ $txn->due_date?->format('M j, Y') }}</td>
                        <td><span class="badge {{ $txn->status==='active'?'badge-success':($txn->status==='overdue'?'badge-danger':($txn->status==='returned'?'badge-info':'badge-gray')) }}">{{ ucfirst($txn->status) }}</span></td>
                    </tr>
                    @empty
                    <tr><td colspan="4" style="text-align:center;color:var(--text-light);padding:20px">No checkout history</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
