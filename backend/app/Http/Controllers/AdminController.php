<?php

namespace App\Http\Controllers;

use App\Models\Alat;
use App\Models\DetailPinjam;
use App\Models\Kategori;
use App\Models\LogAktivitas;
use App\Models\Peminjaman;
use App\Models\Pengembalian;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    // =========================================================
    // Dashboard
    // =========================================================

    public function index()
    {
        $totalAlat       = Alat::count();
        $totalUser       = User::count();
        $totalPeminjaman = Peminjaman::count();
        $sedangDipinjam  = Peminjaman::where('status', 'dipinjam')->count();
        $diajukan        = Peminjaman::where('status', 'diajukan')->count();
        $logs            = LogAktivitas::with('user')->latest()->take(10)->get();

        return view('admin.dashboard', compact(
            'totalAlat', 'totalUser', 'totalPeminjaman',
            'sedangDipinjam', 'diajukan', 'logs'
        ));
    }

    // Log Aktivitas — daftar penuh dengan pagination
    public function logAktivitas(Request $request)
    {
        $search = $request->input('search');
        $logs   = LogAktivitas::with('user')
            ->when($search, fn($q, $s) => $q->where('aktivitas', 'like', "%{$s}%")
                ->orWhereHas('user', fn($u) => $u->where('name', 'like', "%{$s}%")))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.log.index', compact('logs', 'search'));
    }

    // Laporan Peminjaman — cetak / ekspor HTML
    public function laporan(Request $request)
    {
        $bulan  = $request->input('bulan', now()->format('Y-m'));
        $status = $request->input('status', '');

        [$tahun, $bln] = explode('-', $bulan);

        $peminjamans = Peminjaman::with(['user', 'detailPinjam.alat', 'pengembalian'])
            ->whereYear('tgl_pinjam', $tahun)
            ->whereMonth('tgl_pinjam', $bln)
            ->when($status, fn($q, $s) => $q->where('status', $s))
            ->orderBy('tgl_pinjam')
            ->get();

        $totalDenda = $peminjamans->sum(fn($p) => optional($p->pengembalian)->denda ?? 0);

        return view('admin.laporan.index', compact('peminjamans', 'bulan', 'status', 'totalDenda'));
    }

    // Cetak Laporan (versi print-friendly tanpa layout)
    public function cetakLaporan(Request $request)
    {
        $bulan  = $request->input('bulan', now()->format('Y-m'));
        $status = $request->input('status', '');

        [$tahun, $bln] = explode('-', $bulan);

        $peminjamans = Peminjaman::with(['user', 'detailPinjam.alat', 'pengembalian'])
            ->whereYear('tgl_pinjam', $tahun)
            ->whereMonth('tgl_pinjam', $bln)
            ->when($status, fn($q, $s) => $q->where('status', $s))
            ->orderBy('tgl_pinjam')
            ->get();

        $totalDenda = $peminjamans->sum(fn($p) => optional($p->pengembalian)->denda ?? 0);

        return view('admin.laporan.cetak', compact('peminjamans', 'bulan', 'status', 'totalDenda'));
    }

    // =========================================================
    // CRUD Alat
    // =========================================================

    // 1. Menampilkan daftar alat (Search & Pagination)
    public function indexAlat(Request $request)
    {
        $search = $request->input('search');

        $alats = Alat::with('kategori')
            ->when($search, function ($query, $search) {
                return $query->where('nama_alat', 'like', "%{$search}%")
                    ->orWhere('status_kondisi', 'like', "%{$search}%")
                    ->orWhereHas('kategori', function ($q) use ($search) {
                        $q->where('nama_kategori', 'like', "%{$search}%");
                    });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.alat.index', compact('alats', 'search'));
    }

    // 2. Menampilkan form tambah alat
    public function createAlat()
    {
        $kategoris = Kategori::all();
        return view('admin.alat.create', compact('kategoris'));
    }

    // 3. Menyimpan alat baru
    public function storeAlat(Request $request)
    {
        $request->validate([
            'nama_alat'      => 'required|string|max:255',
            'kategori_id'    => 'required|exists:kategori,id',
            'stok'           => 'required|integer|min:0',
            'status_kondisi' => 'required|string|max:100',
            'deskripsi'      => 'nullable|string',
            'gambar'         => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $data = $request->all();

        // Handle Upload Gambar jika ada
        if ($request->hasFile('gambar')) {
            $file     = $request->file('gambar');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('storage/alat'), $filename);
            $data['gambar'] = 'storage/alat/' . $filename;
        }

        Alat::create($data);

        LogAktivitas::create([
            'user_id'   => auth()->id(),
            'aktivitas' => 'Menambahkan alat baru: ' . $request->nama_alat,
        ]);

        return redirect()->route('admin.alat.index')->with('success', 'Data alat berhasil ditambahkan.');
    }

    // 4. Menampilkan form edit alat
    public function editAlat($id)
    {
        $alat      = Alat::findOrFail($id);
        $kategoris = Kategori::all();
        return view('admin.alat.edit', compact('alat', 'kategoris'));
    }

    // 5. Memperbarui data alat
    public function updateAlat(Request $request, $id)
    {
        $alat = Alat::findOrFail($id);

        $request->validate([
            'nama_alat'      => 'required|string|max:255',
            'kategori_id'    => 'required|exists:kategori,id',
            'stok'           => 'required|integer|min:0',
            'status_kondisi' => 'required|string|max:100',
            'deskripsi'      => 'nullable|string',
            'gambar'         => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $data = $request->all();

        // Handle Update Gambar jika ada file baru
        if ($request->hasFile('gambar')) {
            // Hapus gambar lama jika ada
            if ($alat->gambar && file_exists(public_path($alat->gambar))) {
                unlink(public_path($alat->gambar));
            }

            $file     = $request->file('gambar');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('storage/alat'), $filename);
            $data['gambar'] = 'storage/alat/' . $filename;
        }

        $alat->update($data);

        return redirect()->route('admin.alat.index')->with('success', 'Data alat berhasil diperbarui.');
    }

    // 6. Menghapus data alat
    public function destroyAlat($id)
    {
        $alat = Alat::findOrFail($id);

        // Hapus file gambar fisik jika ada
        if ($alat->gambar && file_exists(public_path($alat->gambar))) {
            unlink(public_path($alat->gambar));
        }

        $alat->delete();

        return redirect()->route('admin.alat.index')->with('success', 'Data alat berhasil dihapus.');
    }

    // =========================================================
    // CRUD Kategori
    // =========================================================

    // 1. Menampilkan daftar kategori (Search & Pagination)
    public function indexKategori(Request $request)
    {
        $search = $request->input('search');

        $kategoris = Kategori::when($search, function ($query, $search) {
            return $query->where('nama_kategori', 'like', "%{$search}%");
        })
            ->latest()
            ->paginate(5)
            ->withQueryString();

        return view('admin.kategori.index', compact('kategoris', 'search'));
    }

    // 2. Menampilkan form tambah kategori
    public function createKategori()
    {
        return view('admin.kategori.create');
    }

    // 3. Menyimpan kategori baru
    public function storeKategori(Request $request)
    {
        $request->validate([
            'nama_kategori' => 'required|string|max:255|unique:kategori,nama_kategori',
        ]);

        Kategori::create(['nama_kategori' => $request->nama_kategori]);

        return redirect()->route('admin.kategori.index')->with('success', 'Kategori berhasil ditambahkan.');
    }

    // 4. Menampilkan form edit kategori
    public function editKategori($id)
    {
        $kategori = Kategori::findOrFail($id);
        return view('admin.kategori.edit', compact('kategori'));
    }

    // 5. Memperbarui kategori
    public function updateKategori(Request $request, $id)
    {
        $kategori = Kategori::findOrFail($id);

        $request->validate([
            'nama_kategori' => 'required|string|max:255|unique:kategori,nama_kategori,' . $id,
        ]);

        $kategori->update(['nama_kategori' => $request->nama_kategori]);

        return redirect()->route('admin.kategori.index')->with('success', 'Kategori berhasil diperbarui.');
    }

    // 6. Menghapus kategori
    public function destroyKategori($id)
    {
        $kategori = Kategori::findOrFail($id);

        if ($kategori->alat()->count() > 0) {
            return redirect()->route('admin.kategori.index')
                ->with('error', 'Kategori tidak dapat dihapus karena masih digunakan oleh data alat.');
        }

        $kategori->delete();

        return redirect()->route('admin.kategori.index')->with('success', 'Kategori berhasil dihapus.');
    }

    // =========================================================
    // CRUD User
    // =========================================================

    // Menampilkan daftar user (Search & Pagination)
    public function indexUser(Request $request)
    {
        $search = $request->input('search');

        $users = User::when($search, function ($query, $search) {
            return $query->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
                ->orWhere('role', 'like', "%{$search}%");
        })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.user.index', compact('users', 'search'));
    }

    // Menampilkan form tambah user
    public function createUser()
    {
        return view('admin.user.create');
    }

    // Menyimpan user baru
    public function storeUser(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
            'role'     => 'required|in:admin,petugas,peminjam',
        ]);

        User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'role'     => $request->role,
            'no_hp'    => $request->no_hp,
        ]);

        return redirect()->route('admin.user.index')->with('success', 'User berhasil ditambahkan.');
    }

    // Menampilkan form edit user
    public function editUser($id)
    {
        $user = User::findOrFail($id);
        return view('admin.user.edit', compact('user'));
    }

    // Memperbarui data user
    public function updateUser(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'name'  => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $id,
            'role'  => 'required|in:admin,petugas,peminjam',
        ]);

        $data = [
            'name'  => $request->name,
            'email' => $request->email,
            'role'  => $request->role,
            'no_hp' => $request->no_hp,
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return redirect()->route('admin.user.index')->with('success', 'Data user berhasil diperbarui.');
    }

    // Menghapus user
    public function destroyUser($id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        return redirect()->route('admin.user.index')->with('success', 'User berhasil dihapus.');
    }

    // =========================================================
    // CRUD Peminjaman (Admin)
    // =========================================================

    // 1. Menampilkan daftar peminjaman
    public function indexPeminjaman(Request $request)
    {
        $search = $request->input('search');

        $peminjamans = Peminjaman::with(['user', 'detailPinjam.alat'])
            ->when($search, function ($query, $search) {
                return $query->where('status', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.peminjaman.index', compact('peminjamans', 'search'));
    }

    // 2. Menampilkan form tambah peminjaman
    public function createPeminjaman()
    {
        $users = User::where('role', 'peminjam')->get();
        $alats = Alat::where('stok', '>', 0)->get();
        return view('admin.peminjaman.create', compact('users', 'alats'));
    }

    // 3. Menyimpan data peminjaman baru
    public function storePeminjaman(Request $request)
    {
        $request->validate([
            'user_id'          => 'required|exists:users,id',
            'tgl_pinjam'       => 'required|date',
            'tgl_kembali_plan' => 'required|date|after_or_equal:tgl_pinjam',
            'alat_id'          => 'required|array',
            'alat_id.*'        => 'exists:alat,id',
            'jumlah'           => 'required|array',
            'jumlah.*'         => 'integer|min:1',
        ]);

        DB::beginTransaction();
        try {
            // Buat transaksi utama peminjaman
            $peminjaman = Peminjaman::create([
                'user_id'          => $request->user_id,
                'tgl_pinjam'       => $request->tgl_pinjam,
                'tgl_kembali_plan' => $request->tgl_kembali_plan,
                'status'           => 'diajukan',
            ]);

            // Simpan detail alat yang dipinjam
            foreach ($request->alat_id as $index => $alatId) {
                $jumlahPinjam = $request->jumlah[$index];
                $alat         = Alat::findOrFail($alatId);

                // Validasi stok
                if ($alat->stok < $jumlahPinjam) {
                    throw new \Exception("Stok alat '{$alat->nama_alat}' tidak mencukupi.");
                }

                DetailPinjam::create([
                    'peminjaman_id' => $peminjaman->id,
                    'alat_id'       => $alatId,
                    'jumlah'        => $jumlahPinjam,
                ]);
            }

            DB::commit();
            return redirect()->route('admin.peminjaman.index')->with('success', 'Data peminjaman berhasil diajukan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    // 4. Memperbarui status peminjaman (diajukan → dipinjam → selesai / telat)
    public function updateStatusPeminjaman(Request $request, $id)
    {
        $peminjaman = Peminjaman::with('detailPinjam.alat')->findOrFail($id);

        $request->validate([
            'status' => 'required|in:diajukan,dipinjam,selesai,telat',
        ]);

        DB::beginTransaction();
        try {
            $statusLama = $peminjaman->status;
            $statusBaru = $request->status;

            // Kurangi stok saat status berubah menjadi 'dipinjam'
            if ($statusLama !== 'dipinjam' && $statusBaru === 'dipinjam') {
                foreach ($peminjaman->detailPinjam as $detail) {
                    $alat = $detail->alat;
                    if ($alat->stok < $detail->jumlah) {
                        throw new \Exception("Stok alat '{$alat->nama_alat}' tidak mencukupi untuk dipinjam.");
                    }
                    $alat->decrement('stok', $detail->jumlah);
                }
            }

            // Kembalikan stok saat status berubah menjadi 'selesai'
            if ($statusLama === 'dipinjam' && $statusBaru === 'selesai') {
                foreach ($peminjaman->detailPinjam as $detail) {
                    $detail->alat->increment('stok', $detail->jumlah);
                }
            }

            $peminjaman->update(['status' => $statusBaru]);

            DB::commit();
            return redirect()->back()->with('success', 'Status peminjaman berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage());
        }
    }

    // 5. Menghapus data peminjaman
    public function destroyPeminjaman($id)
    {
        $peminjaman = Peminjaman::with('detailPinjam')->findOrFail($id);

        // Jika statusnya sedang dipinjam, kembalikan stok terlebih dahulu
        if ($peminjaman->status === 'dipinjam') {
            foreach ($peminjaman->detailPinjam as $detail) {
                $detail->alat->increment('stok', $detail->jumlah);
            }
        }

        $peminjaman->delete();

        return redirect()->route('admin.peminjaman.index')->with('success', 'Data peminjaman berhasil dihapus.');
    }

    // =========================================================
    // Kelola Pengembalian (Admin)
    // =========================================================

    // 1. Daftar semua pengembalian (search + pagination)
    public function indexPengembalian(Request $request)
    {
        $search = $request->input('search');

        $pengembalians = Pengembalian::with(['peminjaman.user', 'peminjaman.detailPinjam.alat', 'petugas'])
            ->when($search, function ($query, $search) {
                return $query->where('kondisi_kembali', 'like', "%{$search}%")
                    ->orWhereHas('peminjaman.user', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.pengembalian.index', compact('pengembalians', 'search'));
    }

    // 2. Form catat pengembalian baru
    public function createPengembalian()
    {
        // Hanya peminjaman berstatus 'dipinjam' yang belum ada pengembaliannya
        $peminjamans = Peminjaman::with(['user', 'detailPinjam.alat'])
            ->where('status', 'dipinjam')
            ->whereDoesntHave('pengembalian')
            ->get();

        return view('admin.pengembalian.create', compact('peminjamans'));
    }

    // 3. Simpan data pengembalian baru
    public function storePengembalian(Request $request)
    {
        $request->validate([
            'peminjaman_id'   => 'required|exists:peminjaman,id',
            'tgl_kembali'     => 'required|date',
            'kondisi_kembali' => 'required|string|max:255',
            'denda'           => 'nullable|integer|min:0',
        ]);

        DB::beginTransaction();
        try {
            $peminjaman = Peminjaman::with('detailPinjam.alat')->findOrFail($request->peminjaman_id);

            if ($peminjaman->pengembalian) {
                throw new \Exception('Data pengembalian untuk peminjaman ini sudah ada.');
            }

            Pengembalian::create([
                'peminjaman_id'   => $peminjaman->id,
                'tgl_kembali'     => $request->tgl_kembali,
                'kondisi_kembali' => $request->kondisi_kembali,
                'denda'           => $request->denda ?? 0,
                'petugas_id'      => auth()->id(),
            ]);

            // Pulihkan stok alat
            foreach ($peminjaman->detailPinjam as $detail) {
                $detail->alat->increment('stok', $detail->jumlah);
            }

            // Tandai peminjaman selesai
            $peminjaman->update(['status' => 'selesai']);

            DB::commit();
            return redirect()->route('admin.pengembalian.index')
                ->with('success', 'Pengembalian berhasil dicatat dan stok alat telah dipulihkan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    // 4. Detail satu pengembalian
    public function showPengembalian($id)
    {
        $pengembalian = Pengembalian::with([
            'peminjaman.user',
            'peminjaman.detailPinjam.alat',
            'petugas',
        ])->findOrFail($id);

        return view('admin.pengembalian.show', compact('pengembalian'));
    }

    // 5. Hapus data pengembalian (rollback stok & status)
    public function destroyPengembalian($id)
    {
        $pengembalian = Pengembalian::with('peminjaman.detailPinjam.alat')->findOrFail($id);
        $peminjaman   = $pengembalian->peminjaman;

        DB::beginTransaction();
        try {
            // Tarik kembali stok yang sudah dikembalikan
            foreach ($peminjaman->detailPinjam as $detail) {
                $detail->alat->decrement('stok', $detail->jumlah);
            }

            // Kembalikan status ke dipinjam
            $peminjaman->update(['status' => 'dipinjam']);

            $pengembalian->delete();

            DB::commit();
            return redirect()->route('admin.pengembalian.index')
                ->with('success', 'Data pengembalian dihapus dan status peminjaman dikembalikan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage());
        }
    }
}
