@extends('layout.app')
@section('title', 'Edit Staff Account')
@section('content')

<div class="page-header-row mb-6">
    <div>
        <h1 class="page-title">Edit Staff Account</h1>
        <p class="page-subtitle">{{ $user->name }}</p>
    </div>
    <a href="{{ route('users.index') }}" class="btn btn-outline">
        <i class="fas fa-arrow-left"></i> Back
    </a>
</div>

<div style="max-width:540px">
    <div class="card">
        <div class="card-header"><span class="card-title">Account Details</span></div>
        <div class="card-body">
            <form method="POST" action="{{ route('users.update', $user) }}">
                @csrf @method('PUT')

                <div class="form-group">
                    <label class="form-label">Full Name</label>
                    <input type="text" name="name" class="form-control" value="{{ old('name',$user->name) }}" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" value="{{ old('email',$user->email) }}" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Role</label>
                    <select name="role" class="form-control" required>
                        <option value="librarian" {{ old('role',$user->role)==='librarian'?'selected':'' }}>Librarian</option>
                        <option value="admin"     {{ old('role',$user->role)==='admin'?'selected':'' }}>Admin</option>
                    </select>
                </div>

                <div style="background:var(--cream-dark);border-radius:var(--radius);padding:14px;margin-bottom:16px">
                    <div style="font-size:12px;font-weight:600;color:var(--text-mid);margin-bottom:10px">Change Password (leave blank to keep current)</div>
                    <div class="form-group" style="margin-bottom:10px">
                        <label class="form-label">New Password</label>
                        <input type="password" name="password" class="form-control" placeholder="Min. 8 characters">
                    </div>
                    <div class="form-group" style="margin-bottom:0">
                        <label class="form-label">Confirm Password</label>
                        <input type="password" name="password_confirmation" class="form-control">
                    </div>
                </div>

                <div class="flex gap-2 justify-end">
                    <a href="{{ route('users.index') }}" class="btn btn-outline">Cancel</a>
                    <button type="submit" class="btn btn-gold">Update Account</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
