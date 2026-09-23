@extends('layouts.petugas')

@section('title', 'Kelola Peminjaman - Petugas')
@section('header-title', 'Kelola Peminjaman')

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
        {{-- Header + Filter Status --}}
        <div class="p-5 border-b border-gray-200 bg-gray-50 flex flex-col md:flex-row justify-between items-center gap-3">
            <h3 class="text-lg font-bold text-gray-800">Daftar Pengajuan Peminjaman</h3>
            <form action="{{ route('petugas.peminjaman.index') }}" method="GET" class="flex items-center gap-2">
                <select name="status" onchange="this.form.submit()"
                        class="px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
                    <option value="">Semua Status</option>
                    <option value="diajukan"     {{ $status === 'diajukan'     ? 'selected' : '' }}>Diajukan</option>
                    <option value="dipinjam"     {{ $status === 'dipinjam'     ? 'selected' : '' }}>Dipinjam</option>
                    <option value="dikembalikan" {{ $status === 'dikembalikan' ? 'selected' : '' }}>Dikembalikan</option>
                    <option value="selesai"      {{ $status === 'selesai'      ? 'selected' : '' }}>Selesai</option>
                    <option value="ditolak"      {{ $status === 'ditolak'      ? 'selected' : '' }}>Ditolak</option>
                </select>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm">
                <thead>
                    <tr class="bg-gray-100 text-gray-600 uppercase tracking-wider text-xs">
                        <th class="py-3 px-4 border-b">Peminjam</th>
                        <th class="py-3 px-4 border-b">Alat yang Dipinjam</th>
                        <th class="py-3 px-4 border-b">Tgl Pinjam</th>
                        <th class="py-3 px-4 border-b">Rencana Kembali</th>
                        <th class="py-3 px-4 border-b">Status</th>
                        <th class="py-3 px-4 border-b">Aksi</th>
                    </tr>
                </thead>
                <tbody class="text-gray-700">
                    @forelse($peminjamans as $item)
                        <tr class="hover:bg-gray-50 transition align-top">
                            <td class="py-3 px-4 border-b">
                                <p class="font-semibold text-gray-900">{{ $item->user->name ?? '-' }}</p>
                                <p class="text-xs text-gray-400">{{ $item->user->no_hp ?? '' }}</p>
                            </td>
                            <td class="py-3 px-4 border-b">
                                <ul class="list-disc list-inside space-y-0.5">
                                    @foreach($item->detailPinjam as $detail)
                                        <li class="text-xs">
                                            {{ $detail->alat->nama_alat ?? 'Alat dihapus' }}
                                            <span class="text-gray-400">({{ $detail->jumlah }} pcs)</span>
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
                                <span class="{{ $telat ? 'text-red-600 font-semibold' : 'text-gray-700' }}">
                                    {{ $item->tgl_kembali_plan }}
                                </span>
                                @if($telat)
                                    <span class="block text-red-500 text-xs font-normal">
                                        Telat {{ \Carbon\Carbon::today()->diffInDays(\Carbon\Carbon::parse($item->tgl_kembali_plan)) }} hari
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 px-4 border-b">
                                <span class="px-2.5 py-1 text-xs font-semibold rounded-full
                                    @if($item->status === 'diajukan')     bg-yellow-100 text-yellow-800
                                    @elseif($item->status === 'dipinjam') bg-blue-100 text-blue-800
                                    @elseif($item->status === 'selesai')  bg-emerald-100 text-emerald-800
                                    @elseif($item->status === 'ditolak')  bg-red-100 text-red-800
                                    @else bg-gray-100 text-gray-700 @endif">
                                    {{ ucfirst($item->status) }}
                                </span>
                            </td>
                            <td class="py-3 px-4 border-b">
                                <div class="flex flex-col space-y-2 min-w-max">

                                    {{-- Setujui (hanya jika diajukan) --}}
                                    @if($item->status === 'diajukan')
                                        <form action="{{ route('petugas.peminjaman.setujui', $item->id) }}"
                                              method="POST"
                                              onsubmit="return confirm('Setujui peminjaman ini? Stok alat akan dikurangi.')">
                                            @csrf
                                            <button type="submit"
                                                    class="w-full bg-emerald-500 hover:bg-emerald-600 text-white px-3 py-1.5 rounded text-xs font-semibold transition">
                                                Setujui
                                            </button>
                                        </form>
                                        <form action="{{ route('petugas.peminjaman.tolak', $item->id) }}"
                                              method="POST"
                                              onsubmit="return confirm('Tolak peminjaman ini?')">
                                            @csrf
                                            <button type="submit"
                                                    class="w-full bg-red-500 hover:bg-red-600 text-white px-3 py-1.5 rounded text-xs font-semibold transition">
                                                Tolak
                                            </button>
                                        </form>

                                    {{-- Proses Pengembalian (jika dipinjam / dikembalikan) --}}
                                    @elseif(in_array($item->status, ['dipinjam', 'dikembalikan']))
                                        <div x-data="{ open: false }">
                                            <button onclick="toggleForm('form-{{ $item->id }}')"
                                                    class="w-full bg-amber-500 hover:bg-amber-600 text-white px-3 py-1.5 rounded text-xs font-semibold transition">
                                                Proses Kembali
                                            </button>
                                        </div>
                                        <div id="form-{{ $item->id }}" class="hidden mt-1 p-3 bg-gray-50 border border-gray-200 rounded-lg space-y-2">
                                            <form action="{{ route('petugas.pengembalian.proses', $item->id) }}" method="POST">
                                                @csrf
                                                <div class="mb-1">
                                                    <label class="text-xs text-gray-600 font-semibold">Kondisi Alat</label>
                                                    <select name="kondisi_kembali" required
                                                            class="w-full mt-1 px-2 py-1.5 text-xs border border-gray-300 rounded focus:outline-none">
                                                        <option value="Baik">Baik</option>
                                                        <option value="Rusak Ringan">Rusak Ringan</option>
                                                        <option value="Rusak Berat">Rusak Berat</option>
                                                    </select>
                                                </div>
                                                <div class="mb-2">
                                                    <label class="text-xs text-gray-600 font-semibold">Denda (Rp)</label>
                                                    <input type="number" name="denda" value="0" min="0"
                                                           class="w-full mt-1 px-2 py-1.5 text-xs border border-gray-300 rounded focus:outline-none">
                                                </div>
                                                <button type="submit"
                                                        class="w-full bg-green-600 hover:bg-green-700 text-white px-2 py-1.5 rounded text-xs font-semibold transition">
                                                    Konfirmasi Kembali
                                                </button>
                                            </form>
                                        </div>
                                    @else
                                        <span class="text-xs text-gray-400 italic">Selesai</span>
                                    @endif

                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-6 text-center text-gray-400">
                                Tidak ada data peminjaman
                                @if($status) dengan status <strong>{{ $status }}</strong> @endif.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <script>
        function toggleForm(id) {
            const el = document.getElementById(id);
            el.classList.toggle('hidden');
        }
    </script>

@endsection
