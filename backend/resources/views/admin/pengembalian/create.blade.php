@extends('layouts.app')

@section('title', 'Catat Pengembalian - Panel Admin')
@section('header-title', 'Catat Pengembalian Alat')

@section('content')

    <div class="max-w-2xl bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <a href="{{ route('admin.pengembalian.index') }}"
           class="text-sm text-gray-500 hover:text-gray-700 mb-4 inline-block">&larr; Kembali</a>

        @if(session('error'))
            <div class="mb-4 bg-red-50 border border-red-200 text-red-800 p-3 rounded-lg text-sm">
                {{ session('error') }}
            </div>
        @endif

        @if($peminjamans->isEmpty())
            <div class="p-4 bg-amber-50 border border-amber-200 text-amber-800 rounded-lg text-sm">
                Tidak ada peminjaman berstatus <strong>dipinjam</strong> yang menunggu pengembalian.
                <a href="{{ route('admin.pengembalian.index') }}" class="underline ml-1">Kembali ke daftar.</a>
            </div>
        @else

        <form action="{{ route('admin.pengembalian.store') }}" method="POST">
            @csrf

            {{-- Pilih Peminjaman --}}
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-semibold mb-2">
                    Pilih Transaksi Peminjaman
                    <span class="text-gray-400 font-normal text-xs">(hanya yang berstatus dipinjam)</span>
                </label>
                <select name="peminjaman_id" id="peminjaman_id" required onchange="loadDetail(this)"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">-- Pilih Peminjaman --</option>
                    @foreach($peminjamans as $p)
                        <option value="{{ $p->id }}"
                                data-user="{{ $p->user->name ?? '-' }}"
                                data-tglpinjam="{{ $p->tgl_pinjam }}"
                                data-tglplan="{{ $p->tgl_kembali_plan }}"
                                data-alat="{{ $p->detailPinjam->map(fn($d) => $d->alat->nama_alat . ' (' . $d->jumlah . ' pcs)')->implode(', ') }}"
                                {{ old('peminjaman_id') == $p->id ? 'selected' : '' }}>
                            #{{ $p->id }} — {{ $p->user->name ?? '-' }} (Rencana kembali: {{ $p->tgl_kembali_plan }})
                        </option>
                    @endforeach
                </select>
                @error('peminjaman_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>

            {{-- Info Detail Peminjaman (diisi otomatis via JS) --}}
            <div id="detail-box" class="mb-4 hidden p-4 bg-blue-50 border border-blue-200 rounded-lg text-sm space-y-1">
                <p><span class="font-semibold text-gray-700">Peminjam:</span> <span id="info-user"></span></p>
                <p><span class="font-semibold text-gray-700">Tanggal Pinjam:</span> <span id="info-tglpinjam"></span></p>
                <p><span class="font-semibold text-gray-700">Rencana Kembali:</span> <span id="info-tglplan"></span></p>
                <p><span class="font-semibold text-gray-700">Alat yang Dipinjam:</span> <span id="info-alat"></span></p>
            </div>

            {{-- Tanggal Kembali Aktual --}}
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-semibold mb-2">Tanggal Kembali Aktual</label>
                <input type="date" name="tgl_kembali"
                       value="{{ old('tgl_kembali', date('Y-m-d')) }}" required
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                @error('tgl_kembali') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>

            {{-- Kondisi Alat Saat Kembali --}}
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-semibold mb-2">Kondisi Alat Saat Dikembalikan</label>
                <select name="kondisi_kembali" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="Baik"         {{ old('kondisi_kembali') == 'Baik' ? 'selected' : '' }}>Baik</option>
                    <option value="Rusak Ringan"  {{ old('kondisi_kembali') == 'Rusak Ringan' ? 'selected' : '' }}>Rusak Ringan</option>
                    <option value="Rusak Berat"   {{ old('kondisi_kembali') == 'Rusak Berat' ? 'selected' : '' }}>Rusak Berat</option>
                </select>
                @error('kondisi_kembali') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>

            {{-- Denda (Rp) --}}
            <div class="mb-6">
                <label class="block text-gray-700 text-sm font-semibold mb-2">
                    Denda
                    <span class="text-gray-400 font-normal text-xs">(Isi 0 jika tidak ada denda)</span>
                </label>
                <div class="flex items-center">
                    <span class="bg-gray-100 border border-r-0 border-gray-300 px-3 py-2 rounded-l-lg text-sm text-gray-600">Rp</span>
                    <input type="number" name="denda" value="{{ old('denda', 0) }}" min="0"
                           class="flex-1 px-3 py-2 border border-gray-300 rounded-r-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                @error('denda') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>

            <div class="flex justify-end space-x-2">
                <a href="{{ route('admin.pengembalian.index') }}"
                   class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded-lg text-sm font-semibold transition">
                    Batal
                </a>
                <button type="submit"
                        class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-lg text-sm font-semibold transition">
                    Simpan Pengembalian
                </button>
            </div>
        </form>

        @endif
    </div>

    <script>
        function loadDetail(select) {
            const opt = select.options[select.selectedIndex];
            const box = document.getElementById('detail-box');

            if (!opt.value) {
                box.classList.add('hidden');
                return;
            }

            document.getElementById('info-user').textContent     = opt.dataset.user;
            document.getElementById('info-tglpinjam').textContent = opt.dataset.tglpinjam;
            document.getElementById('info-tglplan').textContent   = opt.dataset.tglplan;
            document.getElementById('info-alat').textContent     = opt.dataset.alat;
            box.classList.remove('hidden');
        }

        // Trigger on page load jika ada old value
        document.addEventListener('DOMContentLoaded', function () {
            const sel = document.getElementById('peminjaman_id');
            if (sel && sel.value) loadDetail(sel);
        });
    </script>

@endsection
