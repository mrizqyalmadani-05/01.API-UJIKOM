@extends('layouts.app')

@section('title', 'Log Aktivitas - Admin')
@section('header-title', 'Log Aktivitas Sistem')

@section('content')

    <div class="bg-white rounded-lg shadow-sm overflow-hidden border border-gray-200">
        <div class="p-5 border-b border-gray-200 bg-gray-50 flex flex-col md:flex-row justify-between items-center gap-4">
            <h3 class="text-lg font-bold text-gray-800">Semua Log Aktivitas</h3>
            <form action="{{ route('admin.log.index') }}" method="GET" class="flex w-full md:w-80">
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Cari nama user / aktivitas..."
                       class="w-full px-3 py-2 text-sm border border-gray-300 rounded-l-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                <button type="submit"
                        class="bg-gray-800 hover:bg-gray-900 text-white px-4 py-2 text-sm font-semibold rounded-r-lg transition">
                    Cari
                </button>
                @if(request('search'))
                    <a href="{{ route('admin.log.index') }}"
                       class="ml-2 bg-gray-300 hover:bg-gray-400 text-gray-700 px-3 py-2 text-sm rounded-lg flex items-center">
                        Reset
                    </a>
                @endif
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left border-collapse">
                <thead>
                    <tr class="bg-gray-100 text-gray-600 uppercase text-xs tracking-wider">
                        <th class="py-3 px-4 border-b w-44">Waktu</th>
                        <th class="py-3 px-4 border-b w-48">User</th>
                        <th class="py-3 px-4 border-b">Role</th>
                        <th class="py-3 px-4 border-b">Aktivitas</th>
                    </tr>
                </thead>
                <tbody class="text-gray-700">
                    @forelse($logs as $log)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="py-3 px-4 border-b text-xs text-gray-500 whitespace-nowrap">
                                {{ $log->created_at->format('d M Y, H:i:s') }}
                            </td>
                            <td class="py-3 px-4 border-b font-medium">{{ $log->user->name ?? 'Sistem' }}</td>
                            <td class="py-3 px-4 border-b">
                                @if($log->user)
                                    <span class="px-2 py-0.5 text-xs font-semibold rounded-full
                                        @if($log->user->role === 'admin') bg-purple-100 text-purple-800
                                        @elseif($log->user->role === 'petugas') bg-blue-100 text-blue-800
                                        @else bg-green-100 text-green-800 @endif">
                                        {{ ucfirst($log->user->role) }}
                                    </span>
                                @else
                                    <span class="text-gray-400 text-xs">-</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 border-b">{{ $log->aktivitas }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="py-6 text-center text-gray-400">Belum ada log aktivitas.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-gray-200 bg-gray-50">
            {{ $logs->links() }}
        </div>
    </div>

@endsection
