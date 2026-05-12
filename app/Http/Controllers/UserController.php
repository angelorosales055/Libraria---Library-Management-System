<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    public function index()
    {
        $staffUsers = User::whereIn('role', ['admin','librarian'])->latest()->get();
        $activeMembers = User::where('role', 'user')->where('is_active', true)->latest()->get();
        $deactivatedMembers = User::where('role', 'user')->where('is_active', false)->latest()->get();
        $users = $staffUsers->concat($activeMembers);
        return view('user-management.index', compact('users', 'staffUsers', 'activeMembers', 'deactivatedMembers'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'     => 'required|string|max:100',
            'email'    => 'required|email|unique:users,email',
            'role'     => 'required|in:admin,librarian',
            'password' => ['required','confirmed', Password::min(8)],
        ]);

        User::create([
            'name'      => $data['name'],
            'email'     => $data['email'],
            'role'      => $data['role'],
            'password'  => Hash::make($data['password']),
            'is_active' => true,
        ]);

        return redirect()->route('users.index')
            ->with('toast_success', "Staff account for '{$data['name']}' created.");
    }

    public function edit(User $user)
    {
        return view('user-management.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name'  => 'required|string|max:100',
            'email' => 'required|email|unique:users,email,'.$user->id,
            'role'  => 'required|in:admin,librarian',
        ]);

        $user->update($data);

        if ($request->filled('password')) {
            $request->validate(['password' => ['confirmed', Password::min(8)]]);
            $user->update(['password' => Hash::make($request->password)]);
        }

        return redirect()->route('users.index')
            ->with('toast_success', 'Account updated successfully.');
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot deactivate your own account.');
        }
        $user->update(['is_active' => false]);
        return redirect()->route('users.index')
            ->with('toast_success', 'Account deactivated.');
    }
}
