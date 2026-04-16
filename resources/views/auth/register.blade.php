@extends('layout.auth')
@section('title', 'Register')
@section('content')
<h2 class="auth-title">Create account</h2>
<p class="auth-subtitle">Join Libraria to access the library system</p>

<form method="POST" action="{{ route('register') }}">
    @csrf
    <div class="form-group">
        <label class="form-label">Username</label>
        <input type="text" name="name" class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}"
               placeholder="Juan dela Cruz" value="{{ old('name') }}" required>
        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
        <small class="form-text text-muted">This username must be unique and cannot be taken by another user.</small>
    </div>
    <div class="form-group">
        <label class="form-label">Email Address</label>
        <input type="email" name="email" class="form-control {{ $errors->has('email') ? 'is-invalid' : '' }}"
               placeholder="your@email.com" value="{{ old('email') }}" required>
        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="form-group">
        <label class="form-label">Member ID (optional)</label>
        <input type="text" name="member_id" class="form-control"
               placeholder="e.g. MBR-2025-0001" value="{{ old('member_id', $nextMemberId ?? '') }}">
        <small class="form-text text-muted">Leave blank to auto-generate a professional member ID. Suggested: {{ $nextMemberId ?? 'MBR-YYYY-0001' }}</small>
    </div>
    <div class="form-group">
        <label class="form-label">Password</label>
        <input type="password" name="password" class="form-control {{ $errors->has('password') ? 'is-invalid' : '' }}"
               placeholder="Min. 8 characters" required>
        @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="form-group">
        <label class="form-label">Confirm Password</label>
        <input type="password" name="password_confirmation" class="form-control" placeholder="Repeat password" required>
    </div>
    <button type="submit" class="btn-auth">Create Account</button>
</form>

<div class="auth-footer">
    Already have an account? <a href="{{ route('login') }}">Log in</a>
</div>
@endsection
