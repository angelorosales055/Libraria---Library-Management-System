@extends('layout.app')
@section('title', 'Reports & Analytics')
@section('content')

@php
    $reportOptions = [
        'index' => 'Summary',
        'circulation' => 'Circulation',
        'inventory' => 'Inventory',
        'overdue' => 'Overdue',
        'fines' => 'Fines & Collection',
        'members' => 'Member Activity',
        'payments' => 'Payments',
    ];
@endphp

<div class="page-header-row mb-6" style="align-items:flex-end;">
    <div>
        <h1 class="page-title">Reports &amp; Analytics</h1>
        <p class="page-subtitle">Generate, filter, and export library reports</p>
    </div>
    <form method="GET" action="{{ route('reports.index') }}" class="flex gap-3 flex-wrap items-end">
        <div>
            <label class="form-label">Month</label>
            <select name="month" class="form-control" style="min-width:140px;">
                @foreach(range(1,12) as $m)
                    <option value="{{ $m }}" {{ $selectedMonth == $m ? 'selected' : '' }}>{{ DateTime::createFromFormat('!m', $m)->format('F') }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="form-label">Year</label>
            <select name="year" class="form-control" style="min-width:120px;">
                @foreach(range(now()->subYears(2)->year, now()->addYear()->year) as $year)
                    <option value="{{ $year }}" {{ $selectedYear == $year ? 'selected' : '' }}>{{ $year }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="btn btn-outline btn-sm">Filter</button>
    </form>
    <button type="button" class="btn btn-gold" onclick="openModal('reportExportModal')">
        <i class="fas fa-file-pdf"></i> Export Reports
    </button>
</div>

{{-- TOP STATS --}}
<div class="stat-cards">
    <div class="stat-card blue">
        <div class="stat-label">Monthly Checkouts</div>
        <div class="stat-value">{{ $monthlyCheckouts ?? 0 }}</div>
        <div class="stat-sub text-success">+{{ $checkoutChange ?? 0 }}% from last month</div>
    </div>
    <div class="stat-card gold">
        <div class="stat-label">Fines Collected</div>
        <div class="stat-value" style="color:var(--gold)">₱{{ number_format($finesCollected ?? 0, 2) }}</div>
        <div class="stat-sub">{{ now()->format('F Y') }}</div>
    </div>
    <div class="stat-card green">
        <div class="stat-label">New Acquisitions</div>
        <div class="stat-value">{{ $newAcquisitions ?? 0 }}</div>
        <div class="stat-sub">Books added this month</div>
    </div>
    <div class="stat-card red">
        <div class="stat-label">Avg. Per Member</div>
        <div class="stat-value">{{ $avgBorrowPerMember ?? '—' }}</div>
        <div class="stat-sub">Monthly</div>
    </div>
</div>

<div class="grid-2" style="gap:20px;align-items:start">

    {{-- AVAILABLE REPORTS --}}
    <div class="card">
        <div class="card-header">
            <span class="card-title">Available Reports</span>
            <span class="badge badge-info">{{ now()->format('M Y') }}</span>
        </div>
        <div class="card-body" style="padding:18px;">
            @php
            $reports = [
                ['icon'=>'📋','title'=>'Circulation Report','desc'=>'All checkouts & returns','route'=>'reports.circulation'],
                ['icon'=>'📚','title'=>'Book Inventory','desc'=>'Full catalog with availability','route'=>'reports.inventory'],
                ['icon'=>'⚠️','title'=>'Overdue Report','desc'=>'Members with outstanding loans','route'=>'reports.overdue'],
                ['icon'=>'💰','title'=>'Fines & Collection','desc'=>'Fine ledger & payments','route'=>'reports.fines'],
                ['icon'=>'👥','title'=>'Member Activity','desc'=>'Borrowing history per member','route'=>'reports.members'],
                ['icon'=>'💳','title'=>'Payments Report','desc'=>'All collected payments','route'=>'reports.payments'],
            ];
            @endphp
            <div class="grid-2" style="gap:16px;">
                @foreach($reports as $rpt)
                <a href="{{ route($rpt['route']) }}" class="btn btn-outline" style="display:flex;flex-direction:column;gap:14px;padding:18px;border:1px solid rgba(148,163,184,0.16);border-radius:var(--radius);background:#ffffff;text-decoration:none;color:inherit;min-height:160px;box-shadow:0 12px 25px rgba(15,23,42,0.04);transition:transform .18s ease;">
                    <div style="display:flex;align-items:center;gap:14px;">
                        <span style="width:46px;height:46px;border-radius:14px;background:rgba(16,185,129,0.12);display:flex;align-items:center;justify-content:center;font-size:20px;">{{ $rpt['icon'] }}</span>
                        <div>
                            <div style="font-size:14px;font-weight:700;color:#111827;">{{ $rpt['title'] }}</div>
                            <div style="font-size:12px;color:#6b7280; margin-top:4px;">{{ $rpt['desc'] }}</div>
                        </div>
                    </div>
                    <div style="margin-top:auto; display:flex;justify-content:flex-end;">
                        <span class="badge badge-info" style="padding:6px 10px; border-radius:999px; font-size:11px; background:#e0f2fe; color:#0c4a6e;">Generate</span>
                    </div>
                </a>
                @endforeach
            </div>

        </div>
    </div>

    {{-- MONTHLY SUMMARY --}}
    <div class="card">
        <div class="card-header"><span class="card-title">Monthly Summary</span></div>
        <div class="card-body">
            {{-- Chart with constrained height --}}
            <div style="margin-bottom:16px">
                <div style="font-size:12px;color:var(--text-light);margin-bottom:8px">Checkouts per month ({{ now()->year }})</div>
                <div style="position:relative;height:200px;max-height:220px">
                    <canvas id="checkoutChart"></canvas>
                </div>
                <div style="position:relative;height:180px;max-height:220px;margin-top:20px">
                    <canvas id="monthlyTrendChart"></canvas>
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

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
@php
    $months = [];
    for ($i = 5; $i >= 0; $i--) {
        $months[] = now()->copy()->subMonths($i)->format('M');
    }
@endphp
const ctx = document.getElementById('checkoutChart').getContext('2d');
new Chart(ctx, {
    type: 'bar',
    data: {
        labels: @json($months),
        datasets: [{
            label: 'Checkouts',
            data: {{ json_encode($monthlyData ?? [0,0,0,0,0,0]) }},
            backgroundColor: 'rgba(61,122,110,0.7)',
            borderColor: 'rgba(61,122,110,1)',
            borderWidth: 1,
            borderRadius: 4,
            barThickness: 24,
            maxBarThickness: 32
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
            y: { beginAtZero: true, ticks: { stepSize: 10 } },
            x: { grid: { display: false } }
        }
    }
});

const trendCtx = document.getElementById('monthlyTrendChart').getContext('2d');
new Chart(trendCtx, {
    type: 'line',
    data: {
        labels: @json($dailyLabels ?? []),
        datasets: [{
            label: 'Daily checkouts',
            data: {{ json_encode($dailyData ?? []) }},
            backgroundColor: 'rgba(61,122,110,0.1)',
            borderColor: 'rgba(61,122,110,1)',
            borderWidth: 2,
            pointRadius: 3,
            fill: true,
            tension: 0.35,
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
            y: { beginAtZero: true, ticks: { stepSize: 5 } },
            x: { grid: { display: false } }
        }
    }
});

const toggleButton = document.getElementById('toggle-report-selection');
if (toggleButton) {
    toggleButton.addEventListener('click', () => {
        const inputs = document.querySelectorAll('input[name="reports[]"]');
        const allChecked = Array.from(inputs).every(input => input.checked);
        inputs.forEach(input => input.checked = !allChecked);
        toggleButton.textContent = allChecked ? 'Select All' : 'Deselect All';
    });
}
</script>
@endpush

<div class="modal-overlay" id="reportExportModal">
    <div class="modal">
        <div class="modal-header">
            <span class="modal-title">Export Reports</span>
            <button class="modal-close" onclick="closeModal('reportExportModal')">×</button>
        </div>
        <div class="modal-body">
            <form method="GET" action="{{ route('reports.export') }}">
                <div class="form-group">
                    <label class="form-label">Select Reports</label>
                    <div class="grid-2" style="gap:12px;">
                        @foreach($reportOptions as $value => $label)
                            <label class="form-group" style="display:flex; align-items:center; gap:10px; margin-bottom:8px;">
                                <input type="checkbox" name="reports[]" value="{{ $value }}" checked>
                                <span>{{ $label }}</span>
                            </label>
                        @endforeach
                    </div>
                    <button type="button" id="toggle-report-selection" class="btn btn-outline btn-sm">Deselect All</button>
                </div>
                <div class="grid-2" style="gap:16px;">
                    <div class="form-group">
                        <label class="form-label">Start Date</label>
                        <input type="date" name="start_date" class="form-control">
                    </div>
                    <div class="form-group">
                        <label class="form-label">End Date</label>
                        <input type="date" name="end_date" class="form-control">
                    </div>
                </div>
                <div class="modal-footer" style="border:none; margin-top:0; padding:0; justify-content:flex-end; gap:10px;">
                    <button type="button" class="btn btn-outline" onclick="closeModal('reportExportModal')">Cancel</button>
                    <button type="submit" class="btn btn-gold">Export PDF</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection
