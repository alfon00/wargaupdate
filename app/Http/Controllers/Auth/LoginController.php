<?php

namespace App\Http\Controllers\Auth;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Support\AuthContent;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function create(): View|RedirectResponse
    {
        if ($user = auth()->user()) {
            return redirect()->intended($user->dashboardRoute());
        }

        return view('auth.login-hub', [
            'introTitle' => AuthContent::introTitle(),
            'introLead' => AuthContent::introLead(),
            'formLead' => AuthContent::formLead(),
            'loginNote' => AuthContent::loginNote(),
            'securityBenefits' => AuthContent::securityBenefits(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ], [
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Masukkan email yang valid.',
            'password.required' => 'Kata sandi wajib diisi.',
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()->withErrors(['email' => 'Email atau kata sandi salah.'])->onlyInput('email');
        }

        $request->session()->regenerate();
        $user = $request->user();

        if ($user->role === UserRole::Warga) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return back()->withErrors([
                'email' => 'Akun warga tidak dapat masuk di sini. Gunakan Lacak permohonan.',
            ])->onlyInput('email');
        }

        if ($user->role === UserRole::Kelurahan) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return back()->withErrors([
                'email' => 'Akun ini tidak memiliki akses panel pengurus.',
            ])->onlyInput('email');
        }

        if (! $user->role?->isPengurus()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return back()->withErrors([
                'email' => 'Akun ini tidak memiliki akses panel pengurus.',
            ])->onlyInput('email');
        }

        return redirect()->intended($user->dashboardRoute());
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}
