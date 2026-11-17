<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class NoCacheForAuth
{
    /**
     * Tambahkan header no-cache pada semua response halaman yang butuh autentikasi,
     * sehingga browser tidak menyimpan halaman ini di cache.
     * Efeknya: tombol Back setelah logout akan memaksa browser request ulang ke server,
     * dan server akan mengarahkan ke login — bukan menampilkan halaman cached.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        return $response->withHeaders([
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
            'Pragma'        => 'no-cache',
            'Expires'       => 'Sat, 01 Jan 2000 00:00:00 GMT',
        ]);
    }
}
