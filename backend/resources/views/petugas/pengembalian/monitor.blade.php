@extends('layouts.petugas')

@section('title', 'Pantau Pengembalian - Petugas')
@section('header-title', 'Pantau Pengembalian Alat')

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

    {{-- Tab Filter Status --}}
    <div class="flex gap-2 mb-5 flex-wrap">
        @foreach(['dipinjam' => 'Sedang Dipinjam', 'dikembalikan' => 'Menunggu Konfirmasi', 'selesai' => 'Selesai', '' => 'Semua'] as $val => $label)
            <a href="{{ route('petugas.pengembalian.monitor', ['status' => $val]) }}"
               class="px-4 py-2 rounded-full text-sm font-semibold transition
                      {{ $status === $val
                         ? 'bg-green-700 text-white shadow'
                         : 'bg-white border border-gray-300 text-gray-600 hover:bg-gray-100' }}">
                {{ $label }}
                @if($val === 'dikembalikan')
                    @php $jml = $peminjamans->where('status','dikembalikan')->count(); @endphp
                    @if($jml > 0)
                        <span class="ml-1 bg-red-500 text-white text-xs rounded-full px-1.5 py-0.5">{{ $jml }}</span>
                    @endif
                @endif
            </a>
        @endforeach
    </div>

    <div class="bg-white rounded-lg shadow-sm overflow-hidden border border-gray-200">
        <div class="p-5 border-b border-gray-200 bg-gray-50">
            <h3 class="text-base font-bold text-gray-800">
                @if($status === 'dipinjam') Alat Sedang Dipinjam
                @elseif($status === 'dikembalikan') Pengajuan Pengembalian — Menunggu Konfirmasi Petugas
                @elseif($status === 'selesai') Transaksi Selesai
                @else Semua Transaksi Peminjaman
                @endif
            </h3>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm">
                <thead>
                    <tr class="bg-gray-100 text-gray-600 uppercase tracking-wider text-xs">
                        <th class="py-3 px-4 border-b">Peminjam</th>
                        <th class="py-3 px-4 border-b">Alat</th>
                        <th class="py-3 px-4 border-b">Tgl Pinjam</th>
                        <th class="py-3 px-4 border-b">Rencana Kembali</th>
                        <th class="py-3 px-4 border-b">Status</th>
                        <th class="py-3 px-4 border-b">Kondisi (diisi peminjam)</th>
                        <th class="py-3 px-4 border-b">Aksi</th>
                    </tr>
                </thead>
                <tbody class="text-gray-700">
                    @forelse($peminjamans as $item)
                        <tr class="hover:bg-gray-50 transition align-top
                                   {{ $item->status === 'dikembalikan' ? 'bg-amber-50' : '' }}">
                            <td class="py-3 px-4 border-b font-medium">{{ $item->user->name ?? '-' }}</td>
                            <td class="py-3 px-4 border-b">
                                <ul class="space-y-0.5">
                                    @foreach($item->detailPinjam as $d)
                                        <li class="text-xs">{{ $d->alat->nama_alat ?? '-' }} ({{ $d->jumlah }} pcs)</li>
                                    @endforeach
                                </ul>
                            </td>
                            <td class="py-3 px-4 border-b text-xs whitespace-nowrap">{{ $item->tgl_pinjam }}</td>
                            <td class="py-3 px-4 border-b text-xs whitespace-nowrap">
                                @php
                                    $telat = \Carbon\Carbon::today()->gt(\Carbon\Carbon::parse($item->tgl_kembali_plan))
                                             && in_array($item->status, ['dipinjam','dikembalikan']);
                                @endphp
                                <span class="{{ $telat ? 'text-red-600 font-bold' : '' }}">
                                    {{ $item->tgl_kembali_plan }}
                                    @if($telat)
                                        <br><span class="text-red-500 font-normal">
                                            Telat {{ \Carbon\Carbon::today()->diffInDays(\Carbon\Carbon::parse($item->tgl_kembali_plan)) }} hari
                                        </span>
                                    @endif
                                </span>
                            </td>
                            <td class="py-3 px-4 border-b">
                                <span class="px-2.5 py-1 text-xs font-semibold rounded-full
                                    @if($item->status === 'dipinjam')     bg-blue-100 text-blue-800
                                    @elseif($item->status === 'dikembalikan') bg-amber-100 text-amber-800
                                    @elseif($item->status === 'selesai')  bg-emerald-100 text-emerald-800
                                    @else bg-gray-100 text-gray-700 @endif">
                                    {{ ucfirst($item->status) }}
                                </span>
                            </td>
                            {{-- Kondisi yang diisi peminjam saat mengajukan kembali --}}
                            <td class="py-3 px-4 border-b text-xs">
                                @if($item->pengembalian && !$item->pengembalian->petugas_id)
                                    <span class="font-semibold text-amber-700">
                                        {{ $item->pengembalian->kondisi_kembali }}
                                    </span>
                                    <span class="block text-gray-400">
                                        ({{ $item->pengembalian->tgl_kembali }})
                                    </span>
                                @elseif($item->pengembalian && $item->pengembalian->petugas_id)
                                    <span class="text-emerald-700 font-semibold">{{ $item->pengembalian->kondisi_kembali }}</span>
                                    @if($item->pengembalian->denda > 0)
                                        <span class="block text-red-600 font-bold text-xs">
                                            Denda: Rp {{ number_format($item->pengembalian->denda, 0, ',', '.') }}
                                        </span>
                                    @endif
                                @else
                                    <span class="text-gray-400 italic">Belum dikembalikan</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 border-b">
                                @if(in_array($item->status, ['dipinjam', 'dikembalikan']))
                                    <button onclick="toggleForm('proses-{{ $item->id }}')"
                                            class="bg-amber-500 hover:bg-amber-600 text-white px-3 py-1.5 rounded text-xs font-semibold transition w-full">
                                        Proses Kembali
                                    </button>
                                    <div id="proses-{{ $item->id }}" class="hidden mt-2 p-3 bg-gray-50 border border-gray-200 rounded-lg">
                                        <form action="{{ route('petugas.pengembalian.proses', $item->id) }}" method="POST">
                                            @csrf
                                            <div class="mb-2">
                                                <label class="block text-xs font-semibold text-gray-600 mb-1">Kondisi Alat</label>
                                                <select name="kondisi_kembali" required
                                                        class="w-full px-2 py-1.5 text-xs border border-gray-300 rounded focus:outline-none">
                                                    <option value="Baik">Baik</option>
                                                    <option value="Rusak Ringan">Rusak Ringan</option>
                                                    <option value="Rusak Berat">Rusak Berat</option>
                                                </select>
                                            </div>
                                            <div class="mb-2">
                                                <label class="block text-xs font-semibold text-gray-600 mb-1">Denda (Rp)</label>
                                                <input type="number" name="denda"
                                                       value="{{ $telat ? \Carbon\Carbon::today()->diffInDays(\Carbon\Carbon::parse($item->tgl_kembali_plan)) * 5000 : 0 }}"
                                                       min="0"
                                                       class="w-full px-2 py-1.5 text-xs border border-gray-300 rounded focus:outline-none">
                                            </div>
                                            <button type="submit"
                                                    class="w-full bg-green-600 hover:bg-green-700 text-white px-2 py-1.5 rounded text-xs font-semibold transition">
                                                Konfirmasi Selesai
                                            </button>
                                        </form>
                                    </div>
                                @elseif($item->status === 'selesai')
                                    <span class="text-xs text-emerald-600 font-semibold">✓ Selesai</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-6 text-center text-gray-400">Tidak ada data.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <script>
        function toggleForm(id) {
            document.getElementById(id).classList.toggle('hidden');
        }
    </script>

@endsection
