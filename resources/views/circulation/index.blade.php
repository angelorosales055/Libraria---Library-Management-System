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
        <div><div class="step-text">Scan Member ID</div><div class="step-sub">Barcode or mandatory</div></div>
    </div>
    <div class="workflow-step">
        <div class="step-num">2</div>
        <div><div class="step-text">Verify Member</div><div class="step-sub">Check standing & limits</div></div>
    </div>
    <div class="workflow-step">
        <div class="step-num">3</div>
        <div><div class="step-text">Scan Book</div><div class="step-sub">Accession/barcode scan</div></div>
    </div>
    <div class="workflow-step">
        <div class="step-num">4</div>
        <div><div class="step-text">Set Due Date</div><div class="step-sub">Default: 14 days</div></div>
    </div>
    <div class="workflow-step">
        <div class="step-num">5</div>
        <div><div class="step-text">Confirm</div><div class="step-sub">Print / save receipt</div></div>
    </div>
</div>

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
        <span class="card-title">Recent Transactions</span>
        <a href="{{ route('circulation.index') }}?all=1" class="btn btn-outline btn-sm">All Transactions ↑</a>
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
                    <th>Fine</th>
                    <th>Fine Status</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($transactions ?? [] as $txn)
                <tr>
                    <td><code style="font-size:11px;color:var(--text-mid)">TXN-{{ str_pad($txn->id,4,'0',STR_PAD_LEFT) }}</code></td>
                    <td>{{ $txn->member->name ?? '—' }}</td>
                    <td>{{ $txn->book->title ?? '—' }}</td>
                    <td>{{ $txn->issued_date?->format('M j') }}</td>
                    <td>{{ $txn->due_date?->format('M j') }}</td>
                    <td>{{ $txn->renewal_count }}/{{ $txn->max_renewals }}</td>
                    <td>
                        @if($txn->fine > 0)
                            <span class="text-danger font-bold">₱{{ number_format($txn->fine,2) }}</span>
                        @else —
                        @endif
                    </td>
                    <td>
                        @if($txn->is_pending_fine)
                            <span class="badge badge-warning">Pending</span>
                        @elseif($txn->fine > 0)
                            <span class="badge badge-success">Paid</span>
                        @else
                            <span class="badge badge-gray">None</span>
                        @endif
                    </td>
                    <td>
                        @php $s = $txn->status; @endphp
                        <span class="badge {{ $s==='active'?'badge-success':($s==='overdue'?'badge-danger':($s==='returned'?'badge-info':'badge-warning')) }}">
                            {{ ucfirst($s) }}
                        </span>
                    </td>
                    <td>
                        @if(!$txn->returned_date)
                            @if($txn->canRenew())
                                <form method="POST" action="{{ route('circulation.renew', $txn) }}" style="display:inline;margin-right:4px">
                                    @csrf @method('PATCH')
                                    <button class="btn btn-outline btn-xs">Renew</button>
                                </form>
                            @endif
                            <button type="button" class="btn btn-outline btn-xs" onclick="openReturnConfirm({{ $txn->id }}, '{{ addslashes($txn->book->title ?? 'Book') }}', {{ $txn->computed_fine }})">Return</button>
                        @elseif($txn->is_pending_fine)
                            <button class="btn btn-danger btn-xs" onclick="openFineModal({{ $txn->id }}, '{{ addslashes($txn->book->title ?? 'Book') }}', {{ $txn->fine }})">
                                Collect Fine
                            </button>
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
    @if(isset($transactions) && $transactions->hasPages())
    <div style="padding:16px 20px">{{ $transactions->links() }}</div>
    @endif
</div>

<div class="modal-overlay" id="returnConfirmModal" style="display:none;">
    <div class="modal" role="dialog" aria-modal="true" aria-labelledby="returnConfirmTitle">
        <div class="modal-header">
            <div>
                <div class="modal-title" id="returnConfirmTitle">Confirm Return</div>
                <div id="returnConfirmSubtitle" style="color:var(--muted);font-size:13px;margin-top:6px">Verify the final fine amount before completing return.</div>
            </div>
            <button type="button" class="modal-close" onclick="closeReturnConfirm()">&times;</button>
        </div>
        <div class="modal-body">
            <div style="margin-bottom:18px;">
                <div style="font-size:14px;font-weight:700;">Book: <span id="returnConfirmBook"></span></div>
                <div style="margin-top:10px;font-size:18px;font-weight:700;color:var(--danger);">Estimated Fine: ₱<span id="returnConfirmFine"></span></div>
            </div>
            <div style="font-size:13px;color:var(--muted);">If this book is overdue, collect the amount shown before marking it returned.</div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-outline" onclick="closeReturnConfirm()">Cancel</button>
            <button type="button" class="btn btn-success" onclick="submitReturnConfirm()">Confirm Return</button>
        </div>
        <form id="returnConfirmForm" method="POST" style="display:none;" action="">
            @csrf
            @method('PATCH')
        </form>
    </div>
</div>

<div class="modal-overlay" id="fineModal" style="display:none;">
    <div class="modal" role="dialog" aria-modal="true" aria-labelledby="fineModalTitle">
        <div class="modal-header">
            <div>
                <div class="modal-title" id="fineModalTitle">Collect Fine Payment</div>
                <div id="fineModalSubtitle" style="color:var(--muted);font-size:13px;margin-top:6px">Complete payment before returning the book to inventory.</div>
            </div>
            <button type="button" class="modal-close" onclick="closeFineModal()">&times;</button>
        </div>
        <div class="modal-body">
            <div style="margin-bottom:18px;">
                <div style="font-size:14px;font-weight:700;">Transaction ID: <span id="fineModalTxn"></span></div>
                <div style="margin-top:6px;color:var(--muted);">Book: <span id="fineModalBook"></span></div>
                <div style="margin-top:10px;font-size:18px;font-weight:700;color:var(--danger);">Amount: ₱<span id="fineModalAmount"></span></div>
            </div>
            <div class="form-group">
                <label class="form-label">Payment Method</label>
                <select id="finePaymentMethod" name="payment_method" class="form-control" required>
                    <option value="cash">Cash</option>
                    <option value="gcash">GCash</option>
                    <option value="paymaya">PayMaya</option>
                </select>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-outline" onclick="closeFineModal()">Cancel</button>
            <button type="button" class="btn btn-success" onclick="submitFinePayment()">Submit Payment</button>
        </div>
        <form id="finePaymentForm" method="POST" style="display:none;" action="">
            @csrf
            @method('PATCH')
            <input type="hidden" name="payment_method" id="finePaymentMethodInput">
        </form>
    </div>
</div>

@push('scripts')
<script>
function openFineModal(txnId, bookTitle, amount) {
    document.getElementById('fineModalTxn').textContent = 'TXN-' + String(txnId).padStart(4, '0');
    document.getElementById('fineModalBook').textContent = bookTitle;
    document.getElementById('fineModalAmount').textContent = Number(amount).toFixed(2);
    document.getElementById('finePaymentMethod').value = 'cash';
    const form = document.getElementById('finePaymentForm');
    form.action = '{{ route('circulation.fine', ['txn' => 0]) }}'.replace('/0', '/' + txnId);
    document.getElementById('fineModal').style.display = 'flex';
}

function closeFineModal() {
    document.getElementById('fineModal').style.display = 'none';
}

function submitFinePayment() {
    const paymentMethod = document.getElementById('finePaymentMethod').value;
    document.getElementById('finePaymentMethodInput').value = paymentMethod;
    document.getElementById('finePaymentForm').submit();
}

function openReturnConfirm(txnId, bookTitle, amount) {
    document.getElementById('returnConfirmBook').textContent = bookTitle;
    document.getElementById('returnConfirmFine').textContent = Number(amount).toFixed(2);
    const form = document.getElementById('returnConfirmForm');
    form.action = '{{ route('circulation.return', ['txn' => 0]) }}'.replace('/0', '/' + txnId);
    document.getElementById('returnConfirmModal').style.display = 'flex';
}

function closeReturnConfirm() {
    document.getElementById('returnConfirmModal').style.display = 'none';
}

function submitReturnConfirm() {
    document.getElementById('returnConfirmForm').submit();
}

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
</script>
@endpush
@endsection
