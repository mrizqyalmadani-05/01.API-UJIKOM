@php
    /**
     * Tentukan URL tujuan tombol aksi peminjaman berdasarkan status login.
     *
     * - Belum login         → /login?redirect=/peminjam/katalog
     * - Login sbg peminjam  → /peminjam/katalog
     * - Login sbg admin     → /admin/dashboard
     * - Login sbg petugas   → /petugas/peminjaman
     */
    $katalogUrl = route('peminjam.katalog');

    if (auth()->check()) {
        $role = auth()->user()->role;
        $borrowTarget = match($role) {
            'admin'   => route('admin.dashboard'),
            'petugas' => route('petugas.peminjaman.index'),
            default   => route('peminjam.katalog'),
        };
    } else {
        // Belum login: arahkan ke login dengan intended URL
        $borrowTarget = route('login') . '?redirect=' . urlencode('/peminjam/katalog');
    }
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Peminjaman Alat - Layanan Peminjaman Internal</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --navy:   #1E3A8A;
            --blue:   #1D4ED8;
            --bg-light: #F8FAFC;
            --text-main: #0F172A;
            --text-sec: #64748B;
            --border: #E2E8F0;
            --green: #15803D;
        }

        html { scroll-behavior: smooth; }

        body {
            font-family: 'Inter', system-ui, sans-serif;
            color: var(--text-main);
            background: #fff;
            font-size: 16px;
            line-height: 1.6;
        }

        /* ───── NAVBAR ───── */
        .navbar {
            position: sticky;
            top: 0;
            z-index: 100;
            background: #fff;
            border-bottom: 1px solid var(--border);
            padding: 0 64px;
            height: 68px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .nav-logo {
            font-size: 17px;
            font-weight: 800;
            color: var(--navy);
            letter-spacing: 0.04em;
            text-decoration: none;
        }

        .nav-links {
            display: flex;
            align-items: center;
            gap: 32px;
            list-style: none;
        }

        .nav-links a {
            font-size: 15px;
            font-weight: 500;
            color: var(--text-sec);
            text-decoration: none;
            transition: color .2s;
        }

        .nav-links a:hover { color: var(--navy); }

        .nav-actions {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .btn { display: inline-flex; align-items: center; justify-content: center; border-radius: 8px; font-size: 15px; font-weight: 600; cursor: pointer; transition: background .2s, color .2s, border-color .2s, box-shadow .2s; text-decoration: none; white-space: nowrap; }
        .btn-sm { padding: 8px 18px; font-size: 14px; }
        .btn-md { padding: 11px 24px; }
        .btn-lg { padding: 14px 28px; font-size: 16px; }

        .btn-outline-navy { border: 2px solid var(--navy); color: var(--navy); background: transparent; }
        .btn-outline-navy:hover { background: var(--navy); color: #fff; }

        .btn-blue { background: var(--blue); color: #fff; border: 2px solid var(--blue); }
        .btn-blue:hover { background: #1a44c8; border-color: #1a44c8; box-shadow: 0 4px 14px rgba(29,78,216,.35); }

        .btn-white { background: #fff; color: var(--navy); border: 2px solid #fff; }
        .btn-white:hover { background: #f1f5f9; }

        .btn-outline-white { background: transparent; color: #fff; border: 2px solid rgba(255,255,255,.65); }
        .btn-outline-white:hover { background: rgba(255,255,255,.12); border-color: #fff; }

        /* Hamburger */
        .hamburger { display: none; flex-direction: column; gap: 5px; background: none; border: none; cursor: pointer; padding: 4px; }
        .hamburger span { display: block; width: 24px; height: 2px; background: var(--navy); border-radius: 2px; transition: .3s; }

        .mobile-menu {
            display: none;
            flex-direction: column;
            gap: 0;
            background: #fff;
            border-bottom: 1px solid var(--border);
            padding: 12px 24px 20px;
        }

        .mobile-menu.open { display: flex; }
        .mobile-menu a { font-size: 15px; font-weight: 500; color: var(--text-sec); padding: 10px 0; text-decoration: none; border-bottom: 1px solid var(--border); }
        .mobile-menu a:last-of-type { border-bottom: none; }
        .mobile-menu .mobile-actions { display: flex; gap: 10px; margin-top: 14px; }
        .mobile-menu .mobile-actions .btn { flex: 1; justify-content: center; }

        /* ───── HERO ───── */
        .hero {
            background: var(--navy);
            color: #fff;
            padding: 80px 64px;
        }

        .hero-inner {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: 1fr 400px;
            gap: 60px;
            align-items: center;
        }

        .hero-badge {
            display: inline-block;
            background: rgba(255,255,255,.12);
            border: 1px solid rgba(255,255,255,.25);
            color: rgba(255,255,255,.9);
            font-size: 13px;
            font-weight: 600;
            padding: 5px 14px;
            border-radius: 999px;
            margin-bottom: 20px;
            letter-spacing: 0.03em;
        }

        .hero h1 {
            font-size: 52px;
            font-weight: 800;
            line-height: 1.12;
            margin-bottom: 20px;
            letter-spacing: -0.02em;
        }

        .hero p {
            font-size: 17px;
            color: rgba(255,255,255,.8);
            max-width: 540px;
            margin-bottom: 32px;
            line-height: 1.7;
        }

        .hero-actions { display: flex; gap: 12px; flex-wrap: wrap; margin-bottom: 44px; }

        .hero-stats {
            display: flex;
            gap: 32px;
            flex-wrap: wrap;
        }

        .stat-item { display: flex; flex-direction: column; }
        .stat-number { font-size: 28px; font-weight: 800; color: #fff; }
        .stat-label { font-size: 13px; color: rgba(255,255,255,.65); font-weight: 500; margin-top: 2px; }

        /* Catalog card */
        .catalog-card {
            background: #fff;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(0,0,0,.25);
        }

        .catalog-card-header {
            padding: 16px 20px;
            border-bottom: 1px solid var(--border);
        }

        .catalog-card-header p {
            font-size: 13px;
            color: var(--text-sec);
            font-weight: 500;
            margin: 0;
        }

        .catalog-card-header strong {
            font-size: 15px;
            color: var(--text-main);
            font-weight: 700;
        }

        .catalog-items { padding: 8px 0; }

        .catalog-item {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 12px 20px;
            border-bottom: 1px solid var(--border);
        }

        .catalog-item:last-child { border-bottom: none; }

        .catalog-item-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            background: #EFF6FF;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .catalog-item-icon svg { color: var(--blue); }

        .catalog-item-info { flex: 1; min-width: 0; }
        .catalog-item-info .item-name { font-size: 13px; font-weight: 600; color: var(--text-main); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .catalog-item-info .item-cat { font-size: 12px; color: var(--text-sec); margin-top: 2px; }

        .item-stock {
            font-size: 12px;
            font-weight: 700;
            color: var(--green);
            white-space: nowrap;
        }

        .catalog-card-footer {
            padding: 14px 20px;
            border-top: 1px solid var(--border);
        }

        .catalog-card-footer .btn { width: 100%; }

        /* ───── SECTION WRAPPER ───── */
        .section { padding: 96px 64px; }
        .section-inner { max-width: 1200px; margin: 0 auto; }

        .section-label {
            display: inline-block;
            font-size: 12px;
            font-weight: 700;
            color: var(--blue);
            letter-spacing: 0.1em;
            text-transform: uppercase;
            margin-bottom: 12px;
        }

        .section-title {
            font-size: 38px;
            font-weight: 800;
            color: var(--text-main);
            line-height: 1.18;
            letter-spacing: -0.02em;
            margin-bottom: 12px;
        }

        .section-sub {
            font-size: 17px;
            color: var(--text-sec);
            max-width: 560px;
            line-height: 1.7;
        }

        /* ───── KEUNGGULAN ───── */
        .keunggulan { background: var(--bg-light); }

        .keunggulan-header { margin-bottom: 52px; }

        .cards-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 24px;
        }

        .card {
            background: #fff;
            border: 1px solid var(--border);
            border-radius: 14px;
            padding: 32px 28px;
        }

        .card-icon {
            width: 48px;
            height: 48px;
            background: #EFF6FF;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 20px;
        }

        .card h3 {
            font-size: 19px;
            font-weight: 700;
            color: var(--text-main);
            margin-bottom: 10px;
        }

        .card p {
            font-size: 15px;
            color: var(--text-sec);
            line-height: 1.7;
        }

        /* ───── CARA KERJA ───── */
        .cara-kerja { background: #fff; }
        .cara-kerja-header { margin-bottom: 60px; text-align: center; }
        .cara-kerja-header .section-sub { margin: 0 auto; }

        .steps-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 32px;
            position: relative;
        }

        .steps-grid::before {
            content: '';
            position: absolute;
            top: 28px;
            left: calc(16.66% + 16px);
            right: calc(16.66% + 16px);
            height: 2px;
            background: var(--border);
            z-index: 0;
        }

        .step {
            text-align: center;
            position: relative;
            z-index: 1;
        }

        .step-number {
            width: 56px;
            height: 56px;
            border-radius: 50%;
            background: var(--blue);
            color: #fff;
            font-size: 22px;
            font-weight: 800;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            box-shadow: 0 4px 16px rgba(29,78,216,.3);
        }

        .step h3 {
            font-size: 19px;
            font-weight: 700;
            margin-bottom: 10px;
            color: var(--text-main);
        }

        .step p {
            font-size: 15px;
            color: var(--text-sec);
            line-height: 1.7;
            max-width: 280px;
            margin: 0 auto;
        }

        /* ───── CTA ───── */
        .cta-section { background: #F8FAFC; padding: 80px 64px; }

        .cta-box {
            max-width: 1200px;
            margin: 0 auto;
            background: var(--navy);
            border-radius: 20px;
            padding: 64px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 40px;
        }

        .cta-text h2 {
            font-size: 34px;
            font-weight: 800;
            color: #fff;
            margin-bottom: 10px;
            letter-spacing: -0.02em;
        }

        .cta-text p {
            font-size: 16px;
            color: rgba(255,255,255,.75);
            max-width: 460px;
            line-height: 1.7;
        }

        .cta-actions { display: flex; gap: 12px; flex-shrink: 0; flex-wrap: wrap; }

        /* ───── FAQ ───── */
        .faq-section { background: #fff; }
        .faq-header { margin-bottom: 48px; text-align: center; }
        .faq-header .section-sub { margin: 0 auto; }

        .faq-list { max-width: 760px; margin: 0 auto; display: flex; flex-direction: column; gap: 12px; }

        .faq-item {
            border: 1px solid var(--border);
            border-radius: 12px;
            overflow: hidden;
        }

        .faq-question {
            width: 100%;
            background: none;
            border: none;
            text-align: left;
            padding: 20px 24px;
            font-size: 16px;
            font-weight: 600;
            color: var(--text-main);
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            transition: background .2s;
        }

        .faq-question:hover { background: var(--bg-light); }

        .faq-icon { flex-shrink: 0; color: var(--blue); transition: transform .25s; }
        .faq-item.open .faq-icon { transform: rotate(45deg); }

        .faq-answer {
            max-height: 0;
            overflow: hidden;
            transition: max-height .3s ease, padding .3s ease;
            font-size: 15px;
            color: var(--text-sec);
            line-height: 1.7;
            padding: 0 24px;
        }

        .faq-item.open .faq-answer { max-height: 300px; padding: 0 24px 20px; }

        /* ───── FOOTER ───── */
        footer {
            background: #fff;
            border-top: 1px solid var(--border);
            padding: 28px 64px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 12px;
        }

        footer .footer-logo { font-size: 15px; font-weight: 800; color: var(--navy); letter-spacing: .04em; }
        footer .footer-copy { font-size: 14px; color: var(--text-sec); }

        /* ───── RESPONSIVE ───── */
        @media (max-width: 1024px) {
            .hero-inner { grid-template-columns: 1fr; }
            .catalog-card { max-width: 440px; }
            .hero h1 { font-size: 42px; }
        }

        @media (max-width: 768px) {
            .navbar { padding: 0 24px; }
            .nav-links, .nav-actions { display: none; }
            .hamburger { display: flex; }

            .hero { padding: 60px 24px; }
            .hero h1 { font-size: 34px; }
            .hero p { font-size: 16px; }
            .hero-stats { gap: 20px; }
            .stat-number { font-size: 24px; }
            .catalog-card { max-width: 100%; }

            .section { padding: 64px 24px; }

            .cards-grid { grid-template-columns: 1fr; }
            .steps-grid { grid-template-columns: 1fr; }
            .steps-grid::before { display: none; }

            .section-title { font-size: 28px; }

            .cta-section { padding: 48px 24px; }
            .cta-box { flex-direction: column; padding: 40px 28px; text-align: center; }
            .cta-text p { max-width: 100%; margin: 0 auto; }
            .cta-actions { justify-content: center; }
            .cta-text h2 { font-size: 26px; }

            footer { padding: 20px 24px; flex-direction: column; align-items: flex-start; }
        }
    </style>
</head>
<body>

<!-- ============================================================
     NAVBAR
     ============================================================ -->
<nav class="navbar" role="navigation" aria-label="Navigasi utama">
    <a href="{{ url('/') }}" class="nav-logo">PEMINJAMAN ALAT</a>

    <ul class="nav-links">
        <li><a href="#katalog">Katalog</a></li>
        <li><a href="#cara-kerja">Cara Kerja</a></li>
        <li><a href="#keunggulan">Keunggulan</a></li>
        <li><a href="#faq">FAQ</a></li>
    </ul>

    <div class="nav-actions">
        <a href="{{ route('login') }}" class="btn btn-sm btn-outline-navy">Masuk</a>
        <a href="{{ route('register') }}" class="btn btn-sm btn-blue">Daftar</a>
    </div>

    <button class="hamburger" id="hamburgerBtn" aria-label="Buka menu" aria-expanded="false" aria-controls="mobileMenu">
        <span></span>
        <span></span>
        <span></span>
    </button>
</nav>

<!-- Mobile Menu -->
<div class="mobile-menu" id="mobileMenu" role="menu">
    <a href="#katalog" role="menuitem">Katalog</a>
    <a href="#cara-kerja" role="menuitem">Cara Kerja</a>
    <a href="#keunggulan" role="menuitem">Keunggulan</a>
    <a href="#faq" role="menuitem">FAQ</a>
    <div class="mobile-actions">
        <a href="{{ route('login') }}" class="btn btn-md btn-outline-navy">Masuk</a>
        <a href="{{ route('register') }}" class="btn btn-md btn-blue">Daftar</a>
    </div>
</div>


<!-- ============================================================
     HERO
     ============================================================ -->
<section class="hero" id="katalog">
    <div class="hero-inner">

        <!-- Left: Copy -->
        <div class="hero-left">
            <span class="hero-badge">Layanan peminjaman alat internal</span>
            <h1>Pinjam alat yang Anda butuhkan, kapan saja.</h1>
            <p>Lihat ketersediaan alat secara real-time, ajukan peminjaman dalam hitungan menit, dan pantau riwayat pengembalian Anda dari satu tempat.</p>

            <div class="hero-actions">
                <a href="{{ $borrowTarget }}" class="btn btn-lg btn-white">Mulai Meminjam</a>
                <a href="#keunggulan" class="btn btn-lg btn-outline-white">Lihat Katalog</a>
            </div>

            <div class="hero-stats">
                <div class="stat-item">
                    <span class="stat-number">120+</span>
                    <span class="stat-label">Jenis alat</span>
                </div>
                <div class="stat-item">
                    <span class="stat-number">24 jam</span>
                    <span class="stat-label">Proses persetujuan</span>
                </div>
                <div class="stat-item">
                    <span class="stat-number">1 klik</span>
                    <span class="stat-label">Ajukan peminjaman</span>
                </div>
            </div>
        </div>

        <!-- Right: Catalog Preview Card -->
        <div class="hero-right">
            <div class="catalog-card">
                <div class="catalog-card-header">
                    <strong>Katalog Alat Tersedia</strong>
                    <p>Stok diperbarui secara real-time</p>
                </div>

                <div class="catalog-items">
                    @forelse($alats as $alat)
                    <div class="catalog-item">
                        <div class="catalog-item-icon">
                            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <rect x="3" y="3" width="18" height="14" rx="2"/><path d="M8 21h8M12 17v4"/>
                            </svg>
                        </div>
                        <div class="catalog-item-info">
                            <div class="item-name">{{ $alat->nama_alat }}</div>
                            <div class="item-cat">{{ $alat->kategori->nama_kategori ?? '-' }}</div>
                        </div>
                        <span class="item-stock">{{ $alat->stok }} unit</span>
                    </div>
                    @empty
                    <div class="catalog-item" style="justify-content:center; color: var(--text-sec); font-size:13px; padding: 20px;">
                        Belum ada alat tersedia saat ini.
                    </div>
                    @endforelse
                </div>

                <div class="catalog-card-footer">
                    <a href="{{ $borrowTarget }}" class="btn btn-md btn-blue">Ajukan Peminjaman</a>
                </div>
            </div>
        </div>

    </div>
</section>


<!-- ============================================================
     KEUNGGULAN
     ============================================================ -->
<section class="section keunggulan" id="keunggulan">
    <div class="section-inner">
        <div class="keunggulan-header">
            <span class="section-label">Keunggulan</span>
            <h2 class="section-title">Semua yang Anda butuhkan<br>dalam satu sistem</h2>
            <p class="section-sub">Dari pengajuan hingga pengembalian, prosesnya tercatat dan transparan.</p>
        </div>

        <div class="cards-grid">
            <!-- Card 1 -->
            <div class="card">
                <div class="card-icon">
                    <svg width="24" height="24" fill="none" stroke="#1D4ED8" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M3 3h18M3 9h18M3 15h18M3 21h18"/><circle cx="17" cy="6" r="3" fill="#DBEAFE" stroke="none"/><circle cx="17" cy="6" r="1.5" fill="#1D4ED8" stroke="none"/>
                    </svg>
                </div>
                <h3>Stok real-time</h3>
                <p>Jumlah unit tersedia diperbarui otomatis, sehingga Anda tidak perlu menebak apakah alat masih ada.</p>
            </div>

            <!-- Card 2 -->
            <div class="card">
                <div class="card-icon">
                    <svg width="24" height="24" fill="none" stroke="#1D4ED8" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2"/><rect x="9" y="3" width="6" height="4" rx="1"/><path d="M9 12l2 2 4-4"/>
                    </svg>
                </div>
                <h3>Pengajuan multi-alat</h3>
                <p>Pilih beberapa alat sekaligus dalam satu pengajuan, lengkap dengan jumlah dan rencana tanggal kembali.</p>
            </div>

            <!-- Card 3 -->
            <div class="card">
                <div class="card-icon">
                    <svg width="24" height="24" fill="none" stroke="#1D4ED8" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M12 8v4l3 3M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <h3>Riwayat lengkap</h3>
                <p>Setiap peminjaman dan pengembalian tercatat, mudah dilacak kapan pun Anda membutuhkannya.</p>
            </div>
        </div>
    </div>
</section>


<!-- ============================================================
     CARA KERJA
     ============================================================ -->
<section class="section cara-kerja" id="cara-kerja">
    <div class="section-inner">
        <div class="cara-kerja-header">
            <span class="section-label">Cara Kerja</span>
            <h2 class="section-title">Cara meminjam dalam 3 langkah</h2>
            <p class="section-sub">Proses yang sederhana dan cepat — dari pilih alat hingga pengajuan selesai dalam hitungan menit.</p>
        </div>

        <div class="steps-grid">
            <div class="step">
                <div class="step-number">1</div>
                <h3>Pilih alat</h3>
                <p>Centang alat yang ingin dipinjam dan tentukan jumlahnya di katalog.</p>
            </div>
            <div class="step">
                <div class="step-number">2</div>
                <h3>Ajukan peminjaman</h3>
                <p>Isi rencana tanggal kembali lalu kirim pengajuan. Tim akan meninjau permintaan Anda.</p>
            </div>
            <div class="step">
                <div class="step-number">3</div>
                <h3>Ambil dan kembalikan</h3>
                <p>Ambil alat setelah disetujui, lalu kembalikan tepat waktu sesuai rencana.</p>
            </div>
        </div>
    </div>
</section>


<!-- ============================================================
     CTA
     ============================================================ -->
<section class="cta-section">
    <div class="cta-box">
        <div class="cta-text">
            <h2>Siap meminjam alat hari ini?</h2>
            <p>Masuk dengan akun Anda dan mulai ajukan peminjaman sekarang.</p>
        </div>
        <div class="cta-actions">
            <a href="{{ route('login') }}" class="btn btn-lg btn-white">Masuk ke Akun</a>
            <a href="#cara-kerja" class="btn btn-lg btn-outline-white">Pelajari Lebih Lanjut</a>
        </div>
    </div>
</section>


<!-- ============================================================
     FAQ
     ============================================================ -->
<section class="section faq-section" id="faq">
    <div class="section-inner">
        <div class="faq-header">
            <span class="section-label">FAQ</span>
            <h2 class="section-title">Pertanyaan yang sering diajukan</h2>
            <p class="section-sub">Jawaban cepat untuk pertanyaan umum seputar layanan peminjaman alat.</p>
        </div>

        <div class="faq-list">
            <div class="faq-item">
                <button class="faq-question" aria-expanded="false">
                    Siapa yang bisa menggunakan layanan ini?
                    <svg class="faq-icon" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>
                </button>
                <div class="faq-answer">Layanan ini ditujukan untuk karyawan atau anggota organisasi yang sudah memiliki akun terdaftar. Daftar akun terlebih dahulu, lalu akun akan diaktifkan oleh admin.</div>
            </div>

            <div class="faq-item">
                <button class="faq-question" aria-expanded="false">
                    Berapa lama proses persetujuan peminjaman?
                    <svg class="faq-icon" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>
                </button>
                <div class="faq-answer">Petugas biasanya memproses pengajuan dalam 24 jam kerja. Anda akan dapat melihat statusnya langsung di halaman riwayat peminjaman.</div>
            </div>

            <div class="faq-item">
                <button class="faq-question" aria-expanded="false">
                    Bisakah saya meminjam lebih dari satu alat sekaligus?
                    <svg class="faq-icon" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>
                </button>
                <div class="faq-answer">Ya. Anda bisa memilih beberapa alat sekaligus dari katalog dan mengirimkan satu pengajuan peminjaman yang mencakup semua alat tersebut.</div>
            </div>

            <div class="faq-item">
                <button class="faq-question" aria-expanded="false">
                    Bagaimana cara mengembalikan alat?
                    <svg class="faq-icon" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>
                </button>
                <div class="faq-answer">Buka halaman riwayat peminjaman, pilih peminjaman yang ingin dikembalikan, lalu klik tombol "Kembalikan". Petugas akan memverifikasi pengembalian secara langsung.</div>
            </div>
        </div>
    </div>
</section>


<!-- ============================================================
     FOOTER
     ============================================================ -->
<footer>
    <span class="footer-logo">PEMINJAMAN ALAT</span>
    <span class="footer-copy">&copy; 2026 Peminjaman Alat. Hak cipta dilindungi.</span>
</footer>


<script>
    // ── Hamburger menu ──
    const btn  = document.getElementById('hamburgerBtn');
    const menu = document.getElementById('mobileMenu');

    btn.addEventListener('click', () => {
        const open = menu.classList.toggle('open');
        btn.setAttribute('aria-expanded', open);
        btn.setAttribute('aria-label', open ? 'Tutup menu' : 'Buka menu');
    });

    // Close mobile menu on nav link click
    menu.querySelectorAll('a[href^="#"]').forEach(link => {
        link.addEventListener('click', () => {
            menu.classList.remove('open');
            btn.setAttribute('aria-expanded', 'false');
        });
    });

    // ── FAQ accordion ──
    document.querySelectorAll('.faq-question').forEach(q => {
        q.addEventListener('click', () => {
            const item = q.closest('.faq-item');
            const isOpen = item.classList.contains('open');

            // Close all
            document.querySelectorAll('.faq-item').forEach(i => {
                i.classList.remove('open');
                i.querySelector('.faq-question').setAttribute('aria-expanded', 'false');
            });

            // Toggle clicked
            if (!isOpen) {
                item.classList.add('open');
                q.setAttribute('aria-expanded', 'true');
            }
        });
    });
</script>

</body>
</html>
