@extends('layout.portal')
@section('title', 'Receipt')
@section('content')

@php
    $receiptLabel = 'Receipt';
    $receiptSubtitle = 'Official transaction receipt.';
    if ($txn->action === 'checkout') {
        $receiptLabel = 'Checkout Receipt';
        $receiptSubtitle = 'Official checkout receipt.';
    } elseif ($txn->status === 'returned' || $txn->action === 'return' || $txn->action === 'damage_return') {
        $receiptLabel = 'Return Receipt';
        $receiptSubtitle = 'Official receipt for the recorded return.';
    } elseif ($txn->fine > 0) {
        $receiptLabel = 'Payment Receipt';
        $receiptSubtitle = 'Official receipt for fine payment.';
    }
@endphp

<div class="section-header" style="margin-bottom:28px">
    <div>
        <h2>{{ $receiptLabel }}</h2>
        <p style="color:var(--muted);margin-top:8px">{{ $receiptSubtitle }}</p>
    </div>
    <button onclick="window.print()" class="btn btn-primary">
        <i class="fas fa-print"></i> Print
    </button>
</div>

<div class="card" style="max-width:600px;margin:0 auto;border:2px solid var(--accent);padding:32px" id="receiptCard">
    <div style="text-align:center;margin-bottom:24px">
        <div style="font-size:28px;font-weight:700;color:var(--accent)">Libraria</div>
        <div style="font-size:13px;color:var(--muted)">Library Management System</div>
    </div>

    <div style="text-align:center;margin-bottom:24px">
        <div style="font-size:22px;font-weight:700">OFFICIAL RECEIPT</div>
        <div style="font-size:14px;color:var(--muted);margin-top:4px">{{ $txn->receipt_no }}</div>
    </div>

    <div style="border-top:1px solid rgba(255,255,255,0.1);border-bottom:1px solid rgba(255,255,255,0.1);padding:20px 0;margin-bottom:20px">
        <div style="display:flex;justify-content:space-between;margin-bottom:10px">
            <span style="color:var(--muted)">Date & Time:</span>
            <span style="font-weight:600">{{ ($txn->paid_at ?? $txn->issued_date)?->format('F j, Y g:i A') }}</span>
        </div>
        <div style="display:flex;justify-content:space-between;margin-bottom:10px">
            <span style="color:var(--muted)">Member:</span>
            <span style="font-weight:600">{{ $txn->member?->name }}</span>
        </div>
        <div style="display:flex;justify-content:space-between;margin-bottom:10px">
            <span style="color:var(--muted)">Member ID:</span>
            <span style="font-weight:600">{{ $txn->member?->member_id }}</span>
        </div>
        <div style="display:flex;justify-content:space-between;margin-bottom:10px">
            <span style="color:var(--muted)">Book:</span>
            <span style="font-weight:600">{{ $txn->book?->title }}</span>
        </div>
        <div style="display:flex;justify-content:space-between;margin-bottom:10px">
            <span style="color:var(--muted)">ISBN:</span>
            <span style="font-weight:600">{{ $txn->book?->isbn ?? '—' }}</span>
        </div>
        <div style="display:flex;justify-content:space-between;margin-bottom:10px">
            <span style="color:var(--muted)">Due Date:</span>
            <span style="font-weight:600">{{ $txn->due_date?->format('M j, Y') }}</span>
        </div>
        <div style="display:flex;justify-content:space-between;margin-bottom:10px">
            <span style="color:var(--muted)">Returned:</span>
            <span style="font-weight:600">{{ $txn->returned_date?->format('M j, Y') }}</span>
        </div>
        <div style="display:flex;justify-content:space-between;margin-bottom:10px">
            <span style="color:var(--muted)">Days Overdue:</span>
            <span style="font-weight:600">{{ intval($txn->days_overdue ?? 0) }} days</span>
        </div>
        @if($txn->action === 'damage_return')
            <div style="display:flex;justify-content:space-between;margin-bottom:10px">
                <span style="color:var(--muted)">Overdue Fee:</span>
                <span style="font-weight:600">₱{{ number_format($txn->overdue_fine_amount, 2) }}</span>
            </div>
            <div style="display:flex;justify-content:space-between;margin-bottom:10px">
                <span style="color:var(--muted)">Damage Fee:</span>
                <span style="font-weight:600">₱{{ number_format($txn->damage_fee_amount, 2) }}</span>
            </div>
        @endif
    </div>

    <div style="display:flex;justify-content:space-between;align-items:center;padding:16px 0;border-bottom:2px dashed rgba(255,255,255,0.15);margin-bottom:20px">
        <span style="font-size:16px;font-weight:700">{{ $txn->fine > 0 ? 'TOTAL PAID' : 'NO PAYMENT DUE' }}</span>
        <span style="font-size:24px;font-weight:700;color:var(--accent)">₱{{ number_format($txn->fine, 2) }}</span>
    </div>

    <div style="display:flex;justify-content:space-between;margin-bottom:10px">
        <span style="color:var(--muted)">Payment Method:</span>
        <span style="font-weight:600">{{ $txn->fine > 0 ? ucfirst($txn->payment_method ?? 'N/A') : ($txn->action === 'checkout' ? 'Checkout' : 'N/A') }}</span>
    </div>
    <div style="display:flex;justify-content:space-between;margin-bottom:10px">
        <span style="color:var(--muted)">Collected By:</span>
        <span style="font-weight:600">{{ $txn->collectedBy?->name ?? $issuedBy?->name ?? 'System' }}</span>
    </div>
    <div style="display:flex;justify-content:space-between">
        <span style="color:var(--muted)">Transaction ID:</span>
        <span style="font-weight:600;font-size:12px">TXN-{{ str_pad($txn->id,4,'0',STR_PAD_LEFT) }}</span>
    </div>

    <div style="text-align:center;margin-top:32px;padding-top:20px;border-top:1px solid rgba(255,255,255,0.08)">
        <div style="font-size:12px;color:var(--muted)">Thank you for using Libraria!</div>
        <div style="font-size:11px;color:var(--muted);margin-top:4px">This is a computer-generated receipt.</div>
    </div>
</div>

@push('styles')
<style>
@media print {
    body { background: #fff !important; color: #000 !important; }
    .portal-header, .toast { display: none !important; }
    #receiptCard { border: 2px solid #333 !important; color: #000 !important; background: #fff !important; }
}
</style>
@endpush
@endsection

