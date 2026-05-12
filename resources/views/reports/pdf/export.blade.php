<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        body { font-family: 'DejaVu Sans', 'Helvetica Neue', Helvetica, Arial, sans-serif; color:#1f2b38; margin:28px; background:#fafbfc; }
        .header { margin-bottom:24px; }
        .header h1 { margin:0; font-size:28px; letter-spacing:-0.02em; color:#111827; }
        .header .subtitle { color:#6b7280; margin-top:8px; font-size:14px; }
        .meta { display:flex; flex-wrap:wrap; gap:12px; margin-top:20px; font-size:12px; }
        .meta div { flex:1 1 180px; background:#f8fafc; padding:14px 16px; border-radius:12px; border:1px solid #e2e8f0; box-shadow: inset 0 1px 2px rgba(15,23,42,0.04); }
        .meta strong { display:block; margin-bottom:6px; font-size:11px; color:#374151; text-transform:uppercase; letter-spacing:0.08em; }
        .meta span { color:#111827; font-size:14px; line-height:1.5; }
        .section-title { margin:32px 0 12px; font-size:15px; color:#111827; text-transform:uppercase; letter-spacing:0.12em; }
        .section-description { margin-top:-4px; font-size:13px; color:#6b7280; }
        table { width:100%; border-collapse:collapse; margin-bottom:28px; font-size:13px; background:#ffffff; }
        th, td { padding:12px 14px; border:1px solid #e5e7eb; text-align:left; }
        th { background:#f9fafb; color:#374151; font-weight:700; text-transform:uppercase; letter-spacing:0.05em; font-size:12px; }
        tbody tr:nth-child(even) { background:#f8fafc; }
        tbody tr:hover { background:#eef2ff; }
        .stripe-row { background:#f3f4f6; }
        .badge { display:inline-flex; align-items:center; padding:4px 10px; border-radius:999px; font-size:10px; font-weight:700; letter-spacing:0.04em; }
        .badge-success { background:#dcfce7; color:#166534; }
        .badge-danger { background:#fee2e2; color:#991b1b; }
        .badge-warning { background:#fef3c7; color:#92400e; }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $title }}</h1>
        <div class="subtitle">Exported from Libraria Reporting System</div>
        <div class="meta">
            <div><strong>Who</strong><span>{{ $meta['who'] }}</span></div>
            <div><strong>What</strong><span>{{ $meta['what'] }}</span></div>
            <div><strong>Where</strong><span>{{ $meta['where'] }}</span></div>
            <div><strong>When</strong><span>{{ $meta['when'] }}</span></div>
            <div><strong>Why</strong><span>{{ $meta['why'] }}</span></div>
        </div>
    </div>

    @foreach($sections as $sectionIndex => $section)
        <div class="section-title">{{ $section['title'] }}</div>

        @switch($section['type'])
            @case('circulation')
                <table>
                    <thead>
                        <tr>
                            <th>TXN ID</th>
                            <th>Member</th>
                            <th>Book</th>
                            <th>Action</th>
                            <th>Issued</th>
                            <th>Due</th>
                            <th>Returned</th>
                            <th>Fine</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($section['data'] as $txn)
                            <tr>
                                <td>TXN-{{ str_pad($txn->id, 4, '0', STR_PAD_LEFT) }}</td>
                                <td>{{ $txn->member?->name ?? '—' }}</td>
                                <td>{{ $txn->book?->title ?? '—' }}</td>
                                <td>{{ ucfirst($txn->action) }}</td>
                                <td>{{ $txn->issued_date?->format('M j, Y') }}</td>
                                <td>{{ $txn->due_date?->format('M j, Y') }}</td>
                                <td>{{ $txn->returned_date?->format('M j, Y') ?? '—' }}</td>
                                <td>{!! '&#x20B1;' . number_format($txn->fine > 0 ? $txn->fine : 0, 2) !!}</td>
                                <td>{{ ucfirst($txn->status) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                @break

            @case('inventory')
                <table>
                    <thead>
                        <tr>
                            <th>Accession No.</th>
                            <th>Title</th>
                            <th>Author</th>
                            <th>Category</th>
                            <th>ISBN</th>
                            <th>Shelf</th>
                            <th>Total</th>
                            <th>Available</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($section['data'] as $book)
                            <tr>
                                <td>{{ $book->accession_no ?? '—' }}</td>
                                <td>{{ $book->title ?? '—' }}</td>
                                <td>{{ $book->author ?? '—' }}</td>
                                <td>{{ $book->category?->name ?? '—' }}</td>
                                <td>{{ $book->isbn ?? '—' }}</td>
                                <td>{{ $book->shelf ?? '—' }}</td>
                                <td>{{ $book->copies }}</td>
                                <td>{{ $book->available_copies ?? $book->copies }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                @break

            @case('overdue')
                <table>
                    <thead>
                        <tr>
                            <th>TXN ID</th>
                            <th>Member</th>
                            <th>Book</th>
                            <th>Issued</th>
                            <th>Due Date</th>
                            <th>Days Overdue</th>
                            <th>Fine</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($section['data'] as $txn)
                            <tr>
                                <td>TXN-{{ str_pad($txn->id, 4, '0', STR_PAD_LEFT) }}</td>
                                <td>{{ $txn->member?->name ?? '—' }}</td>
                                <td>{{ $txn->book?->title ?? '—' }}</td>
                                <td>{{ $txn->issued_date?->format('M j, Y') }}</td>
                                <td>{{ $txn->due_date?->format('M j, Y') }}</td>
                                <td>{{ $txn->due_date ? intval(max(0, now()->diffInDays($txn->due_date, false) * -1)) : '—' }}</td>
                                <td>{!! '&#x20B1;' . number_format($txn->fine > 0 ? $txn->fine : 0, 2) !!}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                @break

            @case('fines')
                <table>
                    <thead>
                        <tr>
                            <th>TXN ID</th>
                            <th>Member</th>
                            <th>Book</th>
                            <th>Due Date</th>
                            <th>Returned</th>
                            <th>Fine</th>
                            <th>Paid</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($section['data'] as $txn)
                            <tr>
                                <td>TXN-{{ str_pad($txn->id, 4, '0', STR_PAD_LEFT) }}</td>
                                <td>{{ $txn->member?->name ?? '—' }}</td>
                                <td>{{ $txn->book?->title ?? '—' }}</td>
                                <td>{{ $txn->due_date?->format('M j, Y') ?? '—' }}</td>
                                <td>{{ $txn->returned_date?->format('M j, Y') ?? '—' }}</td>
                                <td>{!! '&#x20B1;' . number_format($txn->fine > 0 ? $txn->fine : 0, 2) !!}</td>
                                <td>{!! $txn->fine_paid ? '<span class="badge badge-success">Yes</span>' : '<span class="badge badge-danger">No</span>' !!}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                @break

            @case('members')
                <table>
                    <thead>
                        <tr>
                            <th>Member ID</th>
                            <th>Name</th>
                            <th>Type</th>
                            <th>Total Transactions</th>
                            <th>Active Loans</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($section['data'] as $member)
                            <tr>
                                <td>{{ $member->member_id ?? 'MBR-' . str_pad($member->id, 7, '0', STR_PAD_LEFT) }}</td>
                                <td>{{ $member->name }}</td>
                                <td>{{ ucfirst($member->type ?? 'student') }}</td>
                                <td>{{ $member->total_txns }}</td>
                                <td>{{ $member->active_txns }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                @break

            @case('index')
                <table>
                    <tbody>
                        <tr>
                            <th style="width:40%">Monthly Checkouts</th>
                            <td>{{ $section['data']['monthly_checkouts'] }}</td>
                        </tr>
                        <tr>
                            <th>Fines Collected</th>
                            <td>&#x20B1;{{ number_format($section['data']['fines_collected'], 2) }}</td>
                        </tr>
                        <tr>
                            <th>New Acquisitions</th>
                            <td>{{ $section['data']['new_acquisitions'] }}</td>
                        </tr>
                        <tr>
                            <th>Avg. Per Member</th>
                            <td>{{ $section['data']['avg_per_member'] }}</td>
                        </tr>
                    </tbody>
                </table>
                <div class="section-title">Checkout History</div>
                <table>
                    <thead>
                        <tr>
                            <th>Month</th>
                            <th>Checkouts</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($section['data']['checkout_history'] as $row)
                            <tr>
                                <td>{{ $row['month'] }}</td>
                                <td>{{ $row['count'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                @break

            @default
                <table>
                    <thead>
                        <tr>
                            <th>Receipt No</th>
                            <th>Member</th>
                            <th>Book</th>
                            <th>Amount</th>
                            <th>Method</th>
                            <th>Paid At</th>
                            <th>Collected By</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($section['data'] as $txn)
                            <tr>
                                <td>{{ $txn->receipt_no }}</td>
                                <td>{{ $txn->member?->name ?? '—' }}</td>
                                <td>{{ $txn->book?->title ?? '—' }}</td>
                                <td>{!! '&#x20B1;' . number_format($txn->fine > 0 ? $txn->fine : 0, 2) !!}</td>
                                <td>{{ ucfirst($txn->payment_method ?? '—') }}</td>
                                <td>{{ $txn->paid_at?->format('M j, Y g:i A') ?? '—' }}</td>
                                <td>{{ $txn->collectedBy?->name ?? '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
        @endswitch

        @if($sectionIndex !== count($sections) - 1)
            <div style="page-break-after: always;"></div>
        @endif
    @endforeach

    <div style="font-size:11px;color:#666;margin-top:12px">Generated on {{ now()->format('F j, Y g:i A') }}</div>
</body>
</html>
