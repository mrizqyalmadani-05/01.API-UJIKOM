<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Panel Peminjam')</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 font-sans antialiased">

    <div class="flex h-screen overflow-hidden">

        <!-- SIDEBAR -->
        <aside class="w-64 bg-blue-900 text-white flex flex-col hidden md:flex flex-shrink-0">
            <div class="p-5 text-xl font-bold tracking-wider border-b border-blue-800">
                PEMINJAMAN ALAT
            </div>

            <nav class="flex-1 p-4 space-y-1">
                <a href="{{ route('peminjam.katalog') }}"
                   class="block px-4 py-2 rounded-lg text-sm font-medium transition
                          {{ request()->routeIs('peminjam.katalog') ? 'bg-blue-700 text-white' : 'text-blue-200 hover:bg-blue-800 hover:text-white' }}">
                    Katalog Alat
                </a>
                <a href="{{ route('peminjam.riwayat') }}"
                   class="block px-4 py-2 rounded-lg text-sm font-medium transition
                          {{ request()->routeIs('peminjam.riwayat') || request()->routeIs('peminjam.kembalikan.*') ? 'bg-blue-700 text-white' : 'text-blue-200 hover:bg-blue-800 hover:text-white' }}">
                    Riwayat & Pengembalian
                </a>
            </nav>

            <div class="p-4 border-t border-blue-800 text-xs text-blue-300">
                Login sebagai: <span class="font-semibold text-white">{{ auth()->user()->name }}</span>
            </div>
        </aside>

        <!-- MAIN -->
        <div class="flex-1 flex flex-col overflow-auto">

            <header class="bg-white shadow-sm h-16 flex items-center justify-between px-6 z-10 flex-shrink-0">
                <div class="text-lg font-semibold text-gray-800">
                    @yield('header-title', 'Panel Peminjam')
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
