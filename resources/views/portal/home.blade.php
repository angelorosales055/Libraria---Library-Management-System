@extends('layout.portal')
@section('title', 'Home')
@section('content')
<div class="hero-card">
    <div class="hero-copy">
        <p style="color:var(--accent);font-weight:700;letter-spacing:1px;text-transform:uppercase;margin-bottom:14px">Welcome to HomeLibrary</p>
        <h1>Discover thousands of books and borrow with ease.</h1>
        <p>Explore featured titles, manage your active loans, and return books before the due date—all from your personal library portal.</p>
        <div class="hero-actions">
            <a href="{{ route('portal.collection') }}" class="btn btn-primary">Book Collection</a>
            <a href="{{ route('portal.transactions') }}" class="btn btn-secondary">Borrowed Items</a>
        </div>
    </div>
    <div class="hero-image">
        <div>
            <div style="font-size:28px;font-weight:700;margin-bottom:10px">HomeLibrary</div>
            <div style="color:var(--muted);font-size:14px;max-width:220px">Your portal for borrowing books, viewing due dates, and checking notifications.</div>
        </div>
    </div>
</div>
<div class="summary-grid">
    <div class="summary-card">
        <span>Active Borrowings</span>
        <h2>{{ $activeBorrowings }}</h2>
    </div>
    <div class="summary-card">
        <span>Overdue Books</span>
        <h2>{{ $overdueCount }}</h2>
    </div>
    <div class="summary-card">
        <span>Total Fees</span>
        <h2>₱{{ number_format($outstandingFees, 2) }}</h2>
    </div>
    <div class="summary-card">
        <span>Total Transactions</span>
        <h2>{{ $totalTransactions }}</h2>
    </div>
</div>
<div class="section">
    <div class="section-header">
        <div>
            <h2>Notifications</h2>
            <p style="color:var(--muted);margin-top:8px">Keep track of overdue books, upcoming due dates, and recent returns.</p>
        </div>
        <a href="{{ route('portal.transactions') }}" class="nav-link">View all</a>
    </div>
    <div class="featured-grid">
        @forelse($notifications as $note)
            <div class="book-card" style="padding:22px;">
                <div class="book-category">{{ ucfirst(str_replace('_', ' ', $note['type'])) }}</div>
                <div class="book-title" style="margin-top:10px; margin-bottom:10px;">{{ $note['title'] }}</div>
                <div class="book-author" style="font-size:13px; color:var(--muted);">{{ $note['message'] }}</div>
                <div style="margin-top:16px; color:var(--muted); font-size:12px;">{{ $note['date'] }}</div>
            </div>
        @empty
            <div style="color:var(--muted);padding:32px;background:var(--surface);border-radius:20px">No notifications yet. Your latest borrowing activity will appear here.</div>
        @endforelse
    </div>
</div>
<div class="section">
    <div class="section-header">
        <h2>Featured Books</h2>
        <a href="{{ route('portal.collection') }}" class="nav-link">View all</a>
    </div>
    <div class="featured-grid">
        @forelse($featured as $book)
        <div class="book-card">
            @if($book->cover_image)
                <img src="{{ asset('storage/'.$book->cover_image) }}" alt="{{ $book->title }}">
            @else
                <div class="book-cover-placeholder" style="background: rgba(255,255,255,0.05); display:flex; align-items:center; justify-content:center;">📚</div>
            @endif
            <div class="book-card-body">
                <div class="book-category">{{ $book->category?->name ?? 'General' }}</div>
                <div class="book-title">{{ $book->title }}</div>
                <div class="book-author">{{ $book->author }}</div>
                <div class="book-meta">
                    <span class="badge {{ $book->is_available ? 'available' : '' }}">{{ $book->available_copies > 0 ? $book->available_copies.' available' : 'Out of stock' }}</span>
                </div>
                <form method="POST" action="{{ route('portal.borrow', $book) }}">
                    @csrf
                    <button type="submit" class="btn-sm {{ $book->is_available ? '' : 'disabled' }}" {{ $book->is_available ? '' : 'disabled' }}>
                        {{ $book->is_available ? 'Borrow Book' : 'Out of stock' }}
                    </button>
                </form>
            </div>
        </div>
        @empty
            <div style="color:var(--muted);padding:32px;background:var(--surface);border-radius:20px">No books are currently featured.</div>
        @endforelse
    </div>
</div>
<div class="section">
    <div class="section-header">
        <h2>Explore by Category</h2>
        <span style="color:var(--muted);font-size:14px">Browse books across every genre.</span>
    </div>
    <div class="category-grid">
        @foreach($categories as $category)
            <a href="{{ route('portal.collection', ['category_id' => $category->id]) }}" class="book-card" style="padding:22px;">
                <div style="font-size:13px;color:var(--muted);text-transform:uppercase;font-weight:700;margin-bottom:12px">{{ $category->name }}</div>
                <div style="font-size:20px;font-weight:700;line-height:1.2;">{{ $category->description }}</div>
            </a>
        @endforeach
    </div>
</div>
@endsection
