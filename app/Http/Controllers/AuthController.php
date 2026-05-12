<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\ActivityLog;
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
            $user = auth()->user();

            ActivityLog::log(
                $user->id,
                'login',
                "User {$user->name} logged in.",
                'success'
            );

            return redirect()->intended($this->redirectFor($user));
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
        $user = auth()->user();
        if ($user) {
            ActivityLog::log(
                $user->id,
                'logout',
                "User {$user->name} logged out.",
                'success'
            );
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }

    public function profile()
    {
        return view('auth.profile', ['user' => auth()->user()]);
    }

    public function updateProfile(Request $request)
    {
        $user = auth()->user();
        $data = $request->validate([
            'name'    => 'required|string|max:100|unique:users,name,'.$user->id,
            'email'   => 'required|email|unique:users,email,'.$user->id,
            'contact' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
        ]);

        $user->update($data);
        return back()->with('toast_success', 'Profile updated successfully.');
    }

    public function showChangePassword()
    {
        return view('auth.change-password');
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'password'         => ['required', 'confirmed', \Illuminate\Validation\Rules\Password::min(8)],
        ]);

        if (!Hash::check($request->current_password, auth()->user()->password)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect.']);
        }

        auth()->user()->update(['password' => Hash::make($request->password)]);
        return back()->with('toast_success', 'Password changed successfully.');
    }
}
