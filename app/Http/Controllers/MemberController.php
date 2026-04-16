<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class MemberController extends Controller
{
    public function index(Request $request)
    {
        $query = User::where('role', 'user');

        if ($search = $request->search) {
            $query->where(function($q) use ($search) {
                $q->where('name',  'like', "%{$search}%")
                  ->orWhere('email','like', "%{$search}%")
                  ->orWhere('member_id','like', "%{$search}%");
            });
        }
        if ($status = $request->status) {
            // dynamic status via subquery
            if ($status === 'overdue') {
                $query->whereHas('transactions', fn($q) => $q->where('status','overdue'));
            } elseif ($status === 'active') {
                $query->whereDoesntHave('transactions', fn($q) => $q->where('status','overdue'));
            }
        }

        $members      = $query->latest()->get();
        $totalMembers = User::where('role','user')->count();

        // Append computed attributes for JS
        $members->each(function($m) {
            $m->append(['total_borrowed','all_borrowed','outstanding_fine','status']);
        });

        return view('members.index', compact('members','totalMembers'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'      => 'required|string|max:100|unique:users,name',
            'email'     => 'nullable|email|unique:users,email',
            'member_id' => 'nullable|string|max:50|unique:users,member_id',
            'contact'   => 'nullable|string|max:20',
            'type'      => 'required|in:student,faculty,public',
            'school_id' => 'nullable|string|max:50',
            'address'   => 'nullable|string|max:255',
        ]);

        $memberId = $data['member_id'] ?: User::generateMemberId();

        User::create([
            'name'      => $data['name'],
            'email'     => $data['email'] ?? null,
            'member_id' => $memberId,
            'contact'   => $data['contact'] ?? null,
            'type'      => $data['type'],
            'school_id' => $data['school_id'] ?? null,
            'address'   => $data['address'] ?? null,
            'role'      => 'user',
            'is_active' => true,
            'password'  => Hash::make(str()->random(16)), // no login needed for members
        ]);

        return redirect()->route('members.index')
            ->with('toast_success', "Member '{$data['name']}' registered successfully! ID: {$memberId}");
    }

    public function show(User $member)
    {
        $member->load('transactions.book');

        $pendingFineTransactions = $member->transactions()
            ->where('fine_paid', false)
            ->where('fine', '>', 0)
            ->get();

        $pendingFineTotal = $pendingFineTransactions->sum('fine');
        $pendingFineCount = $pendingFineTransactions->count();
        $pendingFineByStatus = $pendingFineTransactions
            ->groupBy('status')
            ->map(fn($group, $status) => [
                'count' => $group->count(),
                'total' => $group->sum('fine'),
            ])->toArray();

        return view('members.show', compact('member', 'pendingFineTotal', 'pendingFineCount', 'pendingFineByStatus'));
    }

    public function edit(User $member)
    {
        return view('members.edit', compact('member'));
    }

    public function update(Request $request, User $member)
    {
        $data = $request->validate([
            'name'    => 'required|string|max:100|unique:users,name,'.$member->id,
            'email'   => 'nullable|email|unique:users,email,'.$member->id,
            'contact' => 'nullable|string|max:20',
            'type'    => 'required|in:student,faculty,public',
            'address' => 'nullable|string|max:255',
        ]);

        $member->update($data);
        return redirect()->route('members.index')->with('toast_success', 'Member updated successfully.');
    }

    public function destroy(User $member)
    {
        if ($member->activeTransactions()->exists()) {
            return back()->with('error', 'Cannot deactivate: member has active checkouts.');
        }
        $member->update(['is_active' => false]);
        return redirect()->route('members.index')->with('toast_success', 'Member deactivated.');
    }
}
