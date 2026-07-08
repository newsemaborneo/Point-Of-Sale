<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    // 10. Manajemen Pengguna: login, hak akses (role), log aktivitas

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if (!Auth::attempt($credentials)) {
            throw ValidationException::withMessages(['email' => 'Email atau password salah']);
        }

        $request->session()->regenerate();
        $user = Auth::user();

        ActivityLog::create([
            'user_id' => $user->id,
            'module' => 'Auth',
            'action' => 'login',
            'description' => "{$user->name} login ke sistem",
            'ip_address' => $request->ip(),
        ]);

        return redirect()->intended(route('dashboard'));
    }

    public function logout(Request $request)
    {
        ActivityLog::create([
            'user_id' => $request->user()?->id,
            'module' => 'Auth',
            'action' => 'logout',
            'description' => $request->user()?->name . ' logout dari sistem',
            'ip_address' => $request->ip(),
        ]);

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    public function me(Request $request)
    {
        if (! $request->user()) {
            return redirect()->route('login');
        }

        return redirect()->route('dashboard');
    }
}
