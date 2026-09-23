<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dashboard Admin')</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 font-sans antialiased">

    <div class="flex h-screen overflow-hidden">

        <!-- SIDEBAR -->
        <aside class="w-64 bg-gray-900 text-white flex flex-col hidden md:flex flex-shrink-0">
            <div class="p-5 text-xl font-bold tracking-wider border-b border-gray-800">
                PANEL ADMIN
            </div>

            <nav class="flex-1 p-4 space-y-1">
                <a href="{{ route('admin.dashboard') }}"
                   class="block px-4 py-2 rounded-lg text-sm font-medium transition
                          {{ request()->routeIs('admin.dashboard') ? 'bg-gray-700 text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">
                    Dashboard
                </a>
                <a href="{{ route('admin.user.index') }}"
                   class="block px-4 py-2 rounded-lg text-sm font-medium transition
                          {{ request()->routeIs('admin.user.*') ? 'bg-gray-700 text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">
                    Kelola User
                </a>
                <a href="{{ route('admin.kategori.index') }}"
                   class="block px-4 py-2 rounded-lg text-sm font-medium transition
                          {{ request()->routeIs('admin.kategori.*') ? 'bg-gray-700 text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">
                    Kelola Kategori
                </a>
                <a href="{{ route('admin.alat.index') }}"
                   class="block px-4 py-2 rounded-lg text-sm font-medium transition
                          {{ request()->routeIs('admin.alat.*') ? 'bg-gray-700 text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">
                    Kelola Alat
                </a>
                {{-- Menu Kelola Peminjaman --}}
                <a href="{{ route('admin.peminjaman.index') }}"
                   class="block px-4 py-2 rounded-lg text-sm font-medium transition
                          {{ request()->routeIs('admin.peminjaman.*') ? 'bg-gray-700 text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">
                    Kelola Peminjaman
                </a>
                {{-- Menu Kelola Pengembalian --}}
                <a href="{{ route('admin.pengembalian.index') }}"
                   class="block px-4 py-2 rounded-lg text-sm font-medium transition
                          {{ request()->routeIs('admin.pengembalian.*') ? 'bg-gray-700 text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">
                    Kelola Pengembalian
                </a>
                {{-- Laporan --}}
                <a href="{{ route('admin.laporan.index') }}"
                   class="block px-4 py-2 rounded-lg text-sm font-medium transition
                          {{ request()->routeIs('admin.laporan.*') ? 'bg-gray-700 text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">
                    Laporan
                </a>
                {{-- Log Aktivitas --}}
                <a href="{{ route('admin.log.index') }}"
                   class="block px-4 py-2 rounded-lg text-sm font-medium transition
                          {{ request()->routeIs('admin.log.*') ? 'bg-gray-700 text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">
                    Log Aktivitas
                </a>
            </nav>

            <div class="p-4 border-t border-gray-800 text-xs text-gray-400">
                Logged in as: <span class="font-semibold text-white">{{ auth()->user()->name }}</span>
            </div>
        </aside>

        <!-- MAIN CONTENT CONTAINER -->
        <div class="flex-1 flex flex-col overflow-auto">

            <!-- NAVBAR ATAS -->
            <header class="bg-white shadow-sm h-16 flex items-center justify-between px-6 z-10 flex-shrink-0">
                <div class="text-lg font-semibold text-gray-800">
                    @yield('header-title', 'Dashboard')
                </div>
                <div>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit"
                                class="bg-red-500 hover:bg-red-600 text-white text-sm font-semibold px-4 py-2 rounded-lg transition">
                            Logout
                        </button>
                    </form>
                </div>
            </header>

            <!-- KONTEN UTAMA HALAMAN -->
            <main class="flex-1 p-6">
                @yield('content')
            </main>

        </div>
    </div>

</body>
</html>
