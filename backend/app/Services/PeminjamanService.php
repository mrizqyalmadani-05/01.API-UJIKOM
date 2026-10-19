<?php

namespace App\Services;

use App\Models\Alat;
use App\Models\DetailPinjam;
use App\Models\Peminjaman;
use Illuminate\Support\Facades\DB;

/**
 * Lapisan logika bisnis peminjaman.
 * Diambil dari OmmniDevv/api-ujikom.
 *
 * - ajukan(): pola reservasi stok — stok didecrement saat diajukan memakai
 *   pessimistic locking (lockForUpdate) agar tidak terjadi race condition.
 * - approve(): diajukan -> dipinjam (stok tidak diubah lagi, sudah direserve).
 * - tolak(): stok dikembalikan (increment), status -> 'ditolak'
 *   (record TIDAK dihapus, sesuai fix bug pjm-pos-006 di repo referensi).
 */
class PeminjamanService
{
    /**
     * @param int    $userId
     * @param string $tglPinjam       Format Y-m-d
     * @param string $tglKembaliPlan  Format Y-m-d
     * @param array  $detail          [['alat_id' => int, 'jumlah' => int], ...]
     */
    public function ajukan(int $userId, string $tglPinjam, string $tglKembaliPlan, array $detail): array
    {
        DB::beginTransaction();
        try {
            $peminjaman = Peminjaman::create([
                'user_id' => $userId,
                'tgl_pinjam' => $tglPinjam,
                'tgl_kembali_plan' => $tglKembaliPlan,
                'status' => 'diajukan',
            ]);

            $namaAlat = [];

            $seen = [];
            foreach ($detail as $item) {
                if (in_array($item['alat_id'], $seen, true)) {
                    DB::rollBack();
                    return ['success' => false, 'error' => 'Duplikat alat dalam pengajuan.', 'peminjaman' => null, 'namaAlat' => []];
                }
                $seen[] = $item['alat_id'];
                $alat = Alat::lockForUpdate()->findOrFail($item['alat_id']);

                if (! $alat->stokCukup($item['jumlah'])) {
                    DB::rollBack();

                    return [
                        'success' => false,
                        'error' => "Stok alat \"{$alat->nama_alat}\" tidak mencukupi (tersedia: {$alat->stok}).",
                        'peminjaman' => null,
                        'namaAlat' => [],
                    ];
                }

                $alat->decrement('stok', $item['jumlah']);

                DetailPinjam::create([
                    'peminjaman_id' => $peminjaman->id,
                    'alat_id' => $item['alat_id'],
                    'jumlah' => $item['jumlah'],
                ]);

                $namaAlat[] = "{$alat->nama_alat} ({$item['jumlah']} unit)";
            }

            DB::commit();

            return [
                'success' => true,
                'peminjaman' => $peminjaman,
                'namaAlat' => $namaAlat,
                'error' => null,
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function approve(Peminjaman $peminjaman): void
    {
        if ($peminjaman->status !== 'diajukan') {
            throw new \RuntimeException('Hanya peminjaman berstatus "diajukan" yang bisa disetujui.');
        }

        $peminjaman->update(['status' => 'dipinjam']);
    }

    /**
     * @return string[] Nama alat yang stoknya dikembalikan
     */
    public function tolak(Peminjaman $peminjaman): array
    {
        if ($peminjaman->status !== 'diajukan') {
            throw new \RuntimeException('Hanya peminjaman berstatus "diajukan" yang bisa ditolak.');
        }

        DB::beginTransaction();
        try {
            $namaAlat = [];

            foreach ($peminjaman->detailPinjam as $detail) {
                $detail->alat->increment('stok', $detail->jumlah);
                $namaAlat[] = $detail->alat->nama_alat;
            }

            $peminjaman->update(['status' => 'ditolak']);
            DB::commit();

            return $namaAlat;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
