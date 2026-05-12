<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Receipt {{ $txn->receipt_no }}</title>
    <style>
        body { font-family: 'DejaVu Sans', 'Segoe UI', sans-serif; color: #1f3636; margin: 0; padding: 40px; background: #fff; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #2f7a6b; padding-bottom: 20px; }
        .header h1 { color: #2f7a6b; margin: 0; font-size: 24px; }
        .header p { color: #5c7a70; margin: 6px 0 0; font-size: 13px; }
        .receipt-title { text-align: center; margin: 20px 0; font-size: 18px; font-weight: 700; }
        .receipt-no { text-align: center; color: #5c7a70; font-size: 13px; margin-bottom: 24px; }
        .info-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .info-table td { padding: 10px 0; border-bottom: 1px solid #e5f3ee; font-size: 13px; }
        .info-table td:first-child { color: #5c7a70; width: 40%; }
        .info-table td:last-child { font-weight: 600; }
        .total-box { border: 2px dashed #2f7a6b; padding: 18px; margin: 24px 0; text-align: center; border-radius: 10px; background: #f4fbfa; }
        .total-box .label { font-size: 13px; color: #5c7a70; margin-bottom: 6px; }
        .total-box .amount { font-size: 28px; font-weight: 700; color: #2f7a6b; }
        .status-paid { display: inline-block; padding: 6px 14px; background: #2f7a6b; color: #fff; border-radius: 999px; font-size: 12px; font-weight: 700; }
        .footer { text-align: center; margin-top: 40px; padding-top: 20px; border-top: 1px solid #e5f3ee; font-size: 11px; color: #5c7a70; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Libraria</h1>
        <p>Library Management System</p>
    </div>

    <div class="receipt-title">OFFICIAL RECEIPT</div>
    <div class="receipt-no">{{ $txn->receipt_no }}</div>

    @php $daysOverdue = intval($txn->days_overdue ?? 0); @endphp
    <table class="info-table">
        <tr>
            <td>Date &amp; Time</td>
            <td>{{ $txn->paid_at?->format('F j, Y g:i A') }}</td>
        </tr>
        <tr>
            <td>Member</td>
            <td>{{ $txn->member?->name }}</td>
        </tr>
        <tr>
            <td>Member ID</td>
            <td>{{ $txn->member?->member_id }}</td>
        </tr>
        <tr>
            <td>Book</td>
            <td>{{ $txn->book?->title }}</td>
        </tr>
        <tr>
            <td>ISBN</td>
            <td>{{ $txn->book?->isbn ?? '—' }}</td>
        </tr>
        <tr>
            <td>Due Date</td>
            <td>{{ $txn->due_date?->format('M j, Y') }}</td>
        </tr>
        <tr>
            <td>Returned</td>
            <td>{{ $txn->returned_date?->format('M j, Y') }}</td>
        </tr>
        <tr>
            <td>Days Overdue</td>
            <td>{{ $daysOverdue }} days</td>
        </tr>
        @if($txn->action === 'damage_return')
            <tr>
                <td>Overdue Fee</td>
                <td>&#x20B1;{{ number_format($txn->overdue_fine_amount, 2) }}</td>
            </tr>
            <tr>
                <td>Damage Fee</td>
                <td>&#x20B1;{{ number_format($txn->damage_fee_amount, 2) }}</td>
            </tr>
        @endif
    </table>

    <div class="total-box">
        <div class="label">TOTAL PAID</div>
        <div class="amount">&#x20B1;{{ number_format($txn->fine, 2) }}</div>
    </div>

    <table class="info-table">
        <tr>
            <td>Payment Method</td>
            <td>{{ ucfirst($txn->payment_method) }}</td>
        </tr>
        <tr>
            <td>Status</td>
            <td><span class="status-paid">PAID</span></td>
        </tr>
        <tr>
            <td>Collected By</td>
            <td>{{ $txn->collectedBy?->name ?? 'System' }}</td>
        </tr>
        <tr>
            <td>Transaction ID</td>
            <td>TXN-{{ str_pad($txn->id, 4, '0', STR_PAD_LEFT) }}</td>
        </tr>
    </table>

    <div class="footer">
        <p>Thank you for using Libraria!</p>
        <p>This is a computer-generated receipt.</p>
    </div>
</body>
</html>
