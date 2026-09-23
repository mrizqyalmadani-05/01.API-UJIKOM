@extends('layouts.app')

@section('title', 'Kelola Pengembalian - Panel Admin')
@section('header-title', 'Manajemen Data Pengembalian Alat')

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
        {{-- Header: judul + search + tombol catat --}}
        <div class="p-5 border-b border-gray-200 bg-gray-50 flex flex-col md:flex-row justify-between items-center gap-4">
            <h3 class="text-lg font-bold text-gray-800">Daftar Pengembalian Alat</h3>

            <div class="flex items-center gap-3 w-full md:w-auto">
                <form action="{{ route('admin.pengembalian.index') }}" method="GET" class="flex w-full md:w-80">
                    <input type="text" name="search" value="{{ request('search') }}"
                           placeholder="Cari nama peminjam / kondisi..."
                           class="w-full px-3 py-2 text-sm border border-gray-300 rounded-l-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <button type="submit"
                            class="bg-gray-800 hover:bg-gray-900 text-white px-4 py-2 text-sm font-semibold rounded-r-lg transition">
                        Cari
                    </button>
                    @if(request('search'))
                        <a href="{{ route('admin.pengembalian.index') }}"
                           class="ml-2 bg-gray-300 hover:bg-gray-400 text-gray-700 px-3 py-2 text-sm rounded-lg flex items-center transition">
                            Reset
                        </a>
                    @endif
                </form>

                <a href="{{ route('admin.pengembalian.create') }}"
                   class="bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold px-4 py-2 rounded-lg transition whitespace-nowrap">
                    + Catat Pengembalian
                </a>
            </div>
        </div>

        {{-- Tabel --}}
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm">
                <thead>
                    <tr class="bg-gray-100 text-gray-600 uppercase tracking-wider text-xs">
                        <th class="py-3 px-4 border-b">Peminjam</th>
                        <th class="py-3 px-4 border-b">Alat Dikembalikan</th>
                        <th class="py-3 px-4 border-b">Tgl Pinjam</th>
                        <th class="py-3 px-4 border-b">Tgl Kembali</th>
                        <th class="py-3 px-4 border-b">Kondisi</th>
                        <th class="py-3 px-4 border-b">Denda</th>
                        <th class="py-3 px-4 border-b">Dicatat Oleh</th>
                        <th class="py-3 px-4 border-b">Aksi</th>
                    </tr>
                </thead>
                <tbody class="text-gray-700">
                    @forelse($pengembalians as $item)
                        <tr class="hover:bg-gray-50 transition align-top">
                            {{-- Nama peminjam --}}
                            <td class="py-3 px-4 border-b font-medium text-gray-900">
                                {{ $item->peminjaman->user->name ?? '-' }}
                            </td>

                            {{-- Daftar alat --}}
                            <td class="py-3 px-4 border-b">
                                <ul class="list-disc list-inside space-y-0.5">
                                    @foreach($item->peminjaman->detailPinjam as $detail)
                                        <li class="text-xs">
                                            {{ $detail->alat->nama_alat ?? 'Alat dihapus' }}
                                            <span class="text-gray-400">({{ $detail->jumlah }} pcs)</span>
                                        </li>
                                    @endforeach
                                </ul>
                            </td>

                            {{-- Tanggal pinjam --}}
                            <td class="py-3 px-4 border-b text-xs text-gray-600 whitespace-nowrap">
                                {{ $item->peminjaman->tgl_pinjam }}
                            </td>

                            {{-- Tanggal kembali aktual --}}
                            <td class="py-3 px-4 border-b text-xs whitespace-nowrap">
                                @php
                                    $tglPlan   = \Carbon\Carbon::parse($item->peminjaman->tgl_kembali_plan);
                                    $tglKembali = \Carbon\Carbon::parse($item->tgl_kembali);
                                    $telat = $tglKembali->gt($tglPlan);
                                @endphp
                                <span class="{{ $telat ? 'text-red-600 font-semibold' : 'text-gray-700' }}">
                                    {{ $item->tgl_kembali }}
                                </span>
                                @if($telat)
                                    <span class="block text-red-500 text-xs">
                                        Telat {{ $tglKembali->diffInDays($tglPlan) }} hari
                                    </span>
                                @endif
                            </td>

                            {{-- Kondisi alat saat kembali --}}
                            <td class="py-3 px-4 border-b">
                                <span class="px-2.5 py-1 text-xs font-semibold rounded-full
                                    @if(strtolower($item->kondisi_kembali) === 'baik')
                                        bg-emerald-100 text-emerald-800
                                    @elseif(str_contains(strtolower($item->kondisi_kembali), 'ringan'))
                                        bg-amber-100 text-amber-800
                                    @else
                                        bg-red-100 text-red-800
                                    @endif">
                                    {{ $item->kondisi_kembali }}
                                </span>
                            </td>

                            {{-- Denda --}}
                            <td class="py-3 px-4 border-b font-medium
                                {{ $item->denda > 0 ? 'text-red-600' : 'text-gray-400' }}">
                                @if($item->denda > 0)
                                    Rp {{ number_format($item->denda, 0, ',', '.') }}
                                @else
                                    -
                                @endif
                            </td>

                            {{-- Petugas --}}
                            <td class="py-3 px-4 border-b text-xs text-gray-500">
                                {{ $item->petugas->name ?? 'Sistem' }}
                            </td>

                            {{-- Aksi --}}
                            <td class="py-3 px-4 border-b">
                                <div class="flex items-center space-x-2">
                                    <a href="{{ route('admin.pengembalian.show', $item->id) }}"
                                       class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1.5 rounded text-xs font-semibold transition">
                                        Detail
                                    </a>
                                    <form action="{{ route('admin.pengembalian.destroy', $item->id) }}"
                                          method="POST"
                                          onsubmit="return confirm('Hapus data pengembalian ini? Stok dan status peminjaman akan di-rollback.')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="bg-red-500 hover:bg-red-600 text-white px-3 py-1.5 rounded text-xs font-semibold transition">
                                            Hapus
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-6 text-center text-gray-500">
                                Belum ada data pengembalian.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="p-4 border-t border-gray-200 bg-gray-50">
            {{ $pengembalians->links() }}
        </div>
    </div>

@endsection
