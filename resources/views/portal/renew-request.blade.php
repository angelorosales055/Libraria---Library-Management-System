@extends('layout.portal')
@section('title', 'Request Book Renewal')
@section('content')
<div class="max-w-md mx-auto">
    <div class="card">
        <div class="card-header">
            <h2>Request Renewal</h2>
            <p>Submit renewal request for admin approval</p>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('portal.renew.request', $txn) }}">
                @csrf
                <div class="form-group">
                    <label>Book</label>
                    <p class="font-semibold">{{ $txn->book->title }}</p>
                </div>
                <div class="grid-2">
                    <div>
                        <label>Current Due</label>
                        <p>{{ $txn->due_date->format('M j, Y') }}</p>
                    </div>
                    <div>
                        <label>Renewals Used</label>
                        <p>{{ $txn->renewal_count }}/{{ $txn->max_renewals }}</p>
                    </div>
                </div>
                <div class="form-group">
                    <label>Proposed Due Date</label>
                    <input type="date" name="due_date" value="{{ $proposedDueDate->format('Y-m-d') }}" class="form-control" required>
                    <p class="text-xs text-muted">Admin will adjust if needed</p>
                </div>
                <div class="form-group">
                    <label>Notes (optional)</label>
                    <textarea name="notes" class="form-control" rows="3" placeholder="Reason for renewal..."></textarea>
                </div>
                <div class="flex gap-2">
                    <a href="{{ route('portal.transactions') }}" class="btn btn-outline">Cancel</a>
                    <button type="submit" class="btn btn-primary">Submit Renewal Request</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
