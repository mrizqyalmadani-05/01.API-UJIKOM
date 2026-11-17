<?php

use App\Http\Middleware\CheckRole;
use App\Http\Middleware\NoCacheForAuth;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'role'     => CheckRole::class,
            'no-cache' => NoCacheForAuth::class,
        ]);

        // Jika user sudah login mencoba membuka halaman guest (login/register),
        // arahkan ke halaman yang sesuai berdasarkan role-nya.
        $middleware->redirectUsersTo(function ($request) {
            $user = auth()->user();
            if (!$user) {
                return route('login');
            }
            return match($user->role) {
                'admin'   => route('admin.dashboard'),
                'petugas' => route('petugas.peminjaman.index'),
                default   => route('peminjam.katalog'),
            };
        });
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
