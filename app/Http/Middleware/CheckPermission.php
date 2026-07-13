<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Penggunaan di route:
 *   ->middleware('permission:pengaturan.user')         // min 1 (view)
 *   ->middleware('permission:pengaturan.user,3')       // min 3 (create/edit)
 *   ->middleware('permission:pengaturan.user,7')       // min 7 (delete)
 */
class CheckPermission
{
    public function handle(Request $request, Closure $next, string $menuKode, int $minPerm = 1)
    {
        $user = auth()->user();
        $perm = $user->getMenuPermission($menuKode);

        if ($perm < $minPerm) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Akses ditolak.'], 403);
            }
            abort(403, 'Akses ditolak.');
        }

        // Simpan ke request agar controller bisa baca
        $request->merge(['_user_permission' => $perm]);

        return $next($request);
    }
}
