<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'nip' => ['required'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            // Redirect to dashboard if admin (Case insensitive check)
            if (strtolower(Auth::user()->role) === 'admin') {
                $request->session()->put('is_admin', true);
                return redirect()->intended('/');
            }

            // User role is not allowed in web dashboard for now
            Auth::logout();
            return back()->withErrors([
                'nip' => 'Akses dashboard hanya untuk Administrator.',
            ])->onlyInput('nip');
        }

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
