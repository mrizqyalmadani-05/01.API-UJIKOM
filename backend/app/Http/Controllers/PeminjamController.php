<?php

namespace App\Http\Controllers;

use App\Http\Requests\Profile\UpdateProfileRequest;
use App\Models\Alat;
use App\Models\DetailPinjam;
use App\Models\Peminjaman;
use App\Models\Pengembalian;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PeminjamController extends Controller
{
    // =========================================================
    // Katalog & Pengajuan Peminjaman
    // =========================================================

    public function katalogAlat()
    {
        $alats = Alat::with('kategori')->where('stok', '>', 0)->get();
        return view('peminjam.katalog', compact('alats'));
    }

    public function ajukanPeminjaman(Request $request)
    {
        $request->validate([
            'tgl_kembali_plan' => 'required|date|after:today',
            'alat_id'          => 'required|array',
            'alat_id.*'        => 'exists:alat,id',
            'jumlah'           => 'required|array',
            'jumlah.*'         => 'integer|min:1',
        ]);

        DB::beginTransaction();
        try {
            $peminjaman = Peminjaman::create([
                'user_id'          => auth()->id(),
                'tgl_pinjam'       => now()->toDateString(),
                'tgl_kembali_plan' => $request->tgl_kembali_plan,
                'status'           => 'diajukan',
            ]);

            foreach ($request->alat_id as $index => $alatId) {
                DetailPinjam::create([
                    'peminjaman_id' => $peminjaman->id,
                    'alat_id'       => $alatId,
                    'jumlah'        => $request->jumlah[$index],
                ]);
            }

            DB::commit();
            return redirect()->route('peminjam.riwayat')
                ->with('success', 'Pengajuan peminjaman berhasil dikirim. Tunggu persetujuan petugas.');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'Gagal mengajukan: ' . $e->getMessage());
        }
    }

    // =========================================================
    // Riwayat Peminjaman
    // =========================================================

    public function riwayatPeminjaman()
    {
        $peminjamans = Peminjaman::with(['detailPinjam.alat', 'pengembalian'])
            ->where('user_id', auth()->id())
            ->latest()
            ->get();

        return view('peminjam.riwayat', compact('peminjamans'));
    }

    // =========================================================
    // Pengembalian Alat (Peminjam mengajukan kembali)
    // =========================================================

    // Menampilkan form pengajuan pengembalian
    public function formKembalikan($peminjaman_id)
    {
        $peminjaman = Peminjaman::with('detailPinjam.alat')
            ->where('user_id', auth()->id())
            ->where('status', 'dipinjam')
            ->whereDoesntHave('pengembalian')
            ->findOrFail($peminjaman_id);

        return view('peminjam.kembalikan', compact('peminjaman'));
    }

    // Proses pengajuan pengembalian oleh peminjam
    public function ajukanPengembalian(Request $request, $peminjaman_id)
    {
        $request->validate([
            'kondisi_kembali' => 'required|string|max:255',
        ]);

        $peminjaman = Peminjaman::with('detailPinjam.alat')
            ->where('user_id', auth()->id())
            ->where('status', 'dipinjam')
            ->whereDoesntHave('pengembalian')
            ->findOrFail($peminjaman_id);

        DB::beginTransaction();
        try {
            // Peminjam mencatat pengembalian, tapi stok baru kembali setelah petugas konfirmasi
            // Status berubah ke 'dikembalikan' — menunggu verifikasi petugas
            Pengembalian::create([
                'peminjaman_id'   => $peminjaman->id,
                'tgl_kembali'     => now()->toDateString(),
                'kondisi_kembali' => $request->kondisi_kembali,
                'denda'           => 0,
                'petugas_id'      => null, // belum diverifikasi petugas
            ]);

            $peminjaman->update(['status' => 'dikembalikan']);

            DB::commit();
            return redirect()->route('peminjam.riwayat')
                ->with('success', 'Pengajuan pengembalian berhasil. Tunggu konfirmasi petugas.');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    // =========================================================
    // Fitur tambahan dari OmmniDevv/api-ujikom: Ajukan Kembali
    // (peminjam meminta petugas memproses pengembalian —
    //  status 'dipinjam' -> 'pengajuan_kembali', tanpa ubah UI)
    // =========================================================

    public function ajukanKembali($peminjaman_id)
    {
        $peminjaman = Peminjaman::where('user_id', auth()->id())
            ->findOrFail($peminjaman_id);

        if ($peminjaman->status !== 'dipinjam') {
            return redirect()->back()
                ->with('error', 'Hanya peminjaman berstatus "dipinjam" yang bisa diajukan pengembaliannya.');
        }

        try {
            $peminjaman->update(['status' => 'pengajuan_kembali']);

            \App\Services\ActivityLogger::log(
                'Ajukan Pengembalian',
                "Peminjaman #{$peminjaman->id} diajukan pengembalian oleh " . auth()->user()->name
            );

            return redirect()->back()
                ->with('success', 'Pengajuan pengembalian berhasil dikirim. Tunggu petugas memprosesnya.');
        } catch (\Exception $e) {
            \App\Services\ActivityLogger::logError('Gagal Ajukan Pengembalian', $e->getMessage());

            return redirect()->back()->with('error', 'Terjadi kesalahan. Silakan coba lagi.');
        }
    }

    // =========================================================
    // Profil Peminjam (info akun, menu profil, foto profil)
    // =========================================================

    // Menampilkan halaman profil peminjam yang sedang login
    public function profil()
    {
        $user = auth()->user();

        return view('peminjam.profil', compact('user'));
    }

    // Memperbarui data profil (nama, email, no HP, alamat, password, foto)
    public function updateProfil(UpdateProfileRequest $request)
    {
        $user = auth()->user();

        $data = $request->only(['name', 'email', 'no_hp', 'alamat']);

        // Password hanya diubah jika diisi
        if ($request->filled('password')) {
            $data['password'] = $request->password; // otomatis di-hash oleh cast model User
        }

        try {
            // Upload foto profil baru (foto lama dihapus dari storage)
            if ($request->hasFile('foto_profile')) {
                if ($user->foto_profile) {
                    Storage::disk('public')->delete($user->foto_profile);
                }

                $data['foto_profile'] = $request->file('foto_profile')
                    ->store('foto_profil', 'public');
            }

            $user->update($data);

            \App\Services\ActivityLogger::log(
                'Update Profil',
                "Peminjam {$user->name} memperbarui profilnya"
            );

            return redirect()->route('peminjam.profil')
                ->with('success', 'Profil berhasil diperbarui.');
        } catch (\Exception $e) {
            \App\Services\ActivityLogger::logError('Gagal Update Profil', $e->getMessage());

            return redirect()->back()->withInput()
                ->with('error', 'Gagal memperbarui profil. Silakan coba lagi.');
        }
    }

    // Menghapus foto profil (kembali ke tampilan default)
    public function hapusFotoProfil()
    {
        $user = auth()->user();

        if (! $user->foto_profile) {
            return redirect()->route('peminjam.profil')
                ->with('error', 'Foto profil belum ada.');
        }

        try {
            Storage::disk('public')->delete($user->foto_profile);
            $user->update(['foto_profile' => null]);

            return redirect()->route('peminjam.profil')
                ->with('success', 'Foto profil berhasil dihapus.');
        } catch (\Exception $e) {
            \App\Services\ActivityLogger::logError('Gagal Hapus Foto Profil', $e->getMessage());

            return redirect()->back()->with('error', 'Terjadi kesalahan. Silakan coba lagi.');
        }
    }
}
