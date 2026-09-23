@extends('layouts.app')

@section('title', 'Detail Pengembalian - Panel Admin')
@section('header-title', 'Detail Pengembalian Alat')

@section('content')

    <div class="max-w-2xl space-y-6">

        {{-- Tombol kembali --}}
        <a href="{{ route('admin.pengembalian.index') }}"
           class="text-sm text-gray-500 hover:text-gray-700 inline-block">&larr; Kembali ke Daftar</a>

        {{-- Card: Info Pengembalian --}}
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
            <div class="p-4 bg-emerald-600 text-white">
                <h3 class="text-base font-bold">Informasi Pengembalian #{{ $pengembalian->id }}</h3>
            </div>
            <div class="p-5 divide-y divide-gray-100 text-sm">
                <div class="flex justify-between py-2.5">
                    <span class="text-gray-500 font-medium">Tanggal Kembali</span>
                    <span class="font-semibold text-gray-800">{{ $pengembalian->tgl_kembali }}</span>
                </div>
                <div class="flex justify-between py-2.5">
                    <span class="text-gray-500 font-medium">Kondisi Alat</span>
                    <span class="px-2.5 py-1 text-xs font-semibold rounded-full
                        @if(strtolower($pengembalian->kondisi_kembali) === 'baik')
                            bg-emerald-100 text-emerald-800
                        @elseif(str_contains(strtolower($pengembalian->kondisi_kembali), 'ringan'))
                            bg-amber-100 text-amber-800
                        @else
                            bg-red-100 text-red-800
                        @endif">
                        {{ $pengembalian->kondisi_kembali }}
                    </span>
                </div>
                <div class="flex justify-between py-2.5">
                    <span class="text-gray-500 font-medium">Denda</span>
                    <span class="{{ $pengembalian->denda > 0 ? 'text-red-600 font-bold' : 'text-gray-400' }}">
                        @if($pengembalian->denda > 0)
                            Rp {{ number_format($pengembalian->denda, 0, ',', '.') }}
                        @else
                            Tidak ada denda
                        @endif
                    </span>
                </div>
                <div class="flex justify-between py-2.5">
                    <span class="text-gray-500 font-medium">Dicatat Oleh</span>
                    <span class="font-semibold text-gray-800">{{ $pengembalian->petugas->name ?? 'Sistem' }}</span>
                </div>
                <div class="flex justify-between py-2.5">
                    <span class="text-gray-500 font-medium">Waktu Pencatatan</span>
                    <span class="text-gray-600">{{ $pengembalian->created_at->format('d M Y, H:i') }}</span>
                </div>
            </div>
        </div>

        {{-- Card: Info Peminjaman Terkait --}}
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
            <div class="p-4 bg-blue-600 text-white">
                <h3 class="text-base font-bold">Peminjaman Terkait #{{ $pengembalian->peminjaman->id }}</h3>
            </div>
            <div class="p-5 divide-y divide-gray-100 text-sm">
                <div class="flex justify-between py-2.5">
                    <span class="text-gray-500 font-medium">Nama Peminjam</span>
                    <span class="font-semibold text-gray-800">{{ $pengembalian->peminjaman->user->name ?? '-' }}</span>
                </div>
                <div class="flex justify-between py-2.5">
                    <span class="text-gray-500 font-medium">Tanggal Pinjam</span>
                    <span class="text-gray-700">{{ $pengembalian->peminjaman->tgl_pinjam }}</span>
                </div>
                <div class="flex justify-between py-2.5">
                    <span class="text-gray-500 font-medium">Rencana Kembali</span>
                    @php
                        $tglPlan    = \Carbon\Carbon::parse($pengembalian->peminjaman->tgl_kembali_plan);
                        $tglKembali = \Carbon\Carbon::parse($pengembalian->tgl_kembali);
                        $telat      = $tglKembali->gt($tglPlan);
                    @endphp
                    <span class="{{ $telat ? 'text-red-600 font-semibold' : 'text-gray-700' }}">
                        {{ $pengembalian->peminjaman->tgl_kembali_plan }}
                        @if($telat)
                            <span class="text-xs ml-1">(Telat {{ $tglKembali->diffInDays($tglPlan) }} hari)</span>
                        @endif
                    </span>
                </div>
                <div class="flex justify-between py-2.5">
                    <span class="text-gray-500 font-medium">Status Akhir</span>
                    <span class="px-2.5 py-1 text-xs font-semibold rounded-full
                        @if($pengembalian->peminjaman->status == 'selesai') bg-emerald-100 text-emerald-800
                        @elseif($pengembalian->peminjaman->status == 'dipinjam') bg-blue-100 text-blue-800
                        @else bg-gray-100 text-gray-700 @endif">
                        {{ ucfirst($pengembalian->peminjaman->status) }}
                    </span>
                </div>
            </div>
        </div>

        {{-- Card: Daftar Alat yang Dikembalikan --}}
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
            <div class="p-4 bg-gray-700 text-white">
                <h3 class="text-base font-bold">Alat yang Dikembalikan</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-100 text-gray-600 uppercase text-xs tracking-wider">
                            <th class="py-3 px-4 border-b">Nama Alat</th>
                            <th class="py-3 px-4 border-b">Kategori</th>
                            <th class="py-3 px-4 border-b text-center">Jumlah</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-700">
                        @foreach($pengembalian->peminjaman->detailPinjam as $detail)
                            <tr class="hover:bg-gray-50">
                                <td class="py-3 px-4 border-b font-medium">{{ $detail->alat->nama_alat ?? 'Alat dihapus' }}</td>
                                <td class="py-3 px-4 border-b text-gray-500">{{ $detail->alat->kategori->nama_kategori ?? '-' }}</td>
                                <td class="py-3 px-4 border-b text-center font-semibold">{{ $detail->jumlah }} pcs</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Tombol Hapus --}}
        <div class="flex justify-end">
            <form action="{{ route('admin.pengembalian.destroy', $pengembalian->id) }}" method="POST"
                  onsubmit="return confirm('Hapus data pengembalian ini? Stok dan status peminjaman akan di-rollback.')">
                @csrf
                @method('DELETE')
                <button type="submit"
                        class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg text-sm font-semibold transition">
                    Hapus Data Pengembalian
                </button>
            </form>
        </div>

    </div>

@endsection
