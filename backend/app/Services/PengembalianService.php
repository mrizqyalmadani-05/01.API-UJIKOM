<?php

namespace App\Services;

use App\Models\Peminjaman;
use App\Models\Pengembalian;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Lapisan logika bisnis pengembalian.
 * Diambil dari OmmniDevv/api-ujikom (termasuk fix bug kbli-pos-003:
 * denda manual sebagai override opsional, default tetap otomatis).
 *
 * - Denda otomatis = hari_telat x Rp 5.000
 * - $dendaManual   = override opsional (nullable, >= 0)
 */
class PengembalianService
{
    public function proses(
        Peminjaman $peminjaman,
        string $tglKembali,
        string $kondisiKembali,
        int $petugasId,
        ?string $catatan = null,
        ?int $dendaManual = null
    ): array {
        if (! in_array($peminjaman->status, ['dipinjam', 'pengajuan_kembali'])) {
            throw new \RuntimeException('Peminjaman ini tidak dalam status "dipinjam".');
        }

        if ($peminjaman->pengembalian()->exists()) {
            throw new \RuntimeException('Pengembalian untuk peminjaman ini sudah diproses.');
        }

        DB::beginTransaction();
        try {
            $tglKembaliCarbon = Carbon::parse($tglKembali);
            $tglRencana = $peminjaman->tgl_kembali_plan;

            $terlambatHari = max(0, (int) $tglRencana->startOfDay()->diffInDays($tglKembaliCarbon->startOfDay(), false));

            $denda = $dendaManual ?? ($terlambatHari * 5000);
            $namaAlat = [];

            foreach ($peminjaman->detailPinjam as $detail) {
                if (! $detail->alat) {
                    continue;
                }
                if ($kondisiKembali !== 'baik') {
                    $detail->alat->update(['status_kondisi' => $kondisiKembali]);
                }
                $detail->alat->increment('stok', $detail->jumlah);
                $namaAlat[] = "{$detail->alat->nama_alat} ({$detail->jumlah} unit)";
            }

            $payload = [
                'peminjaman_id' => $peminjaman->id,
                'tgl_kembali' => $tglKembali,
                'kondisi_kembali' => $kondisiKembali,
                'denda' => $denda,
                'petugas_id' => $petugasId,
            ];
            if ($catatan !== null && Schema::hasColumn('pengembalian', 'catatan')) {
                $payload['catatan'] = $catatan;
            }
            Pengembalian::create($payload);

            $statusBaru = $terlambatHari > 0 ? 'telat' : 'dikembalikan';
            $peminjaman->update(['status' => $statusBaru]);

            DB::commit();

            return [
                'denda' => $denda,
                'terlambatHari' => $terlambatHari,
                'namaAlat' => $namaAlat,
                'statusBaru' => $statusBaru,
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
