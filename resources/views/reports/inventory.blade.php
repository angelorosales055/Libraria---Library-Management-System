@extends('layout.app')
@section('title', 'Book Inventory Report')
@section('content')

<div class="page-header-row mb-6">
    <div>
        <h1 class="page-title">Book Inventory</h1>
        <p class="page-subtitle">Full catalog with availability status</p>
    </div>
    <div class="flex gap-2">
        <a href="{{ route('reports.export', ['report' => 'inventory']) }}" class="btn btn-gold btn-sm">
            <i class="fas fa-file-pdf"></i> Export PDF
        </a>
        <a href="{{ route('reports.index') }}" class="btn btn-outline btn-sm">
            <i class="fas fa-arrow-left"></i> Back
        </a>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <span class="card-title">All Books</span>
        <span class="badge badge-info">{{ $books->count() }} titles</span>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Accession No.</th>
                    <th>Title</th>
                    <th>Author</th>
                    <th>Category</th>
                    <th>ISBN</th>
                    <th>Shelf</th>
                    <th>Total</th>
                    <th>Available</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($books as $book)
                @php $avail = $book->available_copies ?? $book->copies; @endphp
                <tr>
                    <td><code style="font-size:11px">{{ $book->accession_no ?? '—' }}</code></td>
                    <td style="font-weight:500">{{ $book->title }}</td>
                    <td>{{ $book->author }}</td>
                    <td>{{ $book->category?->name ?? '—' }}</td>
                    <td><code style="font-size:11px">{{ $book->isbn }}</code></td>
                    <td style="font-size:12px;color:var(--text-light)">{{ $book->shelf ?? '—' }}</td>
                    <td style="text-align:center">{{ $book->copies }}</td>
                    <td style="text-align:center;font-weight:600;color:{{ $avail>0?'var(--success)':'var(--danger)' }}">{{ $avail }}</td>
                    <td>
                        @if($avail > 2)
                            <span class="badge badge-success">Available</span>
                        @elseif($avail > 0)
                            <span class="badge badge-warning">Low Stock</span>
                        @else
                            <span class="badge badge-danger">Out of Stock</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="9" style="text-align:center;padding:24px;color:var(--text-light)">No books found</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
