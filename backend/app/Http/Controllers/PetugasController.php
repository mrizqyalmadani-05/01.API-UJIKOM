<?php

namespace App\Http\Controllers;

use App\Models\Alat;
use App\Models\LogAktivitas;
use App\Models\Peminjaman;
use App\Models\Pengembalian;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PetugasController extends Controller
{
    // =========================================================
    // Menyetujui / Kelola Peminjaman
    // =========================================================

    public function indexPeminjaman(Request $request)
    {
        $status = $request->input('status', '');

        $peminjamans = Peminjaman::with(['user', 'detailPinjam.alat'])
            ->when($status, fn($q, $s) => $q->where('status', $s))
            ->latest()
            ->get();

        return view('petugas.peminjaman.index', compact('peminjamans', 'status'));
    }

    public function setujuiPeminjaman($id)
    {
        DB::beginTransaction();
        try {
            $peminjaman = Peminjaman::with('detailPinjam.alat')->findOrFail($id);

            if ($peminjaman->status !== 'diajukan') {
                throw new \Exception('Peminjaman tidak dalam status diajukan.');
            }

            foreach ($peminjaman->detailPinjam as $detail) {
                $alat = $detail->alat;
                if ($alat->stok < $detail->jumlah) {
                    throw new \Exception("Stok alat '{$alat->nama_alat}' tidak mencukupi.");
                }
                $alat->decrement('stok', $detail->jumlah);
            }

            $peminjaman->update(['status' => 'dipinjam']);

            LogAktivitas::create([
                'user_id'   => auth()->id(),
                'aktivitas' => 'Menyetujui peminjaman #' . $peminjaman->id . ' oleh ' . optional($peminjaman->user)->name,
            ]);

            DB::commit();
            return redirect()->back()->with('success', 'Peminjaman disetujui dan stok alat dikurangi.');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function tolakPeminjaman($id)
    {
        $peminjaman = Peminjaman::findOrFail($id);
        $peminjaman->update(['status' => 'ditolak']);

        LogAktivitas::create([
            'user_id'   => auth()->id(),
            'aktivitas' => 'Menolak peminjaman #' . $peminjaman->id,
        ]);

        return redirect()->back()->with('success', 'Peminjaman berhasil ditolak.');
    }

    // =========================================================
    // Memantau & Memproses Pengembalian
    // =========================================================

    public function memantauPengembalian(Request $request)
    {
        $status = $request->input('status', 'dipinjam');

        // Peminjaman yang sedang aktif / belum dikembalikan
        $peminjamans = Peminjaman::with(['user', 'detailPinjam.alat', 'pengembalian'])
            ->when($status, fn($q, $s) => $q->where('status', $s))
            ->latest()
            ->get();

        return view('petugas.pengembalian.monitor', compact('peminjamans', 'status'));
    }

    public function prosesPengembalian(Request $request, $peminjaman_id)
    {
        $request->validate([
            'kondisi_kembali' => 'required|string',
            'denda'           => 'nullable|integer|min:0',
        ]);

        DB::beginTransaction();
        try {
            $peminjaman = Peminjaman::with('detailPinjam.alat')->findOrFail($peminjaman_id);

            if ($peminjaman->pengembalian) {
                throw new \Exception('Pengembalian untuk peminjaman ini sudah dicatat.');
            }

            Pengembalian::create([
                'peminjaman_id'   => $peminjaman->id,
                'tgl_kembali'     => now(),
                'kondisi_kembali' => $request->kondisi_kembali,
                'denda'           => $request->denda ?? 0,
                'petugas_id'      => auth()->id(),
            ]);

            $peminjaman->update(['status' => 'selesai']);

            foreach ($peminjaman->detailPinjam as $detail) {
                $detail->alat->increment('stok', $detail->jumlah);
            }

            LogAktivitas::create([
                'user_id'   => auth()->id(),
                'aktivitas' => 'Memproses pengembalian peminjaman #' . $peminjaman->id . ' oleh ' . optional($peminjaman->user)->name,
            ]);

            DB::commit();
            return redirect()->back()->with('success', 'Pengembalian berhasil dicatat dan stok dipulihkan.');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    // =========================================================
    // Cetak Laporan (Petugas)
    // =========================================================

    public function laporanIndex(Request $request)
    {
        $bulan  = $request->input('bulan', now()->format('Y-m'));
        $status = $request->input('status', '');

        [$tahun, $bln] = explode('-', $bulan);

        $peminjamans = Peminjaman::with(['user', 'detailPinjam.alat', 'pengembalian'])
            ->whereYear('tgl_pinjam', $tahun)
            ->whereMonth('tgl_pinjam', $bln)
            ->when($status, fn($q, $s) => $q->where('status', $s))
            ->orderBy('tgl_pinjam')
            ->get();

        $totalDenda = $peminjamans->sum(fn($p) => optional($p->pengembalian)->denda ?? 0);

        return view('petugas.laporan.index', compact('peminjamans', 'bulan', 'status', 'totalDenda'));
    }

    public function cetakLaporan(Request $request)
    {
        $bulan  = $request->input('bulan', now()->format('Y-m'));
        $status = $request->input('status', '');

        [$tahun, $bln] = explode('-', $bulan);

        $peminjamans = Peminjaman::with(['user', 'detailPinjam.alat', 'pengembalian'])
            ->whereYear('tgl_pinjam', $tahun)
            ->whereMonth('tgl_pinjam', $bln)
            ->when($status, fn($q, $s) => $q->where('status', $s))
            ->orderBy('tgl_pinjam')
            ->get();

        $totalDenda = $peminjamans->sum(fn($p) => optional($p->pengembalian)->denda ?? 0);

        return view('petugas.laporan.cetak', compact('peminjamans', 'bulan', 'status', 'totalDenda'));
    }

    // =========================================================
    // Fitur tambahan dari OmmniDevv/api-ujikom: Export Excel
    // (OpenSpout — memory-efficient streaming, tanpa ubah UI)
    // =========================================================

    public function excelLaporan(Request $request)
    {
        $bulan  = $request->input('bulan', now()->format('Y-m'));
        $status = $request->input('status', '');

        [$tahun, $bln] = explode('-', $bulan);

        $peminjamans = Peminjaman::with(['user', 'detailPinjam.alat', 'pengembalian'])
            ->whereYear('tgl_pinjam', $tahun)
            ->whereMonth('tgl_pinjam', $bln)
            ->when($status, fn($q, $s) => $q->where('status', $s))
            ->orderBy('tgl_pinjam')
            ->get();

        $filename = 'laporan-peminjaman-' . $bulan . '.xlsx';

        return (new \App\Exports\LaporanPeminjamanExport($peminjamans))->download($filename);
    }
}
