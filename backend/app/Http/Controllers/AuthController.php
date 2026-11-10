<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    // Menampilkan Form Login
    public function showLoginForm()
    {
        return view('auth.login');
    }

    // Menampilkan Form Registrasi
    public function showRegisterForm()
    {
        return view('auth.register');
    }

    // Memproses Registrasi (akun baru selalu berperan sebagai peminjam)
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'max:255', 'unique:users,email'],
            'no_hp'    => ['nullable', 'string', 'max:20'],
            'alamat'   => ['nullable', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'email.unique'       => 'Email sudah terdaftar. Silakan gunakan email lain atau login.',
            'email.email'        => 'Format email tidak valid.',
            'password.min'       => 'Password minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
        ]);

        $user = User::create([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'no_hp'    => $validated['no_hp'] ?? null,
            'alamat'   => $validated['alamat'] ?? null,
            'password' => $validated['password'], // otomatis di-hash oleh cast model User
            'role'     => 'peminjam',
        ]);

        // Langsung login sebagai peminjam baru
        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('peminjam.katalog')
            ->with('success', 'Registrasi berhasil. Selamat datang, ' . $user->name . '!');
    }

    // Memproses Login
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            $user = Auth::user();

            // Redirect berdasarkan Role sesuai matriks
            if ($user->role === 'admin') {
                return redirect()->route('admin.dashboard');
            } elseif ($user->role === 'petugas') {
                return redirect()->route('petugas.peminjaman.index');
            } elseif ($user->role === 'peminjam') {
                return redirect()->route('peminjam.katalog');
            }

            Auth::logout();
            return redirect()->route('login')->with('error', 'Role tidak dikenali.');
        }

        return back()->withErrors([
            'email' => 'Email atau password salah.',
        ])->onlyInput('email');
    }

    // Proses Logout
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
