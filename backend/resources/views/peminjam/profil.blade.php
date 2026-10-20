@extends('layouts.peminjam')

@section('title', 'Profil Saya')
@section('header-title', 'Profil Saya')

@section('content')

    @if(session('success'))
        <div class="mb-4 bg-emerald-50 border border-emerald-200 text-emerald-800 p-4 rounded-lg text-sm">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="mb-4 bg-red-50 border border-red-200 text-red-800 p-4 rounded-lg text-sm">
            {{ session('error') }}
        </div>
    @endif
    @if($errors->any())
        <div class="mb-4 bg-red-50 border border-red-200 text-red-800 p-4 rounded-lg text-sm">
            <p class="font-semibold mb-1">Periksa kembali isian Anda:</p>
            <ul class="list-disc list-inside space-y-0.5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">

        {{-- ===================== KARTU PROFIL & MENU ===================== --}}
        <div class="space-y-6">

            {{-- Informasi Profil --}}
            <div id="informasi" class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 text-center">
                @if($user->foto_profile)
                    <img src="{{ asset('storage/' . $user->foto_profile) }}" alt="Foto Profil"
                         class="w-28 h-28 rounded-full object-cover mx-auto border-4 border-blue-100">
                @else
                    <div class="w-28 h-28 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center mx-auto border-4 border-blue-50 text-4xl font-bold">
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    </div>
                @endif

                <h3 class="mt-4 text-lg font-bold text-gray-800">{{ $user->name }}</h3>
                <p class="text-sm text-gray-500">{{ $user->email }}</p>
                <span class="inline-block mt-2 px-2.5 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                    {{ ucfirst($user->role) }}
                </span>

                <div class="mt-5 text-left text-sm divide-y divide-gray-100">
                    <div class="py-2 flex justify-between">
                        <span class="text-gray-500">No. HP</span>
                        <span class="font-medium text-gray-700">{{ $user->no_hp ?: '-' }}</span>
                    </div>
                    <div class="py-2 flex justify-between">
                        <span class="text-gray-500">Alamat</span>
                        <span class="font-medium text-gray-700 text-right">{{ $user->alamat ?: '-' }}</span>
                    </div>
                    <div class="py-2 flex justify-between">
                        <span class="text-gray-500">Bergabung</span>
                        <span class="font-medium text-gray-700">{{ $user->created_at->format('d M Y') }}</span>
                    </div>
                </div>
            </div>

            {{-- Menu Profil --}}
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                <div class="p-4 border-b border-gray-200 bg-gray-50">
                    <h3 class="text-sm font-bold text-gray-800">Menu Profil</h3>
                </div>
                <nav class="p-2 space-y-1 text-sm">
                    <a href="#informasi"
                       class="block px-3 py-2 rounded-lg text-gray-700 hover:bg-blue-50 hover:text-blue-700 transition">
                        Informasi Profil
                    </a>
                    <a href="#edit-profil"
                       class="block px-3 py-2 rounded-lg text-gray-700 hover:bg-blue-50 hover:text-blue-700 transition">
                        Edit Profil &amp; Foto
                    </a>
                    <a href="{{ route('peminjam.katalog') }}"
                       class="block px-3 py-2 rounded-lg text-gray-700 hover:bg-blue-50 hover:text-blue-700 transition">
                        Katalog Alat
                    </a>
                    <a href="{{ route('peminjam.riwayat') }}"
                       class="block px-3 py-2 rounded-lg text-gray-700 hover:bg-blue-50 hover:text-blue-700 transition">
                        Riwayat &amp; Pengembalian
                    </a>

                    @if($user->foto_profile)
                        <form action="{{ route('peminjam.profil.foto.hapus') }}" method="POST"
                              onsubmit="return confirm('Hapus foto profil?')">
                            @csrf
                            <button type="submit"
                                    class="w-full text-left px-3 py-2 rounded-lg text-red-600 hover:bg-red-50 transition">
                                Hapus Foto Profil
                            </button>
                        </form>
                    @endif

                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit"
                                class="w-full text-left px-3 py-2 rounded-lg text-red-600 hover:bg-red-50 transition">
                            Logout
                        </button>
                    </form>
                </nav>
            </div>
        </div>

        {{-- ===================== FORM EDIT PROFIL ===================== --}}
        <div id="edit-profil" class="lg:col-span-2 bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
            <div class="p-5 border-b border-gray-200 bg-gray-50">
                <h3 class="text-base font-bold text-gray-800">Edit Profil</h3>
                <p class="text-xs text-gray-500 mt-1">
                    Perbarui data diri Anda. Kosongkan password jika tidak ingin mengubahnya.
                </p>
            </div>

            <form action="{{ route('peminjam.profil.update') }}" method="POST" enctype="multipart/form-data" class="p-5 space-y-4">
                @csrf
                @method('PUT')

                {{-- Foto Profil --}}
                <div>
                    <label for="foto_profile" class="block text-sm font-medium text-gray-700 mb-1">Foto Profil</label>
                    <div class="flex items-center gap-4">
                        <img id="foto-preview"
                             src="{{ $user->foto_profile ? asset('storage/' . $user->foto_profile) : '' }}"
                             alt="Preview Foto Profil"
                             class="w-16 h-16 rounded-full object-cover border border-gray-200 {{ $user->foto_profile ? '' : 'hidden' }}">
                        @unless($user->foto_profile)
                            <div id="foto-placeholder"
                                 class="w-16 h-16 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center text-xl font-bold">
                                {{ strtoupper(substr($user->name, 0, 1)) }}
                            </div>
                        @endunless
                        <div class="flex-1">
                            <input type="file" id="foto_profile" name="foto_profile" accept="image/*"
                                   class="block w-full text-sm text-gray-600 file:mr-3 file:px-3 file:py-2 file:rounded-lg file:border-0 file:bg-blue-600 file:text-white file:text-sm file:font-semibold hover:file:bg-blue-700">
                            <p class="text-xs text-gray-400 mt-1">Format: JPG, PNG, GIF, atau WebP. Maksimal 2 MB.</p>
                        </div>
                    </div>
                    @error('foto_profile')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap</label>
                        <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" required
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        @error('name')
                            <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                        <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}" required
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        @error('email')
                            <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="no_hp" class="block text-sm font-medium text-gray-700 mb-1">No. HP</label>
                        <input type="text" id="no_hp" name="no_hp" value="{{ old('no_hp', $user->no_hp) }}"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        @error('no_hp')
                            <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="alamat" class="block text-sm font-medium text-gray-700 mb-1">Alamat</label>
                        <input type="text" id="alamat" name="alamat" value="{{ old('alamat', $user->alamat) }}"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        @error('alamat')
                            <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password Baru</label>
                        <input type="password" id="password" name="password" autocomplete="new-password"
                               placeholder="Kosongkan jika tidak diubah"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        @error('password')
                            <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">Konfirmasi Password Baru</label>
                        <input type="password" id="password_confirmation" name="password_confirmation" autocomplete="new-password"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>

                <div class="pt-2">
                    <button type="submit"
                            class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold px-5 py-2.5 rounded-lg transition">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.getElementById('foto_profile').addEventListener('change', function (e) {
            const file = e.target.files[0];
            if (!file) return;
            const reader = new FileReader();
            reader.onload = function (ev) {
                const preview = document.getElementById('foto-preview');
                preview.src = ev.target.result;
                preview.classList.remove('hidden');
                const placeholder = document.getElementById('foto-placeholder');
                if (placeholder) placeholder.classList.add('hidden');
            };
            reader.readAsDataURL(file);
        });
    </script>

@endsection
