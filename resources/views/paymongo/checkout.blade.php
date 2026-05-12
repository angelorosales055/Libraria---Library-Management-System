@extends('layout.portal')
@section('title', 'PayMongo Checkout')
@section('content')

<div class="section" style="max-width:480px;margin:0 auto;text-align:center">
    <div style="font-size:48px;margin-bottom:16px">💳</div>
    <h2 style="margin-bottom:8px">Secure Payment</h2>
    <p style="color:var(--muted);margin-bottom:32px">Complete your payment via {{ ucfirst($method) }}</p>

    <div style="background:var(--surface);border:1px solid rgba(255,255,255,0.08);border-radius:24px;padding:28px;margin-bottom:24px">
        <div style="display:flex;justify-content:space-between;margin-bottom:12px">
            <span style="color:var(--muted)">Book</span>
            <span style="font-weight:700">{{ $txn->book?->title ?? 'Unknown' }}</span>
        </div>
        <div style="display:flex;justify-content:space-between;margin-bottom:12px">
            <span style="color:var(--muted)">Reference</span>
            <span style="font-weight:600;font-size:12px">{{ $reference }}</span>
        </div>
        <div style="border-top:1px solid rgba(255,255,255,0.08);margin:16px 0"></div>
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px">
            <span style="color:var(--muted)">Amount to Pay</span>
            <span style="font-size:28px;font-weight:700;color:var(--accent)">₱{{ number_format($amount, 2) }}</span>
        </div>
        @if(!empty($contact))
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px">
            <span style="color:var(--muted)">Contact</span>
            <span style="font-weight:600">{{ $contact }}</span>
        </div>
        @endif
    </div>

    <div style="display:flex;gap:12px">
        <a href="{{ route('portal.fines') }}" class="btn btn-outline" style="flex:1;padding:14px;border-radius:16px;text-align:center">Cancel</a>
        <form method="POST" action="{{ route('paymongo.success', ['txn' => $txn]) }}" style="flex:1">
            @csrf
            <button type="submit" class="btn btn-primary" style="width:100%;padding:14px;border-radius:16px;font-weight:700;background:var(--accent);color:#06131f;border:none;cursor:pointer">
                Pay with {{ ucfirst($method) }}
            </button>
        </form>
    </div>

    <div style="margin-top:24px;font-size:12px;color:var(--muted)">
        <i class="fas fa-lock" style="margin-right:4px"></i> Secured by PayMongo. This is a simulated checkout.
    </div>
</div>

@endsection

