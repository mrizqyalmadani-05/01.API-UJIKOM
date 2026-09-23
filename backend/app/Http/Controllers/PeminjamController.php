<?php

namespace App\Http\Controllers;

use App\Models\Alat;
use App\Models\DetailPinjam;
use App\Models\Peminjaman;
use App\Models\Pengembalian;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
}
