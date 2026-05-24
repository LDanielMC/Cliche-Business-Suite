<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            $request->session()->put('last_activity', now()->timestamp);

            $user = Auth::user();

            return match ($user->role) {
                User::ROLE_ADMIN => redirect()->intended('/admin/dashboard'),
                User::ROLE_SUPERVISOR => redirect()->intended('/supervisor/dashboard'),
                User::ROLE_CLIENTE => redirect()->intended('/cliente/dashboard'),
                default => redirect()->intended('/'),
            };
        }

        throw ValidationException::withMessages([
            'email' => __('auth.failed'),
        ]);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }

    public function adminDashboard()
    {
        return view('dashboards.admin', ['user' => Auth::user()]);
    }

    public function supervisorDashboard()
    {
        return view('dashboards.supervisor', ['user' => Auth::user()]);
    }

    public function clienteDashboard()
    {
        return view('dashboards.cliente', ['user' => Auth::user()]);
    }
}
