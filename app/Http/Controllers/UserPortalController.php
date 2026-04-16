<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;

class UserPortalController extends Controller
{
    protected function ensureUser(): void
    {
        if (!auth()->check() || auth()->user()->role !== 'user') {
            abort(403, 'Access denied.');
        }
    }

    public function home()
    {
        $this->ensureUser();

        $featured = Book::available()->latest()->take(4)->get();
        $categories = Category::orderBy('name')->get();
        $activeBooks = Book::available()->count();
        $totalBooks = Book::count();
        $activeBorrowings = Transaction::where('member_id', auth()->id())
            ->whereIn('status', ['active', 'overdue'])
            ->count();
        $overdueCount = Transaction::where('member_id', auth()->id())
            ->where('status', 'overdue')
            ->count();
        $outstandingFees = Transaction::where('member_id', auth()->id())
            ->where('fine_paid', false)
            ->sum('fine');
        $totalTransactions = Transaction::where('member_id', auth()->id())
            ->count();
        $notifications = $this->portalNotifications();

        return view('portal.home', compact(
            'featured',
            'categories',
            'activeBooks',
            'totalBooks',
            'activeBorrowings',
            'overdueCount',
            'outstandingFees',
            'totalTransactions',
            'notifications'
        ));
    }

    public function collection(Request $request)
    {
        $this->ensureUser();

        $query = Book::with('category');

        if ($search = $request->search) {
            $query->search($search);
        }
        if ($categoryId = $request->category_id) {
            $query->byCategory($categoryId);
        }
        if ($request->status === 'available') {
            $query->available();
        }

        $books = $query->latest()->paginate(12)->withQueryString();
        $categories = Category::orderBy('name')->get();

        $notifications = $this->portalNotifications();
        return view('portal.collection', compact('books', 'categories', 'notifications'));
    }

    public function transactions()
    {
        $this->ensureUser();

        $userId = auth()->id();
        $activeBorrowings = Transaction::where('member_id', $userId)
            ->whereIn('status', ['active', 'overdue'])
            ->with('book')
            ->get();

        $history = Transaction::where('member_id', $userId)
            ->where('status', 'returned')
            ->with('book')
            ->latest()
            ->get();

        $overdueCount = Transaction::where('member_id', $userId)
            ->where('status', 'overdue')
            ->count();

        $outstandingFees = Transaction::where('member_id', $userId)
            ->where('status', 'returned')
            ->where('fine_paid', false)
            ->sum('fine');

        $notifications = $this->portalNotifications();
        return view('portal.transactions', compact('activeBorrowings', 'history', 'notifications', 'overdueCount', 'outstandingFees'));
    }

    public function borrow(Request $request, Book $book)
    {
        $this->ensureUser();

        $data = $request->validate([
            'due_date' => 'required|date|after:today',
            'notes'    => 'nullable|string|max:255',
        ]);

        $user = auth()->user();

        if (!$user->can_borrow) {
            return back()->with('error', 'You cannot borrow books at this time. Please check your account status.');
        }

        if (!$book->is_circulating) {
            return back()->with('error', 'This book is not available for circulation.');
        }

        if (!$book->is_available) {
            return $this->placeHold($book);
        }

        if (Transaction::where('member_id', $user->id)
            ->where('book_id', $book->id)
            ->whereIn('status', ['active', 'overdue'])
            ->exists()) {
            return back()->with('error', 'You already have this book checked out.');
        }

        Transaction::create([
            'member_id'   => $user->id,
            'book_id'     => $book->id,
            'issued_by'   => $user->id,
            'action'      => 'checkout',
            'status'      => 'active',
            'issued_date' => today(),
            'due_date'    => $data['due_date'],
            'fine'        => 0,
            'fine_paid'   => false,
            'notes'       => $data['notes'] ?? null,
            'max_renewals' => 2,
        ]);

        $book->decrement('available_copies');

        return redirect()->route('portal.transactions')
            ->with('toast_success', 'Success! Your borrow has been confirmed and is now visible in your transactions.');
    }

    protected function placeHold(Book $book)
    {
        $user = auth()->user();

        // Check if already has hold
        if (\App\Models\Hold::where('user_id', $user->id)->where('book_id', $book->id)->where('status', '!=', 'expired')->exists()) {
            return back()->with('error', 'You already have a hold on this book.');
        }

        $queuePosition = $book->holds()->pending()->count() + 1;

        \App\Models\Hold::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'queue_position' => $queuePosition,
            'requested_at' => now(),
        ]);

        return back()->with('toast_success', "Hold placed for '{$book->title}'. You are #{$queuePosition} in queue.");
    }

    private function portalNotifications(): array
    {
        $userId = auth()->id();
        $today = today();
        $notifications = [];

        $overdue = Transaction::with('book')
            ->where('member_id', $userId)
            ->where('status', 'overdue')
            ->latest('due_date')
            ->take(3)
            ->get();

        foreach ($overdue as $txn) {
            $days = $today->diffInDays($txn->due_date);
            $notifications[] = [
                'type' => 'overdue',
                'title' => 'Overdue book',
                'message' => "{$txn->book?->title} is overdue by {$days} day" . ($days === 1 ? '' : 's'),
                'date' => $txn->due_date->format('M j'),
            ];
        }

        $dueSoon = Transaction::with('book')
            ->where('member_id', $userId)
            ->where('status', 'active')
            ->whereNotNull('due_date')
            ->whereBetween('due_date', [$today, $today->copy()->addDays(3)])
            ->latest('due_date')
            ->take(3)
            ->get();

        foreach ($dueSoon as $txn) {
            $days = $today->diffInDays($txn->due_date);
            $notifications[] = [
                'type' => 'due_soon',
                'title' => 'Due soon',
                'message' => "{$txn->book?->title} is due in {$days} day" . ($days === 1 ? '' : 's'),
                'date' => $txn->due_date->format('M j'),
            ];
        }

        $recentReturns = Transaction::with('book')
            ->where('member_id', $userId)
            ->where('status', 'returned')
            ->where('returned_date', '>=', $today->subDays(5))
            ->latest('returned_date')
            ->take(3)
            ->get();

        foreach ($recentReturns as $txn) {
            $notifications[] = [
                'type' => 'returned',
                'title' => 'Return recorded',
                'message' => "{$txn->book?->title} was returned on {$txn->returned_date->format('M j')}",
                'date' => $txn->returned_date->format('M j'),
            ];
        }

        return array_slice($notifications, 0, 5);
    }

    public function renew(Request $request, Transaction $transaction)
    {
        $this->ensureUser();

        if ($transaction->member_id !== auth()->id()) {
            abort(403);
        }

        if (!$transaction->canRenew()) {
            return back()->with('error', 'This book cannot be renewed.');
        }

        if ($transaction->renew()) {
            return back()->with('toast_success', "Book renewed. New due date: {$transaction->due_date->format('M j, Y')}");
        }

        return back()->with('error', 'Renewal failed.');
    }

    public function return(Request $request, Transaction $transaction)
    {
        $this->ensureUser();

        if ($transaction->member_id !== auth()->id()) {
            abort(403);
        }

        if (!in_array($transaction->status, ['active', 'overdue'])) {
            return back()->with('error', 'This book cannot be returned.');
        }

        $fine = 0;
        if ($transaction->due_date && today()->gt($transaction->due_date)) {
            $fine = today()->diffInDays($transaction->due_date) * Transaction::FINE_PER_DAY;
        }

        $transaction->markReturned(false);

        if (!$transaction->is_pending_fine) {
            $transaction->book->increment('available_copies');
        }

        return back()->with('toast_success', $transaction->is_pending_fine
            ? 'Book returned with fine pending. Please submit payment.'
            : 'Book returned successfully.');
    }
}
