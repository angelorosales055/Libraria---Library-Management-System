<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BookController extends Controller
{
    public function index(Request $request)
    {
        $query = Book::with('category');

        if ($search = $request->search) {
            $query->search($search);
        }
        if ($cat = $request->category_id) {
            $query->byCategory($cat);
        }
        if ($request->status === 'available') {
            $query->available();
        } elseif ($request->status === 'out') {
            $query->where('available_copies', 0);
        }

        $books      = $query->withCount(['holds as pending_holds_count' => function ($q) {
            $q->where('status', 'pending');
        }])->latest()->paginate(24)->withQueryString();
        $categories = Category::orderBy('name')->get();
        $totalBooks = Book::count();

        return view('books.index', compact('books', 'categories', 'totalBooks'));
    }

    public function create()
    {
        $categories = Category::orderBy('name')->get();
        $showForm   = true;
        return view('books.index', compact('categories', 'showForm'))->with([
            'books'      => Book::with('category')->latest()->paginate(24),
            'totalBooks' => Book::count(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'isbn'         => 'required|string|unique:books,isbn',
            'accession_no' => 'nullable|string|unique:books,accession_no',
            'title'        => 'required|string|max:255',
            'author'       => 'required|string|max:255',
            'category_id'  => 'required|exists:categories,id',
            'copies'       => 'required|integer|min:1',
            'shelf'        => 'nullable|string|max:100',
            'description'  => 'nullable|string',
            'cover_image'  => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('cover_image')) {
            $data['cover_image'] = $request->file('cover_image')->store('covers', 'public');
        }

        $data['available_copies'] = $data['copies'];
        Book::create($data);

        return redirect()->route('books.index')->with('toast_success', 'Book added successfully!');
    }

    public function show(Book $book)
    {
        $book->load('category', 'transactions.member');
        $categories = Category::orderBy('name')->get();
        return view('books.show', compact('book', 'categories'));
    }

    public function edit(Book $book)
    {
        $categories = Category::orderBy('name')->get();
        return view('books.edit', compact('book', 'categories'));
    }

    public function update(Request $request, Book $book)
    {
        $data = $request->validate([
            'title'        => 'required|string|max:255',
            'author'       => 'required|string|max:255',
            'category_id'  => 'required|exists:categories,id',
            'copies'       => 'required|integer|min:1',
            'shelf'        => 'nullable|string|max:100',
            'description'  => 'nullable|string',
            'cover_image'  => 'nullable|image|max:2048',
        ]);

        // Adjust available copies if total changed
        $diff = $data['copies'] - $book->copies;
        $data['available_copies'] = max(0, ($book->available_copies ?? $book->copies) + $diff);

        if ($request->hasFile('cover_image')) {
            if ($book->cover_image) Storage::disk('public')->delete($book->cover_image);
            $data['cover_image'] = $request->file('cover_image')->store('covers', 'public');
        }

        $book->update($data);
        return redirect()->route('books.index')->with('toast_success', 'Book updated successfully!');
    }

    public function destroy(Book $book)
    {
        if ($book->activeTransactions()->exists()) {
            return back()->with('error', 'Cannot delete: book has active checkouts.');
        }
        if ($book->cover_image) Storage::disk('public')->delete($book->cover_image);
        $book->delete();
        return redirect()->route('books.index')->with('toast_success', 'Book deleted.');
    }
}
