<?php

namespace App\Http\Requests\Profile;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
{
    /**
     * Hanya user yang sedang login (peminjam) yang boleh mengubah profilnya sendiri.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Aturan validasi untuk update profil peminjam.
     */
    public function rules(): array
    {
        return [
            'name'                  => ['required', 'string', 'max:255'],
            'email'                 => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($this->user()->id)],
            'no_hp'                 => ['nullable', 'string', 'max:20'],
            'alamat'                => ['nullable', 'string', 'max:255'],
            'password'              => ['nullable', 'string', 'min:8', 'confirmed'],
            'foto_profile'          => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:2048'],
        ];
    }

    public function messages(): array
    {
        return [
            'foto_profile.image' => 'Foto profil harus berupa file gambar.',
            'foto_profile.mimes' => 'Format foto profil harus jpeg, png, jpg, gif, atau webp.',
            'foto_profile.max'   => 'Ukuran foto profil maksimal 2 MB.',
            'password.confirmed' => 'Konfirmasi password baru tidak cocok.',
        ];
    }
}
