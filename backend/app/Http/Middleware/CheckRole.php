<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        // Jika belum login, arahkan ke halaman login
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        // Gabungkan semua argumen lalu pisahkan berdasarkan koma
        // Ini menangani sintaks 'role:petugas,admin' maupun 'role:admin'
        $allowedRoles = [];
        foreach ($roles as $role) {
            foreach (explode(',', $role) as $r) {
                $allowedRoles[] = trim($r);
            }
        }

        // Jika role user tidak ada dalam daftar yang diizinkan, tolak akses
        if (!in_array(auth()->user()->role, $allowedRoles)) {
            abort(403, 'Anda tidak memiliki akses ke halaman ini.');
        }

        return $next($request);
    }
}
