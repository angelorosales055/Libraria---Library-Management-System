@extends('layout.portal')
@section('title', 'My Fines')
@section('content')

<div class="summary-grid">
    @php 
        $damageFines = $pendingFines->filter(fn ($txn) => $txn->action === 'damage_return' || $txn->status === 'damaged');
        $overdueFines = $pendingFines->filter(fn ($txn) => !($txn->action === 'damage_return' || $txn->status === 'damaged'));
        $damageTotal = max(0, $damageFines->sum(fn ($txn) => $txn->outstanding_fine));
        $overdueTotal = max(0, $overdueFines->sum(fn ($txn) => $txn->outstanding_fine));
        $summaryTotal = max(0, $damageTotal + $overdueTotal);
        $paidDamageTotal = max(0, $paidFines->where('action', 'damage_return')->sum('fine'));
        $paidOverdueTotal = max(0, $paidFines->where('action', '!=', 'damage_return')->sum('fine'));
    @endphp
    <div class="summary-card">
        <span>Outstanding Fines</span>
        <h2 style="color:var(--danger)">₱{{ number_format(abs($summaryTotal), 2) }}</h2>
    </div>
    <div class="summary-card">
        <span>Pending Damage Fines ({{ $damageFines->count() }})</span>
        <h2 style="color:var(--danger)">₱{{ number_format($damageTotal, 2) }}</h2>
    </div>
    <div class="summary-card">
        <span>Pending Overdue Fines ({{ $overdueFines->count() }})</span>
        <h2 style="color:var(--danger)">₱{{ number_format($overdueTotal, 2) }}</h2>
    </div>
</div>
<div style="margin: 16px 0 0 0; color: var(--muted); font-size: 14px; line-height: 1.5;">
    Total outstanding fine is <strong>₱{{ number_format($summaryTotal, 2) }}</strong>, made up of <strong>₱{{ number_format($overdueTotal, 2) }}</strong> overdue fine and <strong>₱{{ number_format($damageTotal, 2) }}</strong> damage fine.
</div>

<div class="section">
    <div class="section-header">
        <div>
            <h2>Pending Fines</h2>
            <p style="color:var(--muted);margin-top:8px">Fines waiting for payment. Select a payment method and confirm to pay.</p>
        </div>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Book</th>
                    <th>Due Date</th>
                    <th>Returned</th>
                    <th>Fine</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($pendingFines as $txn)
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
                    <td>{{ $txn->due_date?->format('M j, Y') }}</td>
                    <td>{{ $txn->returned_date?->format('M j, Y') ?? '—' }}</td>
                    @php
                        $displayFine = abs($txn->outstanding_fine);
                        $isDamageFine = $txn->action === 'damage_return' || $txn->status === 'damaged';
                    @endphp
                    <td style="font-weight:700;color:var(--danger)">
                        ₱{{ number_format($displayFine,2) }}
                        @if($isDamageFine)
                            <div style="font-size:12px;color:var(--muted);margin-top:4px;">
                                Damage ₱{{ number_format($txn->damage_fee_amount, 2) }} + Overdue ₱{{ number_format($txn->overdue_fine_amount, 2) }}
                            </div>
                        @endif
                    </td>
                    <td>
                        @if($isDamageFine)
                            <div class="damage-alert-card">
                                <div class="damage-icon">
                                    <i class="fas fa-book-damaged"></i>
                                </div>
                                <div class="damage-details">
                                    <div class="damage-label">Damaged Book</div>
                                    <div class="damage-reason">Damage fee + Overdue charges</div>
                                </div>
                            </div>
                        @elseif($txn->status === 'overdue')
                            <span class="status-chip status-overdue">
                                <i class="fas fa-clock me-1"></i>Overdue
                            </span>
                        @else
                            <span class="status-chip status-active">
                                <i class="fas fa-exclamation-circle me-1"></i>Unpaid
                            </span>
                        @endif
                    </td>
                    <td>
                        <button type="button" class="btn-sm" style="width:auto;padding:10px 18px" onclick='openPayModal({{ $txn->id }}, {{ $displayFine }}, {!! json_encode($txn->book?->title ?? 'Unknown Book') !!}, {{ $txn->days_overdue }}, {!! json_encode($txn->status ?? '') !!}, {!! json_encode($txn->due_date?->format('M j, Y') ?? '—') !!}, {{ json_encode($txn->fine_paid) }}, {!! json_encode($txn->returned_date?->format('M j, Y') ?? null) !!})'>Pay Now ₱{{ number_format($displayFine, 2) }}</button>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" style="color:var(--muted);padding:24px;text-align:center;">No pending fines. Great job!</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="section">
    <div class="section-header">
        <div>
            <h2>Fine Payment History</h2>
            <p style="color:var(--muted);margin-top:8px">Previously paid fines with receipts.</p>
        </div>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Book</th>
                    <th>Fine</th>
                    <th>Paid At</th>
                    <th>Method</th>
                    <th>Status</th>
                    <th>Receipt</th>
                </tr>
            </thead>
            <tbody>
                @forelse($paidFines as $txn)
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
                    <td style="font-weight:700;color:var(--accent)">₱{{ number_format($txn->fine,2) }}</td>
                    <td>{{ $txn->paid_at?->format('M j, Y g:i A') }}</td>
                    <td>
                        <span style="display:inline-flex;align-items:center;gap:6px">
                            <i class="fas {{ $txn->payment_method === 'cash' ? 'fa-money-bill' : ($txn->payment_method === 'gcash' ? 'fa-mobile-alt' : 'fa-credit-card') }}"></i>
                            {{ ucfirst($txn->payment_method) }}
                        </span>
                    </td>
                    <td>
                        @if($txn->action === 'damage_return')
                            <span class="badge badge-orange" style="font-size: 0.8em;padding:6px 10px;">Damage Paid</span>
                        @else
                            <span class="badge badge-success" style="font-size: 0.8em;padding:6px 10px;">Paid</span>
                        @endif
                    </td>
                    <td>
                        <a href="{{ route('receipt.show', $txn) }}" class="btn-sm btn-outline" style="width:auto;padding:8px 14px;display:inline-flex">
                            <i class="fas fa-receipt" style="margin-right:6px"></i> View
                        </a>
                        <a href="{{ route('receipt.download', $txn) }}" class="btn-sm btn-outline" style="width:auto;padding:8px 14px;display:inline-flex;margin-left:6px">
                            <i class="fas fa-download" style="margin-right:6px"></i> PDF
                        </a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" style="color:var(--muted);padding:24px;text-align:center;">No paid fines yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Payment Confirmation Modal --}}
<div class="modal-overlay" id="payModal">
    <div class="modal" style="max-width:420px">
        <div class="modal-header">
            <div class="modal-title" id="modalTitle">Pay Fine</div>
            <button class="modal-close" onclick="closePayModal()">&times;</button>
        </div>
        <div class="modal-body" style="text-align:center">
            <div style="font-size:42px;margin-bottom:12px">💳</div>
            <div style="font-size:18px;font-weight:700;margin-bottom:4px" id="modalBookTitle">Book Title</div>
            <div style="color:var(--muted);font-size:13px;margin-bottom:18px" id="modalPaymentSubtitle">Fine amount to pay</div>
            <div style="font-size:16px;font-weight:600;margin-bottom:8px" id="modalBookDueStatus">Due: — · Status: —</div>
            <div style="font-size:14px;color:var(--muted);margin-bottom:6px" id="modalTransactionDetails">Txn ID: — · Fine paid: — · Returned: —</div>
            <div id="modalPaymentNotice" style="display:none;color:#ffb3b3;font-size:13px;margin-bottom:14px"></div>
            <div style="font-size:36px;font-weight:700;color:var(--danger);margin-bottom:12px" id="modalFineAmount">₱0.00</div>
            <div id="modalPaymentSummary" style="color:var(--muted);font-size:13px;margin-bottom:18px">Rate: ₱25 / day · Days overdue: 0</div>
            <form id="payForm" method="POST" action="" onsubmit="return validatePaymentForm(event)">
                @csrf
                <div class="form-group">
                    <label class="form-label">Contact Number</label>
                    <input type="tel" id="paymentContactNumber" name="contact_number" class="form-control" placeholder="Enter 11-digit mobile number" inputmode="numeric" pattern="[0-9]{11}" minlength="11" maxlength="11" title="Enter exactly 11 digits" autocomplete="tel">
                </div>
                <div class="form-group">
                    <label class="form-label">Amount to Pay (₱)</label>
                    <input type="text" id="paymentAmountDisplay" class="form-control" readonly>
                    <input type="hidden" id="paymentAmount" name="amount" value="0.00">
                    <div style="color:var(--muted);font-size:12px;margin-top:6px">This amount is fixed to your outstanding fine and cannot be edited.</div>
                </div>
                <div style="display:flex;gap:10px;margin-bottom:20px;justify-content:center">
                    <label class="pay-method-option">
                        <input type="radio" name="payment_method" value="cash" checked onchange="updatePayAction()">
                        <span><i class="fas fa-money-bill" style="display:block;font-size:20px;margin-bottom:6px"></i>Cash</span>
                    </label>
                    <label class="pay-method-option">
                        <input type="radio" name="payment_method" value="gcash" onchange="updatePayAction()">
                        <span><i class="fas fa-mobile-alt" style="display:block;font-size:20px;margin-bottom:6px"></i>GCash</span>
                    </label>
                    <label class="pay-method-option">
                        <input type="radio" name="payment_method" value="paymaya" onchange="updatePayAction()">
                        <span><i class="fas fa-credit-card" style="display:block;font-size:20px;margin-bottom:6px"></i>PayMaya</span>
                    </label>
                </div>

                <div class="modal-footer" style="padding:0">
                    <button type="button" class="btn btn-outline" onclick="closePayModal()" style="flex:1;padding:12px 16px;border-radius:14px">Cancel</button>
                    <button type="submit" class="btn btn-primary" style="flex:1;padding:12px 16px;border-radius:14px">Confirm Pay</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('styles')
<style>
.pay-method-option {
    cursor: pointer;
    flex: 1;
}
.pay-method-option input {
    display: none;
}
.form-control[readonly] {
    background: rgba(255,255,255,0.06);
    cursor: not-allowed;
    color: var(--text);
}
.pay-method-option span {
    display: block;
    padding: 14px 8px;
    border-radius: 14px;
    border: 2px solid rgba(255,255,255,0.12);
    background: rgba(255,255,255,0.06);
    font-size: 12px;
    font-weight: 600;
    transition: all .15s;
}
.pay-method-option input:checked + span {
    border-color: var(--accent);
    background: var(--accent-soft);
    color: var(--accent);
}
</style>
@endpush

@push('scripts')
<script>
let currentTxnId = null;
function openPayModal(txnId, fineAmount, bookTitle, daysOverdue, status, dueDate, finePaid, returnedDate) {
    currentTxnId = txnId;
    document.getElementById('modalTitle').textContent = `Pay ₱${Math.abs(Number(fineAmount) || 0).toFixed(2)}`;
    document.getElementById('modalBookTitle').textContent = bookTitle;
    const amountValue = Math.abs(Number(fineAmount)) || 0;
    document.getElementById('modalFineAmount').textContent = '₱' + amountValue.toFixed(2);
    document.getElementById('paymentAmountDisplay').value = amountValue.toFixed(2);
    document.getElementById('paymentAmount').value = amountValue.toFixed(2);
    document.getElementById('paymentContactNumber').value = '';
    document.getElementById('modalBookDueStatus').textContent = `Due: ${dueDate} · Status: ${status ? status.replace('_', ' ') : 'Unknown'}`;
    document.getElementById('modalTransactionDetails').textContent = `Txn ID: ${txnId} · Fine paid: ${finePaid ? 'Yes' : 'No'} · Returned: ${returnedDate ?? 'Not returned'}`;

    const notice = document.getElementById('modalPaymentNotice');
    const confirmButton = document.querySelector('#payForm button[type="submit"]');
    let noteText = '';
    let disablePayment = false;
    daysOverdue = Number.isFinite(Number(daysOverdue)) ? Math.floor(Number(daysOverdue)) : 0;

    if (finePaid) {
        noteText = 'This transaction is already paid and cannot be paid again.';
        disablePayment = true;
    } else if (returnedDate) {
        noteText = `This book was returned on ${returnedDate}. Please proceed to settle the outstanding fine.`;
        disablePayment = false;
    }

    if (noteText) {
        notice.textContent = noteText;
        notice.style.display = 'block';
    } else {
        notice.textContent = '';
        notice.style.display = 'none';
    }

    confirmButton.disabled = disablePayment;
    confirmButton.style.opacity = disablePayment ? '0.6' : '1';

    const daysText = daysOverdue === 1 ? 'day' : 'days';
    if (status === 'damage_return') {
        document.getElementById('modalPaymentSummary').textContent = `Damage penalty plus overdue penalty · ${Math.abs(daysOverdue)} ${daysText} overdue`;
        document.getElementById('modalPaymentSubtitle').textContent = 'Total assessed penalty';
    } else {
        document.getElementById('modalPaymentSummary').textContent = `Rate: ₱25 / day · ${daysOverdue} ${daysText} overdue`;
        document.getElementById('modalPaymentSubtitle').textContent = 'Calculated overdue amount';
    }

    updatePayAction();
    document.getElementById('payModal').classList.add('open');
}
function closePayModal() {
    document.getElementById('payModal').classList.remove('open');
}
function updatePayAction() {
    const method = document.querySelector('input[name="payment_method"]:checked').value;
    const form = document.getElementById('payForm');
    const contactField = document.getElementById('paymentContactNumber');
    const isDigital = method !== 'cash';

    contactField.required = isDigital;
    contactField.placeholder = isDigital
        ? 'Enter 11-digit mobile number'
        : 'Optional for cash payments';

    // Always POST to the same endpoint; controller handles routing
    form.action = '/portal/pay-fine/' + currentTxnId;
    form.method = 'POST';
}

function validatePaymentForm(event) {
    const method = document.querySelector('input[name="payment_method"]:checked').value;
    const contactNumber = document.getElementById('paymentContactNumber').value.trim();
    
    if (method !== 'cash' && !contactNumber) {
        event.preventDefault();
        alert('Contact number is required for digital payments (GCash/PayMaya)');
        document.getElementById('paymentContactNumber').focus();
        return false;
    }
    
    if (method !== 'cash' && contactNumber.length !== 11) {
        event.preventDefault();
        alert('Contact number must be exactly 11 digits');
        document.getElementById('paymentContactNumber').focus();
        return false;
    }
    
    return true;
}
document.getElementById('payModal').addEventListener('click', function(e) {
    if (e.target === this) closePayModal();
});
</script>
@endpush
@endsection
