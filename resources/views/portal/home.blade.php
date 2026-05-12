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
    <div class="summary-card"><span>Active Borrowings</span><h2>{{ $activeBorrowings }}</h2></div>
    <div class="summary-card"><span>Overdue Books</span><h2>{{ $overdueCount }}</h2></div>
    <div class="summary-card"><span>Total Fees</span><h2>₱{{ number_format($outstandingFees, 2) }}</h2></div>
    <div class="summary-card"><span>Total Transactions</span><h2>{{ $totalTransactions }}</h2></div>
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
            @php
                $transactionId = $note['transaction_id'] ?? null;
                $notifLink = route('portal.transactions');

                if (($note['type'] ?? '') === 'payment_success' && $transactionId) {
                    $notifLink = route('receipt.show', $transactionId);
                } elseif (($note['type'] ?? '') === 'book_damaged') {
                    $notifLink = route('portal.fines');
                } elseif (in_array(($note['type'] ?? ''), ['borrow_success','return_approved','rejected','overdue_reminder'], true)) {
                    $notifLink = route('portal.transactions');
                }
            @endphp

            <div class="book-card"
                 style="padding:22px;{{ (($note['type'] ?? '') === 'book_damaged') ? 'border-left: 5px solid #ff5722; background: linear-gradient(135deg, rgba(255,87,34,0.08), rgba(255,152,0,0.04));' : '' }}"
                 data-notif-link="{{ $notifLink }}"
                 onclick="handleNotifClick(event, this)">
                <div class="book-category" style="{{ (($note['type'] ?? '') === 'book_damaged') ? 'background: rgba(255,87,34,0.15); color: #ff5722;' : '' }}">
                    @if((($note['type'] ?? '') === 'book_damaged'))
                        <i class="fas fa-book-damaged" style="margin-right:6px;"></i>
                    @endif
                    {{ ucfirst(str_replace('_', ' ', $note['type'])) }}
                </div>
                <div class="book-title" style="margin-top:10px; margin-bottom:10px;">{{ $note['title'] }}</div>
                <div class="book-author" style="font-size:13px; color:var(--muted);">{{ $note['message'] }}</div>
                <div style="margin-top:16px; color:var(--muted); font-size:12px;">{{ $note['date'] }}</div>
            </div>
        @empty
            <div style="color:var(--muted);padding:32px;background:var(--surface);border-radius:20px">No notifications yet.</div>
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
        <div class="book-card" data-book-details
            data-book-title="{{ $book->title }}"
            data-book-author="{{ $book->author }}"
            data-book-description="{{ $book->description }}"
            data-book-cover="{{ $book->cover_image ? asset('storage/'.$book->cover_image) : '' }}"
            data-book-category="{{ $book->category?->name ?? 'General' }}"
            data-book-isbn="{{ $book->isbn }}">
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
                @if($book->is_available)
                    <button type="button" class="btn-sm" onclick="openBorrowModal({{ $book->id }}, '{{ addslashes($book->title) }}', '{{ addslashes($book->author) }}', '{{ $book->cover_image ? asset('storage/'.$book->cover_image) : '' }}', {{ $book->category?->loan_period_days ?? 14 }})">Request Book</button>
                @else
                    <form method="POST" action="{{ route('portal.borrow', $book) }}" style="display:inline">
                        @csrf
                        <button type="submit" class="btn-sm btn-outline">Place Hold</button>
                    </form>
                @endif
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
            <a href="{{ route('portal.collection', ['category_id' => $category->id]) }}" class="book-card" style="padding:22px;text-decoration:none">
                <div style="font-size:13px;color:var(--accent);text-transform:uppercase;font-weight:700;margin-bottom:12px;letter-spacing:0.5px">{{ $category->name }}</div>
                <div style="font-size:14px;color:var(--muted);line-height:1.5">{{ $category->books_count ?? 0 }} {{ Str::plural('book', $category->books_count ?? 0) }} available</div>
                <div style="margin-top:14px;display:flex;align-items:center;gap:6px;font-size:12px;color:var(--accent)">
                    <span>Browse</span>
                    <i class="fas fa-arrow-right" style="font-size:10px"></i>
                </div>
            </a>
        @endforeach
    </div>
</div>

<div class="modal-overlay" id="borrowModal">
    <div class="modal" role="dialog" aria-modal="true" aria-labelledby="borrowModalTitle">
        <div class="modal-header">
            <div>
                <div class="modal-title" id="borrowModalTitle">Request Book</div>
                <div id="borrowModalSubtitle" style="color:var(--muted);font-size:13px;margin-top:6px">Confirm your request for staff approval</div>
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
            <label for="borrowDuration">Request Duration</label>
            <select id="borrowDuration" class="form-control">
                <option value="3">3 days</option>
                <option value="7">7 days</option>
                <option value="14" selected>14 days</option>
                <option value="21">21 days</option>
                <option value="30">30 days</option>
            </select>
            <label for="borrowNotes">Notes (optional)</label>
            <input id="borrowNotes" type="text" class="form-control" placeholder="Add a note for this borrow">
            <p style="margin-top:12px;color:var(--muted);font-size:13px">By clicking Submit Request, you agree to return this book on time or face late fees.</p>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn-outline" onclick="closeBorrowModal()">Cancel</button>
            <button type="button" class="btn-primary" onclick="submitBorrowForm()">Submit Request</button>
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
    if (cover) { coverEl.src = cover; coverEl.style.display = 'block'; }
    else { coverEl.src = ''; coverEl.style.display = 'block'; }
    document.getElementById('borrowDuration').value = defaultDays;
    updateBorrowDueDate(defaultDays);
    document.getElementById('borrowModal').classList.add('open');
}
function updateBorrowDueDate(days) {
    const dueDate = new Date();
    dueDate.setDate(dueDate.getDate() + parseInt(days, 10));
    const formatted = new Intl.DateTimeFormat('en-US', { month: 'long', day: 'numeric', year: 'numeric' }).format(dueDate);
    document.getElementById('borrowDueDate').value = dueDate.toISOString().slice(0,10);
    document.getElementById('borrowModalDueDate').textContent = 'Due on: ' + formatted;
}
document.getElementById('borrowDuration')?.addEventListener('change', function() { updateBorrowDueDate(this.value); });
function closeBorrowModal() { document.getElementById('borrowModal').classList.remove('open'); }
function submitBorrowForm() {
    const form = document.getElementById('borrowForm');
    const notes = document.getElementById('borrowNotes').value.trim();
    const action = '{{ route('portal.borrow', ['book' => 0]) }}'.replace('/0', '/' + selectedBookId);
    document.getElementById('borrowNotesInput').value = notes;
    form.action = action;
    form.submit();
}
document.getElementById('borrowModal')?.addEventListener('click', function(e) { if (e.target === this) closeBorrowModal(); });
</script>
@endpush
