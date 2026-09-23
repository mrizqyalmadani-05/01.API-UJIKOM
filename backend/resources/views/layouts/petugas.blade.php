<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Panel Petugas')</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 font-sans antialiased">

    <div class="flex h-screen overflow-hidden">

        <!-- SIDEBAR -->
        <aside class="w-64 bg-green-900 text-white flex flex-col hidden md:flex flex-shrink-0">
            <div class="p-5 text-xl font-bold tracking-wider border-b border-green-800">
                PANEL PETUGAS
            </div>

            <nav class="flex-1 p-4 space-y-1">
                <a href="{{ route('petugas.peminjaman.index') }}"
                   class="block px-4 py-2 rounded-lg text-sm font-medium transition
                          {{ request()->routeIs('petugas.peminjaman.*') ? 'bg-green-700 text-white' : 'text-green-200 hover:bg-green-800 hover:text-white' }}">
                    Kelola Peminjaman
                </a>
                <a href="{{ route('petugas.pengembalian.monitor') }}"
                   class="block px-4 py-2 rounded-lg text-sm font-medium transition
                          {{ request()->routeIs('petugas.pengembalian.*') ? 'bg-green-700 text-white' : 'text-green-200 hover:bg-green-800 hover:text-white' }}">
                    Pantau Pengembalian
                </a>
                <a href="{{ route('petugas.laporan.index') }}"
                   class="block px-4 py-2 rounded-lg text-sm font-medium transition
                          {{ request()->routeIs('petugas.laporan.*') ? 'bg-green-700 text-white' : 'text-green-200 hover:bg-green-800 hover:text-white' }}">
                    Cetak Laporan
                </a>
            </nav>

            <div class="p-4 border-t border-green-800 text-xs text-green-300">
                Login sebagai: <span class="font-semibold text-white">{{ auth()->user()->name }}</span>
            </div>
        </aside>

        <!-- MAIN -->
        <div class="flex-1 flex flex-col overflow-auto">

            <header class="bg-white shadow-sm h-16 flex items-center justify-between px-6 z-10 flex-shrink-0">
                <div class="text-lg font-semibold text-gray-800">
                    @yield('header-title', 'Panel Petugas')
                </div>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit"
                            class="bg-red-500 hover:bg-red-600 text-white text-sm font-semibold px-4 py-2 rounded-lg transition">
                        Logout
                    </button>
                </form>
            </header>

            <main class="flex-1 p-6">
                @yield('content')
            </main>
        </div>
    </div>

</body>
</html>
