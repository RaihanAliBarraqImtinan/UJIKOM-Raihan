<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Alat;
use App\Models\Peminjaman;
use App\Models\DetailPinjam;
use Illuminate\Support\Facades\Auth;

class PeminjamController extends Controller
{
    // Dashboard Utama Peminjam
    public function dashboard()
    {
        $userId = Auth::id();

        $totalPinjam = Peminjaman::where('user_id', $userId)->count();
        $sedangDipinjam = Peminjaman::where('user_id', $userId)
            ->whereIn('status', ['dipinjam', 'disetujui'])
            ->count();
        $selesai = Peminjaman::where('user_id', $userId)
            ->where('status', 'selesai')
            ->count();

        return view('peminjam.dashboard', compact('totalPinjam', 'sedangDipinjam', 'selesai'));
    }

    // FITUR 1: Melihat Daftar Alat (Katalog)
    public function katalogAlat()
    {
        $alats = Alat::with('kategori')
            ->where('stok', '>', 0)
            ->paginate(10);

        return view('peminjam.katalog', compact('alats'));
    }

    // FITUR 2: Mengajukan Peminjaman
    public function ajukanPeminjaman(Request $request)
    {
        $request->validate([
            'tgl_kembali_plan' => 'required|date|after:today',
            'alat_id' => 'required|array|min:1',
            'jumlah' => 'required|array',
        ]);

        $peminjaman = Peminjaman::create([
            'user_id' => Auth::id(),
            'tgl_pinjam' => now()->toDateString(),
            'tgl_kembali_plan' => $request->tgl_kembali_plan,
            'status' => 'diajukan',
        ]);

        foreach ($request->alat_id as $idAlat) {
            $qty = $request->jumlah[$idAlat] ?? 1;

            DetailPinjam::create([
                'peminjaman_id' => $peminjaman->id,
                'alat_id' => $idAlat,
                'jumlah' => $qty,
            ]);
        }

        return redirect()->route('peminjam.pengembalian')->with('success', 'Pengajuan peminjaman berhasil dikirim!');
    }

    // FITUR 3: Mengembalikan Alat (Mengirim variabel $peminjamans)
    public function pengembalian()
    {
        $peminjamans = Peminjaman::where('user_id', Auth::id())
            ->whereIn('status', ['dipinjam', 'disetujui'])
            ->with('detailPinjams.alat')
            ->latest()
            ->paginate(10);

        return view('peminjam.pengembalian', compact('peminjamans'));
    }

    // FITUR 3: Aksi Pengembalian Alat
    public function prosesPengembalian($id)
    {
        $peminjaman = Peminjaman::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $peminjaman->update([
            'status' => 'menunggu_pengembalian'
        ]);

        return redirect()->route('peminjam.pengembalian')->with('success', 'Pengajuan pengembalian berhasil dikirim ke petugas!');
    }

    // Riwayat Peminjaman (Menu Pendukung)
    public function riwayatPeminjaman()
    {
        $peminjamans = Peminjaman::where('user_id', Auth::id())
            ->with('detailPinjams.alat')
            ->latest()
            ->paginate(10);

        return view('peminjam.riwayat', compact('peminjamans'));
    }

    // Profil Saya (Menu Pendukung)
    public function profil()
    {
        $user = Auth::user();
        return view('peminjam.profil', compact('user'));
    }
}