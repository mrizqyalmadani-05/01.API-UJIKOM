<?php

namespace App\Services;

/**
 * Keamanan dokumen laporan: nomor dokumen terenkripsi + checksum SHA-256.
 * Diambil dari OmmniDevv/api-ujikom (DOKUMENTASI §6.6 — anti pemalsuan dokumen).
 *
 * Checksum = SHA256(DocID + Timestamp + TotalData + AppKey), 64 karakter,
 * dicetak di footer laporan. Jika angka di dokumen diubah, checksum tidak
 * akan cocok saat diverifikasi ulang terhadap database.
 */
class LaporanSecurity
{
    /**
     * Format: SPJ-DOC-YYYYMMDD-[HASH-6-DIGIT]
     */
    public static function generateDocId(string $prefix = 'SPJ-DOC'): string
    {
        $seed = $prefix . now()->format('YmdHis') . auth()->id();
        $hash = strtoupper(substr(hash('crc32b', $seed), 0, 6));

        return "{$prefix}-" . now()->format('Ymd') . "-{$hash}";
    }

    /**
     * Checksum SHA-256 untuk verifikasi integritas dokumen.
     */
    public static function checksum(string $docId, int $totalData): string
    {
        return strtoupper(hash('sha256', $docId . now()->timestamp . $totalData . config('app.key')));
    }

    /**
     * Verifikasi ulang checksum sebuah dokumen.
     * (Dipakai bila nanti ada halaman verifikasi dokumen.)
     */
    public static function verify(string $docId, int $timestamp, int $totalData, string $checksum): bool
    {
        $expected = strtoupper(hash('sha256', $docId . $timestamp . $totalData . config('app.key')));

        return hash_equals($expected, strtoupper($checksum));
    }
}
