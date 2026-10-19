<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Alat extends Model
{
    protected $table = 'alat';

    protected $fillable = [
        'kategori_id', 'nama_alat', 'stok', 'status_kondisi', 'deskripsi', 'gambar'
    ];

    protected function casts(): array {
        return [
            'stok' => 'integer',
        ];
    }

    public function kategori(): BelongsTo {
        return $this->belongsTo(Kategori::class);
    }

    public function detailPinjam(): HasMany {
        return $this->hasMany(DetailPinjam::class);
    }

    // =========================================================
    // Fitur tambahan dari OmmniDevv/api-ujikom
    // =========================================================

    /**
     * Scope pencarian: nama alat, deskripsi, atau nama kategori.
     */
    public function scopeSearch($query, ?string $keyword)
    {
        if ($keyword) {
            $query->where(function ($q) use ($keyword) {
                $q->where('nama_alat', 'like', "%{$keyword}%")
                    ->orWhere('deskripsi', 'like', "%{$keyword}%")
                    ->orWhereHas('kategori', fn ($k) => $k->where('nama_kategori', 'like', "%{$keyword}%"));
            });
        }

        return $query;
    }

    /**
     * Scope filter kategori.
     */
    public function scopeByKategori($query, ?int $kategoriId)
    {
        if ($kategoriId) {
            $query->where('kategori_id', $kategoriId);
        }

        return $query;
    }

    /**
     * Cek apakah stok mencukupi untuk jumlah yang diminta.
     */
    public function stokCukup(int $jumlah): bool
    {
        return $this->stok >= $jumlah;
    }

    /**
     * Cek apakah alat sedang dipinjam / dalam pengajuan aktif.
     */
    public function masihDipinjam(): bool
    {
        return $this->detailPinjam()
            ->whereHas('peminjaman', fn ($q) => $q->whereIn('status', ['diajukan', 'dipinjam']))
            ->exists();
    }
}