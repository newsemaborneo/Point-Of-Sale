<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!Auth::check()) {
            return redirect('login');
        }

        $user = Auth::user();

        // Pastikan $user adalah instance dari App\Models\User
        if (!$user instanceof \App\Models\User) {
            return redirect('login');
        }

        // If no roles specified, just pass
        if (empty($roles)) {
            return $next($request);
        }

        foreach ($roles as $role) {
            if ($user->hasRole($role)) {
                return $next($request);
            }
        }

        // Untuk kasus role middleware, render sebagai halaman 403 agar UI modal error tampil.
        // Penting: gunakan view 403 yang sudah disediakan.
        return response()->view('errors.403', [
            'message' => 'Tindakan tidak sah. Anda tidak memiliki peran yang dibutuhkan.',
        ], 403);

    }
}
