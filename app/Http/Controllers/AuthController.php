<?php

namespace App\Http\Controllers;

use App\Mail\AccountActivation;
use App\Mail\PasswordResetCode as PasswordResetCodeMail;
use App\Models\PasswordResetCode;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
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
                User::ROLE_OPERADOR => redirect()->intended('/operador/dashboard'),
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

    public function operadorDashboard()
    {
        return view('dashboards.operador', ['user' => Auth::user()]);
    }

    public function clienteDashboard()
    {
        return view('dashboards.cliente', ['user' => Auth::user()]);
    }

    // Recuperación de contraseña por código temporal
    public function showForgotPassword()
    {
        return view('auth.forgot-password');
    }

    public function sendResetCode(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email', 'exists:users,email'],
        ], [
            'email.exists' => 'No encontramos una cuenta con ese correo electrónico.',
        ]);

        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        PasswordResetCode::where('email', $request->email)
            ->whereNull('used_at')
            ->update(['used_at' => now()]);

        PasswordResetCode::create([
            'email' => $request->email,
            'code' => $code,
            'expires_at' => now()->addMinutes(15),
        ]);

        Mail::to($request->email)->send(new PasswordResetCodeMail($code));

        return redirect()->route('password.verify', ['email' => $request->email])
            ->with('success', 'Te enviamos un código de 6 dígitos a tu correo electrónico.');
    }

    public function showVerifyCode(Request $request)
    {
        return view('auth.verify-code', ['email' => $request->email]);
    }

    public function verifyCode(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
            'code' => ['required', 'string', 'size:6'],
        ]);

        $resetCode = PasswordResetCode::where('email', $request->email)
            ->where('code', $request->code)
            ->whereNull('used_at')
            ->where('expires_at', '>', now())
            ->first();

        if (!$resetCode) {
            throw ValidationException::withMessages([
                'code' => 'El código es inválido o ha expirado.',
            ]);
        }

        return redirect()->route('password.reset', [
            'email' => $request->email,
            'code' => $request->code,
        ]);
    }

    public function showNewPassword(Request $request)
    {
        return view('auth.reset-password', [
            'email' => $request->email,
            'code' => $request->code,
        ]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
            'code' => ['required', 'string', 'size:6'],
            'password' => ['required', 'confirmed', 'min:8'],
        ]);

        $resetCode = PasswordResetCode::where('email', $request->email)
            ->where('code', $request->code)
            ->whereNull('used_at')
            ->where('expires_at', '>', now())
            ->first();

        if (!$resetCode) {
            throw ValidationException::withMessages([
                'code' => 'El código es inválido o ha expirado.',
            ]);
        }

        $user = User::where('email', $request->email)->first();
        $user->password = Hash::make($request->password);
        $user->save();

        $resetCode->used_at = now();
        $resetCode->save();

        return redirect()->route('login')
            ->with('success', 'Tu contraseña ha sido actualizada. Inicia sesión con tu nueva contraseña.');
    }

    // Activación de cuenta para clientes nuevos
    public function showActivationForm($token)
    {
        $user = User::where('activation_token', $token)
            ->where('activation_expires_at', '>', now())
            ->first();

        if (!$user) {
            return redirect()->route('login')
                ->with('error', 'El enlace de activación es inválido o ha expirado.');
        }

        return view('auth.set-password', ['token' => $token]);
    }

    public function setPassword(Request $request, $token)
    {
        $request->validate([
            'password' => ['required', 'confirmed', 'min:8'],
        ]);

        $user = User::where('activation_token', $token)
            ->where('activation_expires_at', '>', now())
            ->first();

        if (!$user) {
            return redirect()->route('login')
                ->with('error', 'El enlace de activación es inválido o ha expirado.');
        }

        $user->password = Hash::make($request->password);
        $user->activation_token = null;
        $user->activation_expires_at = null;
        $user->save();

        return redirect()->route('login')
            ->with('success', 'Cuenta activada correctamente. Inicia sesión con tu nueva contraseña.');
    }
}
