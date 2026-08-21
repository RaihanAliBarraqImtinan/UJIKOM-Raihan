<?php

namespace App\Http\Controllers;

use App\Models\Alat;
use App\Models\Peminjaman;
use App\Models\DetailPinjam;
use App\Models\Pengembalian;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PeminjamController extends Controller
{
    // 1. Dashboard Peminjam
    public function dashboard()
    {
        $totalPinjam = Peminjaman::where('user_id', auth()->id())->count();
        $sedangDipinjam = Peminjaman::where('user_id', auth()->id())->where('status', 'dipinjam')->count();
        $selesai = Peminjaman::where('user_id', auth()->id())->where('status', 'selesai')->count();

        return view('peminjam.dashboard', compact('totalPinjam', 'sedangDipinjam', 'selesai'));
    }

    // 2. Melihat daftar/katalog alat yang tersedia
    public function katalogAlat(Request $request)
    {
        $search = $request->input('search');
        
        $alats = Alat::with('kategori')
            ->where('stok', '>', 0)
            ->when($search, function ($query) use ($search) {
                $query->where('nama_alat', 'like', "%{$search}%");
            })
            ->paginate(10);

        return view('peminjam.katalog', compact('alats', 'search'));
    }

    // 3. Mengajukan Peminjaman
    public function ajukanPeminjaman(Request $request)
    {
        $request->validate([
            'tgl_kembali_plan' => 'required|date|after:today',
            'alat_id' => 'required|array',
            'jumlah' => 'required|array',
        ]);

        DB::beginTransaction();
        try {
            $peminjaman = Peminjaman::create([
                'user_id' => auth()->id(),
                'tgl_pinjam' => now(),
                'tgl_kembali_plan' => $request->tgl_kembali_plan,
                'status' => 'diajukan',
            ]);

            foreach ($request->alat_id as $index => $alatId) {
                if (!isset($request->jumlah[$index]) || $request->jumlah[$index] <= 0) {
                    continue;
                }

                DetailPinjam::create([
                    'peminjaman_id' => $peminjaman->id,
                    'alat_id' => $alatId,
                    'jumlah' => $request->jumlah[$index],
                ]);
            }

            DB::commit();
            return redirect()->route('peminjam.riwayat')->with('success', 'Pengajuan peminjaman berhasil dikirim.');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'Gagal mengajukan peminjaman: ' . $e->getMessage());
        }
    }

    // 4. Melihat riwayat peminjaman user yang sedang login
    public function riwayatPeminjaman()
    {
        $peminjamans = Peminjaman::with('detailPinjams.alat')
            ->where('user_id', auth()->id())
            ->latest()
            ->paginate(10);

        return view('peminjam.riwayat', compact('peminjamans'));
    }

    // 5. Melihat daftar pengembalian user
    public function pengembalian()
    {
        $pengembalians = Pengembalian::whereHas('peminjaman', function ($q) {
            $q->where('user_id', auth()->id());
        })->with(['peminjaman.detailPinjams.alat'])->latest()->paginate(10);

        return view('peminjam.pengembalian', compact('pengembalians'));
    }

    // 6. Profil Saya
    public function profil()
    {
        $user = auth()->user();
        return view('peminjam.profil', compact('user'));
    }
}