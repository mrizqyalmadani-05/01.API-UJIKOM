<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Menambah kolom catatan petugas pada tabel pengembalian.
     * Diambil dari OmmniDevv/api-ujikom (2026_09_24_070000).
     */
    public function up(): void
    {
        if (! Schema::hasColumn('pengembalian', 'catatan')) {
            Schema::table('pengembalian', function (Blueprint $table) {
                $table->text('catatan')->nullable()->after('denda');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('pengembalian', 'catatan')) {
            Schema::table('pengembalian', function (Blueprint $table) {
                $table->dropColumn('catatan');
            });
        }
    }
};
