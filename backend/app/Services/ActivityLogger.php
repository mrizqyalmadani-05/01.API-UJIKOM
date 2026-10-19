<?php

namespace App\Services;

use App\Models\LogAktivitas;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

/**
 * Pencatat jejak audit (audit trail) terpusat.
 * Diambil dari OmmniDevv/api-ujikom.
 *
 * Menyimpan ke tabel log_aktivitas (kolom keterangan) dan ke file log Laravel.
 * Jika penyimpanan DB gagal, aplikasi tidak ikut crash — hanya dicatat ke Log.
 */
class ActivityLogger
{
    public static function log(string $aksi, ?string $keterangan = null, ?int $userId = null): void
    {
        $userId = $userId ?? auth()->id();

        try {
            $payload = [
                'user_id' => $userId,
                'aktivitas' => $aksi,
            ];

            if ($keterangan !== null && Schema::hasColumn('log_aktivitas', 'keterangan')) {
                $payload['keterangan'] = $keterangan;
            }

            LogAktivitas::create($payload);
        } catch (\Exception $e) {
            Log::error('Gagal simpan log aktivitas ke DB: ' . $e->getMessage());
        }

        Log::info("[AKTIVITAS] {$aksi}", [
            'user_id' => $userId,
            'keterangan' => $keterangan,
            'timestamp' => now()->toDateTimeString(),
        ]);
    }

    public static function logError(string $aksi, string $error, ?int $userId = null): void
    {
        Log::warning("[AKTIVITAS-GAGAL] {$aksi}", [
            'user_id' => $userId ?? auth()->id(),
            'error' => $error,
            'timestamp' => now()->toDateTimeString(),
        ]);
    }
}
