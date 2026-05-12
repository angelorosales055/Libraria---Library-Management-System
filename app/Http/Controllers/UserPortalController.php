<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Category;
use App\Models\Notification;
use App\Models\Transaction;
use App\Models\ActivityLog;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
        $user = auth()->user();
        $outstandingFees = $user->outstanding_fine;
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

    public function transactions(Request $request)
    {
        $this->ensureUser();

        $userId = auth()->id();
        $search = $request->get('search');
        $filter = $request->get('filter', 'all');

        $baseQuery = function ($statuses) use ($userId, $search) {
            return Transaction::where('member_id', $userId)
                ->whereIn('status', $statuses)
                ->when($search, function ($q) use ($search) {
                    return $q->whereHas('book', function ($bq) use ($search) {
                        $bq->where('title', 'like', '%' . $search . '%')
                           ->orWhere('author', 'like', '%' . $search . '%');
                    });
                })
                ->with('book')
                ->latest();
        };

        $activeBorrowings = $baseQuery(['active', 'overdue'])->get();
        $pendingRequests = $baseQuery(['pending', 'requested', 'renew_requested'])->get();
        $rejectedRequests = $baseQuery(['rejected'])->get();
        $history = $baseQuery(['returned'])->get();

        $overdueCount = Transaction::where('member_id', $userId)
            ->where('status', 'overdue')
            ->count();

        $outstandingFees = auth()->user()->outstanding_fine;

        $notifications = $this->portalNotifications();
        return view('portal.transactions', compact(
            'activeBorrowings',
            'pendingRequests',
            'rejectedRequests',
            'history',
            'notifications',
            'overdueCount',
            'outstandingFees',
            'search',
            'filter'
        ));
    }

    public function fines()
    {
        $this->ensureUser();

        $pendingFines = Transaction::where('member_id', auth()->id())
            ->where('fine_paid', false)
            ->where(function ($query) {
                $query->where('fine', '>', 0)
                      ->orWhere(function ($q) {
                          $q->whereNull('returned_date')
                            ->whereNotNull('due_date')
                            ->whereIn('status', ['active', 'overdue'])
                            ->whereDate('due_date', '<', today());
                      });
            })
            ->with('book')
            ->latest()
            ->get();

        $paidFines = Transaction::where('member_id', auth()->id())
            ->where('fine_paid', true)
            ->where('fine', '>', 0)
            ->with('book')
            ->latest()
            ->get();

        $totalOutstanding = max(0, $pendingFines->sum(fn ($txn) => $txn->outstanding_fine));
        $totalPaid = max(0, $paidFines->sum('fine'));

        $notifications = $this->portalNotifications();
        return view('portal.fines', compact('pendingFines', 'paidFines', 'totalOutstanding', 'totalPaid', 'notifications'));
    }

    public function payFine(Request $request, Transaction $txn)
    {
        $this->ensureUser();

        if ($txn->member_id !== auth()->id()) {
            abort(403);
        }

        $request->validate([
            'payment_method' => 'required|in:cash,gcash,paymaya',
            'contact_number' => 'nullable|digits:11',
        ]);

        if (in_array($request->payment_method, ['gcash', 'paymaya']) && !$request->filled('contact_number')) {
            return back()->with('error', 'Contact number is required for digital payments.');
        }

        if ($txn->fine_paid) {
            return back()->with('error', 'This fine has already been paid.');
        }

        $txn->loadMissing('book');
        $fineAmount = $txn->outstanding_fine;

        if ($fineAmount <= 0) {
            return back()->with('error', 'There is no outstanding fine for this transaction.');
        }

        if ($request->payment_method === 'cash') {
            $notes = trim(($txn->notes ?? '') .
                ($request->filled('contact_number') ? "\n[Cash contact: {$request->contact_number}]" : '')
            );

            if ($notes !== ($txn->notes ?? '')) {
                $txn->update(['notes' => $notes]);
            }

            DB::transaction(function () use ($txn, $fineAmount) {
                $txn->completeFinePayment('cash');

                Notification::create([
                    'user_id' => auth()->id(),
                    'type' => 'payment_success',
                    'title' => 'Cash Payment Recorded',
                    'message' => "Cash payment of ₱" . number_format($fineAmount, 2) . " for {$txn->book?->title} was successfully recorded.",
                    'data' => ['transaction_id' => $txn->id, 'amount' => $fineAmount],
                ]);

                ActivityLog::log(
                    auth()->id(),
                    'payment',
                    "Completed cash payment of ₱" . number_format($fineAmount, 2) . " for TXN-{$txn->id}",
                    'success'
                );
            });

            return redirect()->route('portal.fines')
                ->with('toast_success', 'Cash payment of ₱' . number_format($fineAmount, 2) . ' has been recorded.');
        }

        $reference = 'PAY-' . strtoupper($request->payment_method) . '-' . now()->format('YmdHis') . '-' . $txn->id;
        $notes = trim(($txn->notes ?? '') .
            ($request->filled('contact_number') ? "\n[Payment contact: {$request->contact_number}]" : '')
        );

        $txn->update([
            'paymongo_reference' => $reference,
            'payment_method' => $request->payment_method,
            'notes' => $notes,
        ]);

        ActivityLog::log(
            auth()->id(),
            'payment',
            "Initiated {$request->payment_method} payment of ₱" . number_format($fineAmount, 2) . " for TXN-{$txn->id}",
            'pending'
        );

        return redirect()->route('paymongo.checkout', [
            'txn' => $txn->id,
            'method' => $request->payment_method,
            'contact_number' => $request->contact_number,
        ]);
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
            ->whereIn('status', ['active', 'overdue', 'pending', 'requested'])
            ->exists()) {
            return back()->with('error', 'You already have a request or checkout for this book.');
        }

        Transaction::create([
            'member_id'    => $user->id,
            'book_id'      => $book->id,
            'issued_by'    => $user->id,
            'action'       => 'checkout',
            'status'       => 'pending',
            'issued_date'  => today(),
            'due_date'     => $data['due_date'],
            'fine'         => 0,
            'fine_paid'    => false,
            'notes'        => $data['notes'] ?? null,
            'max_renewals' => 2,
        ]);

        return redirect()->route('portal.transactions')
            ->with('toast_success', 'Success! Your request has been submitted for staff approval.');
    }

    protected function placeHold(Book $book)
    {
        $user = auth()->user();

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
            $days = $today->diffInDays($txn->due_date, true);
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
            $days = $today->diffInDays($txn->due_date, true);
            $notifications[] = [
                'type' => 'due_soon',
                'title' => 'Due soon',
                'message' => "{$txn->book?->title} is due in {$days} day" . ($days === 1 ? '' : 's'),
                'date' => $txn->due_date->format('M j'),
            ];
        }

        $rejected = Transaction::with('book')
            ->where('member_id', $userId)
            ->where('status', 'rejected')
            ->where('updated_at', '>=', $today->subDays(7))
            ->latest()
            ->take(3)
            ->get();

        foreach ($rejected as $txn) {
            $notifications[] = [
                'type' => 'rejected',
                'title' => 'Request rejected',
                'message' => "Your request for {$txn->book?->title} was rejected by staff",
                'date' => $txn->updated_at->format('M j'),
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

    public function requestRenew(Request $request, Transaction $origTxn)
    {
        $this->ensureUser();

        if ($origTxn->member_id !== auth()->id()) {
            abort(403);
        }

        if (!$origTxn->canRequestRenew()) {
            return back()->with('error', 'Cannot request renewal for this book. Already requested or not eligible.');
        }

        $loanDays = $origTxn->book->category->loan_period_days ?? 14;
        $proposedDueDate = $origTxn->due_date->copy()->addDays($loanDays);

        $newTxn = Transaction::create([
            'member_id' => $origTxn->member_id,
            'book_id' => $origTxn->book_id,
            'issued_by' => auth()->id(),
            'original_transaction_id' => $origTxn->id,
            'action' => 'renew_request',
            'status' => 'renew_requested',
            'issued_date' => today(),
            'due_date' => $proposedDueDate,
            'fine' => 0,
            'fine_paid' => false,
            'notes' => "Renewal request for original TXN #{$origTxn->id} ({$origTxn->book->title})",
            'max_renewals' => $origTxn->max_renewals,
            'renewal_count' => $origTxn->renewal_count,
        ]);

        return back()->with('toast_success', "Renewal request submitted for '{$origTxn->book->title}'. Awaiting staff approval. Proposed due date: {$proposedDueDate->format('M j, Y')}");
    }

    public function resubmitRequest(Transaction $txn)
    {
        $this->ensureUser();

        if ($txn->member_id !== auth()->id() || $txn->status !== 'rejected') {
            abort(403);
        }

        $book = $txn->book;
        if (!$book) {
            return back()->with('error', 'Book information is missing.');
        }

        if (!$book->is_circulating) {
            return back()->with('error', 'This book is not available for circulation.');
        }

        if (Transaction::where('member_id', $txn->member_id)
            ->where('book_id', $book->id)
            ->whereIn('status', ['active', 'overdue', 'pending', 'requested', 'renew_requested'])
            ->exists()) {
            return back()->with('error', 'You already have an active or pending request for this book.');
        }

        if (($book->available_copies ?? $book->copies) < 1) {
            return $this->placeHold($book);
        }

        $loanDays = $book->category->loan_period_days ?? 14;
        $dueDate = today()->addDays($loanDays);

        DB::transaction(function () use ($txn, $book, $loanDays, $dueDate) {
            $newTxn = Transaction::create([
                'member_id' => $txn->member_id,
                'book_id' => $book->id,
                'issued_by' => auth()->id(),
                'action' => 'checkout',
                'status' => 'pending',
                'issued_date' => today(),
                'due_date' => $dueDate,
                'fine' => 0,
                'fine_paid' => false,
                'notes' => trim("Resubmitted after rejection. Original request notes: " . ($txn->notes ?? 'No comment')),
                'max_renewals' => 2,
            ]);

            ActivityLog::log(
                auth()->id(),
                'request',
                "Resubmitted request for '{$book->title}' (TXN-{$newTxn->id}) after previous rejection",
                'success'
            );
        });

        return redirect()->route('portal.transactions')
            ->with('toast_success', 'Your request has been resubmitted for staff approval.');
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

        $transaction->markReturned(false);
        $transaction->book->increment('available_copies');

        return back()->with('toast_success', $transaction->is_pending_fine
            ? 'Book returned with fine pending. Please submit payment.'
            : 'Book returned successfully.');
    }
}
