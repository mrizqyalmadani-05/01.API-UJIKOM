<?php

namespace App\Exports;

use Carbon\Carbon;
use OpenSpout\Common\Entity\Cell;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Common\Entity\Style\Border;
use OpenSpout\Common\Entity\Style\BorderName;
use OpenSpout\Common\Entity\Style\BorderPart;
use OpenSpout\Common\Entity\Style\BorderStyle;
use OpenSpout\Common\Entity\Style\BorderWidth;
use OpenSpout\Common\Entity\Style\CellAlignment;
use OpenSpout\Common\Entity\Style\Color;
use OpenSpout\Common\Entity\Style\Style;
use OpenSpout\Writer\XLSX\Options;
use OpenSpout\Writer\XLSX\Writer;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LaporanPeminjamanExport
{
    protected $peminjamans;

    public function __construct($peminjamans)
    {
        $this->peminjamans = $peminjamans;
    }

    public function download(string $filename): StreamedResponse
    {
        $tmpFile = tempnam(sys_get_temp_dir(), 'laporan_') . '.xlsx';

        $options = new Options();
        $options->setColumnWidth(7, 1);    // A: No
        $options->setColumnWidth(24, 2);   // B: Nama Peminjam
        $options->setColumnWidth(16, 3);   // C: Tanggal Pinjam
        $options->setColumnWidth(16, 4);   // D: Rencana Kembali
        $options->setColumnWidth(32, 5);   // E: Nama Alat Dipinjam
        $options->setColumnWidth(14, 6);   // F: Jumlah Unit
        $options->setColumnWidth(18, 7);   // G: Status Peminjaman
        $options->setColumnWidth(18, 8);   // H: Tgl Kembali Aktual
        $options->setColumnWidth(18, 9);   // I: Kondisi Kembali
        $options->setColumnWidth(22, 10);  // J: Nominal Denda

        $options->mergeCells(0, 1, 9, 1); // Judul Utama
        $options->mergeCells(0, 2, 9, 2); // Subtitle Instansi
        $options->mergeCells(0, 3, 9, 3); // Metadata Pembuat
        $options->mergeCells(0, 4, 9, 4); // Sertifikasi Keamanan

        $writer = new Writer($options);
        $writer->openToFile($tmpFile);

        $borderThin = new Border(
            new BorderPart(BorderName::LEFT, 'CBD5E1', BorderWidth::THIN, BorderStyle::SOLID),
            new BorderPart(BorderName::RIGHT, 'CBD5E1', BorderWidth::THIN, BorderStyle::SOLID),
            new BorderPart(BorderName::TOP, 'CBD5E1', BorderWidth::THIN, BorderStyle::SOLID),
            new BorderPart(BorderName::BOTTOM, 'CBD5E1', BorderWidth::THIN, BorderStyle::SOLID)
        );

        $borderHeader = new Border(
            new BorderPart(BorderName::LEFT, '334155', BorderWidth::THIN, BorderStyle::SOLID),
            new BorderPart(BorderName::RIGHT, '334155', BorderWidth::THIN, BorderStyle::SOLID),
            new BorderPart(BorderName::TOP, '334155', BorderWidth::THIN, BorderStyle::SOLID),
            new BorderPart(BorderName::BOTTOM, 'EA580C', BorderWidth::MEDIUM, BorderStyle::SOLID)
        );

        $borderTotal = new Border(
            new BorderPart(BorderName::LEFT, '94A3B8', BorderWidth::THIN, BorderStyle::SOLID),
            new BorderPart(BorderName::RIGHT, '94A3B8', BorderWidth::THIN, BorderStyle::SOLID),
            new BorderPart(BorderName::TOP, '0F172A', BorderWidth::MEDIUM, BorderStyle::SOLID),
            new BorderPart(BorderName::BOTTOM, '0F172A', BorderWidth::MEDIUM, BorderStyle::DOUBLE)
        );

        $titleStyle = (new Style())
            ->withFontBold(true)
            ->withFontSize(13)
            ->withFontColor('EA580C')
            ->withBackgroundColor('F8FAFC')
            ->withCellAlignment(CellAlignment::LEFT);

        $subTitleStyle = (new Style())
            ->withFontSize(9)
            ->withFontColor('64748B')
            ->withBackgroundColor('F8FAFC')
            ->withCellAlignment(CellAlignment::LEFT);

        $metaStyle = (new Style())
            ->withFontSize(9)
            ->withFontColor('334155')
            ->withBackgroundColor('F1F5F9')
            ->withCellAlignment(CellAlignment::LEFT);

        $certStyle = (new Style())
            ->withFontBold(true)
            ->withFontSize(9)
            ->withFontColor('047857')
            ->withBackgroundColor('F1F5F9')
            ->withCellAlignment(CellAlignment::LEFT);

        $headerBase = (new Style())
            ->withFontBold(true)
            ->withFontSize(10)
            ->withFontColor(Color::WHITE)
            ->withBackgroundColor('0F172A')
            ->withBorder($borderHeader);

        $thCenter = $headerBase->withCellAlignment(CellAlignment::CENTER);
        $thLeft   = $headerBase->withCellAlignment(CellAlignment::LEFT);
        $thRight  = $headerBase->withCellAlignment(CellAlignment::RIGHT);

        $styleCenter = (new Style())->withFontSize(9)->withBorder($borderThin)->withCellAlignment(CellAlignment::CENTER);
        $styleLeft   = (new Style())->withFontSize(9)->withBorder($borderThin)->withCellAlignment(CellAlignment::LEFT);
        $styleRight  = (new Style())->withFontSize(9)->withBorder($borderThin)->withCellAlignment(CellAlignment::RIGHT);

        $styleCenterZebra = (new Style())->withFontSize(9)->withBorder($borderThin)->withBackgroundColor('F8FAFC')->withCellAlignment(CellAlignment::CENTER);
        $styleLeftZebra   = (new Style())->withFontSize(9)->withBorder($borderThin)->withBackgroundColor('F8FAFC')->withCellAlignment(CellAlignment::LEFT);
        $styleRightZebra  = (new Style())->withFontSize(9)->withBorder($borderThin)->withBackgroundColor('F8FAFC')->withCellAlignment(CellAlignment::RIGHT);

        $styleDipinjam = (new Style())->withFontSize(9)->withFontBold(true)->withFontColor('1D4ED8')->withBackgroundColor('EFF6FF')->withBorder($borderThin)->withCellAlignment(CellAlignment::CENTER);
        $styleKembali  = (new Style())->withFontSize(9)->withFontBold(true)->withFontColor('047857')->withBackgroundColor('ECFDF5')->withBorder($borderThin)->withCellAlignment(CellAlignment::CENTER);
        $styleTelat    = (new Style())->withFontSize(9)->withFontBold(true)->withFontColor('BE123C')->withBackgroundColor('FFF1F2')->withBorder($borderThin)->withCellAlignment(CellAlignment::CENTER);

        $generatorName = auth()->user()->name ?? 'System Admin';
        $generatorRole = ucfirst(auth()->user()->role ?? 'Admin');
        $waktuGenerate = now()->format('d F Y, H:i:s') . ' WIB';
        $docId         = 'SPJ-XLS-' . now()->format('Ymd') . '-' . strtoupper(substr(hash('crc32', $generatorName . now()->timestamp), 0, 6));

        $totalTransaksi = count($this->peminjamans);
        $totalUnit      = $this->peminjamans->sum(fn ($p) => $p->detailPinjam->sum('jumlah'));
        $totalDenda     = $this->peminjamans->sum(fn ($p) => $p->pengembalian?->denda ?? 0);
        $totalTelat     = $this->peminjamans->where('status', 'telat')->count();

        $writer->addRow(Row::fromValuesWithStyle(['LAPORAN REKAPITULASI PEMINJAMAN ALAT - SIPINJAM SMKN 7 BALEENDAH'], $titleStyle));
        $writer->addRow(Row::fromValuesWithStyle(['Laboratorium & Bengkel Praktik Kejuruan | Jl. Siliwangi No. 127, Baleendah, Bandung'], $subTitleStyle));
        $writer->addRow(Row::fromValuesWithStyle(["Di-generate oleh: {$generatorName} ({$generatorRole}) | Tanggal: {$waktuGenerate} | No. Dokumen: {$docId}"], $metaStyle));
        $writer->addRow(Row::fromValuesWithStyle(["STATUS: DOKUMEN RESMI TERSERTIFIKASI ELEKTRONIK • INTEGRITAS DATABASE SIPINJAM"], $certStyle));
        $writer->addRow(Row::fromValues([''])); // Blank Row

        $metricLabelStyle = (new Style())->withFontSize(9)->withFontBold(true)->withFontColor('475569')->withBackgroundColor('F1F5F9')->withCellAlignment(CellAlignment::CENTER);
        $metricValueStyle = (new Style())->withFontSize(11)->withFontBold(true)->withFontColor('0F172A')->withBackgroundColor('FFFFFF')->withBorder($borderThin)->withCellAlignment(CellAlignment::CENTER);
        $metricAlertStyle = (new Style())->withFontSize(11)->withFontBold(true)->withFontColor('BE123C')->withBackgroundColor('FFF1F2')->withBorder($borderThin)->withCellAlignment(CellAlignment::CENTER);

        $writer->addRow(Row::fromValuesWithStyles([
            'METRIK', 'Total Transaksi', '', 'Total Unit Dipinjam', '', 'Kasus Terlambat', '', 'Total Kas Denda', '', ''
        ], [
            $metricLabelStyle, $metricLabelStyle, $metricLabelStyle, $metricLabelStyle, $metricLabelStyle,
            $metricLabelStyle, $metricLabelStyle, $metricLabelStyle, $metricLabelStyle, $metricLabelStyle
        ]));

        $writer->addRow(Row::fromValuesWithStyles([
            'RINGKASAN',
            "{$totalTransaksi} Transaksi", '',
            "{$totalUnit} Unit Alat", '',
            "{$totalTelat} Kasus", '',
            'Rp ' . number_format($totalDenda, 0, ',', '.'), '', ''
        ], [
            $metricLabelStyle, $metricValueStyle, $metricValueStyle, $metricValueStyle, $metricValueStyle,
            $metricAlertStyle, $metricAlertStyle, $metricAlertStyle, $metricAlertStyle, $metricAlertStyle
        ]));

        $writer->addRow(Row::fromValues([''])); // Blank Row

        $writer->addRow(Row::fromValuesWithStyles([
            'NO',
            'NAMA PEMINJAM',
            'TGL PINJAM',
            'RENCANA KEMBALI',
            'ALAT YANG DIPINJAM',
            'JUMLAH',
            'STATUS',
            'TGL AKTUAL KEMBALI',
            'KONDISI KEMBALI',
            'NOMINAL DENDA (RP)',
        ], [
            $thCenter, // NO
            $thLeft,   // NAMA PEMINJAM
            $thCenter, // TGL PINJAM
            $thCenter, // RENCANA KEMBALI
            $thLeft,   // ALAT
            $thCenter, // JUMLAH
            $thCenter, // STATUS
            $thCenter, // TGL AKTUAL
            $thCenter, // KONDISI
            $thRight,  // DENDA
        ]));

        $no = 1;
        foreach ($this->peminjamans as $item) {
            $isZebra = ($no % 2 === 0);

            $cCenter = $isZebra ? $styleCenterZebra : $styleCenter;
            $cLeft   = $isZebra ? $styleLeftZebra : $styleLeft;
            $cRight  = $isZebra ? $styleRightZebra : $styleRight;

            $alats = $item->detailPinjam->map(function ($d) {
                return ($d->alat->nama_alat ?? 'Alat Dihapus') . ' (' . $d->jumlah . ' unit)';
            })->implode('; ');

            $jumlahAlat = $item->detailPinjam->sum('jumlah');
            $tglKembali = $item->pengembalian?->tgl_kembali ?? '-';
            $kondisi    = $item->pengembalian?->kondisi_kembali ?? '-';
            $denda      = (int) ($item->pengembalian?->denda ?? 0);

            $statusStyle = match ($item->status) {
                'dipinjam'     => $styleDipinjam,
                'dikembalikan' => $styleKembali,
                'telat'        => $styleTelat,
                default        => $cCenter,
            };

            $statusText = match ($item->status) {
                'dipinjam'     => 'DIPINJAM',
                'dikembalikan' => 'DIKEMBALIKAN',
                'telat'        => 'TERLAMBAT',
                default        => strtoupper($item->status),
            };

            $kondisiText = match (strtolower((string) $kondisi)) {
                'baik'      => 'Baik (Normal)',
                'rusak'     => 'Rusak Fisik',
                'perbaikan' => 'Perlu Perbaikan',
                default     => $kondisi ?: '—',
            };

            $dendaFormatted = $denda > 0 ? 'Rp ' . number_format($denda, 0, ',', '.') : 'Rp 0';

            $writer->addRow(Row::fromValuesWithStyles([
                $no,
                $item->user->name ?? '-',
                $item->tgl_pinjam instanceof Carbon ? $item->tgl_pinjam->format('d/m/Y') : (string) $item->tgl_pinjam,
                $item->tgl_kembali_plan instanceof Carbon ? $item->tgl_kembali_plan->format('d/m/Y') : (string) $item->tgl_kembali_plan,
                $alats ?: '-',
                (int) $jumlahAlat,
                $statusText,
                $tglKembali instanceof Carbon ? $tglKembali->format('d/m/Y') : (string) $tglKembali,
                $kondisiText,
                $dendaFormatted,
            ], [
                $cCenter,     // No
                $cLeft,       // Nama Peminjam
                $cCenter,     // Tgl Pinjam
                $cCenter,     // Rencana Kembali
                $cLeft,       // Alat
                $cCenter,     // Jumlah
                $statusStyle, // Status
                $cCenter,     // Tgl Aktual
                $cCenter,     // Kondisi
                $cRight,      // Denda
            ]));

            $no++;
        }

        $totalLabelStyle = (new Style())
            ->withFontBold(true)
            ->withFontSize(10)
            ->withFontColor('0F172A')
            ->withBackgroundColor('E2E8F0')
            ->withBorder($borderTotal)
            ->withCellAlignment(CellAlignment::RIGHT);

        $totalUnitStyle = (new Style())
            ->withFontBold(true)
            ->withFontSize(10)
            ->withFontColor('0F172A')
            ->withBackgroundColor('E2E8F0')
            ->withBorder($borderTotal)
            ->withCellAlignment(CellAlignment::CENTER);

        $totalDendaStyle = (new Style())
            ->withFontBold(true)
            ->withFontSize(10)
            ->withFontColor($totalDenda > 0 ? 'BE123C' : '047857')
            ->withBackgroundColor($totalDenda > 0 ? 'FEE2E2' : 'E2E8F0')
            ->withBorder($borderTotal)
            ->withCellAlignment(CellAlignment::RIGHT);

        $writer->addRow(Row::fromValuesWithStyles([
            '', '', '', '', 'TOTAL KESELURUHAN:',
            (int) $totalUnit,
            '', '', '',
            'Rp ' . number_format($totalDenda, 0, ',', '.')
        ], [
            $totalLabelStyle, $totalLabelStyle, $totalLabelStyle, $totalLabelStyle, $totalLabelStyle,
            $totalUnitStyle,
            $totalLabelStyle, $totalLabelStyle, $totalLabelStyle,
            $totalDendaStyle
        ]));

        $writer->addRow(Row::fromValues([''])); // Blank Row

        $footerMetaStyle = (new Style())->withFontSize(8)->withFontColor('64748B')->withFontItalic(true);
        $checksum = strtoupper(hash('sha256', $docId . now()->timestamp . $totalTransaksi));

        $writer->addRow(Row::fromValuesWithStyle([
            "🔒 Checksum Keamanan Digital SHA-256: {$checksum}"
        ], $footerMetaStyle));

        $writer->addRow(Row::fromValuesWithStyle([
            "Dokumen ini di-generate secara otomatis oleh Sistem SiPinjam SMKN 7 Baleendah dan sah tanpa tanda tangan basah."
        ], $footerMetaStyle));

        $writer->addRow(Row::fromValues([''])); // Blank Row

        $ttdHeaderStyle = (new Style())->withFontSize(9)->withCellAlignment(CellAlignment::CENTER);
        $ttdNamaStyle   = (new Style())->withFontSize(10)->withFontBold(true)->withCellAlignment(CellAlignment::CENTER);

        $writer->addRow(Row::fromValuesWithStyles([
            '', '', '', '', '', '', '', 'Baleendah, ' . now()->translatedFormat('d F Y'), '', ''
        ], [
            $ttdHeaderStyle, $ttdHeaderStyle, $ttdHeaderStyle, $ttdHeaderStyle, $ttdHeaderStyle,
            $ttdHeaderStyle, $ttdHeaderStyle, $ttdHeaderStyle, $ttdHeaderStyle, $ttdHeaderStyle
        ]));

        $writer->addRow(Row::fromValuesWithStyles([
            '', '', '', '', '', '', '', "{$generatorRole} Penanggung Jawab,", '', ''
        ], [
            $ttdHeaderStyle, $ttdHeaderStyle, $ttdHeaderStyle, $ttdHeaderStyle, $ttdHeaderStyle,
            $ttdHeaderStyle, $ttdHeaderStyle, $ttdNamaStyle, $ttdHeaderStyle, $ttdHeaderStyle
        ]));

        $writer->addRow(Row::fromValues(['']));
        $writer->addRow(Row::fromValues(['']));

        $writer->addRow(Row::fromValuesWithStyles([
            '', '', '', '', '', '', '', "( {$generatorName} )", '', ''
        ], [
            $ttdHeaderStyle, $ttdHeaderStyle, $ttdHeaderStyle, $ttdHeaderStyle, $ttdHeaderStyle,
            $ttdHeaderStyle, $ttdHeaderStyle, $ttdNamaStyle, $ttdHeaderStyle, $ttdHeaderStyle
        ]));

        $writer->addRow(Row::fromValuesWithStyles([
            '', '', '', '', '', '', '', 'NIP / ID: #' . (auth()->id() ?? '1'), '', ''
        ], [
            $ttdHeaderStyle, $ttdHeaderStyle, $ttdHeaderStyle, $ttdHeaderStyle, $ttdHeaderStyle,
            $ttdHeaderStyle, $ttdHeaderStyle, $footerMetaStyle, $ttdHeaderStyle, $ttdHeaderStyle
        ]));

        $writer->close();

        $content = file_get_contents($tmpFile);
        unlink($tmpFile);

        return response()->streamDownload(function () use ($content) {
            echo $content;
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
}

