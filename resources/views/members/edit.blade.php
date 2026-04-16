@extends('layout.app')
@section('title', 'Edit Member')
@section('content')

<div class="page-header-row mb-6">
    <div>
        <h1 class="page-title">Edit Member</h1>
        <p class="page-subtitle">{{ $member->name }} · {{ $member->member_id }}</p>
    </div>
    <a href="{{ route('members.index') }}" class="btn btn-outline">
        <i class="fas fa-arrow-left"></i> Back
    </a>
</div>

<div style="max-width:600px">
    <div class="card">
        <div class="card-header"><span class="card-title">Member Information</span></div>
        <div class="card-body">
            <form method="POST" action="{{ route('members.update', $member) }}">
                @csrf @method('PUT')

                <div class="form-group">
                    <label class="form-label">Full Name</label>
                    <input type="text" name="name" class="form-control" value="{{ old('name',$member->name) }}" required>
                </div>
                <div class="grid-2">
                    <div class="form-group">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" value="{{ old('email',$member->email) }}">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Contact No.</label>
                        <input type="text" name="contact" class="form-control" value="{{ old('contact',$member->contact) }}">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Member Type</label>
                    <select name="type" class="form-control" required>
                        <option value="student"  {{ old('type',$member->type)==='student'?'selected':'' }}>Student</option>
                        <option value="faculty"  {{ old('type',$member->type)==='faculty'?'selected':'' }}>Faculty</option>
                        <option value="public"   {{ old('type',$member->type)==='public'?'selected':'' }}>Public</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Address</label>
                    <input type="text" name="address" class="form-control" value="{{ old('address',$member->address) }}">
                </div>

                <div class="flex gap-2 justify-end mt-4">
                    <a href="{{ route('members.index') }}" class="btn btn-outline">Cancel</a>
                    <button type="submit" class="btn btn-gold">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
