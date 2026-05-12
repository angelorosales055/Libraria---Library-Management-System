<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\User;
use App\Models\Transaction;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $selectedMonth = $request->get('month', Carbon::now()->month);
        $selectedYear = $request->get('year', Carbon::now()->year);
        $current = Carbon::create($selectedYear, $selectedMonth, 1);

        $monthlyCheckouts = Transaction::whereMonth('issued_date', $current->month)
            ->whereYear('issued_date', $current->year)->count();

        $previous = $current->copy()->subMonth();
        $lastMonthCheckouts = Transaction::whereMonth('issued_date', $previous->month)
            ->whereYear('issued_date', $previous->year)->count();

        $checkoutChange = $lastMonthCheckouts > 0
            ? round((($monthlyCheckouts - $lastMonthCheckouts) / $lastMonthCheckouts) * 100)
            : 0;

        $finesCollected = Transaction::whereMonth('updated_at', $current->month)
            ->whereYear('updated_at', $current->year)
            ->where('fine_paid', true)->sum('fine');

        $activeBorrowers = Transaction::whereMonth('issued_date', $current->month)
            ->whereYear('issued_date', $current->year)
            ->distinct('member_id')->count('member_id');

        $avgBorrowPerMember = $activeBorrowers > 0
            ? round($monthlyCheckouts / $activeBorrowers, 2)
            : 0;

        $monthlyData = [];
        $monthlyLabels = [];
        for ($i = 5; $i >= 0; $i--) {
            $d = $current->copy()->subMonths($i);
            $monthlyLabels[] = $d->format('M');
            $monthlyData[] = Transaction::whereMonth('issued_date', $d->month)
                ->whereYear('issued_date', $d->year)->count();
        }

        $daysInMonth = $current->daysInMonth;
        $dailyLabels = [];
        $dailyData = [];
        for ($day = 1; $day <= $daysInMonth; $day++) {
            $date = $current->copy()->day($day);
            $dailyLabels[] = $date->format('j');
            $dailyData[] = Transaction::whereDate('issued_date', $date->toDateString())->count();
        }

        $mostBorrowed = Transaction::selectRaw('book_id, count(*) as cnt')
            ->groupBy('book_id')->orderByDesc('cnt')->first()?->book?->title ?? '—';

        $mostActiveMember = Transaction::selectRaw('member_id, count(*) as cnt')
            ->groupBy('member_id')->orderByDesc('cnt')->first()?->member?->name ?? '—';

        $newAcquisitions = Book::whereMonth('created_at', $current->month)
            ->whereYear('created_at', $current->year)->count();

        return view('reports.index', compact(
            'monthlyCheckouts','checkoutChange','finesCollected',
            'monthlyData','monthlyLabels','dailyData','dailyLabels','mostBorrowed',
            'mostActiveMember','newAcquisitions','avgBorrowPerMember',
            'selectedMonth','selectedYear'
        ));
    }

    public function circulation()
    {
        $transactions = Transaction::with(['member','book'])->latest()->paginate(30);
        return view('reports.circulation', compact('transactions'));
    }

    public function inventory()
    {
        $books = Book::with('category')->orderBy('title')->get();
        return view('reports.inventory', compact('books'));
    }

    public function overdue(Request $request)
    {
        $transactions = Transaction::with(['member', 'book'])
            ->where('status', 'overdue')
            ->latest('due_date')
            ->paginate(20)
            ->withQueryString();

        return view('reports.overdue', compact('transactions'));
    }

    public function fines(Request $request)
    {
        $transactions = Transaction::with(['member', 'book'])
            ->where('fine', '>', 0)
            ->latest('paid_at')
            ->paginate(20)
            ->withQueryString();

        return view('reports.fines', compact('transactions'));
    }

    public function payments(Request $request)
    {
        $query = Transaction::with(['member', 'book', 'collectedBy'])
            ->where('fine_paid', true)
            ->whereNotNull('fine');

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('paid_at', [
                $request->start_date . ' 00:00:00',
                $request->end_date . ' 23:59:59',
            ]);
        }
        if ($request->filled('method')) {
            $query->where('payment_method', $request->method);
        }

        $totalsQuery = clone $query;
        $totalCollected = $totalsQuery->sum('fine');
        $cashTotal = (clone $totalsQuery)->where('payment_method', 'cash')->sum('fine');
        $digitalTotal = (clone $totalsQuery)->whereIn('payment_method', ['gcash', 'paymaya'])->sum('fine');

        $payments = $query->orderBy('paid_at', 'desc')->paginate(20)->withQueryString();

        return view('reports.payments', compact('payments', 'totalCollected', 'cashTotal', 'digitalTotal'));
    }

    public function export(Request $request)
    {
        $selectedReports = $request->input('reports', []);
        if (empty($selectedReports)) {
            $singleReport = $request->get('report', 'payments');
            $selectedReports = is_array($singleReport) ? $singleReport : [$singleReport];
        }

        $start = $request->start_date;
        $end = $request->end_date;

        $meta = [
            'who' => auth()->user()?->name ?? 'Admin',
            'where' => 'Libraria Reports',
            'when' => ($start && $end)
                ? Carbon::parse($start)->format('M j, Y') . ' — ' . Carbon::parse($end)->format('M j, Y')
                : 'All time',
            'why' => 'Exported for audit, review, or record keeping.',
        ];

        $sections = [];
        foreach ($selectedReports as $reportType) {
            $sections[] = $this->buildReportSection($reportType, $start, $end, $request);
        }

        $title = count($sections) > 1
            ? 'Combined Reports Export'
            : ($sections[0]['title'] ?? 'Report Export');

        $meta['what'] = count($sections) > 1
            ? implode(', ', array_column($sections, 'title'))
            : ($sections[0]['title'] ?? 'Report Export');

        $pdf = Pdf::loadView('reports.pdf.export', compact('sections', 'title', 'meta'))
            ->setPaper('a4', 'landscape');

        $filename = Str::of($title)->slug('_')->append('_' . now()->format('Y-m-d') . '.pdf')->toString();
        return $pdf->download($filename);
    }

    private function buildReportSection(string $reportType, ?string $start, ?string $end, Request $request): array
    {
        switch ($reportType) {
            case 'index':
                $data = [
                    'monthly_checkouts' => Transaction::whereMonth('issued_date', now()->month)
                        ->whereYear('issued_date', now()->year)->count(),
                    'checkout_change' => 0,
                    'fines_collected' => Transaction::whereMonth('updated_at', now()->month)
                        ->whereYear('updated_at', now()->year)
                        ->where('fine_paid', true)->sum('fine'),
                    'new_acquisitions' => Book::whereMonth('created_at', now()->month)
                        ->whereYear('created_at', now()->year)->count(),
                    'avg_per_member' => Transaction::whereMonth('issued_date', now()->month)
                        ->whereYear('issued_date', now()->year)
                        ->distinct('member_id')->count('member_id') > 0
                            ? round(Transaction::whereMonth('issued_date', now()->month)
                                ->whereYear('issued_date', now()->year)->count() /
                                Transaction::whereMonth('issued_date', now()->month)
                                ->whereYear('issued_date', now()->year)
                                ->distinct('member_id')->count('member_id'), 2)
                            : 0,
                ];

                $data['checkout_history'] = [];
                for ($i = 5; $i >= 0; $i--) {
                    $month = now()->copy()->subMonths($i);
                    $data['checkout_history'][] = [
                        'month' => $month->format('M'),
                        'count' => Transaction::whereMonth('issued_date', $month->month)
                            ->whereYear('issued_date', $month->year)->count(),
                    ];
                }

                return ['type' => 'index', 'title' => 'Summary Report', 'data' => $data];

            case 'circulation':
                return [
                    'type' => 'circulation',
                    'title' => 'Circulation Report',
                    'data' => Transaction::with(['member','book'])
                        ->when($start && $end, fn($q) => $q->whereBetween('issued_date', [$start, $end]))
                        ->orderBy('issued_date', 'desc')
                        ->get(),
                ];

            case 'inventory':
                return [
                    'type' => 'inventory',
                    'title' => 'Inventory Report',
                    'data' => Book::with('category')->orderBy('title')->get(),
                ];

            case 'overdue':
                return [
                    'type' => 'overdue',
                    'title' => 'Overdue Report',
                    'data' => Transaction::with(['member','book'])
                        ->where('status', 'overdue')
                        ->when($start && $end, fn($q) => $q->whereBetween('due_date', [$start, $end]))
                        ->orderBy('due_date', 'desc')
                        ->get(),
                ];

            case 'fines':
                return [
                    'type' => 'fines',
                    'title' => 'Fines & Collection Report',
                    'data' => Transaction::with(['member','book'])
                        ->where('fine', '>', 0)
                        ->when($start && $end, fn($q) => $q->whereBetween('updated_at', [$start . ' 00:00:00', $end . ' 23:59:59']))
                        ->orderBy('updated_at', 'desc')
                        ->get(),
                ];

            case 'members':
                return [
                    'type' => 'members',
                    'title' => 'Member Activity Report',
                    'data' => User::where('role','user')
                        ->withCount(['transactions as total_txns', 'transactions as active_txns' => fn($q) => $q->whereIn('status',['active','overdue'])])
                        ->get(),
                ];

            default:
                return [
                    'type' => 'payments',
                    'title' => 'Payments Report',
                    'data' => Transaction::with(['member','book','collectedBy'])
                        ->where('fine_paid', true)
                        ->whereNotNull('fine')
                        ->when($start && $end, fn($q) => $q->whereBetween('paid_at', [$start . ' 00:00:00', $end . ' 23:59:59']))
                        ->when($request->filled('method'), fn($q) => $q->where('payment_method', $request->method))
                        ->orderBy('paid_at', 'desc')
                        ->get(),
                ];
        }
    }

    public function members()
    {
        $members = User::where('role','user')
            ->withCount(['transactions as total_txns',
                         'transactions as active_txns' => fn($q) => $q->whereIn('status',['active','overdue'])])
            ->get();
        return view('reports.members', compact('members'));
    }

}
