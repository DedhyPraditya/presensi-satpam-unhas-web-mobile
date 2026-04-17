<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        Log::info('--- LOGIN ATTEMPT START ---', [
            'nip' => $request->nip,
            'session_id_before' => $request->session()->getId(),
            'ip' => $request->ip()
        ]);

        $credentials = $request->validate([
            'nip' => ['required'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials)) {
            Log::info('Auth::attempt SUCCESS for NIP: ' . $request->nip);
            
            $request->session()->regenerate();
            
            Log::info('Session REGENERATED. New Session ID: ' . $request->session()->getId());

            // Redirect to dashboard if admin (Case insensitive check)
            if (strtolower(Auth::user()->role) === 'admin') {
                Log::info('User is ADMIN. Redirecting to intended(/)');
                $request->session()->put('is_admin', true);
                return redirect()->intended('/');
            }

            Log::info('User is NOT ADMIN. Role: ' . Auth::user()->role . '. Logging out.');
            // User role is not allowed in web dashboard for now
            Auth::logout();
            return back()->withErrors([
                'nip' => 'Akses dashboard hanya untuk Administrator.',
            ])->onlyInput('nip');
        }

        Log::warning('Auth::attempt FAILED for NIP: ' . $request->nip);
        return back()->withErrors([
            'nip' => 'NIP atau Password salah.',
        ])->onlyInput('nip');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }
}
