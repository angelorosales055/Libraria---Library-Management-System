@extends('layout.app')
@section('title', 'Book Catalog')
@section('content')

<div class="page-header-row mb-6">
    <div>
        <h1 class="page-title">Book Catalog</h1>
        <p class="page-subtitle">{{ $totalBooks ?? 0 }} titles in collection. Browse, search, and manage</p>
    </div>
    <div class="flex gap-2">
        <button class="btn btn-outline" onclick="openModal('filterModal')">
            <i class="fas fa-filter"></i> Filter
        </button>
        <a href="{{ route('books.create') }}" class="btn btn-gold">
            <i class="fas fa-plus"></i> Add Book
        </a>
    </div>
</div>

{{-- WORKFLOW --}}
<div class="workflow-steps mb-6">
    <div class="workflow-step">
        <div class="step-num">1</div>
        <div><div class="step-text">Enter Details</div><div class="step-sub">ISBN, title, author</div></div>
    </div>
    <div class="workflow-step">
        <div class="step-num">2</div>
        <div><div class="step-text">Upload Cover</div><div class="step-sub">Optional cover image</div></div>
    </div>
    <div class="workflow-step">
        <div class="step-num">3</div>
        <div><div class="step-text">Set Copies</div><div class="step-sub">Number of copies & shelf</div></div>
    </div>
    <div class="workflow-step">
        <div class="step-num">4</div>
        <div><div class="step-text">Review</div><div class="step-sub">Confirm all details</div></div>
    </div>
    <div class="workflow-step">
        <div class="step-num">5</div>
        <div><div class="step-text">Save & Barcode</div><div class="step-sub">Print accession barcode</div></div>
    </div>
</div>

{{-- ADD BOOK FORM (collapsible) --}}
@if(request()->routeIs('books.create') || isset($showForm))
<div class="card mb-6">
    <div class="card-header"><span class="card-title">Add New Book</span></div>
    <div class="card-body">
        <form method="POST" action="{{ route('books.store') }}" enctype="multipart/form-data">
            @csrf
            <div class="grid-2">
                <div class="form-group">
                    <label class="form-label">ISBN</label>
                    <input type="text" name="isbn" class="form-control" placeholder="978-0-06-112008-4" value="{{ old('isbn') }}" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Accession No.</label>
                    <input type="text" name="accession_no" class="form-control" placeholder="ACC-2025-1285" value="{{ old('accession_no') }}">
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Book Title</label>
                <input type="text" name="title" class="form-control" placeholder="To Kill a Mockingbird" value="{{ old('title') }}" required>
            </div>
            <div class="grid-2">
                <div class="form-group">
                    <label class="form-label">Author</label>
                    <input type="text" name="author" class="form-control" placeholder="Harper Lee" value="{{ old('author') }}" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Category</label>
                    <select name="category_id" class="form-control" required>
                        <option value="">Select category...</option>
                        @foreach($categories ?? [] as $cat)
                            <option value="{{ $cat->id }}" {{ old('category_id')==$cat->id?'selected':'' }}>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="grid-2">
                <div class="form-group">
                    <label class="form-label">No. of Copies</label>
                    <input type="number" name="copies" class="form-control" placeholder="5" min="1" value="{{ old('copies', 1) }}" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Shelf / Section</label>
                    <input type="text" name="shelf" class="form-control" placeholder="Fiction – Row B3" value="{{ old('shelf') }}">
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Cover Image</label>
                <input type="file" name="cover_image" class="form-control" accept="image/*">
            </div>
            <div class="flex gap-2 justify-end">
                <a href="{{ route('books.index') }}" class="btn btn-outline">Cancel</a>
                <button type="submit" class="btn btn-gold">Save &amp; Continue</button>
            </div>
        </form>
    </div>
</div>
@endif

{{-- SEARCH + FILTER BAR --}}
<div class="card mb-4">
    <div class="card-body" style="padding:14px 20px">
        <div class="flex gap-3 items-center" style="flex-wrap:wrap">
            <input type="text" id="bookSearch" class="form-control" style="max-width:280px" placeholder="Search books, ISBN, author...">
            <select id="categoryFilter" class="form-control" style="max-width:180px">
                <option value="">All Categories</option>
                @foreach($categories ?? [] as $cat)
                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                @endforeach
            </select>
            <select id="statusFilter" class="form-control" style="max-width:160px">
                <option value="">All Status</option>
                <option value="available">Available</option>
                <option value="low">Low Stock (≤2)</option>
                <option value="out">Out of Stock</option>
            </select>
        </div>
    </div>
</div>

{{-- BOOKS GRID --}}
<div class="card">
    <div class="card-header"><span class="card-title">All Books</span></div>
    <div class="card-body">
        <div class="book-grid" id="bookGrid">
            @forelse($books ?? [] as $book)
            @php
                $avail = $book->available_copies ?? $book->copies;
                $colorPalette = ['#8B6E5A','#5A7A6E','#6E5A8B','#8B5A5A','#5A6E8B','#7A6E4A','#4A7A6A','#8B7A5A'];
                $bg = $colorPalette[$book->id % count($colorPalette)];
            @endphp
            <div class="book-card" data-title="{{ strtolower($book->title) }}" data-category="{{ $book->category_id }}" data-copies="{{ $avail }}">
                <div class="book-cover" style="background: {{ $bg }}20; border-bottom:3px solid {{ $bg }}">
                    @if($book->cover_image)
                        <img src="{{ asset('storage/'.$book->cover_image) }}" style="width:100%;height:100%;object-fit:cover">
                    @else
                        <span style="font-size:28px">📚</span>
                    @endif
                </div>
                <div class="book-info">
                    <div class="book-title" title="{{ $book->title }}">{{ $book->title }}</div>
                    <div class="book-author">{{ $book->author }}</div>
                    <div class="book-status" style="display:flex;gap:8px;flex-wrap:wrap;align-items:center">
                        @if($avail > 2)
                            <span class="badge badge-success">{{ $avail }} left</span>
                        @elseif($avail > 0)
                            <span class="badge badge-warning">{{ $avail }} left</span>
                        @else
                            <span class="badge badge-danger">Out of stock</span>
                        @endif
                        @if(($book->pending_holds_count ?? 0) > 0)
                            <span class="badge badge-warning">{{ $book->pending_holds_count }} hold{{ $book->pending_holds_count > 1 ? 's' : '' }}</span>
                        @endif
                    </div>
                </div>
                <div style="padding:0 10px 10px; display:flex; gap:4px">
                    <a href="{{ route('books.show', $book) }}" class="btn btn-outline btn-xs" style="flex:1;justify-content:center">View</a>
                    <a href="{{ route('books.edit', $book) }}" class="btn btn-primary btn-xs" style="flex:1;justify-content:center">Edit</a>
                </div>
            </div>
            @empty
            <div style="grid-column:1/-1;text-align:center;padding:40px;color:var(--text-light)">
                <i class="fas fa-book" style="font-size:32px;margin-bottom:12px;display:block"></i>
                No books found. <a href="{{ route('books.create') }}">Add your first book</a>
            </div>
            @endforelse
        </div>
    </div>
</div>

@push('scripts')
<script>
document.getElementById('bookSearch').addEventListener('input', filterBooks);
document.getElementById('categoryFilter').addEventListener('change', filterBooks);
document.getElementById('statusFilter').addEventListener('change', filterBooks);

function filterBooks() {
    const q = document.getElementById('bookSearch').value.toLowerCase();
    const cat = document.getElementById('categoryFilter').value;
    const status = document.getElementById('statusFilter').value;
    document.querySelectorAll('#bookGrid .book-card').forEach(card => {
        const title = card.dataset.title || '';
        const cardCat = card.dataset.category || '';
        const copies = parseInt(card.dataset.copies || 0);
        let show = true;
        if (q && !title.includes(q)) show = false;
        if (cat && cardCat !== cat) show = false;
        if (status === 'available' && copies === 0) show = false;
        if (status === 'low' && (copies > 2 || copies === 0)) show = false;
        if (status === 'out' && copies > 0) show = false;
        card.style.display = show ? '' : 'none';
    });
}
</script>
@endpush
@endsection
