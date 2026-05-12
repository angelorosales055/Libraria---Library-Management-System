<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Payments Report</title>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 12px; color: #111; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h2 { margin: 0; font-size: 18px; }
        .header p { margin: 4px 0; color: #555; }
        .summary { margin-bottom: 20px; font-size: 13px; }
        .summary .label { font-weight: 700; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #ccc; padding: 8px 10px; vertical-align: top; }
        th { background: #f5f5f5; text-align: left; }
        .text-right { text-align: right; }
        .small { font-size: 11px; color: #555; }
    </style>
</head>
<body>
    <div class="header">
        <h2>Library Payments Report</h2>
        <p>Generated on: {{ now()->format('M j, Y g:i A') }}</p>
        @if($request->filled('start_date') && $request->filled('end_date'))
            <p>Date Range: {{ $request->start_date }} to {{ $request->end_date }}</p>
        @endif
        @if($request->filled('method'))
            <p>Payment Method: {{ ucfirst($request->method) }}</p>
        @endif
    </div>

    <div class="summary">
        <div><span class="label">Total Collected:</span> &#x20B1;{{ number_format($totalCollected, 2) }}</div>
        <div class="small">Records exported: {{ $payments->count() }}</div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Receipt No</th>
                <th>Member</th>
                <th>Book Title</th>
                <th>Method</th>
                <th>Paid At</th>
                <th class="text-right">Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach($payments as $txn)
                <tr>
                    <td>{{ $txn->receipt_no }}</td>
                    <td>{{ $txn->member?->name ?? 'N/A' }}</td>
                    <td>{{ $txn->book?->title ?? 'N/A' }}</td>
                    <td>{{ ucfirst($txn->payment_method) }}</td>
                    <td>{{ $txn->paid_at?->format('M j, Y g:i A') }}</td>
                    <td class="text-right">&#x20B1;{{ number_format($txn->fine, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
