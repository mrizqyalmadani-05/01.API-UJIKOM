@extends('layouts.peminjam')

@section('title', 'Riwayat Peminjaman')
@section('header-title', 'Riwayat & Pengembalian Alat')

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

    <div class="bg-white rounded-lg shadow-sm overflow-hidden border border-gray-200">
        <div class="p-5 border-b border-gray-200 bg-gray-50">
            <h3 class="text-base font-bold text-gray-800">Riwayat Peminjaman Saya</h3>
            <p class="text-xs text-gray-500 mt-1">
                Klik "Kembalikan" pada peminjaman yang sudah selesai digunakan.
            </p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm">
                <thead>
                    <tr class="bg-gray-100 text-gray-600 uppercase tracking-wider text-xs">
                        <th class="py-3 px-4 border-b">Alat yang Dipinjam</th>
                        <th class="py-3 px-4 border-b">Tgl Pinjam</th>
                        <th class="py-3 px-4 border-b">Rencana Kembali</th>
                        <th class="py-3 px-4 border-b">Status</th>
                        <th class="py-3 px-4 border-b">Info Pengembalian</th>
                        <th class="py-3 px-4 border-b">Aksi</th>
                    </tr>
                </thead>
                <tbody class="text-gray-700">
                    @forelse($peminjamans as $item)
                        <tr class="hover:bg-gray-50 transition align-top">
                            <td class="py-3 px-4 border-b">
                                <ul class="list-disc list-inside space-y-0.5">
                                    @foreach($item->detailPinjam as $d)
                                        <li class="text-xs">
                                            <span class="font-medium">{{ $d->alat->nama_alat ?? '-' }}</span>
                                            <span class="text-gray-400">({{ $d->jumlah }} pcs)</span>
                                        </li>
                                    @endforeach
                                </ul>
                            </td>
                            <td class="py-3 px-4 border-b text-xs text-gray-600 whitespace-nowrap">
                                {{ $item->tgl_pinjam }}
                            </td>
                            <td class="py-3 px-4 border-b text-xs whitespace-nowrap">
                                @php
                                    $telat = \Carbon\Carbon::today()->gt(\Carbon\Carbon::parse($item->tgl_kembali_plan))
                                             && $item->status === 'dipinjam';
                                @endphp
                                <span class="{{ $telat ? 'text-red-600 font-bold' : 'text-gray-700' }}">
                                    {{ $item->tgl_kembali_plan }}
                                </span>
                                @if($telat)
                                    <span class="block text-red-500 font-normal">
                                        Telat {{ \Carbon\Carbon::today()->diffInDays(\Carbon\Carbon::parse($item->tgl_kembali_plan)) }} hari
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 px-4 border-b">
                                <span class="px-2.5 py-1 text-xs font-semibold rounded-full
                                    @if($item->status === 'diajukan')      bg-yellow-100 text-yellow-800
                                    @elseif($item->status === 'dipinjam')  bg-blue-100 text-blue-800
                                    @elseif($item->status === 'dikembalikan') bg-amber-100 text-amber-800
                                    @elseif($item->status === 'selesai')   bg-emerald-100 text-emerald-800
                                    @elseif($item->status === 'ditolak')   bg-red-100 text-red-800
                                    @else bg-gray-100 text-gray-700 @endif">
                                    {{ ucfirst($item->status) }}
                                </span>
                            </td>
                            <td class="py-3 px-4 border-b text-xs">
                                @if($item->pengembalian)
                                    <p>Tgl: <span class="font-medium">{{ $item->pengembalian->tgl_kembali }}</span></p>
                                    <p>Kondisi: <span class="font-medium">{{ $item->pengembalian->kondisi_kembali }}</span></p>
                                    @if($item->pengembalian->denda > 0)
                                        <p class="text-red-600 font-bold">
                                            Denda: Rp {{ number_format($item->pengembalian->denda, 0, ',', '.') }}
                                        </p>
                                    @endif
                                    @if(!$item->pengembalian->petugas_id)
                                        <p class="text-amber-600 italic">Menunggu konfirmasi petugas</p>
                                    @else
                                        <p class="text-emerald-600">✓ Dikonfirmasi petugas</p>
                                    @endif
                                @else
                                    <span class="text-gray-400 italic">-</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 border-b">
                                @if($item->status === 'dipinjam' && !$item->pengembalian)
                                    <a href="{{ route('peminjam.kembalikan.form', $item->id) }}"
                                       class="bg-amber-500 hover:bg-amber-600 text-white px-3 py-1.5 rounded text-xs font-semibold transition block text-center whitespace-nowrap">
                                        Kembalikan Alat
                                    </a>
                                @elseif($item->status === 'dikembalikan')
                                    <span class="text-xs text-amber-600 font-medium italic">Menunggu konfirmasi</span>
                                @elseif($item->status === 'diajukan')
                                    <span class="text-xs text-yellow-600 font-medium italic">Menunggu persetujuan</span>
                                @elseif($item->status === 'ditolak')
                                    <span class="text-xs text-red-500 font-medium">Ditolak</span>
                                @else
                                    <span class="text-xs text-emerald-600 font-medium">✓ Selesai</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-6 text-center text-gray-400">
                                Anda belum memiliki riwayat peminjaman.
                                <a href="{{ route('peminjam.katalog') }}" class="text-blue-600 hover:underline ml-1">
                                    Pinjam alat sekarang →
                                </a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

@endsection
