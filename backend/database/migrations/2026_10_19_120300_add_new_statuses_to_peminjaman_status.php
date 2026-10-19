<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Menambah nilai status baru pada peminjaman.status:
     * - 'ditolak'           : dari OmmniDevv/api-ujikom; dipakai oleh
     *                         PetugasController::tolakPeminjaman() yang sudah ada
     *                         (sebelumnya error constraint di MySQL & SQLite).
     * - 'pengajuan_kembali' : dari OmmniDevv/api-ujikom; peminjam mengajukan
     *                         agar petugas memproses pengembalian.
     * - 'selesai'           : dipakai oleh PetugasController::prosesPengembalian()
     *                         yang sudah ada (sebelumnya juga tidak ada di daftar).
     *
     * MySQL  : ALTER TABLE ... MODIFY COLUMN ENUM(...)
     * SQLite : Laravel membuat ENUM sebagai VARCHAR + CHECK constraint, jadi
     *          tabel dibangun ulang dengan CHECK yang baru (transaksional,
     *          FK dimatikan selama proses).
     */
    public function up(): void
    {
        $this->rebuildStatusColumn([
            'diajukan', 'dipinjam', 'dikembalikan', 'telat',
            'ditolak', 'pengajuan_kembali', 'selesai',
        ]);
    }

    public function down(): void
    {
        $this->rebuildStatusColumn(['diajukan', 'dipinjam', 'dikembalikan', 'telat']);
    }

    protected function rebuildStatusColumn(array $statuses): void
    {
        $driver = Schema::getConnection()->getDriverName();
        $in = implode("', '", $statuses);

        if ($driver === 'mysql') {
            DB::statement(
                "ALTER TABLE peminjaman MODIFY COLUMN status ENUM('{$in}') NOT NULL DEFAULT 'diajukan'"
            );

            return;
        }

        if ($driver === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = OFF');

            DB::transaction(function () use ($in) {
                DB::statement(
                    "CREATE TABLE peminjaman_new (" .
                    '"id" integer primary key autoincrement not null, ' .
                    '"user_id" integer not null, ' .
                    '"tgl_pinjam" date not null, ' .
                    '"tgl_kembali_plan" date not null, ' .
                    "\"status\" varchar check (\"status\" in ('{$in}')) not null default 'diajukan', " .
                    '"created_at" datetime, ' .
                    '"updated_at" datetime, ' .
                    'foreign key("user_id") references "users"("id") on delete cascade)'
                );
                DB::statement(
                    'INSERT INTO peminjaman_new (id, user_id, tgl_pinjam, tgl_kembali_plan, status, created_at, updated_at) ' .
                    'SELECT id, user_id, tgl_pinjam, tgl_kembali_plan, status, created_at, updated_at FROM peminjaman'
                );
                DB::statement('DROP TABLE peminjaman');
                DB::statement('ALTER TABLE peminjaman_new RENAME TO peminjaman');
            });

            DB::statement('PRAGMA foreign_keys = ON');

            return;
        }
    }
};
