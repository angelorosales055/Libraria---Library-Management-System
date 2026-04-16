@extends('layout.app')
@section('title', 'Reports & Analytics')
@section('content')

<div class="page-header-row mb-6">
    <div>
        <h1 class="page-title">Reports &amp; Analytics</h1>
        <p class="page-subtitle">Generate, view, and export library reports</p>
    </div>
    <a href="{{ route('reports.export') }}" class="btn btn-gold">
        <i class="fas fa-file-pdf"></i> Export PDF
    </a>
</div>

{{-- TOP STATS --}}
<div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:24px">
    <div class="card">
        <div class="card-body">
            <div style="display:flex;align-items:center;justify-content:space-between">
                <div>
                    <div class="stat-label">Monthly Checkouts</div>
                    <div class="stat-value">{{ $monthlyCheckouts ?? 0 }}</div>
                    <div class="stat-sub text-success">+{{ $checkoutChange ?? 0 }}% from last month</div>
                </div>
                <div style="width:44px;height:44px;background:var(--info-light);border-radius:10px;display:flex;align-items:center;justify-content:center">
                    <i class="fas fa-book-open" style="color:var(--info)"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-body">
            <div style="display:flex;align-items:center;justify-content:space-between">
                <div>
                    <div class="stat-label">Fines Collected</div>
                    <div class="stat-value" style="color:var(--gold)">₱{{ number_format($finesCollected ?? 0, 2) }}</div>
                    <div class="stat-sub">{{ now()->format('F Y') }}</div>
                </div>
                <div style="width:44px;height:44px;background:var(--warning-light);border-radius:10px;display:flex;align-items:center;justify-content:center">
                    <i class="fas fa-peso-sign" style="color:var(--warning)"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="grid-2" style="gap:20px;align-items:start">

    {{-- AVAILABLE REPORTS --}}
    <div class="card">
        <div class="card-header">
            <span class="card-title">Available Reports</span>
            <span class="badge badge-info">{{ now()->format('M Y') }}</span>
        </div>
        <div class="card-body" style="display:flex;flex-direction:column;gap:12px;padding-top:12px">
            @php
            $reports = [
                ['icon'=>'📋','title'=>'Circulation Report','desc'=>'All checkouts & returns · Monthly','route'=>'reports.circulation'],
                ['icon'=>'📚','title'=>'Book Inventory','desc'=>'Full catalog with availability','route'=>'reports.inventory'],
                ['icon'=>'⚠️','title'=>'Overdue Report','desc'=>'Members with outstanding loans','route'=>'reports.overdue'],
                ['icon'=>'💰','title'=>'Fines &amp; Collection','desc'=>'Fine ledger &amp; payment records','route'=>'reports.fines'],
                ['icon'=>'👥','title'=>'Member Activity','desc'=>'Borrowing history per member','route'=>'reports.members'],
            ];
            @endphp
            @foreach($reports as $rpt)
            <div style="display:flex;align-items:center;gap:12px;padding:12px 14px;border:1px solid rgba(0,0,0,0.07);border-radius:var(--radius)">
                <span style="font-size:22px;flex-shrink:0">{{ $rpt['icon'] }}</span>
                <div style="flex:1">
                    <div style="font-size:13px;font-weight:600">{{ $rpt['title'] }}</div>
                    <div style="font-size:11px;color:var(--text-light)">{!! $rpt['desc'] !!}</div>
                </div>
                <a href="{{ route($rpt['route']) }}" class="btn btn-outline btn-sm">Generate</a>
            </div>
            @endforeach
        </div>
    </div>

    {{-- MONTHLY SUMMARY --}}
    <div class="card">
        <div class="card-header"><span class="card-title">Monthly Summary</span></div>
        <div class="card-body">
            {{-- Simple bar chart via CSS --}}
            <div style="margin-bottom:16px">
                <div style="font-size:12px;color:var(--text-light);margin-bottom:8px">Checkouts per month ({{ now()->year }})</div>
                <div style="display:flex;align-items:flex-end;gap:6px;height:80px">
                    @php $months = ['Jan','Feb','Mar','Apr','May','Jun']; @endphp
                    @foreach($months as $i => $m)
                    @php $val = $monthlyData[$i] ?? rand(10,50); $pct = min(100, ($val/60)*100); @endphp
                    <div style="flex:1;display:flex;flex-direction:column;align-items:center;gap:4px">
                        <div style="width:100%;background:var(--teal-accent);border-radius:4px 4px 0 0;height:{{ $pct }}%;min-height:4px"></div>
                        <div style="font-size:10px;color:var(--text-light)">{{ $m }}</div>
                    </div>
                    @endforeach
                </div>
            </div>
            <div style="display:flex;flex-direction:column;gap:8px;padding-top:8px;border-top:1px solid rgba(0,0,0,0.06)">
                <div style="display:flex;justify-content:space-between;font-size:13px">
                    <span style="color:var(--text-mid)">Most borrowed</span>
                    <span style="font-weight:600">{{ $mostBorrowed ?? 'Noli Me Tangere' }}</span>
                </div>
                <div style="display:flex;justify-content:space-between;font-size:13px">
                    <span style="color:var(--text-mid)">Most active member</span>
                    <span style="font-weight:600">{{ $mostActiveMember ?? 'Maria Santos' }}</span>
                </div>
                <div style="display:flex;justify-content:space-between;font-size:13px">
                    <span style="color:var(--text-mid)">New acquisitions</span>
                    <span style="font-weight:600">{{ $newAcquisitions ?? 0 }} books</span>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
