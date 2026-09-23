@extends('layouts.petugas')

@section('title', 'Laporan Peminjaman - Petugas')
@section('header-title', 'Laporan Data Peminjaman')

@section('content')

    {{-- Filter --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-5 mb-5">
        <form action="{{ route('petugas.laporan.index') }}" method="GET"
              class="flex flex-col md:flex-row items-end gap-4">
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1 uppercase">Bulan</label>
                <input type="month" name="bulan" value="{{ $bulan }}"
                       class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-green-500">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1 uppercase">Status</label>
                <select name="status"
                        class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-green-500">
                    <option value="">Semua Status</option>
                    @foreach(['diajukan','dipinjam','dikembalikan','selesai','ditolak','telat'] as $s)
                        <option value="{{ $s }}" {{ $status === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex gap-2">
                <button type="submit"
                        class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-semibold transition">
                    Tampilkan
                </button>
                <a href="{{ route('petugas.laporan.cetak', ['bulan' => $bulan, 'status' => $status]) }}"
                   target="_blank"
                   class="bg-gray-700 hover:bg-gray-800 text-white px-4 py-2 rounded-lg text-sm font-semibold transition">
                    &#128438; Cetak / Print
                </a>
            </div>
        </form>
    </div>

    {{-- Ringkasan --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-5">
        <div class="bg-white rounded-lg border border-gray-200 p-4 text-center shadow-sm">
            <p class="text-2xl font-bold text-gray-800">{{ $peminjamans->count() }}</p>
            <p class="text-xs text-gray-500 uppercase tracking-wide mt-1">Total Transaksi</p>
        </div>
        <div class="bg-white rounded-lg border border-gray-200 p-4 text-center shadow-sm">
            <p class="text-2xl font-bold text-emerald-600">{{ $peminjamans->where('status','selesai')->count() }}</p>
            <p class="text-xs text-gray-500 uppercase tracking-wide mt-1">Selesai</p>
        </div>
        <div class="bg-white rounded-lg border border-gray-200 p-4 text-center shadow-sm">
            <p class="text-2xl font-bold text-blue-600">{{ $peminjamans->whereIn('status',['dipinjam','dikembalikan'])->count() }}</p>
            <p class="text-xs text-gray-500 uppercase tracking-wide mt-1">Masih Dipinjam</p>
        </div>
        <div class="bg-white rounded-lg border border-gray-200 p-4 text-center shadow-sm">
            <p class="text-2xl font-bold text-red-600">Rp {{ number_format($totalDenda, 0, ',', '.') }}</p>
            <p class="text-xs text-gray-500 uppercase tracking-wide mt-1">Total Denda</p>
        </div>
    </div>

    {{-- Tabel --}}
    <div class="bg-white rounded-lg shadow-sm overflow-hidden border border-gray-200">
        <div class="p-4 border-b bg-gray-50">
            <h3 class="font-bold text-gray-800 text-sm">
                Bulan: {{ \Carbon\Carbon::parse($bulan . '-01')->translatedFormat('F Y') }}
                @if($status) — Status: <span class="capitalize">{{ $status }}</span> @endif
            </h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left border-collapse">
                <thead>
                    <tr class="bg-gray-100 text-gray-600 uppercase text-xs tracking-wider">
                        <th class="py-3 px-3 border-b">No</th>
                        <th class="py-3 px-3 border-b">Peminjam</th>
                        <th class="py-3 px-3 border-b">Alat</th>
                        <th class="py-3 px-3 border-b">Tgl Pinjam</th>
                        <th class="py-3 px-3 border-b">Rencana Kembali</th>
                        <th class="py-3 px-3 border-b">Tgl Kembali</th>
                        <th class="py-3 px-3 border-b">Status</th>
                        <th class="py-3 px-3 border-b">Denda</th>
                    </tr>
                </thead>
                <tbody class="text-gray-700">
                    @forelse($peminjamans as $i => $p)
                        <tr class="hover:bg-gray-50 transition align-top">
                            <td class="py-2 px-3 border-b text-gray-400">{{ $i + 1 }}</td>
                            <td class="py-2 px-3 border-b font-medium">{{ $p->user->name ?? '-' }}</td>
                            <td class="py-2 px-3 border-b">
                                @foreach($p->detailPinjam as $d)
                                    <div class="text-xs">{{ $d->alat->nama_alat ?? '-' }} ({{ $d->jumlah }})</div>
                                @endforeach
                            </td>
                            <td class="py-2 px-3 border-b text-xs">{{ $p->tgl_pinjam }}</td>
                            <td class="py-2 px-3 border-b text-xs">{{ $p->tgl_kembali_plan }}</td>
                            <td class="py-2 px-3 border-b text-xs">{{ optional($p->pengembalian)->tgl_kembali ?? '-' }}</td>
                            <td class="py-2 px-3 border-b">
                                <span class="px-2 py-0.5 text-xs font-semibold rounded-full
                                    @if($p->status==='selesai') bg-emerald-100 text-emerald-800
                                    @elseif($p->status==='dipinjam') bg-blue-100 text-blue-800
                                    @elseif($p->status==='diajukan') bg-yellow-100 text-yellow-800
                                    @elseif($p->status==='ditolak') bg-red-100 text-red-800
                                    @else bg-gray-100 text-gray-700 @endif">
                                    {{ ucfirst($p->status) }}
                                </span>
                            </td>
                            <td class="py-2 px-3 border-b {{ optional($p->pengembalian)->denda > 0 ? 'text-red-600 font-semibold' : 'text-gray-400' }}">
                                {{ optional($p->pengembalian)->denda > 0 ? 'Rp ' . number_format($p->pengembalian->denda,0,',','.') : '-' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-6 text-center text-gray-400">Tidak ada data.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

@endsection
