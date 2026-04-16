<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            return redirect()->intended($this->redirectFor(auth()->user()));
        }

        return back()->withErrors(['email' => 'Invalid email or password.'])->onlyInput('email');
    }

    public function showRegister()
    {
        $nextMemberId = User::generateMemberId();
        return view('auth.register', compact('nextMemberId'));
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'name'      => 'required|string|max:100|unique:users,name',
            'email'     => 'required|email|unique:users,email',
            'member_id' => 'nullable|string|max:50|unique:users,member_id',
            'password'  => ['required', 'confirmed', Password::min(8)],
        ]);

        $memberId = $data['member_id'] ?: User::generateMemberId();

        $user = User::create([
            'name'      => $data['name'],
            'email'     => $data['email'],
            'member_id' => $memberId,
            'password'  => Hash::make($data['password']),
            'role'      => 'user',
            'is_active' => true,
        ]);

        Auth::login($user);
        return redirect()->route('portal.home');
    }

    protected function redirectFor(User $user)
    {
        return $user->role === 'user' ? route('portal.home') : route('dashboard');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}
