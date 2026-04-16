@extends('layout.app')
@section('title', 'Edit Book')
@section('content')

<div class="page-header-row mb-6">
    <div>
        <h1 class="page-title">Edit Book</h1>
        <p class="page-subtitle">{{ $book->title }}</p>
    </div>
    <a href="{{ route('books.index') }}" class="btn btn-outline">
        <i class="fas fa-arrow-left"></i> Back to Catalog
    </a>
</div>

<div style="max-width:700px">
    <div class="card">
        <div class="card-header"><span class="card-title">Book Information</span></div>
        <div class="card-body">
            <form method="POST" action="{{ route('books.update', $book) }}" enctype="multipart/form-data">
                @csrf @method('PUT')

                <div class="grid-2">
                    <div class="form-group">
                        <label class="form-label">ISBN</label>
                        <input type="text" class="form-control" value="{{ $book->isbn }}" readonly style="background:var(--cream);color:var(--text-light)">
                        <small style="font-size:11px;color:var(--text-light)">ISBN cannot be changed</small>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Accession No.</label>
                        <input type="text" class="form-control" value="{{ $book->accession_no }}" readonly style="background:var(--cream);color:var(--text-light)">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Book Title</label>
                    <input type="text" name="title" class="form-control {{ $errors->has('title')?'is-invalid':'' }}"
                           value="{{ old('title', $book->title) }}" required>
                    @error('title')<div style="font-size:12px;color:var(--danger);margin-top:3px">{{ $message }}</div>@enderror
                </div>

                <div class="grid-2">
                    <div class="form-group">
                        <label class="form-label">Author</label>
                        <input type="text" name="author" class="form-control"
                               value="{{ old('author', $book->author) }}" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Category</label>
                        <select name="category_id" class="form-control" required>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" {{ (old('category_id',$book->category_id)==$cat->id)?'selected':'' }}>
                                    {{ $cat->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid-2">
                    <div class="form-group">
                        <label class="form-label">No. of Copies</label>
                        <input type="number" name="copies" class="form-control" min="1"
                               value="{{ old('copies', $book->copies) }}" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Shelf / Section</label>
                        <input type="text" name="shelf" class="form-control"
                               value="{{ old('shelf', $book->shelf) }}">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="3">{{ old('description', $book->description) }}</textarea>
                </div>

                <div class="form-group">
                    <label class="form-label">Cover Image</label>
                    @if($book->cover_image)
                    <div style="margin-bottom:8px">
                        <img src="{{ asset('storage/'.$book->cover_image) }}" style="height:80px;border-radius:4px;object-fit:cover">
                        <div style="font-size:11px;color:var(--text-light);margin-top:4px">Current cover</div>
                    </div>
                    @endif
                    <input type="file" name="cover_image" class="form-control" accept="image/*">
                </div>

                <div class="flex gap-2 justify-end mt-4">
                    <a href="{{ route('books.index') }}" class="btn btn-outline">Cancel</a>
                    <button type="submit" class="btn btn-gold">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    {{-- DELETE --}}
    <div class="card mt-4" style="border-top:3px solid var(--danger)">
        <div class="card-body" style="display:flex;align-items:center;justify-content:space-between;gap:16px">
            <div>
                <div style="font-size:14px;font-weight:600;color:var(--danger)">Delete Book</div>
                <div style="font-size:12px;color:var(--text-light)">Only allowed if no active checkouts. This action is permanent.</div>
            </div>
            <form method="POST" action="{{ route('books.destroy', $book) }}"
                  onsubmit="return confirm('Delete \'{{ $book->title }}\'? This cannot be undone.')">
                @csrf @method('DELETE')
                <button class="btn btn-danger btn-sm">Delete Book</button>
            </form>
        </div>
    </div>
</div>
@endsection
