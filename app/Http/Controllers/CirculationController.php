<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\User;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CirculationController extends Controller
{
    public function index(Request $request)
    {
        // Refresh overdue statuses
        Transaction::whereIn('status', ['active'])
            ->where('due_date', '<', today())
            ->update(['status' => 'overdue']);

        $query = Transaction::with(['member', 'book'])->latest();

        if ($status = $request->status) {
            $query->where('status', $status);
        }
        if ($request->boolean('unpaid')) {
            $query->where('fine_paid', false)->where('fine', '>', 0);
        }
        if ($search = $request->search) {
            $query->whereHas('member', fn($q) => $q->where('name', 'like', "%{$search}%"))
                  ->orWhereHas('book',   fn($q) => $q->where('title','like', "%{$search}%"));
        }

        $transactions = $query->paginate(15)->withQueryString();
        return view('circulation.index', compact('transactions'));
    }

    public function create()
    {
        return view('circulation.index', [
            'transactions' => Transaction::with(['member','book'])->latest()->paginate(15),
        ]);
    }

    public function checkout(Request $request)
    {
        $data = $request->validate([
            'member_id'       => 'required|string',
            'book_identifier' => 'required|string',
            'due_date'        => 'nullable|date|after:today',
        ]);

        // Find member by member_id field or DB id
        $member = User::where('member_id', $data['member_id'])
                      ->orWhere('id', $data['member_id'])
                      ->where('role', 'user')
                      ->first();

        if (!$member) {
            return back()->with('error', 'Member not found.')->withInput();
        }

        // Check if member can borrow
        if (!$member->can_borrow) {
            $reason = 'Member cannot borrow at this time.';
            if ($member->is_suspended) $reason .= ' Account suspended.';
            if ($member->outstanding_fine >= 5.00) $reason .= ' Outstanding fines.';
            if ($member->total_borrowed >= $member->borrowing_limit) $reason .= ' Borrowing limit reached.';
            return back()->with('error', $reason)->withInput();
        }

        // Find book by ISBN or accession_no
        $book = Book::where('isbn', $data['book_identifier'])
                    ->orWhere('accession_no', $data['book_identifier'])
                    ->orWhere('id', $data['book_identifier'])
                    ->first();

        if (!$book) {
            return back()->with('error', 'Book not found.')->withInput();
        }

        if (!$book->is_circulating) {
            return back()->with('error', 'This book is not available for circulation.')->withInput();
        }

        if (($book->available_copies ?? $book->copies) < 1) {
            // Check if can place hold
            if ($request->has('place_hold')) {
                return $this->placeHold($member, $book);
            }
            return back()->with('error', "'{$book->title}' is out of stock. Would you like to place a hold?")->withInput();
        }

        // Check if member already has this book
        if (Transaction::where('member_id', $member->id)->where('book_id', $book->id)->whereIn('status',['active','overdue'])->exists()) {
            return back()->with('error', 'Member already has this book checked out.')->withInput();
        }

        // Calculate due date
        $loanDays = $book->category->loan_period_days ?? 14;
        if ($member->type === 'faculty') {
            $loanDays = max($loanDays, 21); // Faculty get at least 21 days
        }
        $dueDate = $data['due_date'] ?? today()->addDays($loanDays);

        DB::transaction(function() use ($member, $book, $dueDate) {
            Transaction::create([
                'member_id'   => $member->id,
                'book_id'     => $book->id,
                'issued_by'   => auth()->id(),
                'action'      => 'checkout',
                'status'      => 'active',
                'issued_date' => today(),
                'due_date'    => $dueDate,
                'fine'        => 0,
                'fine_paid'   => false,
                'max_renewals' => 2,
            ]);

            $book->decrement('available_copies');
        });

        return redirect()->route('circulation.index')
            ->with('toast_success', "Checked out '{$book->title}' to {$member->name}. Due: ".Carbon::parse($dueDate)->format('M j, Y'));
    }

    protected function placeHold(User $member, Book $book)
    {
        // Check if member already has a hold on this book
        if (Hold::where('user_id', $member->id)->where('book_id', $book->id)->where('status', '!=', 'expired')->exists()) {
            return back()->with('error', 'You already have a hold on this book.')->withInput();
        }

        $queuePosition = $book->holds()->pending()->count() + 1;

        Hold::create([
            'user_id' => $member->id,
            'book_id' => $book->id,
            'queue_position' => $queuePosition,
            'requested_at' => now(),
        ]);

        return redirect()->route('circulation.index')
            ->with('toast_success', "Hold placed for '{$book->title}'. You are #{$queuePosition} in queue.");
    }

    public function processReturn(Request $request, Transaction $txn)
    {
        if (!in_array($txn->status, ['active','overdue'])) {
            return back()->with('error', 'This transaction cannot be returned.');
        }

        DB::transaction(function() use ($txn) {
            $txn->markReturned(false);

            if (!$txn->is_pending_fine) {
                $txn->book->increment('available_copies');
            }
        });

        $msg = $txn->fine > 0
            ? "Book returned with ₱".number_format($txn->fine,2)." fine pending."
            : "Book returned successfully.";

        return back()->with('toast_success', $msg);
    }

    public function collectFine(Request $request, Transaction $txn)
    {
        $request->validate(['payment_method' => 'required|in:cash,gcash,paymaya']);

        if (!$txn->is_pending_fine) {
            return back()->with('error', 'There is no pending fine for this transaction.');
        }

        DB::transaction(function() use ($txn) {
            $txn->collectFine();
            $txn->book->increment('available_copies');
        });

        return back()->with('toast_success', 'Fine of ₱'.number_format($txn->fine,2).' collected. Book returned.');
    }

    public function renew(Request $request, Transaction $txn)
    {
        if (!$txn->canRenew()) {
            return back()->with('error', 'This book cannot be renewed.');
        }

        if ($txn->renew()) {
            return back()->with('toast_success', "Book renewed. New due date: {$txn->due_date->format('M j, Y')}");
        }

        return back()->with('error', 'Renewal failed.');
    }
}
