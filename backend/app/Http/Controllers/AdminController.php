<?php

namespace App\Http\Controllers;

use App\Models\Alat;
use App\Models\Kategori;
use App\Models\User;
use App\Models\LogAktivitas;
use App\Models\Peminjaman;
use App\Models\DetailPinjam;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AdminController extends Controller
{
    // ==========================================
    // DASHBOARD & LOG AKTIVITAS
    // ==========================================
    public function index()
    {
        // Mengurutkan berdasarkan ID terbesar agar log terbaru selalu berada di paling atas
        $logs = LogAktivitas::with('user')->orderBy('id', 'desc')->take(10)->get();
        return view('admin.dashboard', compact('logs'));
    }

    // ==========================================
    // CRUD ALAT / BARANG
    // ==========================================
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

    public function createAlat()
    {
        $kategoris = Kategori::all();
        return view('admin.alat.create', compact('kategoris'));
    }

    public function storeAlat(Request $request)
    {
        $request->validate([
            'nama_alat' => 'required|string|max:255',
            'kategori_id' => 'required|exists:kategori,id',
            'stok' => 'required|integer|min:0',
            'status_kondisi' => 'required|string|max:100',
            'deskripsi' => 'nullable|string',
            'gambar' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $data = $request->all();

        if ($request->hasFile('gambar')) {
            $file = $request->file('gambar');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('storage/alat'), $filename);
            $data['gambar'] = 'storage/alat/' . $filename;
        }

        $alat = Alat::create($data);

        // Catat Log Aktivitas (Disesuaikan dengan modul)
        LogAktivitas::create([
            'user_id'   => auth()->id(),
            'aktivitas' => "Menambahkan data alat baru: {$alat->nama_alat}.",
            'waktu'     => now(),
        ]);

        return redirect()->route('admin.alat.index')->with('success', 'Data alat berhasil ditambahkan.');
    }

    public function editAlat($id)
    {
        $alat = Alat::findOrFail($id);
        $kategoris = Kategori::all();
        return view('admin.alat.edit', compact('alat', 'kategoris'));
    }

    public function updateAlat(Request $request, $id)
    {
        $alat = Alat::findOrFail($id);

        $request->validate([
            'nama_alat' => 'required|string|max:255',
            'kategori_id' => 'required|exists:kategori,id',
            'stok' => 'required|integer|min:0',
            'status_kondisi' => 'required|string|max:100',
            'deskripsi' => 'nullable|string',
            'gambar' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $data = $request->all();

        if ($request->hasFile('gambar')) {
            if ($alat->gambar && file_exists(public_path($alat->gambar))) {
                unlink(public_path($alat->gambar));
            }

            $file = $request->file('gambar');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('storage/alat'), $filename);
            $data['gambar'] = 'storage/alat/' . $filename;
        }

        $alat->update($data);

        // Catat Log Aktivitas
        LogAktivitas::create([
            'user_id'   => auth()->id(),
            'aktivitas' => "Memperbarui data alat: {$alat->nama_alat}.",
            'waktu'     => now(),
        ]);

        return redirect()->route('admin.alat.index')->with('success', 'Data alat berhasil diperbarui.');
    }

    public function destroyAlat($id)
    {
        $alat = Alat::findOrFail($id);
        $namaAlat = $alat->nama_alat;

        if ($alat->gambar && file_exists(public_path($alat->gambar))) {
            unlink(public_path($alat->gambar));
        }

        $alat->delete();

        // Catat Log Aktivitas
        LogAktivitas::create([
            'user_id'   => auth()->id(),
            'aktivitas' => "Menghapus data alat: {$namaAlat}.",
            'waktu'     => now(),
        ]);

        return redirect()->route('admin.alat.index')->with('success', 'Data alat berhasil dihapus.');
    }

    // ==========================================
    // CRUD USER / PENGGUNA
    // ==========================================
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

    public function createUser()
    {
        return view('admin.user.create');
    }

    public function storeUser(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
            'role' => 'required|in:admin,petugas,peminjam',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'no_hp' => $request->no_hp,
        ]);

        // Catat Log Aktivitas
        LogAktivitas::create([
            'user_id'   => auth()->id(),
            'aktivitas' => "Menambahkan user baru: {$user->name} ({$user->role}).",
            'waktu'     => now(),
        ]);

        return redirect()->route('admin.user.index')->with('success', 'User berhasil ditambahkan.');
    }

    public function editUser($id)
    {
        $user = User::findOrFail($id);
        return view('admin.user.edit', compact('user'));
    }

    public function updateUser(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $id,
            'role' => 'required|in:admin,petugas,peminjam',
        ]);

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
            'no_hp' => $request->no_hp,
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        // Catat Log Aktivitas
        LogAktivitas::create([
            'user_id'   => auth()->id(),
            'aktivitas' => "Memperbarui data user: {$user->name}.",
            'waktu'     => now(),
        ]);

        return redirect()->route('admin.user.index')->with('success', 'Data user berhasil diperbarui.');
    }

    public function destroyUser($id)
    {
        $user = User::findOrFail($id);
        $namaUser = $user->name;
        $user->delete();

        // Catat Log Aktivitas
        LogAktivitas::create([
            'user_id'   => auth()->id(),
            'aktivitas' => "Menghapus user: {$namaUser}.",
            'waktu'     => now(),
        ]);

        return redirect()->route('admin.user.index')->with('success', 'User berhasil dihapus.');
    }

    // ==========================================
    // CRUD KATEGORI
    // ==========================================
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

    public function createKategori()
    {
        return view('admin.kategori.create');
    }

    public function storeKategori(Request $request)
    {
        $request->validate([
            'nama_kategori' => 'required|string|max:255|unique:kategori,nama_kategori',
        ]);

        $kategori = Kategori::create([
            'nama_kategori' => $request->nama_kategori,
        ]);

        // Catat Log Aktivitas
        LogAktivitas::create([
            'user_id'   => auth()->id(),
            'aktivitas' => "Menambahkan kategori baru: {$kategori->nama_kategori}.",
            'waktu'     => now(),
        ]);

        return redirect()->route('admin.kategori.index')->with('success', 'Kategori berhasil ditambahkan.');
    }

    public function editKategori($id)
    {
        $kategori = Kategori::findOrFail($id);
        return view('admin.kategori.edit', compact('kategori'));
    }

    public function updateKategori(Request $request, $id)
    {
        $kategori = Kategori::findOrFail($id);

        $request->validate([
            'nama_kategori' => 'required|string|max:255|unique:kategori,nama_kategori,' . $id,
        ]);

        $kategori->update([
            'nama_kategori' => $request->nama_kategori,
        ]);

        // Catat Log Aktivitas
        LogAktivitas::create([
            'user_id'   => auth()->id(),
            'aktivitas' => "Memperbarui kategori: {$kategori->nama_kategori}.",
            'waktu'     => now(),
        ]);

        return redirect()->route('admin.kategori.index')->with('success', 'Kategori berhasil diperbarui.');
    }

    public function destroyKategori($id)
    {
        $kategori = Kategori::findOrFail($id);

        if ($kategori->alats()->count() > 0) {
            return redirect()->route('admin.kategori.index')
                ->with('error', 'Kategori tidak dapat dihapus karena masih digunakan oleh data alat.');
        }

        $namaKategori = $kategori->nama_kategori;
        $kategori->delete();

        // Catat Log Aktivitas
        LogAktivitas::create([
            'user_id'   => auth()->id(),
            'aktivitas' => "Menghapus kategori: {$namaKategori}.",
            'waktu'     => now(),
        ]);

        return redirect()->route('admin.kategori.index')->with('success', 'Kategori berhasil dihapus.');
    }

    // ==========================================
    // TRANSAKSI PEMINJAMAN
    // ==========================================
    public function indexPeminjaman(Request $request)
    {
        $search = $request->input('search');

        $peminjamans = Peminjaman::with(['user', 'detailPinjams.alat'])
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

    public function createPeminjaman()
    {
        $users = User::where('role', 'peminjam')->get();
        $alats = Alat::where('stok', '>', 0)->get();
        return view('admin.peminjaman.create', compact('users', 'alats'));
    }

    public function storePeminjaman(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'tgl_pinjam' => 'required|date',
            'tgl_kembali_plan' => 'required|date|after_or_equal:tgl_pinjam',
            'alat_id' => 'required|array',
            'alat_id.*' => 'exists:alat,id',
            'jumlah' => 'required|array',
            'jumlah.*' => 'integer|min:1',
        ]);

        DB::beginTransaction();
        try {
            $peminjaman = Peminjaman::create([
                'user_id' => $request->user_id,
                'tgl_pinjam' => $request->tgl_pinjam,
                'tgl_kembali_plan' => $request->tgl_kembali_plan,
                'status' => 'diajukan',
            ]);

            foreach ($request->alat_id as $index => $alatId) {
                $jumlahPinjam = $request->jumlah[$index] ?? 1;
                $alat = Alat::findOrFail($alatId);

                if ($alat->stok < $jumlahPinjam) {
                    throw new \Exception("Stok alat '{$alat->nama_alat}' tidak mencukupi (Sisa stok: {$alat->stok}).");
                }

                DetailPinjam::create([
                    'peminjaman_id' => $peminjaman->id,
                    'alat_id' => $alatId,
                    'jumlah' => $jumlahPinjam,
                ]);
            }

            // Catat Log Aktivitas
            LogAktivitas::create([
                'user_id'   => auth()->id(),
                'aktivitas' => "Mengajukan peminjaman baru untuk Peminjaman ID #{$peminjaman->id}.",
                'waktu'     => now(),
            ]);

            DB::commit();
            return redirect()->route('admin.peminjaman.index')->with('success', 'Data peminjaman berhasil diajukan.');
        } catch (\Exception $e) {
            DB::rollBack();
            dd("Error pada storePeminjaman: " . $e->getMessage());
        }
    }

    public function updateStatusPeminjaman(Request $request, $id)
    {
        $peminjaman = Peminjaman::with('detailPinjams.alat')->findOrFail($id);

        $request->validate([
            'status' => 'required|in:diajukan,dipinjam,selesai,telat',
        ]);

        DB::beginTransaction();
        try {
            $statusLama = $peminjaman->status;
            $statusBaru = $request->status;

            if ($statusLama != 'dipinjam' && $statusBaru == 'dipinjam') {
                foreach ($peminjaman->detailPinjams as $detail) {
                    $alat = $detail->alat;
                    if ($alat->stok < $detail->jumlah) {
                        throw new \Exception("Stok alat '{$alat->nama_alat}' tidak mencukupi untuk dipinjam.");
                    }
                    $alat->decrement('stok', $detail->jumlah);
                }
            } elseif ($statusLama == 'dipinjam' && ($statusBaru == 'selesai')) {
                foreach ($peminjaman->detailPinjams as $detail) {
                    $detail->alat->increment('stok', $detail->jumlah);
                }
            }

            $peminjaman->update(['status' => $statusBaru]);

            // Catat Log Aktivitas
            LogAktivitas::create([
                'user_id'   => auth()->id(),
                'aktivitas' => "Mengubah status peminjaman ID #{$peminjaman->id} dari '{$statusLama}' menjadi '{$statusBaru}'.",
                'waktu'     => now(),
            ]);

            DB::commit();
            return redirect()->route('admin.peminjaman.index')->with('success', 'Status peminjaman berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            dd("Error pada updateStatusPeminjaman: " . $e->getMessage());
        }
    }

    public function destroyPeminjaman($id)
    {
        $peminjaman = Peminjaman::with('detailPinjams')->findOrFail($id);

        if ($peminjaman->status == 'dipinjam') {
            foreach ($peminjaman->detailPinjams as $detail) {
                $detail->alat->increment('stok', $detail->jumlah);
            }
        }

        $idPinjam = $peminjaman->id;
        $peminjaman->delete();

        // Catat Log Aktivitas
        LogAktivitas::create([
            'user_id'   => auth()->id(),
            'aktivitas' => "Menghapus transaksi peminjaman ID #{$idPinjam}.",
            'waktu'     => now(),
        ]);

        return redirect()->route('admin.peminjaman.index')->with('success', 'Data peminjaman berhasil dihapus.');
    }

    // ==========================================
    // MODUL KELOLA PENGEMBALIAN & DENDA
    // ==========================================
    public function indexPengembalian(Request $request)
    {
        $search = $request->input('search');

        $peminjamans = Peminjaman::with(['user', 'detailPinjams.alat'])
            ->whereIn('status', ['dipinjam', 'telat'])
            ->when($search, function ($query, $search) {
                return $query->whereHas('user', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.pengembalian.index', compact('peminjamans', 'search'));
    }

    public function prosesPengembalian(Request $request, $id)
    {
        $request->validate([
            'kondisi_kembali' => 'required|in:Baik,Rusak,Hilang',
        ]);

        DB::beginTransaction();
        try {
            $peminjaman = Peminjaman::with('detailPinjams.alat')->findOrFail($id);

            // 1. Hitung Otomatis Denda Keterlambatan
            $tglRencanaKembali = Carbon::parse($peminjaman->tgl_kembali_plan)->startOfDay();
            $tglDikembalikan = Carbon::now()->startOfDay();

            $hariTelat = 0;
            $dendaTelat = 0;

            if ($tglDikembalikan->greaterThan($tglRencanaKembali)) {
                $hariTelat = $tglRencanaKembali->diffInDays($tglDikembalikan);
                $dendaTelat = $hariTelat * 1000; // Rp 1.000 / hari
            }

            // 2. Hitung Denda Kerusakan/Kehilangan
            $dendaKondisi = 0;
            if ($request->kondisi_kembali == 'Rusak') {
                $dendaKondisi = 30000;
            } elseif ($request->kondisi_kembali == 'Hilang') {
                $dendaKondisi = 50000;
            }

            // 3. Total Denda
            $totalDenda = $dendaTelat + $dendaKondisi;

            // 4. Kembalikan Stok Alat
            foreach ($peminjaman->detailPinjams as $detail) {
                $detail->alat->increment('stok', $detail->jumlah);
            }

            // 5. Update Status Peminjaman menjadi 'selesai'
            $peminjaman->update([
                'status' => 'selesai'
            ]);

            // 6. Simpan Detail Pengembalian ke Database (Opsional jika ada model Pengembalian)
            if (class_exists('App\Models\Pengembalian')) {
                \App\Models\Pengembalian::create([
                    'peminjaman_id'   => $peminjaman->id,
                    'petugas_id'      => auth()->id(),
                    'tgl_kembali'     => now(),
                    'kondisi_kembali' => $request->kondisi_kembali,
                    'hari_telat'      => $hariTelat,
                    'denda_telat'     => $dendaTelat,
                    'denda_kondisi'   => $dendaKondisi,
                    'total_denda'     => $totalDenda,
                ]);
            }

            // 7. Catat Log Aktivitas
            LogAktivitas::create([
                'user_id'   => auth()->id(),
                'aktivitas' => "Memproses pengembalian alat untuk peminjaman ID #{$peminjaman->id} (Kondisi: {$request->kondisi_kembali}, Denda: Rp " . number_format($totalDenda, 0, ',', '.') . ").",
                'waktu'     => now(),
            ]);

            DB::commit();

            $pesan = "Pengembalian berhasil diproses!";
            if ($totalDenda > 0) {
                $pesan .= " Total Denda: Rp " . number_format($totalDenda, 0, ',', '.') . " (Telat {$hariTelat} hari & Kondisi {$request->kondisi_kembali})";
            }

            return redirect()->route('admin.pengembalian.index')->with('success', $pesan);
        } catch (\Exception $e) {
            DB::rollBack();
            dd("Error pada prosesPengembalian: " . $e->getMessage());
        }
    }
}