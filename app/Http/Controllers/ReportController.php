<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\User;
use App\Models\Transaction;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function index()
    {
        $now   = Carbon::now();
        $month = $now->month;
        $year  = $now->year;

        $monthlyCheckouts = Transaction::whereMonth('issued_date', $month)
            ->whereYear('issued_date', $year)->count();

        $lastMonthCheckouts = Transaction::whereMonth('issued_date', $month - 1 ?: 12)
            ->whereYear('issued_date', $month === 1 ? $year - 1 : $year)->count();

        $checkoutChange = $lastMonthCheckouts > 0
            ? round((($monthlyCheckouts - $lastMonthCheckouts) / $lastMonthCheckouts) * 100)
            : 0;

        $finesCollected = Transaction::whereMonth('updated_at', $month)
            ->whereYear('updated_at', $year)
            ->where('fine_paid', true)->sum('fine');

        // Monthly data for chart (last 6 months)
        $monthlyData = [];
        for ($i = 5; $i >= 0; $i--) {
            $d = $now->copy()->subMonths($i);
            $monthlyData[] = Transaction::whereMonth('issued_date', $d->month)
                ->whereYear('issued_date', $d->year)->count();
        }

        $mostBorrowed = Transaction::selectRaw('book_id, count(*) as cnt')
            ->groupBy('book_id')->orderByDesc('cnt')->first()?->book?->title ?? '—';

        $mostActiveMember = Transaction::selectRaw('member_id, count(*) as cnt')
            ->groupBy('member_id')->orderByDesc('cnt')->first()?->member?->name ?? '—';

        $newAcquisitions = Book::whereMonth('created_at', $month)
            ->whereYear('created_at', $year)->count();

        return view('reports.index', compact(
            'monthlyCheckouts','checkoutChange','finesCollected',
            'monthlyData','mostBorrowed','mostActiveMember','newAcquisitions'
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

    public function overdue()
    {
        $transactions = Transaction::with(['member','book'])
            ->where('status','overdue')->latest()->get();
        return view('reports.overdue', compact('transactions'));
    }

    public function fines()
    {
        $transactions = Transaction::with(['member','book'])
            ->where('fine', '>', 0)->latest()->get();
        return view('reports.fines', compact('transactions'));
    }

    public function members()
    {
        $members = User::where('role','user')
            ->withCount(['transactions as total_txns',
                         'transactions as active_txns' => fn($q) => $q->whereIn('status',['active','overdue'])])
            ->get();
        return view('reports.members', compact('members'));
    }

    public function export()
    {
        // Simple HTML-to-print export — in production use laravel-dompdf
        $data = [
            'totalBooks'    => Book::count(),
            'totalMembers'  => User::where('role','user')->count(),
            'totalTxns'     => Transaction::count(),
            'overdueCount'  => Transaction::where('status','overdue')->count(),
            'finesOwed'     => Transaction::where('fine_paid',false)->where('fine','>',0)->sum('fine'),
        ];
        return view('reports.export', $data);
    }
}
