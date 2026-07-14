<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    /**
     * Show the login form.
     */
    public function showLoginForm()
    {
        return view('pages.auth.signin', ['title' => 'Masuk']);
    }

    /**
     * Handle an authentication attempt. Login menerima email, NIK, atau username.
     */
    public function login(Request $request)
    {
        $request->validate([
            'login' => ['required', 'string'],
            'password' => ['required'],
        ]);

        $login = $request->string('login')->trim()->toString();
        $field = match (true) {
            filter_var($login, FILTER_VALIDATE_EMAIL) !== false => 'email',
            ctype_digit($login) => 'nik',
            default => 'username',
        };

        if (Auth::attempt([$field => $login, 'password' => $request->string('password')->toString()], $request->boolean('remember'))) {
            $request->session()->regenerate();

            return redirect()->intended('admin');
        }

        return back()->withErrors([
            'login' => 'The provided credentials do not match our records.',
        ])->onlyInput('login');
    }

    /**
     * Log the user out of the application.
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
