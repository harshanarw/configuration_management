<?php
// ══════════════════════════════════════════════
//  app/Http/Controllers/AuthController.php
// ══════════════════════════════════════════════
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\AuditLog;

class AuthController extends Controller
{
    public function showLogin()
    {
        return Auth::check() ? redirect()->route('dashboard') : view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => ['required','email'],
            'password' => ['required'],
        ]);

        if (!Auth::attempt($credentials, $request->boolean('remember'))) {
            AuditLog::record('Failed login attempt for: '.$request->email, 'auth', 'high');
            return back()->withErrors(['email' => 'Invalid credentials.'])->onlyInput('email');
        }

        $user = Auth::user();
        if (!$user->is_active) {
            Auth::logout();
            return back()->withErrors(['email' => 'Your account has been deactivated.']);
        }

        $user->update(['last_login_at' => now()]);
        AuditLog::record('User authenticated successfully', 'auth', 'low', 'User', $user->id);
        $request->session()->regenerate();

        return redirect()->route('dashboard');
    }

    public function logout(Request $request)
    {
        AuditLog::record('User signed out', 'auth', 'low');
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}
