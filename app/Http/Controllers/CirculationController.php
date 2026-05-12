<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Hold;
use App\Models\Notification;
use App\Models\ActivityLog;
use App\Models\User;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CirculationController extends Controller
{
    public function index(Request $request)
    {
        Transaction::whereIn('status', ['active'])
            ->where('due_date', '<', today())
            ->update(['status' => 'overdue']);

        $query = Transaction::with(['member', 'book'])->latest();

        if ($status = $request->status) {
            if ($status === 'damaged') {
                $query->whereIn('status', ['damaged', 'damage_return']);
            } else {
                $query->where('status', $status);
            }
        }
        if ($request->boolean('unpaid')) {
            $query->where('fine_paid', false)->where('fine', '>', 0);
        }
        if ($search = $request->search) {
            $query->whereHas('member', fn($q) => $q->where('name', 'like', "%{$search}%"))
                  ->orWhereHas('book',   fn($q) => $q->where('title','like', "%{$search}%"));
        }

        $transactions = $query->paginate(15)->withQueryString();

        $pendingTransactions = Transaction::with(['member', 'book.category', 'originalTransaction'])
            ->whereIn('status', ['pending', 'requested', 'renew_requested'])
            ->latest()
            ->get();

        return view('circulation.index', compact('transactions', 'pendingTransactions'));
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

        $member = User::where('member_id', $data['member_id'])
            ->orWhere('id', $data['member_id'])
            ->where('role', 'user')
            ->first();

        if (!$member) {
            return back()->with('error', 'Member not found.')->withInput();
        }

        if (!$member->can_borrow) {
            $reason = 'Member cannot borrow at this time.';
            if ($member->is_suspended) $reason .= ' Account suspended.';
            if ($member->outstanding_fine >= 5.00) $reason .= ' Outstanding fines.';
            if ($member->total_borrowed >= $member->borrowing_limit) $reason .= ' Borrowing limit reached.';
            return back()->with('error', $reason)->withInput();
        }

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
            if ($request->has('place_hold')) {
                return $this->placeHold($member, $book);
            }

            return back()->with('error', "'{$book->title}' is out of stock. Would you like to place a hold?")->withInput();
        }

        $pendingTransaction = Transaction::where('member_id', $member->id)
            ->where('book_id', $book->id)
            ->whereIn('status', ['pending', 'requested'])
            ->latest()
            ->first();

        if (!$pendingTransaction) {
            return back()->with('error', 'No pending request found for this member and book.')->withInput();
        }

        $loanDays = $book->category->loan_period_days ?? 14;
        if ($member->type === 'faculty') {
            $loanDays = max($loanDays, 21);
        }

        $dueDate = $data['due_date'] ?? ($pendingTransaction->due_date ?? today()->addDays($loanDays));

        DB::transaction(function () use ($pendingTransaction, $book, $dueDate) {
            $pendingTransaction->update([
                'issued_by' => auth()->id(),
                'action' => 'checkout',
                'status' => 'active',
                'issued_date' => today(),
                'due_date' => $dueDate,
                'fine' => 0,
                'fine_paid' => false,
                'payment_method' => 'checkout',
                'receipt_no' => Transaction::generateReceiptNo(),
                'max_renewals' => 2,
            ]);

            if ($book->available_copies > 0) {
                $book->decrement('available_copies');
            }
        });

        return redirect()->route('circulation.index')
            ->with('toast_success', "Approved '{$book->title}' for {$member->name}. Due: " . Carbon::parse($dueDate)->format('M j, Y'));
    }

    protected function placeHold(User $member, Book $book)
    {
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

    public function approveReturn(Request $request, Transaction $txn)
    {
        $request->validate([
            'condition' => 'required|in:good_condition,minor_damage,major_damage,lost',
        ]);

        if (!in_array($txn->status, ['active','overdue'])) {
            return back()->with('error', 'This transaction cannot be returned.');
        }

        $condition = $request->input('condition');
        $damageFee = match ($condition) {
            'good_condition' => 0,
            'minor_damage' => 50,
            'major_damage' => 150,
            'lost' => 500,
            default => 0,
        };
        $overdueFine = max(0, $txn->calculateFine(today()));
        $totalFine = $overdueFine + $damageFee;

        DB::transaction(function() use ($txn, $damageFee, $overdueFine) {
            if ($damageFee > 0) {
                $reason = 'Return assessed with damage and overdue penalty.';
                $txn->handleDamage($overdueFine + $damageFee, $reason);
            } else {
                $txn->markReturned(false);
                if ($txn->book) {
                    $txn->book->increment('available_copies');
                }
            }
        });

        if ($damageFee > 0 && $txn->member_id) {
            Notification::create([
                'user_id' => $txn->member_id,
                'type' => 'book_damaged',
                'title' => 'Return Assessed as Damaged',
                'message' => "Book '{$txn->book?->title}' returned with a damage penalty of ₱" . number_format($damageFee, 2) . " and overdue penalty of ₱" . number_format($overdueFine, 2) . ". Please settle payment.",
                'data' => ['transaction_id' => $txn->id, 'amount' => $totalFine, 'condition' => $condition],
            ]);
        } elseif ($totalFine > 0 && $txn->member_id) {
            Notification::create([
                'user_id' => $txn->member_id,
                'type' => 'overdue_reminder',
                'title' => 'Fine Applied',
                'message' => "Book '{$txn->book?->title}' returned with ₱" . number_format($totalFine, 2) . " fine. Please settle payment.",
                'data' => ['transaction_id' => $txn->id, 'amount' => $totalFine],
            ]);
        } elseif ($txn->member_id) {
            Notification::create([
                'user_id' => $txn->member_id,
                'type' => 'return_approved',
                'title' => 'Return Recorded',
                'message' => "'{$txn->book?->title}' was returned successfully on " . now()->format('M j, Y') . ".",
                'data' => ['transaction_id' => $txn->id],
            ]);
        }

        ActivityLog::log(
            auth()->id(),
            $damageFee > 0 ? 'damage_return' : 'return',
            "Approved return for '{$txn->book?->title}' (TXN-{$txn->id})" . ($totalFine > 0 ? " with ₱" . number_format($totalFine, 2) . " fine" : ""),
            $damageFee > 0 ? 'warning' : 'success'
        );

        $msg = $totalFine > 0
            ? "Book returned with ₱".number_format($totalFine,2)." fine pending."
            : "Book returned successfully.";

        if ($request->wantsJson() || $request->ajax() || $request->expectsJson()) {
            return response()->json(['success' => true, 'message' => $msg]);
        }

        return back()->with('toast_success', $msg);
    }

    public function damageReturn(Request $request, Transaction $txn)
    {
        $request->validate([
            'reason' => 'required|string|max:500',
            'fee' => 'required|numeric|min:0',
        ]);

        if (!in_array($txn->status, ['active','overdue'])) {
            return back()->with('error', 'This transaction cannot be marked as damaged.');
        }

        $fee = $request->fee;
        $overdueFine = max(0, $txn->calculateFine(today()));
        $totalFee = $fee + $overdueFine;
        $reason = $request->reason;

        DB::transaction(function () use ($txn, $totalFee, $fee, $overdueFine, $reason) {
            $txn->handleDamage($totalFee, $reason);

            if ($txn->member_id) {
                Notification::create([
                    'user_id' => $txn->member_id,
                    'type' => 'book_damaged',
                    'title' => 'Book Damaged',
                    'message' => "Book '{$txn->book?->title}' was marked as damaged. Reason: {$reason}. Fee: ₱" . number_format($totalFee, 2) . " (damage ₱" . number_format($fee, 2) . " + overdue ₱" . number_format($overdueFine, 2) . "). Please settle payment.",
                    'data' => ['transaction_id' => $txn->id, 'amount' => $totalFee, 'reason' => $reason],
                ]);
            }

            ActivityLog::log(
                auth()->id(),
                'damage_return',
                "Marked '{$txn->book?->title}' (TXN-{$txn->id}) as damaged. Reason: {$reason}. Fee: ₱" . number_format($totalFee, 2),
                'warning'
            );
        });

        return back()->with('toast_success', "Book marked as damaged. Fee ₱" . number_format($totalFee, 2) . " applied. Book removed from inventory.");
    }

    public function collectFine(Request $request, Transaction $txn)
    {
        $request->validate(['payment_method' => 'required|in:cash,gcash,paymaya']);

        if (!$txn->is_pending_fine) {
            return back()->with('error', 'There is no pending fine for this transaction.');
        }

        DB::transaction(function() use ($txn, $request) {
            $txn->completeFinePayment($request->payment_method);

            if ($txn->member_id) {
                Notification::create([
                    'user_id' => $txn->member_id,
                    'type' => 'payment_success',
                    'title' => 'Payment Successful',
                    'message' => "Fine of ₱" . number_format($txn->fine, 2) . " for '{$txn->book?->title}' has been paid.",
                    'data' => ['transaction_id' => $txn->id, 'amount' => $txn->fine, 'method' => $request->payment_method],
                ]);
            }

            ActivityLog::log(
                auth()->id(),
                'payment',
                "Collected fine of ₱" . number_format($txn->fine, 2) . " for TXN-{$txn->id} via " . ucfirst($request->payment_method),
                'success'
            );
        });

        return redirect()->route('receipt.show', $txn)
            ->with('toast_success', 'Fine of ₱'.number_format($txn->fine,2).' collected. Receipt generated.');
    }

    public function notifyFine(Request $request, Transaction $txn)
    {
        if (!$txn->member_id) {
            return back()->with('error', 'Cannot notify fine: transaction has no associated member.');
        }

        if ($txn->fine_paid || $txn->fine <= 0) {
            return back()->with('error', 'There is no outstanding fine to notify for this transaction.');
        }

        $amount = number_format($txn->fine, 2);

        Notification::create([
            'user_id' => $txn->member_id,
            'type' => 'overdue_reminder',
            'title' => 'Fine Payment Reminder',
            'message' => "Please settle the ₱{$amount} fine for '{$txn->book?->title}' as soon as possible.",
            'data' => ['transaction_id' => $txn->id, 'amount' => $txn->fine],
        ]);

        ActivityLog::log(
            auth()->id(),
            'notification',
            "Sent fine payment reminder of ₱{$amount} for TXN-{$txn->id} to {$txn->member?->name}.",
            'warning'
        );

        return back()->with('toast_success', "User notified to pay the ₱{$amount} fine.");
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

    public function approve(Transaction $txn)
    {
        $validStatuses = ['pending', 'requested', 'renew_requested'];
        if (!in_array($txn->status, $validStatuses)) {
            return back()->with('error', 'This transaction is not pending approval.');
        }

        $member = $txn->member;
        $book = $txn->book;

        if (!$member || !$member->can_borrow) {
            return back()->with('error', 'Member cannot borrow at this time.');
        }

        if (!$book || !$book->is_circulating) {
            return back()->with('error', 'This book is not available for circulation.');
        }

        if (($book->available_copies ?? $book->copies) < 1) {
            return back()->with('error', "'{$book->title}' is out of stock.");
        }

        $loanDays = $book->category->loan_period_days ?? 14;
        if ($member->type === 'faculty') {
            $loanDays = max($loanDays, 21);
        }

        $dueDate = $txn->due_date ?? today()->addDays($loanDays);

        DB::transaction(function () use ($txn, $book, $dueDate, $member) {
            $txn->update([
                'issued_by' => auth()->id(),
                'action' => 'checkout',
                'status' => 'active',
                'issued_date' => today(),
                'due_date' => $dueDate,
                'fine' => 0,
                'fine_paid' => false,
                'max_renewals' => 2,
            ]);

            if ($book->available_copies > 0) {
                $book->decrement('available_copies');
            }

            Notification::create([
                'user_id' => $member->id,
                'type' => 'borrow_success',
                'title' => 'Borrow Approved',
                'message' => "Your request for '{$book->title}' has been approved. Due: " . Carbon::parse($dueDate)->format('M j, Y'),
                'data' => ['transaction_id' => $txn->id, 'book_title' => $book->title],
            ]);

            ActivityLog::log(
                auth()->id(),
                'approval',
                "Approved checkout of '{$book->title}' for {$member->name} (TXN-{$txn->id})",
                'success'
            );
        });

        return redirect()->route('circulation.index')
            ->with('toast_success', "Approved '{$book->title}' for {$member->name}. Due: " . Carbon::parse($dueDate)->format('M j, Y'));
    }

    public function reject(Transaction $txn)
    {
        if (!in_array($txn->status, ['pending', 'requested', 'renew_requested'])) {
            return back()->with('error', 'This transaction is not pending approval.');
        }

        $bookTitle = $txn->book?->title ?? 'Book';
        $memberName = $txn->member?->name ?? 'Member';

        $txn->update([
            'status' => 'rejected',
            'action' => 'reject',
            'notes' => ($txn->notes ?? '') . "\n[Rejected by " . auth()->user()->name . " on " . now()->format('M j, Y g:i A') . "]",
        ]);

        $type = $txn->status === 'renew_requested' ? 'renewal' : 'borrow';
        
        if ($txn->member_id) {
            Notification::create([
                'user_id' => $txn->member_id,
                'type' => 'rejected',
                'title' => $type === 'renewal' ? 'Renewal Rejected' : 'Request Rejected',
                'message' => "Your {$type} request for '{$bookTitle}' was rejected by staff.",
                'data' => ['transaction_id' => $txn->id],
            ]);
        }

        ActivityLog::log(
            auth()->id(),
            'rejection',
            "Rejected {$type} request for '{$bookTitle}' from {$memberName} (TXN-{$txn->id})",
            'warning'
        );

        return redirect()->route('circulation.index')
            ->with('toast_success', "Rejected {$type} request for '{$bookTitle}' from {$memberName}.");
    }

    public function receipt(Transaction $txn)
    {
        $user = auth()->user();
        if ($user->role === 'user' && $txn->member_id !== $user->id) {
            abort(403);
        }

        if ($txn->status === 'returned' && ($txn->fine ?? 0) === 0 && !$txn->fine_paid) {
            $txn->update(['fine_paid' => true]);
        }

        if (!$txn->receipt_no) {
            if ($txn->fine_paid || $txn->status === 'returned' || $txn->action === 'checkout') {
                $txn->update(['receipt_no' => Transaction::generateReceiptNo()]);
            }
        }

        if (!$txn->receipt_no) {
            return back()->with('error', 'No receipt available for this transaction.');
        }

        return view('receipt.show', compact('txn'));
    }

    public function approveRenew(Transaction $txn)
    {
        if ($txn->status !== 'renew_requested') {
            return back()->with('error', 'This is not a renewal request.');
        }

        $original = $txn->originalTransaction;
        if (!$original || !$original->canRenew()) {
            return back()->with('error', 'Original transaction cannot be renewed.');
        }

        $loanDays = $original->book->category->loan_period_days ?? 14;
        $newDueDate = $original->due_date->copy()->addDays($loanDays);

        DB::transaction(function () use ($txn, $original, $newDueDate) {
            $original->renew();
            $original->issued_by = auth()->id();
            $original->save();

            $txn->update([
                'status' => 'approved',
                'action' => 'renew_approved',
                'notes' => ($txn->notes ?? '') . "\n[Approved by " . auth()->user()->name . " on " . now()->format('M j, Y g:i A') . " - New due: " . $newDueDate->format('M j, Y') . "]",
            ]);

            if ($original->member_id) {
                Notification::create([
                    'user_id' => $original->member_id,
                    'type' => 'renew_success',
                    'title' => 'Renewal Approved',
                    'message' => "'{$original->book->title}' renewal approved. New due date: " . $newDueDate->format('M j, Y'),
                    'data' => ['transaction_id' => $original->id, 'book_title' => $original->book->title],
                ]);
            }

            ActivityLog::log(
                auth()->id(),
                'renewal',
                "Approved renewal for '{$original->book->title}' (TXN-{$original->id}) for " . ($original->member?->name ?? 'Member') . ". New due: " . $newDueDate->format('M j, Y'),
                'success'
            );
        });

        return redirect()->route('circulation.index')
            ->with('toast_success', "Renewal approved for '{$original->book->title}'. New due: " . $newDueDate->format('M j, Y'));
    }

    public function downloadReceipt(Transaction $txn)
    {
        $user = auth()->user();
        if ($user->role === 'user' && $txn->member_id !== $user->id) {
            abort(403);
        }

        if ($txn->status === 'returned' && ($txn->fine ?? 0) === 0 && !$txn->fine_paid) {
            $txn->update(['fine_paid' => true]);
        }

        if (!$txn->receipt_no) {
            if ($txn->fine_paid || $txn->status === 'returned' || $txn->action === 'checkout') {
                $txn->update(['receipt_no' => Transaction::generateReceiptNo()]);
            }
        }

        if (!$txn->receipt_no) {
            return back()->with('error', 'No receipt available for this transaction.');
        }

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('receipt.pdf', compact('txn'));
        return $pdf->download("Receipt-{$txn->receipt_no}.pdf");
    }
}
