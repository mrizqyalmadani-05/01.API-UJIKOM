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

@endsection
