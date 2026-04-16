<?php

namespace App\Http\Controllers;

use App\Models\Book;
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
            'available_copies' => $book->available_copies ?? $book->copies,
            'category'         => $book->category?->name,
        ]);
    }
}
