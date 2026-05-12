@extends('layout.app')
@section('title', 'User Management')
@section('content')

<div class="page-header-row mb-6">
    <div>
        <h1 class="page-title">User Management</h1>
        <p class="page-subtitle">Manage staff accounts, members, and access roles</p>
    </div>
    <button class="btn btn-gold" onclick="openModal('addUserModal')">
        <i class="fas fa-user-plus"></i> Add Staff Account
    </button>
</div>

<div class="card mb-6">
    <div class="card-header">
        <span class="card-title">Staff Accounts</span>
        <span class="badge badge-info">{{ $staffUsers->count() ?? 0 }} accounts</span>
    </div>
    <div class="card-body" style="padding:0">
        @forelse($staffUsers ?? [] as $user)
        @php
            $roleColors = ['admin'=>'var(--teal-dark)','librarian'=>'var(--teal-accent)'];
            $rc = $roleColors[$user->role] ?? 'var(--text-mid)';
            $initials = strtoupper(substr($user->name,0,2));
        @endphp
        <div style="display:flex;align-items:center;gap:14px;padding:16px 20px;border-bottom:1px solid rgba(0,0,0,0.05)">
            <div style="width:40px;height:40px;border-radius:50%;background:{{ $rc }};color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:14px;flex-shrink:0">
                {{ $initials }}
            </div>
            <div style="flex:1">
                <div style="font-size:14px;font-weight:600">{{ $user->name }}</div>
                <div style="font-size:12px;color:var(--text-light)">{{ $user->email }}</div>
            </div>
            <div style="display:flex;align-items:center;gap:10px">
                <span class="badge {{ $user->role==='admin'?'badge-info':'badge-success' }}">
                    {{ ucfirst($user->role) }}
                </span>
                <span class="badge {{ ($user->is_active??true)?'badge-success':'badge-danger' }}">
                    {{ ($user->is_active??true)?'Active':'Inactive' }}
                </span>
                <a href="{{ route('users.edit', $user) }}" class="btn btn-outline btn-xs">Edit</a>
                @if($user->id !== auth()->id())
                <form method="POST" action="{{ route('users.destroy', $user) }}" style="display:inline"
                      onsubmit="return confirm('Deactivate this account?')">
                    @csrf @method('DELETE')
                    <button class="btn btn-danger btn-xs">Deactivate</button>
                </form>
                @endif
            </div>
        </div>
        @empty
        <div style="text-align:center;padding:32px;color:var(--text-light)">No staff accounts found</div>
        @endforelse
    </div>
</div>

<div class="card mb-6">
    <div class="card-header">
        <span class="card-title">Active Members</span>
        <span class="badge badge-info">{{ $activeMembers->count() ?? 0 }} members</span>
    </div>
    <div class="card-body" style="padding:0">
        @forelse($activeMembers ?? [] as $member)
        @php
            $avatarColors = ['teal','gold','sage','rust','navy'];
            $colorMap = ['teal'=>'#3d7a6e', 'gold'=>'#c9a84c', 'sage'=>'#8a9b87', 'rust'=>'#a8645f', 'navy'=>'#1a3a3a'];
            $color = $colorMap[$avatarColors[$member->id % count($avatarColors)]] ?? '#6a8a7a';
            $initials = strtoupper(substr($member->name,0,2));
        @endphp
        <div style="display:flex;align-items:center;gap:14px;padding:16px 20px;border-bottom:1px solid rgba(0,0,0,0.05)">
            <div style="width:40px;height:40px;border-radius:50%;background:{{ $color }};color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:14px;flex-shrink:0">
                {{ $initials }}
            </div>
            <div style="flex:1">
                <div style="font-size:14px;font-weight:600">{{ $member->name }}</div>
                <div style="font-size:12px;color:var(--text-light)">{{ $member->member_id ?? 'MBR-'.str_pad($member->id,7,'0',STR_PAD_LEFT) }} · {{ ucfirst($member->type ?? 'member') }}</div>
            </div>
            <div style="display:flex;align-items:center;gap:10px">
                <span class="badge badge-success">Active</span>
                <form method="POST" action="{{ route('users.destroy', $member) }}" style="display:inline"
                      onsubmit="return confirm('Deactivate this member?')">
                    @csrf @method('DELETE')
                    <button class="btn btn-danger btn-xs">Deactivate</button>
                </form>
            </div>
        </div>
        @empty
        <div style="text-align:center;padding:32px;color:var(--text-light)">No active members found</div>
        @endforelse
    </div>
</div>

<div class="card mb-6">
    <div class="card-header">
        <span class="card-title">Deactivated Members</span>
        <span class="badge badge-danger">{{ $deactivatedMembers->count() ?? 0 }} members</span>
    </div>
    <div class="card-body" style="padding:0">
        @forelse($deactivatedMembers ?? [] as $member)
        @php
            $avatarColors = ['teal','gold','sage','rust','navy'];
            $colorMap = ['teal'=>'#3d7a6e', 'gold'=>'#c9a84c', 'sage'=>'#8a9b87', 'rust'=>'#a8645f', 'navy'=>'#1a3a3a'];
            $color = $colorMap[$avatarColors[$member->id % count($avatarColors)]] ?? '#6a8a7a';
            $initials = strtoupper(substr($member->name,0,2));
        @endphp
        <div style="display:flex;align-items:center;gap:14px;padding:16px 20px;border-bottom:1px solid rgba(0,0,0,0.05);opacity:0.65">
            <div style="width:40px;height:40px;border-radius:50%;background:{{ $color }};color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:14px;flex-shrink:0">
                {{ $initials }}
            </div>
            <div style="flex:1">
                <div style="font-size:14px;font-weight:600">{{ $member->name }}</div>
                <div style="font-size:12px;color:var(--text-light)">{{ $member->member_id ?? 'MBR-'.str_pad($member->id,7,'0',STR_PAD_LEFT) }} · {{ ucfirst($member->type ?? 'member') }}</div>
            </div>
            <div style="display:flex;align-items:center;gap:10px">
                <span class="badge badge-danger">Deactivated</span>
            </div>
        </div>
        @empty
        <div style="text-align:center;padding:32px;color:var(--text-light)">No deactivated members</div>
        @endforelse
    </div>
</div>

{{-- INFO BUTTON (matching Figma) --}}
<div style="position:fixed;bottom:24px;right:24px">
    <button class="btn btn-gold btn-sm" onclick="openModal('infoModal')">
        <i class="fas fa-info"></i> INFO
    </button>
</div>

{{-- INFO MODAL --}}
<div class="modal-overlay" id="infoModal">
    <div class="modal" style="max-width:360px">
        <div class="modal-header">
            <span class="modal-title">Design Guide</span>
            <button class="modal-close" onclick="closeModal('infoModal')">×</button>
        </div>
        <div class="modal-body">
            <p style="font-size:13px;color:var(--text-mid);line-height:1.6">
                <strong>Color Palette:</strong><br>
                • Teal Dark: #1a3a3a<br>
                • Teal Accent: #3d7a6e<br>
                • Gold: #c9a84c<br>
                • Cream BG: #f5f0e8<br><br>
                <strong>Logo:</strong> "L" in gold square, DM Serif Display font<br><br>
                <strong>Typography:</strong> DM Sans (UI) + DM Serif Display (headings)
            </p>
        </div>
    </div>
</div>

{{-- ADD USER MODAL --}}
<div class="modal-overlay" id="addUserModal">
    <div class="modal">
        <div class="modal-header">
            <span class="modal-title">Add Staff Account</span>
            <button class="modal-close" onclick="closeModal('addUserModal')">×</button>
        </div>
        <div class="modal-body">
            <form method="POST" action="{{ route('users.store') }}">
                @csrf
                <div class="form-group">
                    <label class="form-label">Full Name</label>
                    <input type="text" name="name" class="form-control" placeholder="Rosa Dela Vega" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" placeholder="staff@library.edu" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Role</label>
                    <select name="role" class="form-control" required>
                        <option value="librarian">Librarian</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" placeholder="Min. 8 characters" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Confirm Password</label>
                    <input type="password" name="password_confirmation" class="form-control" required>
                </div>
                <div class="modal-footer" style="padding:0;border:none;margin-top:4px">
                    <button type="button" class="btn btn-outline" onclick="closeModal('addUserModal')">Cancel</button>
                    <button type="submit" class="btn btn-gold">Create Account</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
