@extends('layout.app')
@section('title', 'Access Denied')
@section('content')
<div style="text-align:center;padding:80px 24px">
    <div style="font-size:64px;margin-bottom:16px">🔒</div>
    <h1 style="font-size:28px;font-weight:700;color:var(--text-dark);margin-bottom:8px">Access Denied</h1>
    <p style="color:var(--text-light);margin-bottom:24px">You don't have permission to view this page.</p>
    <a href="{{ route('dashboard') }}" class="btn btn-primary">← Back to Dashboard</a>
</div>
@endsection
