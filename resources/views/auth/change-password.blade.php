@extends('layout.portal')
@section('title', 'Change Password')
@section('content')

<div class="section-header" style="margin-bottom:28px">
    <div>
        <h2>Change Password</h2>
        <p style="color:var(--muted);margin-top:8px">Update your account password to keep your account secure.</p>
    </div>

<div style="display:grid;grid-template-columns:1fr 320px;gap:24px;align-items:start;max-width:900px">
    {{-- Left: Form --}}
    <div class="card">
        <div class="card-body" style="padding:28px">
            <form method="POST" action="{{ route('password.update') }}" id="passwordForm">
                @csrf
                @method('PUT')

                <div class="form-group" style="margin-bottom:20px">
                    <label class="form-label">Current Password</label>
                    <div style="position:relative">
                        <input type="password" name="current_password" id="current_password" class="form-control" required style="padding-right:44px">
                        <button type="button" onclick="toggleVisibility('current_password', this)" style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;color:var(--muted);cursor:pointer">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    @error('current_password')<div style="color:var(--danger);font-size:13px;margin-top:6px">{{ $message }}</div>@enderror
                </div>

                <div class="form-group" style="margin-bottom:20px">
                    <label class="form-label">New Password</label>
                    <div style="position:relative">
                        <input type="password" name="password" id="password" class="form-control" required minlength="8" style="padding-right:44px" oninput="checkStrength(this.value)">
                        <button type="button" onclick="toggleVisibility('password', this)" style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;color:var(--muted);cursor:pointer">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    {{-- Strength Meter --}}
                    <div style="margin-top:10px">
                        <div style="display:flex;gap:4px;margin-bottom:6px">
                            <div id="strength-bar-1" style="flex:1;height:4px;border-radius:2px;background:rgba(255,255,255,0.1);transition:background .3s"></div>
                            <div id="strength-bar-2" style="flex:1;height:4px;border-radius:2px;background:rgba(255,255,255,0.1);transition:background .3s"></div>
                            <div id="strength-bar-3" style="flex:1;height:4px;border-radius:2px;background:rgba(255,255,255,0.1);transition:background .3s"></div>
                            <div id="strength-bar-4" style="flex:1;height:4px;border-radius:2px;background:rgba(255,255,255,0.1);transition:background .3s"></div>
                        <div id="strength-text" style="font-size:12px;color:var(--muted)">Enter at least 8 characters</div>
                    @error('password')<div style="color:var(--danger);font-size:13px;margin-top:6px">{{ $message }}</div>@enderror
                </div>

                <div class="form-group" style="margin-bottom:24px">
                    <label class="form-label">Confirm New Password</label>
                    <div style="position:relative">
                        <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" required style="padding-right:44px">
                        <button type="button" onclick="toggleVisibility('password_confirmation', this)" style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;color:var(--muted);cursor:pointer">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>

                <div style="display:flex;gap:12px;align-items:center">
                    <a href="{{ route('profile') }}" class="btn btn-outline" style="padding:12px 20px;border-radius:14px;font-size:13px">
                        <i class="fas fa-arrow-left" style="margin-right:6px"></i> Back to Profile
                    </a>
                    <button type="submit" class="btn btn-primary" style="padding:12px 24px;border-radius:14px;font-size:13px">
                        <i class="fas fa-lock" style="margin-right:6px"></i> Update Password
                    </button>
                </div>
            </form>
        </div>

    {{-- Right: Tips --}}
    <div class="card" style="padding:24px">
        <div style="font-size:14px;font-weight:700;margin-bottom:16px"><i class="fas fa-shield-alt" style="margin-right:8px;color:var(--accent)"></i>Password Tips</div>
        <ul style="list-style:none;padding:0;margin:0;display:flex;flex-direction:column;gap:12px">
            <li style="display:flex;align-items:flex-start;gap:10px;font-size:12px;color:var(--muted)">
                <i class="fas fa-check-circle" style="margin-top:2px;color:var(--accent);flex-shrink:0"></i>
                Use at least 8 characters
            </li>
            <li style="display:flex;align-items:flex-start;gap:10px;font-size:12px;color:var(--muted)">
                <i class="fas fa-check-circle" style="margin-top:2px;color:var(--accent);flex-shrink:0"></i>
                Mix uppercase &amp; lowercase letters
            </li>
            <li style="display:flex;align-items:flex-start;gap:10px;font-size:12px;color:var(--muted)">
                <i class="fas fa-check-circle" style="margin-top:2px;color:var(--accent);flex-shrink:0"></i>
                Include numbers and symbols
            </li>
            <li style="display:flex;align-items:flex-start;gap:10px;font-size:12px;color:var(--muted)">
                <i class="fas fa-check-circle" style="margin-top:2px;color:var(--accent);flex-shrink:0"></i>
                Avoid common words or sequences
            </li>
        </ul>
    </div>

@push('scripts')
<script>
function toggleVisibility(inputId, btn) {
    const input = document.getElementById(inputId);
    const icon = btn.querySelector('i');
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}

function checkStrength(password) {
    let strength = 0;
    if (password.length >= 8) strength++;
    if (password.length >= 12) strength++;
    if (/[A-Z]/.test(password) && /[a-z]/.test(password)) strength++;
    if (/[0-9]/.test(password) && /[^A-Za-z0-9]/.test(password)) strength++;

    const colors = ['rgba(255,255,255,0.1)', '#ff6b6b', '#f0ad4e', '#4dd4b6', '#4dd4b6'];
    const texts = ['Enter at least 8 characters', 'Weak', 'Fair', 'Good', 'Strong'];

    for (let i = 1; i <= 4; i++) {
        document.getElementById('strength-bar-' + i).style.background = i <= strength ? colors[strength] : 'rgba(255,255,255,0.1)';
    }
    document.getElementById('strength-text').textContent = texts[strength];
    document.getElementById('strength-text').style.color = strength > 0 ? colors[strength] : 'var(--muted)';
}
</script>
@endpush
@endsection
