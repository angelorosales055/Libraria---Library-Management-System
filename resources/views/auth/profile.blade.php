@extends('layout.portal')
@section('title', 'My Profile')
@section('content')

<div style="text-align:center;margin-bottom:36px">
    <h1 style="font-size:28px;font-weight:700;margin-bottom:6px">My Profile</h1>
    <p style="color:var(--muted);font-size:14px;margin:0">Update your personal information</p>
</div>

<div style="display:grid;grid-template-columns:320px 1fr;gap:32px;max-width:1100px;margin:0 auto;align-items:start">
    {{-- Left: Profile Card --}}
    <div class="card" style="text-align:center;padding:36px 28px">
        <div style="width:96px;height:96px;border-radius:50%;background:linear-gradient(135deg, var(--accent), #3a9e8c);display:flex;align-items:center;justify-content:center;margin:0 auto 18px;font-size:36px;font-weight:700;color:#06131f">
            {{ strtoupper(substr($user->name, 0, 1)) }}
        </div>
        <div style="font-size:20px;font-weight:700;margin-bottom:5px">{{ $user->name }}</div>
        <div style="color:var(--muted);font-size:14px;margin-bottom:14px">{{ ucfirst($user->type ?? 'Member') }}</div>
        <div style="display:inline-flex;align-items:center;gap:6px;padding:7px 16px;border-radius:999px;background:rgba(77,212,182,0.14);color:var(--accent);font-size:13px;font-weight:600">
            <i class="fas fa-check-circle" style="font-size:10px"></i> Active
        </div>
        <div style="margin-top:24px;padding-top:24px;border-top:1px solid rgba(255,255,255,0.08);text-align:left">
            <div style="display:flex;justify-content:space-between;font-size:13px;margin-bottom:12px">
                <span style="color:var(--muted)">Member ID</span>
                <span style="font-weight:600">{{ $user->member_id }}</span>
            </div>
            <div style="display:flex;justify-content:space-between;font-size:13px;margin-bottom:12px">
                <span style="color:var(--muted)">Email</span>
                <span style="font-weight:600">{{ $user->email }}</span>
            </div>
            <div style="display:flex;justify-content:space-between;font-size:13px">
                <span style="color:var(--muted)">Joined</span>
                <span style="font-weight:600">{{ $user->created_at?->format('M Y') ?? 'N/A' }}</span>
            </div>
        </div>
    </div>

    {{-- Right: Edit Form --}}
    <div class="card">
        <div class="card-body" style="padding:32px">
            <div style="font-size:17px;font-weight:700;margin-bottom:24px">Personal Information</div>
            <form method="POST" action="{{ route('profile.update') }}">
                @csrf
                @method('PUT')

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:20px">
                    <div class="form-group" style="margin-bottom:0">
                        <label class="form-label">Full Name</label>
                        <input type="text" name="name" class="form-control" value="{{ old('name', $user->name) }}" required>
                        @error('name')<div style="color:var(--danger);font-size:13px;margin-top:6px">{{ $message }}</div>@enderror
                    </div>
                    <div class="form-group" style="margin-bottom:0">
                        <label class="form-label">Email Address</label>
                        <input type="email" name="email" class="form-control" value="{{ old('email', $user->email) }}" required>
                        @error('email')<div style="color:var(--danger);font-size:13px;margin-top:6px">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:28px">
                    <div class="form-group" style="margin-bottom:0">
                        <label class="form-label">Contact Number</label>
                        <input type="text" name="contact" class="form-control" value="{{ old('contact', $user->contact) }}" placeholder="Optional">
                    </div>
                    <div class="form-group" style="margin-bottom:0">
                        <label class="form-label">Address</label>
                        <input type="text" name="address" class="form-control" value="{{ old('address', $user->address) }}" placeholder="Optional">
                    </div>
                </div>

                <div style="display:flex;gap:12px;align-items:center">
                    <a href="{{ route('password.change') }}" class="btn btn-outline" style="padding:12px 20px;border-radius:14px;font-size:13px">
                        <i class="fas fa-lock" style="margin-right:6px"></i> Change Password
                    </a>
                    <button type="submit" class="btn btn-primary" style="padding:12px 24px;border-radius:14px;font-size:13px">
                        <i class="fas fa-save" style="margin-right:6px"></i> Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
