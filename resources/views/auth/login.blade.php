@extends('layout.auth')
@section('title', 'Login')
@section('content')
<h2 class="auth-title">Welcome back</h2>
<p class="auth-subtitle">Sign in to your Libraria account</p>

<form method="POST" action="{{ route('login') }}">
    @csrf
    <div class="form-group">
        <label class="form-label">Email Address</label>
        <input type="email" name="email" class="form-control {{ $errors->has('email') ? 'is-invalid' : '' }}"
               placeholder="your@email.com" value="{{ old('email') }}" required autofocus>
        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="form-group">
        <label class="form-label">Password</label>
        <input type="password" name="password" class="form-control {{ $errors->has('password') ? 'is-invalid' : '' }}"
               placeholder="••••••••" required>
        @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <button type="submit" class="btn-auth">Log In</button>
</form>

<div class="auth-footer">
    Don't have an account? <a href="{{ route('register') }}">Sign up</a>
</div>
@endsection
