<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;

class ApiController extends Controller
{
    public function memberLookup(Request $request)
    {
        $id = $request->id;
        $member = User::where('role','user')
            ->where(fn($q) => $q->where('member_id',$id)->orWhere('id',$id))
            ->first();

        if (!$member) return response()->json(['error'=>'Not found'], 404);

        return response()->json([
            'id'               => $member->id,
            'name'             => $member->name,
            'email'            => $member->email,
            'type'             => $member->type ?? 'student',
            'status'           => $member->status,
            'borrowing_limit'  => $member->borrowing_limit,
            'loan_period_days' => $member->loan_period_days,
            'total_borrowed'   => $member->total_borrowed,
            'outstanding_fine' => $member->outstanding_fine,
            'has_overdue'      => $member->has_overdue_books,
            'can_borrow'       => $member->can_borrow,
        ]);
    }

    public function bookLookup(Request $request)
    {
        $id = $request->id;
        $book = Book::with('category')
            ->where(fn($q) => $q->where('isbn',$id)->orWhere('accession_no',$id)->orWhere('id',$id))
            ->first();

        if (!$book) return response()->json(['error'=>'Not found'], 404);

        return response()->json([
            'id'               => $book->id,
            'title'            => $book->title,
            'author'           => $book->author,
            'description'      => $book->description,
            'isbn'             => $book->isbn,
            'accession_no'     => $book->accession_no,
            'available_copies' => $book->available_copies ?? $book->copies,
            'category'         => $book->category?->name,
            'loan_period_days' => $book->category?->loan_period_days ?? 14,
        ]);
    }

    public function transactionDetails(Request $request, $id)
    {
        $txn = Transaction::with(['book.category', 'member'])->findOrFail($id);
        
        if (auth()->user()->role === 'user' && $txn->member_id !== auth()->id()) {
            abort(403);
        }

        return response()->json([
            'id' => $txn->id,
            'status' => $txn->status,
            'action' => $txn->action,
            'issued_date' => $txn->issued_date?->format('M j, Y g:i A'),
            'due_date' => $txn->due_date?->format('M j, Y g:i A'),
            'returned_date' => $txn->returned_date?->format('M j, Y g:i A'),
            'fine' => (float) ($txn->fine ?? 0),
            'computed_fine' => (float) $txn->computed_fine,
            'outstanding_fine' => (float) $txn->outstanding_fine,
            'is_pending_fine' => (bool) $txn->is_pending_fine,
            'fine_paid' => (bool) $txn->fine_paid,
            'renewal_count' => $txn->renewal_count,
            'max_renewals' => $txn->max_renewals,
            'notes' => $txn->notes,
            'member' => [
                'name' => $txn->member?->name,
                'email' => $txn->member?->email,
                'member_id' => $txn->member?->member_id,
            ],
            'book' => [
                'title' => $txn->book?->title,
                'author' => $txn->book?->author,
                'isbn' => $txn->book?->isbn,
                'accession_no' => $txn->book?->accession_no,
                'category' => $txn->book?->category?->name,
                'description' => $txn->book?->description,
            ],
        ]);
    }
}
