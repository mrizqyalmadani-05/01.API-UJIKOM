<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Menambah kolom keterangan (detail sebelum/sesudah) ke tabel log_aktivitas.
     * Diambil dari OmmniDevv/api-ujikom (audit trail yang lebih kaya).
     */
    public function up(): void
    {
        if (! Schema::hasColumn('log_aktivitas', 'keterangan')) {
            Schema::table('log_aktivitas', function (Blueprint $table) {
                $table->text('keterangan')->nullable()->after('aktivitas');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('log_aktivitas', 'keterangan')) {
            Schema::table('log_aktivitas', function (Blueprint $table) {
                $table->dropColumn('keterangan');
            });
        }
    }
};
