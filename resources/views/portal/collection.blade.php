@extends('layout.portal')
@section('title', 'Collection')
@section('content')
<div class="section">
    <div class="section-header">
        <div>
            <h2>Book Collection</h2>
            <p>Search and borrow from the library catalog.</p>
        </div>
    </div>
    <div class="filters-row">
        <form method="GET" action="{{ route('portal.collection') }}" style="display:flex;gap:12px;flex-wrap:wrap;align-items:center;">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search books, authors, ISBN..." class="filter-input">
            <select name="category_id" class="filter-input">
                <option value="">All Categories</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                @endforeach
            </select>
            <select name="status" class="filter-input">
                <option value="">All Status</option>
                <option value="available" {{ request('status') === 'available' ? 'selected' : '' }}>Available</option>
            </select>
            <button type="submit" class="btn btn-primary">Filter</button>
        </form>
    </div>

    <div class="books-grid">
        @forelse($books as $book)
        <div class="book-card">
            @if($book->cover_image)
                <img src="{{ asset('storage/'.$book->cover_image) }}" alt="{{ $book->title }}">
            @else
                <div class="book-cover-placeholder" style="background: rgba(255,255,255,0.05); display:flex; align-items:center; justify-content:center;">📘</div>
            @endif
            <div class="book-card-body">
                <div class="book-category">{{ $book->category?->name ?? 'General' }}</div>
                <div class="book-title">{{ $book->title }}</div>
                <div class="book-author">{{ $book->author }}</div>
                <div class="book-meta">
                    <span class="badge available">{{ $book->available_copies }} left</span>
                    <span style="color: var(--muted); font-size:12px;">{{ $book->isbn }}</span>
                </div>
                @if($book->is_available)
                    <button type="button" class="btn-sm" onclick="openBorrowModal({{ $book->id }}, '{{ addslashes($book->title) }}', '{{ addslashes($book->author) }}', '{{ $book->cover_image ? asset('storage/'.$book->cover_image) : '' }}', {{ $book->category?->loan_period_days ?? 14 }})">Borrow Book</button>
                @else
                    <form method="POST" action="{{ route('portal.borrow', $book) }}" style="display:inline">
                        @csrf
                        <button type="submit" class="btn-sm btn-outline">Place Hold</button>
                    </form>
                @endif
            </div>
        </div>
        @empty
            <div style="color:var(--muted);padding:32px;background:var(--surface);border-radius:20px;grid-column:1/-1;">No books found.</div>
        @endforelse
    </div>

    <div style="margin-top:24px;">{{ $books->links() }}</div>
</div>

<div class="modal-overlay" id="borrowModal">
    <div class="modal" role="dialog" aria-modal="true" aria-labelledby="borrowModalTitle">
        <div class="modal-header">
            <div>
                <div class="modal-title" id="borrowModalTitle">Borrow Book</div>
                <div id="borrowModalSubtitle" style="color:var(--muted);font-size:13px;margin-top:6px">Confirm your borrow request</div>
            </div>
            <button type="button" class="modal-close" onclick="closeBorrowModal()">&times;</button>
        </div>
        <div class="modal-body">
            <div style="display:flex;gap:16px;align-items:center;margin-bottom:18px">
                <img id="borrowModalCover" src="" alt="Book cover" style="width:84px;height:110px;border-radius:18px;object-fit:cover;background:rgba(255,255,255,0.06)">
                <div>
                    <div id="borrowModalTitleText" style="font-size:16px;font-weight:700;margin-bottom:6px"></div>
                    <div id="borrowModalAuthor" style="color:var(--muted);font-size:13px"></div>
                    <div id="borrowModalDueDate" style="color:var(--muted);font-size:12px;margin-top:8px"></div>
                </div>
            </div>
            <label for="borrowDuration">Borrow Duration</label>
            <select id="borrowDuration" class="form-control">
                <option value="3">3 days</option>
                <option value="7">7 days</option>
                <option value="14" selected>14 days</option>
                <option value="21">21 days</option>
                <option value="30">30 days</option>
            </select>
            <label for="borrowNotes">Notes (optional)</label>
            <input id="borrowNotes" type="text" class="form-control" placeholder="Add a note for this borrow">
            <p style="margin-top:12px;color:var(--muted);font-size:13px">By clicking Confirm Borrow, you agree to return this book on time or face late fees.</p>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn-outline" onclick="closeBorrowModal()">Cancel</button>
            <button type="button" class="btn-primary" onclick="submitBorrowForm()">Confirm Borrow</button>
        </div>
        <form id="borrowForm" method="POST" style="display:none" action="">
            @csrf
            <input type="hidden" name="due_date" id="borrowDueDate">
            <input type="hidden" name="notes" id="borrowNotesInput">
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
let selectedBookId = null;
function openBorrowModal(id, title, author, cover, defaultDays) {
    selectedBookId = id;
    document.getElementById('borrowModalTitleText').textContent = title;
    document.getElementById('borrowModalAuthor').textContent = author;
    const coverEl = document.getElementById('borrowModalCover');
    if (cover) {
        coverEl.src = cover;
        coverEl.style.display = 'block';
    } else {
        coverEl.src = '';
        coverEl.style.display = 'block';
    }
    document.getElementById('borrowDuration').value = defaultDays;
    updateBorrowDueDate(defaultDays);
    document.getElementById('borrowModal').classList.add('open');
}

function updateBorrowDueDate(days) {
    const dueDate = new Date();
    dueDate.setDate(dueDate.getDate() + parseInt(days, 10));
    const formatted = new Intl.DateTimeFormat('en-US', { month: 'long', day: 'numeric', year: 'numeric' }).format(dueDate);
    document.getElementById('borrowDueDate').value = dueDate.toISOString().slice(0,10);
    document.getElementById('borrowModalDueDate').textContent = `Due on: ${formatted}`;
}

document.getElementById('borrowDuration')?.addEventListener('change', function() {
    updateBorrowDueDate(this.value);
});
function closeBorrowModal() {
    document.getElementById('borrowModal').classList.remove('open');
}
function submitBorrowForm() {
    const form = document.getElementById('borrowForm');
    const notes = document.getElementById('borrowNotes').value.trim();
    const action = '{{ route('portal.borrow', ['book' => 0]) }}'.replace('/0', '/' + selectedBookId);
    document.getElementById('borrowNotesInput').value = notes;
    form.action = action;
    form.submit();
}
document.getElementById('borrowModal')?.addEventListener('click', function(e) {
    if (e.target === this) closeBorrowModal();
});
</script>
@endpush
