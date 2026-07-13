<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckAuth
{
    public function handle(Request $request, Closure $next)
    {
        if (! auth()->check()) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Sesi habis. Silakan login kembali.'], 401);
            }
            return redirect()->route('login')->with('error', 'Silakan login terlebih dahulu.');
        }

        $user = auth()->user();

        if (! $user->is_active) {
            auth()->logout();
            return redirect()->route('login')->with('error', 'Akun Anda dinonaktifkan. Hubungi administrator.');
        }

        if ($user->isLocked()) {
            auth()->logout();
            $until = $user->locked_until->format('H:i');
            return redirect()->route('login')->with('error', "Akun dikunci hingga pukul {$until}.");
        }

        return $next($request);
    }
}
