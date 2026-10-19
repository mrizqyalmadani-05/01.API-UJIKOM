<?php

namespace App\Http\Requests\Peminjaman;

use Illuminate\Foundation\Http\FormRequest;

class StorePeminjamanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tgl_pinjam' => ['required', 'date', 'after_or_equal:today'],
            'tgl_kembali_plan' => ['required', 'date', 'after_or_equal:tgl_pinjam'],
            'detail' => ['required', 'array', 'min:1'],
            'detail.*.alat_id' => ['required', 'exists:alat,id'],
            'detail.*.jumlah' => ['required', 'integer', 'min:1'],
        ];
    }

    public function messages(): array
    {
        return [
            'tgl_pinjam.required' => 'Tanggal pinjam wajib diisi.',
            'tgl_pinjam.after_or_equal' => 'Tanggal pinjam tidak boleh sebelum hari ini.',
            'tgl_kembali_plan.required' => 'Tanggal rencana kembali wajib diisi.',
            'tgl_kembali_plan.after_or_equal' => 'Tanggal kembali tidak boleh sebelum tanggal pinjam.',
            'detail.required' => 'Minimal pilih 1 alat.',
            'detail.*.alat_id.exists' => 'Alat tidak ditemukan.',
            'detail.*.jumlah.min' => 'Jumlah minimal 1.',
        ];
    }
}
