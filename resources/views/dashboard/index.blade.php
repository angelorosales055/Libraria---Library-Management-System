@extends('layout.app')
@section('title', 'Dashboard')
@section('content')

<div class="page-header-row mb-6">
    <div>
        <h1 class="page-title">Library Dashboard</h1>
    </div>
    <a href="{{ route('circulation.index') }}" class="btn btn-gold">
        <i class="fas fa-plus"></i> New Transaction
    </a>
</div>

{{-- STAT CARDS --}}
<div class="stat-cards">
    <a href="{{ route('books.index') }}" class="stat-card blue stat-card-link">
        <div class="stat-label">Total Books</div>
        <div class="stat-value">{{ $totalBooks ?? 0 }}</div>
        <div class="stat-sub">+{{ $newBooksMonth ?? 0 }} this month</div>
    </a>
    <a href="{{ route('members.index') }}" class="stat-card green stat-card-link">
        <div class="stat-label">Active Members</div>
        <div class="stat-value">{{ $activeMembers ?? 0 }}</div>
        <div class="stat-sub">+{{ $newMembersWeek ?? 0 }} new registrations</div>
    </a>
    <a href="{{ route('circulation.index', ['status' => 'overdue']) }}" class="stat-card red stat-card-link">
        <div class="stat-label">Overdue</div>
        <div class="stat-value" style="color:var(--danger)">{{ $overdueCount ?? 0 }}</div>
        <div class="stat-sub">{{ $overdueToday ?? 0 }} overdue yesterday</div>
    </a>
    <a href="{{ route('circulation.index', ['status' => 'active']) }}" class="stat-card gold stat-card-link">
        <div class="stat-label">Checked Out</div>
        <div class="stat-value">{{ $checkedOut ?? 0 }}</div>
        <div class="stat-sub">{{ $utilizationRate ?? 0 }}% utilization rate</div>
    </a>
</div>

{{-- CIRCULATION TREND CHART --}}
<div class="card mb-6">
    <div class="card-header">
        <span class="card-title">Circulation Trend (14 Days)</span>
    </div>
    <div class="card-body" style="padding:20px;">
        <canvas id="circulationChart" style="max-height:300px;"></canvas>
    </div>
</div>

{{-- RECENT TRANSACTIONS --}}
<div class="card mb-6">
    <div class="card-header">
        <span class="card-title">Recent Transactions</span>
        <a href="{{ route('circulation.index') }}" class="btn btn-outline btn-sm">View All</a>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Member</th>
                    <th>Book</th>
                    <th>Action</th>
                    <th>Date</th>
                    <th>Due</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentTransactions ?? [] as $txn)

                <tr>
                    <td>{{ $txn->member->name ?? '—' }}</td>
                    <td style="display:flex;align-items:center;gap:8px;">
  @if($txn->book?->cover_image)
    <img src="{{ asset('storage/' . $txn->book->cover_image) }}" alt="Book cover" style="width:28px;height:40px;flex-shrink:0;object-fit:cover;border-radius:4px;">
  @else
    <div style="width:28px;height:40px;background:rgba(0,0,0,0.08);border-radius:4px;display:flex;align-items:center;justify-content:center;font-size:14px;color:#999;">📖</div>
  @endif
  <div style="min-width:0;flex:1;">
    <div style="font-weight:500;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;" title="{{ $txn->book?->title ?? '—' }}">{{ $txn->book->title ?? '—' }}</div>
  </div>
                    </td>
                    <td>{{ ucfirst($txn->action) }}</td>
                    <td>{{ $txn->created_at?->format('M j') }}</td>
                    <td>{{ $txn->due_date?->format('M j') ?? '—' }}</td>
                    <td>
                        @php $s = $txn->status; @endphp
                        <span class="badge {{ $s==='active'?'badge-success':($s==='overdue'?'badge-danger':($s==='returned'?'badge-info':'badge-warning')) }}">
                            {{ ucfirst($s) }}
                        </span>
                    </td>
                </tr>

                @empty
                <tr><td colspan="6" style="text-align:center;color:var(--text-light);padding:24px">No recent transactions</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>


@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const circulationCtx = document.getElementById('circulationChart');
        if (circulationCtx) {
            const circulationData = @json($circulationData ?? []);
            const labels = circulationData.map(d => d.date);
            const data = circulationData.map(d => d.count);

            new Chart(circulationCtx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Daily Transactions',
                        data: data,
                        borderColor: '#c9a84c',
                        backgroundColor: 'rgba(201, 168, 76, 0.1)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.4,
                        pointRadius: 4,
                        pointBackgroundColor: '#c9a84c',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                        pointHoverRadius: 6
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top',
                            labels: {
                                usePointStyle: true,
                                padding: 15,
                                color: 'var(--text-mid)',
                                font: { size: 12, family: 'DM Sans' }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                color: 'var(--text-light)',
                                stepSize: 1,
                                font: { size: 11 }
                            },
                            grid: {
                                color: 'rgba(0,0,0,0.05)',
                                drawBorder: false
                            }
                        },
                        x: {
                            ticks: {
                                color: 'var(--text-light)',
                                font: { size: 11 }
                            },
                            grid: {
                                display: false,
                                drawBorder: false
                            }
                        }
                    }
                }
            });
        }
    });
</script>
@endpush
