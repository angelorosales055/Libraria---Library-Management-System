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
        <h2 style="color:var(--danger);">{{ $overdueCount }}</h2>
    </div>
    <div class="summary-card">
        <span>Pending Requests</span>
        <h2>{{ $pendingRequests->count() }}</h2>
    </div>
    <div class="summary-card">
        <span>Outstanding Fines</span>
        <h2 style="color:var(--danger);">₱{{ number_format($outstandingFees, 2) }}</h2>
    </div>
</div>

@if(isset($rejectedRequests) && $rejectedRequests->count() > 0)
<div class="section">
    <div class="section-header">
        <div>
            <h2>Rejected Requests</h2>
            <p style="color:var(--muted);margin-top:8px">Book requests that were declined by staff.</p>
        </div>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Book</th>
                    <th>Requested</th>
                    <th>Due</th>
                    <th>Reason</th>
                </tr>
            </thead>
            <tbody>
                @foreach($rejectedRequests as $txn)
                <tr>
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
                    <td>{{ $txn->due_date?->format('M j, Y') }}</td>
                    <td style="max-width:320px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="{{ $txn->notes ?? 'No reason provided' }}">
                        {{ $txn->notes ? \Illuminate\Support\Str::limit($txn->notes, 70) : 'No reason provided' }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

<div class="filters-row" style="margin-bottom:24px;">
    <form method="GET" action="{{ route('portal.transactions') }}" style="display:flex;gap:12px;flex-wrap:wrap;align-items:center;width:100%;">
        <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Search book title or author..." class="filter-input" style="min-width:300px;">
        <select name="filter" class="filter-input" style="min-width:200px;">
            <option value="all" {{ ($filter ?? 'all') == 'all' ? 'selected' : '' }}>All Transactions</option>
            <option value="active" {{ ($filter ?? '') == 'active' ? 'selected' : '' }}>Active Borrowings</option>
            <option value="pending" {{ ($filter ?? '') == 'pending' ? 'selected' : '' }}>Pending Requests</option>
            <option value="rejected" {{ ($filter ?? '') == 'rejected' ? 'selected' : '' }}>Rejected</option>
            <option value="history" {{ ($filter ?? '') == 'history' ? 'selected' : '' }}>Return History</option>
        </select>
        <button type="submit" class="btn btn-primary">Filter</button>
        @if($search || $filter != 'all')
            <a href="{{ route('portal.transactions') }}" class="btn btn-outline btn-sm">Clear</a>
        @endif
    </form>
</div>

<div class="section">
    <div class="section-header">
        <div>
            <h2>Pending Requests</h2>
            <p style="color:var(--muted);margin-top:8px">Books waiting for staff approval.</p>
        </div>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Book</th>
                    <th>Requested</th>
                    <th>Due</th>
                    <th>Comment</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($pendingRequests as $txn)
                <tr>
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
                    <td>{{ $txn->due_date?->format('M j, Y') }}</td>
                    <td style="max-width:320px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="{{ $txn->notes ?? 'No comment provided' }}">
                        {{ $txn->notes ? \Illuminate\Support\Str::limit($txn->notes, 70) : 'No comment provided' }}
                    </td>
                    <td><span class="status-chip status-pending">Pending Approval</span></td>
                </tr>
                @empty
                <tr><td colspan="4" style="color:var(--muted);padding:24px;text-align:center;">No pending requests found.</td></tr>
                @endforelse
            </tbody>
        </table>
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
                    <th>Penalty</th>
                    <th>Status</th>
                    <th style="text-align:center;">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($activeBorrowings as $txn)
                <tr>
                    @php
                        $displayPenalty = $txn->fine > 0
                            ? $txn->fine
                            : ($txn->is_overdue ? $txn->computed_fine : 0);
                    @endphp
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
                    <td>{{ $txn->due_date?->format('M j, Y') }}</td>
                    <td style="font-weight:700;color:var(--danger)">₱{{ number_format($displayPenalty, 2) }}</td>
                    <td>
                        @if($txn->status === 'damaged' || $txn->status === 'damage_return')
                            <div style="display:flex;flex-direction:column;gap:8px;align-items:flex-start;">
                                <span class="status-chip status-damaged">
                                    <i class="fas fa-exclamation-triangle"></i>
                                    Book Damaged
                                </span>
                                <span style="font-size:11px;color:var(--text-light);line-height:1.4;padding:6px 10px;background:rgba(255,87,34,0.08);border-radius:6px;border-left:3px solid #ff5722;">
                                    <strong>Action required:</strong> Damage and overdue penalty applied
                                </span>
                            </div>
                        @elseif($txn->status === 'overdue')
                            <span class="status-chip status-overdue">
                                <i class="fas fa-clock"></i>
                                Overdue
                            </span>
                        @else
                            <span class="status-chip status-active">
                                <i class="fas fa-check-circle"></i>
                                {{ ucfirst($txn->status) }}
                            </span>
                        @endif
                    </td>
                    <td style="text-align:right;">
                        <div style="display:flex;justify-content:flex-end;gap:6px;flex-wrap:wrap">
                            <button class="btn btn-outline btn-sm" onclick="openTxnDetailModal({{ $txn->id }})">
                                <i class="fas fa-eye" style="margin-right:4px;font-size:11px"></i> View
                            </button>
                        </div>
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
                    <th>Status</th>
                    <th style="text-align:right;">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($history as $txn)
                <tr>
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
                    <td>{{ $txn->returned_date?->format('M j, Y') }}</td>
                    <td><span class="status-chip status-returned">Returned</span></td>
                    <td style="text-align:right;">
                        <a href="{{ route('receipt.show', $txn) }}" class="btn btn-outline btn-sm" target="_blank">
                            <i class="fas fa-receipt" style="margin-right:4px"></i> Receipt
                        </a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="4" style="color:var(--muted);padding:24px;text-align:center;">No return history yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Transaction Detail Modal --}}
<div class="modal-overlay" id="txnDetailModal" style="display: none;">
    <div class="modal" style="max-width: 550px; max-height: 90vh; overflow-y: auto;">
        <div class="modal-header">
            <div class="modal-title">
                <i class="fas fa-file-invoice-dollar" style="margin-right:8px;color:var(--accent);"></i>
                Transaction Details
            </div>
            <button class="modal-close" onclick="closeTxnDetailModal()">&times;</button>
        </div>
        <div class="modal-body" id="txnDetailBody" style="padding-bottom: 20px;">
            <div style="text-align:center;padding:30px 20px;color:var(--muted);">
                <div style="font-size: 64px; margin-bottom: 16px;">📄</div>
                <div style="font-size: 16px; font-weight: 600; margin-bottom: 8px;">Loading transaction...</div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>


function openTxnDetailModal(txnId) {
    const modal = document.getElementById('txnDetailModal');
    const body = document.getElementById('txnDetailBody');
    
    body.innerHTML = `
        <div style="text-align:center;padding:30px 20px;color:var(--muted);">
            <div style="font-size: 48px; margin-bottom: 16px;">⏳</div>
            <div style="font-size: 16px; font-weight: 600;">Loading transaction details...</div>
        </div>
    `;
    
    modal.style.display = 'flex';
    
    // Fetch data
    fetch(`/api/transactions/${txnId}`)
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                body.innerHTML = `
                    <div style="text-align:center;padding:30px 20px;color:var(--danger);">
                        <div style="font-size: 48px; margin-bottom: 16px;">⚠️</div>
                        <div style="font-size: 16px; font-weight: 600; margin-bottom: 12px;">Error loading details</div>
                        <div style="font-size: 14px; opacity: 0.8;">${data.error}</div>
                    </div>
                `;
                return;
            }

            const overdueFine = data.outstanding_fine && data.outstanding_fine > 0
                ? data.outstanding_fine
                : (data.fine > 0 ? data.fine : data.computed_fine);
            const fineDisplay = overdueFine > 0 
                ? `<span style="color:var(--danger);font-weight:700;">₱${(overdueFine).toFixed(2)}</span> ${data.fine_paid ? '<span class="status-chip status-active ml-1">Paid</span>' : '<span class="status-chip status-overdue ml-1">Unpaid</span>'}`
                : '<span style="color:var(--success);">No fine</span>';

            body.innerHTML = `
                <div style="font-size:12px;color:var(--text-light);text-transform:uppercase;letter-spacing:0.5px;margin-bottom:20px;">Transaction TXN-${String(data.id).padStart(4, '0', 'left')}</div>
                
                <div style="margin-bottom:24px;">
                    <div style="font-size:12px;font-weight:700;color:var(--text-light);margin-bottom:12px;">Book Details</div>
                    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:12px;">
                        <div>
                            <div style="font-size:11px;color:var(--text-light);">Title</div>
                            <div style="font-size:15px;font-weight:600;" title="${data.book.title}">${data.book.title}</div>
                        </div>
                        <div>
                            <div style="font-size:11px;color:var(--text-light);">Author</div>
                            <div style="font-size:14px;">${data.book.author || '—'}</div>
                        </div>
                        <div>
                            <div style="font-size:11px;color:var(--text-light);">Category</div>
                            <div style="font-size:14px;">${data.book.category || '—'}</div>
                        </div>
                        <div>
                            <div style="font-size:11px;color:var(--text-light);">ISBN/Accession</div>
                            <div style="font-size:14px;">${data.book.isbn || data.book.accession_no || '—'}</div>
                        </div>
                    </div>
                </div>

                <div style="border-top:1px solid rgba(0,0,0,0.06);padding-top:24px;">
                    <div style="font-size:12px;font-weight:700;color:var(--text-light);margin-bottom:12px;">Transaction Status</div>
                    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:12px;">
                        <div>
                            <div style="font-size:11px;color:var(--text-light);">Status</div>
                            <div style="font-size:14px;font-weight:600;">${data.status.replace(/^\w/, c => c.toUpperCase())}</div>
                        </div>
                        <div>
                            <div style="font-size:11px;color:var(--text-light);">Issued</div>
                            <div style="font-size:14px;">${data.issued_date || '—'}</div>
                        </div>
                        <div>
                            <div style="font-size:11px;color:var(--text-light);">Due Date</div>
                            <div style="font-size:14px;font-weight:600;">${data.due_date || '—'}</div>
                        </div>
                        <div>
                            <div style="font-size:11px;color:var(--text-light);">Returned</div>
                            <div style="font-size:14px;">${data.returned_date || 'Not returned'}</div>
                        </div>
                        <div>
                            <div style="font-size:11px;color:var(--text-light);">Renewals</div>
                            <div style="font-size:14px;">${data.renewal_count}/${data.max_renewals}</div>
                        </div>
                        <div>
                            <div style="font-size:11px;color:var(--text-light);">Fine</div>
                            <div>${fineDisplay}</div>
                        </div>
                    </div>
                </div>

                ${data.notes ? `
                <div style="margin-top:24px;">
                    <div style="font-size:12px;font-weight:700;color:var(--text-light);margin-bottom:12px;">Notes</div>
                    <div style="background:rgba(0,0,0,0.02);padding:12px 16px;border-radius:12px;font-size:13px;line-height:1.5;white-space:pre-wrap;">${data.notes}</div>
                </div>
                ` : ''}

                <div style="margin-top:24px;">
                    <div style="font-size:12px;font-weight:700;color:var(--text-light);margin-bottom:12px;">Borrower</div>
                    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:12px;">
                        <div>
                            <div style="font-size:11px;color:var(--text-light);">Name</div>
                            <div style="font-size:14px;">${data.member.name || '—'}</div>
                        </div>
                        <div>
                            <div style="font-size:11px;color:var(--text-light);">Member ID</div>
                            <div style="font-size:14px;">${data.member.member_id || '—'}</div>
                        </div>
                        <div>
                            <div style="font-size:11px;color:var(--text-light);">Email</div>
                            <div style="font-size:14px;">${data.member.email || '—'}</div>
                        </div>
                    </div>
                </div>
            `;

            // If there's an outstanding fine, show a quick Pay Now action
            if (overdueFine > 0 && !data.fine_paid) {
                const actionsHtml = `
                    <div style="margin-top:20px;display:flex;gap:10px;justify-content:flex-end;">
                        <a href="/paymongo/create/${data.id}?payment_method=gcash" class="btn btn-primary">Pay Now (GCash)</a>
                        <a href="/portal/fines" class="btn btn-outline">View Fines</a>
                    </div>
                `;
                body.insertAdjacentHTML('beforeend', actionsHtml);
            }
        })
        .catch(error => {
            body.innerHTML = `
                <div style="text-align:center;padding:30px 20px;color:var(--danger);">
                    <div style="font-size: 48px; margin-bottom: 16px;">⚠️</div>
                    <div style="font-size: 16px; font-weight: 600; margin-bottom: 12px;">Unable to load details</div>
                    <div style="font-size: 14px; opacity: 0.8;">Please try again</div>
                </div>
            `;
            console.error('Transaction fetch error:', error);
        });
}

function closeTxnDetailModal() {
    document.getElementById('txnDetailModal').style.display = 'none';
}

// Close on overlay click
document.getElementById('txnDetailModal')?.addEventListener('click', function(e) {
    if (e.target === this) closeTxnDetailModal();
});

// Debounce search
let searchTimeout;
document.querySelector('input[name="search"]')?.addEventListener('input', function() {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        this.form.submit();
    }, 500);
});
</script>
@endpush
@endsection
