<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Peminjaman - Cetak</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; font-size: 12px; color: #111; padding: 20px; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #333; padding-bottom: 12px; }
        .header h2 { font-size: 16px; font-weight: bold; margin-bottom: 4px; }
        .header p  { font-size: 11px; color: #555; }
        .summary { display: flex; gap: 20px; margin-bottom: 16px; }
        .summary-box { border: 1px solid #d1d5db; padding: 8px 16px; border-radius: 6px; text-align: center; }
        .summary-box p:first-child { font-size: 18px; font-weight: bold; }
        .summary-box p:last-child { font-size: 10px; color: #6b7280; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
        th { background: #e5e7eb; padding: 6px 8px; text-align: left; font-size: 10px; text-transform: uppercase; border: 1px solid #d1d5db; }
        td { padding: 5px 8px; border: 1px solid #e5e7eb; vertical-align: top; }
        tr:nth-child(even) { background: #f9fafb; }
        .print-btn { margin-bottom: 16px; }
        .print-btn button { padding: 6px 16px; background: #1d4ed8; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 12px; }
        @media print { .print-btn { display: none; } body { padding: 0; } }
    </style>
</head>
<body>
    <div class="print-btn">
        <button onclick="window.print()">&#128438; Cetak / Print</button>
        <a href="{{ route('admin.laporan.index', ['bulan' => $bulan, 'status' => $status]) }}"
           style="margin-left:12px; font-size:12px; color:#374151; text-decoration:none;">← Kembali</a>
    </div>

    <div class="header">
        <h2>LAPORAN DATA PEMINJAMAN ALAT</h2>
        <p>Bulan: {{ \Carbon\Carbon::parse($bulan . '-01')->translatedFormat('F Y') }}
            @if($status) &nbsp;|&nbsp; Status: {{ ucfirst($status) }} @endif</p>
        <p>Dicetak: {{ now()->format('d M Y, H:i') }} &nbsp;|&nbsp; Oleh: {{ auth()->user()->name }}</p>
    </div>

    <div class="summary">
        <div class="summary-box"><p>{{ $peminjamans->count() }}</p><p>Total Transaksi</p></div>
        <div class="summary-box"><p>{{ $peminjamans->where('status','selesai')->count() }}</p><p>Selesai</p></div>
        <div class="summary-box"><p>{{ $peminjamans->where('status','dipinjam')->count() }}</p><p>Masih Dipinjam</p></div>
        <div class="summary-box"><p style="color:#dc2626;">Rp {{ number_format($totalDenda,0,',','.') }}</p><p>Total Denda</p></div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width:4%">No</th>
                <th>Peminjam</th>
                <th>Alat yang Dipinjam</th>
                <th>Tgl Pinjam</th>
                <th>Rencana Kembali</th>
                <th>Tgl Kembali Aktual</th>
                <th>Status</th>
                <th>Denda</th>
            </tr>
        </thead>
        <tbody>
            @forelse($peminjamans as $i => $p)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $p->user->name ?? '-' }}</td>
                    <td>
                        @foreach($p->detailPinjam as $d)
                            {{ $d->alat->nama_alat ?? '-' }} ({{ $d->jumlah }})@if(!$loop->last), @endif
                        @endforeach
                    </td>
                    <td>{{ $p->tgl_pinjam }}</td>
                    <td>{{ $p->tgl_kembali_plan }}</td>
                    <td>{{ optional($p->pengembalian)->tgl_kembali ?? '-' }}</td>
                    <td>{{ ucfirst($p->status) }}</td>
                    <td>
                        @if(optional($p->pengembalian)->denda > 0)
                            Rp {{ number_format($p->pengembalian->denda,0,',','.') }}
                        @else -
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="8" style="text-align:center;padding:16px;color:#9ca3af;">Tidak ada data.</td></tr>
            @endforelse
        </tbody>
    </table>

    <p style="font-size:11px;color:#6b7280;text-align:right;">
        Total {{ $peminjamans->count() }} transaksi &nbsp;|&nbsp;
        Total Denda: <strong style="color:#dc2626;">Rp {{ number_format($totalDenda,0,',','.') }}</strong>
    </p>
</body>
</html>
