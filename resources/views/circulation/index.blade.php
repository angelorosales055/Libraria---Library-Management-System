@extends('layout.app')
@section('title', 'Circulation')
@section('content')

<div class="page-header-row mb-4">
    <div>
        <h1 class="page-title">Circulation</h1>
        <p class="page-subtitle">Manage book lending, returns, and due dates</p>
    </div>
    <div class="flex gap-2">
        <button class="btn btn-outline" onclick="scrollToSection('returnSection')">
            <i class="fas fa-undo"></i> Process Return
        </button>
        <button class="btn btn-gold" onclick="scrollToSection('checkoutSection')">
            <i class="fas fa-book-open"></i> Issue Book
        </button>
    </div>
</div>

{{-- WORKFLOW --}}
<div class="workflow-steps mb-6">
    <div class="workflow-step">
        <div class="step-num">1</div>
        <div>
            <div class="step-text">Scan Member ID</div>
            <div class="step-sub">Barcode or manual entry</div>
        </div>
    </div>
    <div class="workflow-step">
        <div class="step-num">2</div>
        <div>
            <div class="step-text">Verify Member</div>
            <div class="step-sub">Check standing & limits</div>
        </div>
    </div>
    <div class="workflow-step">
        <div class="step-num">3</div>
        <div>
            <div class="step-text">Scan Book</div>
            <div class="step-sub">Accession/barcode scan</div>
        </div>
    </div>
    <div class="workflow-step">
        <div class="step-num">4</div>
        <div>
            <div class="step-text">Set Due Date</div>
            <div class="step-sub">Default: 14 days</div>
        </div>
    </div>
    <div class="workflow-step">
        <div class="step-num">5</div>
        <div>
            <div class="step-text">Confirm</div>
            <div class="step-sub">Print / save receipt</div>
        </div>
    </div>
</div>

{{-- PENDING REQUESTS --}}
@if($pendingTransactions->count() > 0)
<div class="card mb-6" id="pendingSection">
    <div class="card-header">
        <span class="card-title">Pending Requests</span>
        <span class="badge badge-warning">{{ $pendingTransactions->count() }} awaiting approval</span>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Member</th>
                    <th>Book</th>
                    <th>Requested</th>
                    <th>Due</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($pendingTransactions as $pt)
                <tr>
                    <td>{{ $pt->member->name ?? '—' }}</td>
                    <td style="display:flex;align-items:center;gap:8px;">
  @if($pt->book?->cover_image)
    <img src="{{ asset('storage/' . $pt->book->cover_image) }}" alt="Book cover" style="width:28px;height:40px;flex-shrink:0;object-fit:cover;border-radius:4px;">
  @else
    <div style="width:28px;height:40px;background:rgba(0,0,0,0.08);border-radius:4px;display:flex;align-items:center;justify-content:center;font-size:14px;color:#999;">📖</div>
  @endif
  <div style="min-width:0;flex:1;">
    <div style="font-weight:500;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;" title="{{ $pt->book?->title ?? '—' }}">{{ $pt->book->title ?? '—' }}</div>
  </div>
                    </td>
                    <td>{{ $pt->issued_date?->format('M j, Y') }}</td>
                    <td>{{ $pt->due_date?->format('M j, Y') }}</td>
<td><span class="badge {{ $pt->status === 'renew_requested' ? 'badge-info' : 'badge-warning' }}">{{ ucfirst($pt->status) }}</span>
@if($pt->status === 'renew_requested' && $pt->originalTransaction)
<small class="block text-xs opacity-75">Renew TXN-{{ str_pad($pt->original_transaction_id, 4, '0', STR_PAD_LEFT) }}</small>
@endif</td>
                    <td>
                        <div class="flex gap-2">
                            <button type="button" class="btn btn-success btn-xs"
onclick="openApproveModal({{ $pt->id }}, '{{ addslashes($pt->member->name ?? '—') }}', '{{ addslashes($pt->member->member_id ?? '—') }}', '{{ addslashes($pt->member->email ?? '—') }}', '{{ addslashes($pt->member->type ?? '—') }}', '{{ $pt->issued_date?->format('M j, Y') ?? '—' }}', '{{ $pt->due_date?->format('M j, Y') ?? '—' }}', '{{ addslashes($pt->book->title ?? '—') }}', '{{ addslashes($pt->book->author ?? '—') }}', '{{ addslashes($pt->book->category->name ?? '—') }}', '{{ addslashes($pt->book->isbn ?? '—') }}', '{{ addslashes($pt->book->accession_no ?? '—') }}', '{{ $pt->status }}', '{{ $pt->status }}')">
                                <i class="fas fa-check"></i> Approve
                            </button>
                            <form method="POST" action="{{ route('circulation.reject', $pt) }}" style="display:inline" onsubmit="return confirm('Reject this request?');">
                                @csrf
                                <button type="submit" class="btn btn-danger btn-xs">
                                    <i class="fas fa-times"></i> Reject
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

{{-- QUICK CHECKOUT FORM --}}
<div class="card mb-6" id="checkoutSection">
    <div class="card-header"><span class="card-title">Workflow: Issue / Checkout a Book</span></div>
    <div class="card-body">
        <form method="POST" action="{{ route('circulation.checkout') }}">
            @csrf
            <div class="grid-2">
                <div class="form-group">
                    <label class="form-label">Member ID</label>
                    <input type="text" name="member_id" id="memberIdInput" class="form-control"
                           placeholder="MBR-2024-0142" value="{{ old('member_id') }}" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Member Name</label>
                    <input type="text" id="memberNameDisplay" class="form-control" placeholder="Auto-filled..." readonly
                           style="background:var(--cream)">
                    <input type="hidden" name="member_db_id" id="memberDbId">
                    <div id="memberStatusMessage" style="margin-top:8px;font-size:13px;color:var(--danger);"></div>
                </div>
            </div>
            <div class="grid-2">
                <div class="form-group">
                    <label class="form-label">Accession No. / ISBN</label>
                    <input type="text" name="book_identifier" class="form-control" placeholder="Scan book barcode..." required>
                </div>
                <div class="form-group">
                    <label class="form-label">Due Date</label>
                    <input type="date" name="due_date" id="dueDateInput" class="form-control"
                           value="{{ old('due_date', now()->addDays(14)->format('Y-m-d')) }}" required>
                </div>
            </div>
            <div class="form-group">
                <label class="checkbox">
                    <input type="checkbox" name="place_hold" id="placeHoldToggle" value="1">
                    <span>If book is unavailable, place a hold instead</span>
                </label>
            </div>
            <div class="flex gap-2 justify-end">
                <button type="reset" class="btn btn-outline">Cancel</button>
                <button type="submit" id="checkoutSubmitButton" class="btn btn-success">
                    <i class="fas fa-check"></i> Confirm Checkout
                </button>
            </div>
        </form>
    </div>
</div>

{{-- RECENT TRANSACTIONS TABLE --}}
<div class="card" id="returnSection">
<div class="card-header">
        <div class="flex flex-wrap gap-2 items-center justify-between w-full mb-3">
            <div>
                <span class="card-title">Recent Transactions</span>
                <a href="{{ route('circulation.index') }}?all=1" class="btn btn-outline btn-sm ml-2">All Transactions ↑</a>
            </div>
            <div class="flex gap-2 flex-wrap">
                <form method="GET" action="{{ route('circulation.index') }}" class="flex gap-1" id="circulationSearchForm">
                    <input type="text" name="search" id="circulationSearchInput" value="{{ request('search') }}" placeholder="Search member/book..." class="form-control form-control-sm min-w-[200px]">
                    <select name="status" id="circulationStatusSelect" class="form-control form-control-sm">
                        <option value="">All Status</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="overdue" {{ request('status') == 'overdue' ? 'selected' : '' }}>Overdue</option>
                        <option value="damaged" {{ request('status') == 'damaged' ? 'selected' : '' }}>Damaged</option>
                        <option value="returned" {{ request('status') == 'returned' ? 'selected' : '' }}>Returned</option>
                    </select>

                    <button type="submit" class="btn btn-outline btn-xs">
                        <i class="fas fa-search"></i>
                    </button>
                </form>
            </div>
        </div>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Trans. ID</th>
                    <th>Member</th>
                    <th>Book</th>
                    <th>Issued</th>
                    <th>Due</th>
<th>Renewals</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($transactions ?? [] as $txn)
                <tr>
                    <td><code style="font-size:11px;color:var(--text-mid)">TXN-{{ str_pad($txn->id,4,'0',STR_PAD_LEFT) }}</code></td>
                    <td>{{ $txn->member->name ?? '—' }}</td>
                    <td style="display:flex;align-items:center;gap:8px;">
  @if($txn->book?->cover_image)
    <img src="{{ asset('storage/' . $txn->book->cover_image) }}" alt="Book cover" style="width:28px;height:40px;flex-shrink:0;object-fit:cover;border-radius:4px;">
  @else
    <div style="width:28px;height:40px;background:rgba(0,0,0,0.08);border-radius:4px;display:flex;align-items:center;justify-content:center;font-size:14px;color:#999;">📖</div>
  @endif
  <div style="min-width:0;flex:1;">
    <div style="font-weight:500;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;" title="{{ $txn->book?->title ?? '—' }}">{{ $txn->book->title ?? '—' }}</div>
  </div>
                    </td>
                    <td>{{ $txn->issued_date?->format('M j') }}</td>
                    <td>{{ $txn->due_date?->format('M j') }}</td>
                    <td>{{ $txn->renewal_count }}/{{ $txn->max_renewals }}</td>
@php $s = $txn->status; @endphp
                        <td>
                            @if($s === 'damaged' || $s === 'damage_return')
                                <div style="display:flex;flex-direction:column;gap:8px;align-items:flex-start;">
                                    <span class="status-chip status-damaged" title="{{ $txn->notes ?? 'Damage return' }}" data-bs-toggle="tooltip">
                                        <i class="fas fa-book-damaged"></i>Damaged
                                    </span>
                                    @if($txn->fine > 0 && !$txn->fine_paid)
                                        <span style="font-size:11px;font-weight:700;color:#ff5722;background:rgba(255,87,34,0.12);padding:6px 10px;border-radius:6px;">
                                            <i class="fas fa-money-bill-wave" style="margin-right:4px;"></i>Fine: ₱{{ number_format($txn->fine, 2) }}
                                        </span>
                                    @endif
                                </div>
                            @else
                                <span class="badge {{ $s==='active'?'badge-success':($s==='overdue'?'badge-danger':($s==='returned'?'badge-info':($s==='rejected'?'badge-danger':'badge-warning'))) }}">
                                    {{ ucfirst($s) }}
                                </span>
                            @endif
                        </td>
                    <td>
                        @if(in_array($txn->status, ['active','overdue'], true))
                            @if($txn->canRenew())
                                <form method="POST" action="{{ route('circulation.renew', $txn) }}" style="display:inline;margin-right:4px">
                                    @csrf @method('PATCH')
                                    <button class="btn btn-outline btn-xs">Renew</button>
                                </form>
                            @endif
                            @php
                                $returnMeta = [
                                    'id' => $txn->id,
                                    'title' => $txn->book?->title ?? 'Unknown Book',
                                    'member' => $txn->member?->name ?? 'Unknown Member',
                                    'due_date' => optional($txn->due_date)->format('M j, Y'),
                                    'due_date_iso' => optional($txn->due_date)->toDateString(),
                                    'status' => $txn->status,
                                    'overdue_amount' => max(0, (!$txn->returned_date && $txn->due_date && $txn->due_date->lt(today())) ? ($txn->fine > 0 ? $txn->fine : $txn->computed_fine) : 0),
                                ];
                            @endphp
                            <button type="button" class="btn btn-outline btn-xs" onclick='openReturnBookModal({!! json_encode($returnMeta) !!})'>Return</button>
                        @elseif(in_array($txn->status, ['damaged','damage_return'], true) && $txn->fine > 0 && !$txn->fine_paid)
                            <form method="POST" action="{{ route('circulation.notify-fine', $txn) }}" style="display:inline;margin-right:4px">
                                @csrf
                                @method('PATCH')
                                <button class="btn btn-secondary btn-xs">Notify User</button>
                            </form>
                        @elseif($txn->is_pending_fine)
                            <form method="POST" action="{{ route('circulation.fine', $txn) }}" style="display:inline;margin-right:4px">
                                @csrf @method('PATCH')
                                <input type="hidden" name="payment_method" value="cash">
                                <button class="btn btn-warning btn-xs">Collect Fine</button>
                            </form>
                        @elseif($txn->status === 'rejected')
                            <span style="color:var(--text-light);font-size:12px">No actions for rejected request</span>
                        @elseif($txn->fine_paid || ($txn->status === 'returned' && ($txn->fine ?? 0) === 0))
                            <a href="{{ route('receipt.show', $txn) }}" class="btn btn-outline btn-xs" target="_blank">
                                <i class="fas fa-receipt"></i>
                                @if($txn->status === 'returned' && ($txn->fine ?? 0) === 0 && !$txn->fine_paid)
                                    Return Receipt
                                @else
                                    Receipt
                                @endif
                            </a>
                        @else
                            <span style="color:var(--text-light);font-size:12px">—</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" style="text-align:center;padding:24px;color:var(--text-light)">No transactions found</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Return Book Assessment Modal --}}
    <div class="modal-overlay" id="returnBookModal" style="display:none;">
        <div class="modal" style="max-width:520px;">
            <div class="modal-header">
                <div>
                    <div class="modal-title">Return Book Assessment</div>
                    <div id="returnBookSummary" style="color:var(--muted);font-size:13px;margin-top:4px;">Review the condition and confirm the return.</div>
                </div>
                <button class="modal-close" type="button" onclick="closeReturnBookModal()">&times;</button>
            </div>
            <div class="modal-body" style="padding:24px;">
                <div style="margin-bottom:18px;">
                    <div style="font-size:14px;color:var(--muted);margin-bottom:6px">Transaction</div>
                    <div style="font-weight:700;">TXN-<span id="returnTxnId">0000</span></div>
                    <div style="margin-top:12px;color:var(--muted);font-size:13px">Member: <span id="returnMemberName"></span></div>
                    <div style="margin-top:6px;color:var(--muted);font-size:13px">Book: <span id="returnBookTitle"></span></div>
                    <div style="margin-top:6px;color:var(--muted);font-size:13px">Due: <span id="returnDueDate"></span> · Status: <span id="returnStatus"></span></div>
                    <div id="returnOverdueDays" style="margin-top:4px;color:var(--muted);font-size:13px">Overdue days: 0</div>
                </div>

                <form id="returnBookForm" method="POST" action="">
                    @csrf
                    @method('PATCH')
                    <div class="form-group" style="margin-bottom:18px;">
                        <label class="form-label" for="returnConditionSelect">Book Condition</label>
                        <select id="returnConditionSelect" name="condition" class="form-control" onchange="updateReturnPenalty()">
                            <option value="good_condition">Good Condition</option>
                            <option value="minor_damage">Minor Damage</option>
                            <option value="major_damage">Major Damage</option>
                            <option value="lost">Lost</option>
                        </select>
                    </div>

                    <div style="display:grid;gap:12px;margin-bottom:18px;">
                        <div style="display:flex;justify-content:space-between;font-size:13px;color:var(--muted);">
                            <span>Overdue Penalty</span>
                            <span id="returnOverdueAmount" data-amount="0">₱0.00</span>
                        </div>
                        <div style="display:flex;justify-content:space-between;font-size:13px;color:var(--muted);">
                            <span>Damage Penalty</span>
                            <span id="returnDamageAmount">₱0.00</span>
                        </div>
                        <div style="display:flex;justify-content:space-between;font-weight:700;color:var(--danger);font-size:15px;">
                            <span>Total Amount Due</span>
                            <span id="returnTotalAmount">₱0.00</span>
                        </div>
                    </div>

                    <input type="hidden" name="damage_fee" id="damageFeeInput" value="0">
                    <input type="hidden" name="overdue_fee" id="overdueFeeInput" value="0">
                    <div style="display:flex;gap:12px;justify-content:flex-end;margin-top:18px;">
                        <button type="button" class="btn btn-outline" onclick="closeReturnBookModal()">Cancel</button>
                        <button type="submit" class="btn btn-primary" id="confirmReturnBtn">Confirm Return</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @if(isset($transactions) && $transactions->hasPages())
    <div style="padding:16px 20px">{{ $transactions->links('vendor.pagination.simple-custom') }}</div>
    @endif
</div>

{{-- APPROVE REQUEST MODAL --}}
{{-- Modal overlay + dialog HTML to be shown when a staff member clicks the Approve button in the Pending Requests table --}}
{{-- All fields are populated via JS openApproveModal() and submitted via a hidden form. --}}
{{-- NOTE: This must be placed AFTER the pending table and BEFORE @push('scripts') so it picks up layout.app styles. --}}
@include('circulation.approve-modal')

@push('scripts')
<script>
function updateCheckoutButtonLabel() {
    const holdCheckbox = document.getElementById('placeHoldToggle');
    const button = document.getElementById('checkoutSubmitButton');
    if (!button || !holdCheckbox) return;
    button.innerHTML = `<i class="fas fa-check"></i> ${holdCheckbox.checked ? 'Confirm Reservation' : 'Confirm Checkout'}`;
}

function displayMemberStatus(message, isError = true) {
    const status = document.getElementById('memberStatusMessage');
    if (!status) return;
    status.textContent = message || '';
    status.style.color = isError ? 'var(--danger)' : 'var(--success)';
}

// Auto-fill member name from ID
let memberTimer;
document.getElementById('memberIdInput')?.addEventListener('input', function() {
    clearTimeout(memberTimer);
    memberTimer = setTimeout(() => {
        const val = this.value.trim();
        if (!val) {
            document.getElementById('memberNameDisplay').value = '';
            document.getElementById('memberDbId').value = '';
            displayMemberStatus('');
            return;
        }

        fetch(`/api/members/lookup?id=${encodeURIComponent(val)}`)
            .then(r => r.json())
            .then(data => {
                if (data.name) {
                    document.getElementById('memberNameDisplay').value = data.name;
                    document.getElementById('memberDbId').value = data.id;
                    let statusParts = [];
                    if (data.has_overdue) {
                        statusParts.push('Overdue loans present');
                    }
                    if (data.outstanding_fine > 0) {
                        statusParts.push(`Outstanding fines ₱${Number(data.outstanding_fine).toFixed(2)}`);
                    }
                    statusParts.push(`Borrowing ${data.total_borrowed}/${data.borrowing_limit}`);
                    if (data.can_borrow === false) {
                        statusParts.unshift('Member cannot borrow at this time');
                    }
                    displayMemberStatus(statusParts.join(' · '), data.can_borrow === false || data.has_overdue || data.outstanding_fine > 0);
                    if (data.loan_period_days) {
                        const dueDateInput = document.getElementById('dueDateInput');
                        if (dueDateInput) {
                            const due = new Date();
                            due.setDate(due.getDate() + Number(data.loan_period_days));
                            dueDateInput.value = due.toISOString().slice(0, 10);
                        }
                    }
                } else {
                    document.getElementById('memberNameDisplay').value = 'Member not found';
                    document.getElementById('memberDbId').value = '';
                    displayMemberStatus('Member not found', true);
                }
            }).catch(() => {
                displayMemberStatus('Unable to verify member right now', true);
            });
    }, 500);
});

document.getElementById('placeHoldToggle')?.addEventListener('change', updateCheckoutButtonLabel);
updateCheckoutButtonLabel();

function scrollToSection(id) {
    const el = document.getElementById(id);
    if (!el) return;
    el.scrollIntoView({ behavior: 'smooth', block: 'start' });
    if (id === 'checkoutSection') {
        document.getElementById('memberIdInput')?.focus();
    }
}

const circulationSearchForm = document.getElementById('circulationSearchForm');
const circulationSearchInput = document.getElementById('circulationSearchInput');
const circulationStatusSelect = document.getElementById('circulationStatusSelect');
let circulationSearchTimer;

if (circulationSearchInput) {
    circulationSearchInput.addEventListener('input', () => {
        clearTimeout(circulationSearchTimer);
        circulationSearchTimer = setTimeout(() => circulationSearchForm?.submit(), 500);
    });
}

if (circulationStatusSelect) {
    circulationStatusSelect.addEventListener('change', () => circulationSearchForm?.submit());
}

const DAMAGE_RATES = {
    good_condition: 0,
    minor_damage: 50,
    major_damage: 150,
    lost: 500,
};

function calculateDaysOverdue(dueDateIso) {
    if (!dueDateIso) return 0;
    const dueDate = new Date(dueDateIso + 'T00:00:00');
    const today = new Date();
    dueDate.setHours(0, 0, 0, 0);
    today.setHours(0, 0, 0, 0);
    if (today <= dueDate) return 0;
    const millisecondsPerDay = 1000 * 60 * 60 * 24;
    return Math.floor((today - dueDate) / millisecondsPerDay);
}

function calculateOverdueAmount(dueDateIso) {
    const daysOverdue = calculateDaysOverdue(dueDateIso);
    return Math.max(0, daysOverdue * 25);
}

function openReturnBookModal(txn) {
    const modal = document.getElementById('returnBookModal');
    const form = document.getElementById('returnBookForm');
    if (!modal || !form) return;

    form.action = '{{ route('circulation.return', ['txn' => 0]) }}'.replace('/0', '/' + txn.id);
    document.getElementById('returnTxnId').textContent = String(txn.id).padStart(4, '0');
    document.getElementById('returnMemberName').textContent = txn.member;
    document.getElementById('returnBookTitle').textContent = txn.title;
    document.getElementById('returnDueDate').textContent = txn.due_date || 'N/A';
    document.getElementById('returnStatus').textContent = txn.status ? txn.status.replace(/_/g, ' ') : 'Unknown';
    const overdueDays = calculateDaysOverdue(txn.due_date_iso);
    const overdueAmount = calculateOverdueAmount(txn.due_date_iso);
    document.getElementById('returnOverdueDays').textContent = `Overdue days: ${overdueDays}`;
    document.getElementById('returnOverdueAmount').textContent = '₱' + overdueAmount.toFixed(2);
    document.getElementById('returnOverdueAmount').dataset.amount = overdueAmount.toFixed(2);
    document.getElementById('damageFeeInput').value = '0';
    document.getElementById('returnConditionSelect').value = 'good_condition';
    updateReturnPenalty();
    modal.style.display = 'flex';
}

function closeReturnBookModal() {
    const modal = document.getElementById('returnBookModal');
    if (!modal) return;
    modal.style.display = 'none';
}

function updateReturnPenalty() {
    const condition = document.getElementById('returnConditionSelect')?.value || 'good_condition';
    const damageFee = DAMAGE_RATES[condition] ?? 0;
    const overdueElement = document.getElementById('returnOverdueAmount');
    const overdueAmount = Number(overdueElement?.dataset?.amount || 0);
    const total = overdueAmount + damageFee;

    document.getElementById('returnDamageAmount').textContent = '₱' + damageFee.toFixed(2);
    document.getElementById('returnTotalAmount').textContent = '₱' + total.toFixed(2);
    document.getElementById('damageFeeInput').value = damageFee.toFixed(2);
    document.getElementById('overdueFeeInput').value = overdueAmount.toFixed(2);
}

document.getElementById('returnBookModal')?.addEventListener('click', function(e) {
    if (e.target === this) {
        closeReturnBookModal();
    }
});

// Submit the return form via AJAX to avoid full page redirects when possible
(function() {
    const form = document.getElementById('returnBookForm');
    if (!form) return;

    form.addEventListener('submit', async function(e) {
        e.preventDefault();

        const action = form.action;
        if (!action) {
            alert('Return action is not set. Try reopening the dialog.');
            return;
        }

        const submitBtn = document.getElementById('confirmReturnBtn');
        submitBtn.disabled = true;
        submitBtn.style.opacity = '0.6';

        const formData = new FormData(form);

        try {
            const res = await fetch(action, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: formData
            });

            if (res.ok) {
                // Success - close modal and refresh so the table reflects the updated status
                closeReturnBookModal();
                window.location.reload();
                return;
            }

            // Try to extract JSON error message
            let data = null;
            try { data = await res.json(); } catch (err) { /* ignore */ }
            const msg = data?.message || data?.error || 'Failed to process return. Please try again.';
            alert(msg);
        } catch (err) {
            console.error('Return submit error:', err);
            alert('Network error. Please try again.');
        } finally {
            submitBtn.disabled = false;
            submitBtn.style.opacity = '1';
        }
    });
})();

</script>
@endpush
@endsection

