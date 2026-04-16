<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Libraria – Library Report</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; color: #1a2520; padding: 40px; }
        .header { text-align: center; margin-bottom: 32px; border-bottom: 2px solid #1a3a3a; padding-bottom: 20px; }
        .logo { font-size: 28px; font-weight: 700; color: #1a3a3a; letter-spacing: 2px; }
        .subtitle { font-size: 13px; color: #8a9590; margin-top: 4px; }
        .report-date { font-size: 12px; color: #8a9590; margin-top: 8px; }
        .stats { display: grid; grid-template-columns: repeat(5, 1fr); gap: 16px; margin: 24px 0; }
        .stat { background: #f5f0e8; padding: 16px; border-radius: 8px; text-align: center; }
        .stat-val { font-size: 24px; font-weight: 700; color: #1a3a3a; }
        .stat-lbl { font-size: 11px; color: #8a9590; text-transform: uppercase; margin-top: 4px; }
        .footer { text-align: center; font-size: 11px; color: #8a9590; margin-top: 40px; padding-top: 16px; border-top: 1px solid #ede8de; }
        @media print { button { display:none; } }
    </style>
</head>
<body>
<div class="header">
    <div class="logo">LIBRARIA</div>
    <div class="subtitle">Library Management System – Summary Report</div>
    <div class="report-date">Generated: {{ now()->format('F j, Y \a\t g:i A') }}</div>
</div>

<div class="stats">
    <div class="stat"><div class="stat-val">{{ $totalBooks }}</div><div class="stat-lbl">Total Books</div></div>
    <div class="stat"><div class="stat-val">{{ $totalMembers }}</div><div class="stat-lbl">Members</div></div>
    <div class="stat"><div class="stat-val">{{ $totalTxns }}</div><div class="stat-lbl">All Transactions</div></div>
    <div class="stat"><div class="stat-val" style="color:#c0392b">{{ $overdueCount }}</div><div class="stat-lbl">Overdue</div></div>
    <div class="stat"><div class="stat-val" style="color:#c9a84c">₱{{ number_format($finesOwed,2) }}</div><div class="stat-lbl">Fines Owed</div></div>
</div>

<div style="text-align:center;margin:20px 0">
    <button onclick="window.print()" style="padding:10px 24px;background:#1a3a3a;color:#fff;border:none;border-radius:6px;cursor:pointer;font-size:14px">
        🖨 Print Report
    </button>
    <a href="{{ route('reports.index') }}" style="margin-left:12px;font-size:13px;color:#3d7a6e">← Back to Reports</a>
</div>

<div class="footer">
    Libraria Library Management System · Confidential Document · {{ now()->format('Y') }}
</div>
</body>
</html>
