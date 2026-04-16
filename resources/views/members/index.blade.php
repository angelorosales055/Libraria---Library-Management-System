@extends('layout.app')
@section('title', 'Member Management')
@section('content')

<div class="page-header-row mb-4">
    <div>
        <h1 class="page-title">Member Management</h1>
        <p class="page-subtitle">{{ $totalMembers ?? 0 }} registered library members</p>
    </div>
    <button id="openRegisterMemberModal" class="btn btn-gold" onclick="openModal('registerMemberModal')">
        <i class="fas fa-user-plus"></i> Register Member
    </button>
</div>

{{-- WORKFLOW --}}
<div class="workflow-steps mb-6">
    <div class="workflow-step">
        <div class="step-num">1</div>
        <div><div class="step-text">Personal Info</div><div class="step-sub">Name, contact, address</div></div>
    </div>
    <div class="workflow-step">
        <div class="step-num">2</div>
        <div><div class="step-text">Member Type</div><div class="step-sub">Student, Faculty, Public</div></div>
    </div>
    <div class="workflow-step">
        <div class="step-num">3</div>
        <div><div class="step-text">ID Photo</div><div class="step-sub">School ID or capture</div></div>
    </div>
    <div class="workflow-step">
        <div class="step-num">4</div>
        <div><div class="step-text">Issue Card</div><div class="step-sub">Print library card</div></div>
    </div>
</div>

{{-- MAIN PANEL --}}
<div class="grid-2" style="gap:20px;align-items:start">

    {{-- MEMBER LIST --}}
    <div class="card">
        <div class="card-header">
            <span class="card-title">All Members</span>
            <input type="text" id="memberSearch" class="form-control" style="max-width:180px;font-size:12px;padding:6px 10px" placeholder="Search...">
        </div>
        <div style="max-height:520px;overflow-y:auto">
            @forelse($members ?? [] as $member)
            @php
                $avatarColors = ['avatar-teal','avatar-gold','avatar-sage','avatar-rust','avatar-navy'];
                $ac = $avatarColors[$member->id % count($avatarColors)];
                $initials = strtoupper(substr($member->name,0,1).(strpos($member->name,' ')!==false ? substr($member->name,strpos($member->name,' ')+1,1) : ''));
            @endphp
            <div class="member-row" style="display:flex;align-items:center;gap:12px;padding:14px 20px;border-bottom:1px solid rgba(0,0,0,0.05);cursor:pointer;transition:background .15s"
                 onclick="showMemberDetail({{ $member->id }})"
                 data-name="{{ strtolower($member->name) }}">
                <div class="member-avatar {{ $ac }}">{{ $initials }}</div>
                <div style="flex:1;min-width:0">
                    <div style="font-size:13px;font-weight:600;color:var(--text-dark)">{{ $member->name }}</div>
                    <div style="font-size:11px;color:var(--text-light)">{{ $member->member_id ?? 'MBR-'.str_pad($member->id,7,'0',STR_PAD_LEFT) }} · {{ ucfirst($member->type ?? 'student') }}</div>
                </div>
                @php $ms = $member->status ?? 'active'; @endphp
                <span class="badge {{ $ms==='active'?'badge-success':($ms==='overdue'?'badge-danger':'badge-gray') }}">
                    {{ ucfirst($ms) }}
                </span>
            </div>
            @empty
            <div style="text-align:center;padding:32px;color:var(--text-light)">No members registered</div>
            @endforelse
        </div>
    </div>

    {{-- MEMBER DETAIL PANEL --}}
    <div class="card" id="memberDetailPanel">
        <div class="card-header" style="justify-content:space-between">
            <span class="card-title">Member Details</span>
            <a href="#" id="editMemberLink" class="btn btn-outline btn-sm" style="display:none">Edit</a>
        </div>
        <div class="card-body" id="memberDetailContent" style="text-align:center;padding:40px;color:var(--text-light)">
            <i class="fas fa-user-circle" style="font-size:40px;margin-bottom:12px;display:block"></i>
            Select a member to view details
        </div>
    </div>
</div>

{{-- REGISTER MEMBER MODAL --}}
<div class="modal-overlay" id="registerMemberModal">
    <div class="modal">
        <div class="modal-header">
            <span class="modal-title">Register New Member</span>
            <button class="modal-close" onclick="closeModal('registerMemberModal')">×</button>
        </div>
        <div class="modal-body">
            <form method="POST" action="{{ route('members.store') }}" enctype="multipart/form-data">
                @csrf
                <div class="form-group">
                    <label class="form-label">Full Name</label>
                    <input type="text" name="name" class="form-control" placeholder="Maria Santos" required>
                </div>
                <div class="grid-2">
                    <div class="form-group">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" placeholder="email@example.com">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Contact No.</label>
                        <input type="text" name="contact" class="form-control" placeholder="09xx-xxx-xxxx">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Member ID</label>
                    <input type="text" name="member_id" id="registerMemberId" class="form-control" placeholder="Auto-generate next ID" value="">
                    <small class="form-text text-muted">Leave blank to auto-generate a professional member ID.</small>
                </div>
                <div class="grid-2">
                    <div class="form-group">
                        <label class="form-label">Member Type</label>
                        <select name="type" class="form-control" required>
                            <option value="student">Student</option>
                            <option value="faculty">Faculty</option>
                            <option value="public">Public</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">School / Student ID</label>
                        <input type="text" name="school_id" class="form-control" placeholder="Optional">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Address</label>
                    <input type="text" name="address" class="form-control" placeholder="Street, City, Province">
                </div>
                <div class="modal-footer" style="padding:0;border:none;margin-top:4px">
                    <button type="button" class="btn btn-outline" onclick="closeModal('registerMemberModal')">Cancel</button>
                    <button type="submit" class="btn btn-gold">Register Member</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Client-side search filter
document.getElementById('memberSearch')?.addEventListener('input', function() {
    const q = this.value.toLowerCase();
    document.querySelectorAll('.member-row').forEach(row => {
        row.style.display = row.dataset.name.includes(q) ? '' : 'none';
    });
});

const memberData = @json($members ?? []);
const nextMemberId = (() => {
    const maxId = memberData.length ? Math.max(...memberData.map(m => m.id || 0)) : 0;
    return `MBR-${new Date().getFullYear()}-${String(maxId + 1).padStart(4, '0')}`;
})();

document.getElementById('openRegisterMemberModal')?.addEventListener('click', function() {
    const input = document.getElementById('registerMemberId');
    if (input) {
        input.value = nextMemberId;
    }
});

function showMemberDetail(id) {
    const m = memberData.find ? memberData.find(x => x.id === id) : null;
    const panel = document.getElementById('memberDetailContent');
    if (!m) { panel.innerHTML = '<p style="color:var(--text-light)">Member not found</p>'; return; }

    const avatarColors = ['#1a3a3a','#c9a84c','#5a8a6a','#8a4a2a','#2a4a7a'];
    const bg = avatarColors[id % avatarColors.length];
    const initials = m.name.split(' ').map(w=>w[0]).join('').substring(0,2).toUpperCase();
    const statusColor = m.status==='active'?'var(--success)':'var(--danger)';

    panel.innerHTML = `
        <div style="text-align:center;margin-bottom:16px">
            <div style="width:52px;height:52px;border-radius:50%;background:${bg};color:#fff;display:flex;align-items:center;justify-content:center;font-size:20px;font-weight:700;margin:0 auto 8px">${initials}</div>
            <div style="font-size:16px;font-weight:700">${m.name}</div>
            <div style="font-size:12px;color:var(--text-light)">${m.member_id} · ${(m.type||'student').charAt(0).toUpperCase()+(m.type||'student').slice(1)} · <span style="color:${statusColor};font-weight:600">${(m.status||'Active').charAt(0).toUpperCase()+(m.status||'active').slice(1)}</span></div>
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;text-align:left">
            <div><div style="font-size:11px;color:var(--text-light);text-transform:uppercase;font-weight:600">Books Borrowed</div><div style="font-size:14px;font-weight:600">${m.total_borrowed ?? 0} active</div></div>
            <div><div style="font-size:11px;color:var(--text-light);text-transform:uppercase;font-weight:600">Total Borrowed</div><div style="font-size:14px;font-weight:600">${m.all_borrowed ?? 0} books</div></div>
            <div><div style="font-size:11px;color:var(--text-light);text-transform:uppercase;font-weight:600">Outstanding Fine</div><div style="font-size:14px;font-weight:600;color:${m.outstanding_fine>0?'var(--danger)':'var(--success)'}">${m.outstanding_fine>0?'₱'+m.outstanding_fine.toFixed(2):'₱0.00'}</div></div>
            <div><div style="font-size:11px;color:var(--text-light);text-transform:uppercase;font-weight:600">Member Since</div><div style="font-size:14px;font-weight:600">${m.created_at ? new Date(m.created_at).toLocaleDateString('en-US',{month:'short',day:'numeric',year:'numeric'}) : '—'}</div></div>
        </div>`;

    document.getElementById('editMemberLink').href = `/members/${id}/edit`;
    document.getElementById('editMemberLink').style.display = '';
}
</script>
@endpush
@endsection
