<?php

namespace App\Http\Requests\Pengembalian;

use App\Models\Peminjaman;
use Illuminate\Foundation\Http\FormRequest;

class StorePengembalianRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'peminjaman_id' => ['required', 'exists:peminjaman,id'],
            'tgl_kembali' => ['required', 'date', function (string $attribute, mixed $value, \Closure $fail) {
                $peminjamanId = request()->input('peminjaman_id');
                if (! $peminjamanId) return;
                $p = Peminjaman::find($peminjamanId);
                if (! $p) return;
                if (\Carbon\Carbon::parse($value)->lt(\Carbon\Carbon::parse($p->tgl_pinjam))) {
                    $fail('Tanggal kembali tidak boleh sebelum tanggal pinjam (' . $p->tgl_pinjam->format('d M Y') . ').');
                }
            }],
            'kondisi_kembali' => ['required', 'in:baik,rusak,perbaikan'],
            'catatan' => ['nullable', 'string', 'max:500'],
            'denda_manual' => ['nullable', 'integer', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'peminjaman_id.required' => 'Data peminjaman wajib dipilih.',
            'peminjaman_id.exists' => 'Data peminjaman tidak ditemukan.',
            'tgl_kembali.required' => 'Tanggal kembali wajib diisi.',
            'kondisi_kembali.required' => 'Kondisi alat wajib dipilih.',
            'kondisi_kembali.in' => 'Kondisi alat tidak valid.',
        ];
    }
}
