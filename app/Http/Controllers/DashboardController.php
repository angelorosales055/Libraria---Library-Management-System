<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\User;
use App\Models\Transaction;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        if (auth()->check() && auth()->user()->role === 'user') {
            return redirect()->route('portal.home');
        }

        $now   = Carbon::now();
        $month = $now->month;
        $year  = $now->year;

        // Update overdue statuses
        Transaction::whereIn('status', ['active'])
            ->where('due_date', '<', today())
            ->update(['status' => 'overdue']);

        $totalBooks       = Book::sum('copies');
        $newBooksMonth    = Book::whereMonth('created_at', $month)->whereYear('created_at', $year)->count();
        $activeMembers    = User::where('role', 'user')->where('is_active', true)->count();
        $newMembersWeek   = User::where('role', 'user')->where('created_at', '>=', now()->subWeek())->count();
        $overdueCount     = Transaction::where('status', 'overdue')->count();
        $overdueToday     = Transaction::where('status', 'overdue')->whereDate('due_date', today()->subDay())->count();
        $checkedOut       = Transaction::whereIn('status', ['active', 'overdue'])->count();
        $totalCopies      = Book::sum('copies');
        $utilizationRate  = $totalCopies > 0 ? round(($checkedOut / max($totalCopies, 1)) * 100) : 0;

        $recentTransactions = Transaction::with(['member', 'book'])
            ->latest()
            ->limit(5)
            ->get();

        return view('dashboard.index', compact(
            'totalBooks', 'newBooksMonth', 'activeMembers', 'newMembersWeek',
            'overdueCount', 'overdueToday', 'checkedOut', 'utilizationRate',
            'recentTransactions'
        ));
    }
}
