@extends('layouts.peminjam')

@section('title', 'Kembalikan Alat')
@section('header-title', 'Form Pengembalian Alat')

@section('content')

    <div class="max-w-xl space-y-5">

        <a href="{{ route('peminjam.riwayat') }}"
           class="text-sm text-gray-500 hover:text-gray-700 inline-block">&larr; Kembali ke Riwayat</a>

        @if(session('error'))
            <div class="bg-red-50 border border-red-200 text-red-800 p-4 rounded-lg text-sm">
                {{ session('error') }}
            </div>
        @endif

        {{-- Info Peminjaman --}}
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-5">
            <h3 class="font-bold text-blue-800 text-sm mb-3">Detail Alat yang Dikembalikan</h3>
            <div class="space-y-1 text-sm text-blue-700">
                <p><span class="font-semibold">Tanggal Pinjam:</span> {{ $peminjaman->tgl_pinjam }}</p>
                <p><span class="font-semibold">Rencana Kembali:</span>
                    @php
                        $telat = \Carbon\Carbon::today()->gt(\Carbon\Carbon::parse($peminjaman->tgl_kembali_plan));
                    @endphp
                    <span class="{{ $telat ? 'text-red-600 font-bold' : '' }}">
                        {{ $peminjaman->tgl_kembali_plan }}
                        @if($telat)
                            (Telat {{ \Carbon\Carbon::today()->diffInDays(\Carbon\Carbon::parse($peminjaman->tgl_kembali_plan)) }} hari!)
                        @endif
                    </span>
                </p>
            </div>
            <div class="mt-3">
                <p class="text-xs font-semibold text-blue-700 uppercase tracking-wide mb-2">Daftar Alat:</p>
                <ul class="space-y-1">
                    @foreach($peminjaman->detailPinjam as $d)
                        <li class="flex items-center gap-2 text-sm text-blue-800">
                            <span class="w-2 h-2 rounded-full bg-blue-400 inline-block"></span>
                            <span class="font-medium">{{ $d->alat->nama_alat ?? '-' }}</span>
                            <span class="text-blue-500 text-xs">({{ $d->jumlah }} pcs)</span>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>

        {{-- Form Pengembalian --}}
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h3 class="font-bold text-gray-800 text-sm mb-4">Laporan Kondisi Pengembalian</h3>

            <form action="{{ route('peminjam.kembalikan.ajukan', $peminjaman->id) }}" method="POST">
                @csrf

                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-semibold mb-2">
                        Kondisi Alat Saat Dikembalikan <span class="text-red-500">*</span>
                    </label>
                    <select name="kondisi_kembali" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="Baik"        {{ old('kondisi_kembali') === 'Baik' ? 'selected' : '' }}>
                            Baik — Tidak ada kerusakan
                        </option>
                        <option value="Rusak Ringan" {{ old('kondisi_kembali') === 'Rusak Ringan' ? 'selected' : '' }}>
                            Rusak Ringan — Ada sedikit kerusakan tapi masih bisa digunakan
                        </option>
                        <option value="Rusak Berat"  {{ old('kondisi_kembali') === 'Rusak Berat' ? 'selected' : '' }}>
                            Rusak Berat — Tidak bisa digunakan / perlu perbaikan besar
                        </option>
                    </select>
                    @error('kondisi_kembali')
                        <span class="text-red-500 text-xs">{{ $message }}</span>
                    @enderror
                </div>

                @if($telat)
                    <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700">
                        <strong>Perhatian:</strong> Pengembalian Anda terlambat
                        {{ \Carbon\Carbon::today()->diffInDays(\Carbon\Carbon::parse($peminjaman->tgl_kembali_plan)) }} hari.
                        Denda akan dihitung oleh petugas.
                    </div>
                @endif

                <div class="bg-amber-50 border border-amber-200 rounded-lg p-3 text-xs text-amber-700 mb-5">
                    <strong>Catatan:</strong> Setelah mengklik "Ajukan Pengembalian", status peminjaman akan berubah
                    menjadi <em>Dikembalikan</em> dan menunggu konfirmasi petugas. Stok alat akan dipulihkan
                    setelah petugas mengkonfirmasi.
                </div>

                <div class="flex gap-3 justify-end">
                    <a href="{{ route('peminjam.riwayat') }}"
                       class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded-lg text-sm font-semibold transition">
                        Batal
                    </a>
                    <button type="submit"
                            class="bg-amber-500 hover:bg-amber-600 text-white px-5 py-2 rounded-lg text-sm font-semibold transition">
                        Ajukan Pengembalian
                    </button>
                </div>
            </form>
        </div>
    </div>

@endsection
