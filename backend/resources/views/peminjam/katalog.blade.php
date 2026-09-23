@extends('layouts.peminjam')

@section('title', 'Katalog Alat - Peminjam')
@section('header-title', 'Katalog Alat Tersedia')

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
            <ul class="list-disc list-inside">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('peminjam.peminjaman.ajukan') }}" method="POST">
        @csrf

        {{-- Tanggal Rencana Kembali --}}
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-5 mb-5">
            <label class="block text-sm font-semibold text-gray-700 mb-2">
                Rencana Tanggal Kembali
                <span class="text-red-500">*</span>
            </label>
            <input type="date" name="tgl_kembali_plan"
                   value="{{ old('tgl_kembali_plan') }}"
                   min="{{ date('Y-m-d', strtotime('+1 day')) }}"
                   required
                   class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            @error('tgl_kembali_plan')
                <span class="text-red-500 text-xs block mt-1">{{ $message }}</span>
            @enderror
        </div>

        {{-- Daftar Alat --}}
        <div class="bg-white rounded-lg shadow-sm overflow-hidden border border-gray-200 mb-5">
            <div class="p-5 border-b border-gray-200 bg-gray-50">
                <h3 class="text-base font-bold text-gray-800">Pilih Alat yang Ingin Dipinjam</h3>
                <p class="text-xs text-gray-500 mt-1">Centang alat dan isi jumlah yang ingin dipinjam.</p>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-sm">
                    <thead>
                        <tr class="bg-gray-100 text-gray-600 uppercase tracking-wider text-xs">
                            <th class="py-3 px-4 border-b w-12">Pilih</th>
                            <th class="py-3 px-4 border-b">Nama Alat</th>
                            <th class="py-3 px-4 border-b">Kategori</th>
                            <th class="py-3 px-4 border-b">Stok Tersedia</th>
                            <th class="py-3 px-4 border-b w-32">Jumlah Pinjam</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-700">
                        @forelse($alats as $alat)
                            <tr class="hover:bg-blue-50 transition" id="row-{{ $alat->id }}">
                                <td class="py-3 px-4 border-b text-center">
                                    <input type="checkbox"
                                           name="alat_id[]"
                                           value="{{ $alat->id }}"
                                           class="w-4 h-4 accent-blue-600 cursor-pointer"
                                           onchange="toggleJumlah({{ $alat->id }}, this.checked)">
                                </td>
                                <td class="py-3 px-4 border-b font-medium text-gray-900">
                                    {{ $alat->nama_alat }}
                                    @if($alat->deskripsi)
                                        <p class="text-xs text-gray-400 font-normal">{{ Str::limit($alat->deskripsi, 60) }}</p>
                                    @endif
                                </td>
                                <td class="py-3 px-4 border-b text-gray-500 text-xs">
                                    {{ $alat->kategori->nama_kategori ?? '-' }}
                                </td>
                                <td class="py-3 px-4 border-b">
                                    <span class="font-semibold text-emerald-700">{{ $alat->stok }}</span> unit
                                </td>
                                <td class="py-3 px-4 border-b">
                                    <input type="number"
                                           name="jumlah[]"
                                           id="jumlah-{{ $alat->id }}"
                                           value="1" min="1" max="{{ $alat->stok }}"
                                           disabled
                                           class="w-full px-2 py-1.5 text-sm border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500 disabled:bg-gray-100 disabled:text-gray-400">
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-6 text-center text-gray-400">
                                    Tidak ada alat yang tersedia saat ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if($alats->count() > 0)
            <div class="flex justify-end">
                <button type="submit"
                        class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-2.5 rounded-lg text-sm transition shadow-sm">
                    Ajukan Peminjaman
                </button>
            </div>
        @endif
    </form>

    <script>
        function toggleJumlah(id, checked) {
            const input = document.getElementById('jumlah-' + id);
            input.disabled = !checked;
            if (!checked) input.value = 1;
        }
    </script>

@endsection
