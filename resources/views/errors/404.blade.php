@extends('layout.app')
@section('title', 'Page Not Found')
@section('content')
<div style="text-align:center;padding:80px 24px">
    <div style="font-size:64px;margin-bottom:16px">📭</div>
    <h1 style="font-size:28px;font-weight:700;color:var(--text-dark);margin-bottom:8px">Page Not Found</h1>
    <p style="color:var(--text-light);margin-bottom:24px">The page you're looking for doesn't exist or has been moved.</p>
    <a href="{{ route('dashboard') }}" class="btn btn-primary">← Back to Dashboard</a>
</div>
@endsection
