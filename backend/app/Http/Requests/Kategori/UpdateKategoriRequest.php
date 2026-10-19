<?php

namespace App\Http\Requests\Kategori;

use Illuminate\Foundation\Http\FormRequest;

class UpdateKategoriRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $kategori = $this->route('kategori');
        $kategoriId = $kategori instanceof \App\Models\Kategori ? $kategori->id : $kategori;

        return [
            'nama_kategori' => ['required', 'string', 'max:100', "unique:kategori,nama_kategori,{$kategoriId},id"],
            'deskripsi' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'nama_kategori.required' => 'Nama kategori wajib diisi.',
            'nama_kategori.unique' => 'Nama kategori sudah digunakan kategori lain.',
        ];
    }
}
