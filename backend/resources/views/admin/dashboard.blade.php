@extends('layouts.app')

@section('title', 'Dashboard Admin')
@section('header-title', 'Dashboard Sistem Peminjaman Alat')

@section('content')

    {{-- Kartu Statistik --}}
    <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 text-center">
            <p class="text-2xl font-bold text-blue-600">{{ $totalAlat }}</p>
            <p class="text-xs text-gray-500 mt-1 font-medium uppercase tracking-wide">Total Alat</p>
        </div>
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 text-center">
            <p class="text-2xl font-bold text-purple-600">{{ $totalUser }}</p>
            <p class="text-xs text-gray-500 mt-1 font-medium uppercase tracking-wide">Total User</p>
        </div>
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 text-center">
            <p class="text-2xl font-bold text-gray-700">{{ $totalPeminjaman }}</p>
            <p class="text-xs text-gray-500 mt-1 font-medium uppercase tracking-wide">Total Peminjaman</p>
        </div>
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 text-center">
            <p class="text-2xl font-bold text-amber-500">{{ $diajukan }}</p>
            <p class="text-xs text-gray-500 mt-1 font-medium uppercase tracking-wide">Menunggu Proses</p>
        </div>
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 text-center">
            <p class="text-2xl font-bold text-emerald-600">{{ $sedangDipinjam }}</p>
            <p class="text-xs text-gray-500 mt-1 font-medium uppercase tracking-wide">Sedang Dipinjam</p>
        </div>
    </div>

    {{-- Shortcut Menu --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <a href="{{ route('admin.peminjaman.index') }}"
           class="bg-amber-500 hover:bg-amber-600 text-white rounded-lg p-4 text-center font-semibold text-sm transition shadow-sm">
            Kelola Peminjaman
        </a>
        <a href="{{ route('admin.pengembalian.index') }}"
           class="bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg p-4 text-center font-semibold text-sm transition shadow-sm">
            Kelola Pengembalian
        </a>
        <a href="{{ route('admin.laporan.index') }}"
           class="bg-blue-600 hover:bg-blue-700 text-white rounded-lg p-4 text-center font-semibold text-sm transition shadow-sm">
            Lihat Laporan
        </a>
        <a href="{{ route('admin.log.index') }}"
           class="bg-gray-600 hover:bg-gray-700 text-white rounded-lg p-4 text-center font-semibold text-sm transition shadow-sm">
            Log Aktivitas
        </a>
    </div>

    {{-- Log Aktivitas Terbaru --}}
    <div class="bg-white rounded-lg shadow-sm overflow-hidden border border-gray-200">
        <div class="p-5 border-b border-gray-200 bg-gray-50 flex justify-between items-center">
            <h3 class="text-base font-bold text-gray-800">Log Aktivitas Terbaru</h3>
            <a href="{{ route('admin.log.index') }}"
               class="text-xs text-blue-600 hover:underline font-medium">Lihat semua &rarr;</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left border-collapse">
                <thead>
                    <tr class="bg-gray-100 text-gray-600 uppercase text-xs tracking-wider">
                        <th class="py-3 px-4 border-b">Waktu</th>
                        <th class="py-3 px-4 border-b">User</th>
                        <th class="py-3 px-4 border-b">Aktivitas</th>
                    </tr>
                </thead>
                <tbody class="text-gray-700">
                    @forelse($logs as $log)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="py-3 px-4 border-b text-xs text-gray-500 whitespace-nowrap">
                                {{ $log->created_at->format('d M Y, H:i') }}
                            </td>
                            <td class="py-3 px-4 border-b font-medium">{{ $log->user->name ?? 'Sistem' }}</td>
                            <td class="py-3 px-4 border-b">{{ $log->aktivitas }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="py-4 text-center text-gray-400">Belum ada aktivitas.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

@endsection
