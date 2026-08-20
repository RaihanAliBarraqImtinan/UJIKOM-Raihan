<?php

namespace App\Http\Controllers;

use App\Models\Peminjaman;
use App\Models\Pengembalian;
use App\Models\Alat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Exception;

class PetugasController extends Controller
{
    /**
     * Menampilkan daftar pengajuan peminjaman dari peminjam/siswa.
     */
    public function indexPeminjaman(Request $request)
    {
        $search = $request->input('search');

        $peminjamans = Peminjaman::with(['user', 'detailPinjams.alat'])
            ->when($search, function ($query, $search) {
                return $query->whereHas('user', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('petugas.peminjaman.index', compact('peminjamans', 'search'));
    }

    /**
     * Menampilkan daftar alat yang SEDANG DIPINJAM (siap dikembalikan).
     */
    public function indexPengembalian(Request $request)
    {
        $search = $request->input('search');

        // Menampilkan data dengan status 'dipinjam' atau 'telat'
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

        return view('petugas.pengembalian.index', compact('peminjamans', 'search'));
    }

    /**
     * Menyetujui pengajuan peminjaman dan mengurangkan stok alat.
     */
    public function setujuiPeminjaman($id)
    {
        DB::beginTransaction();
        try {
            $peminjaman = Peminjaman::with('detailPinjams.alat')->findOrFail($id);

            if ($peminjaman->status === 'dipinjam' || $peminjaman->status === 'selesai') {
                return redirect()->back()->with('error', 'Peminjaman ini sudah diproses sebelumnya.');
            }

            foreach ($peminjaman->detailPinjams as $detail) {
                $alat = $detail->alat;
                if (!$alat || $alat->stok < $detail->jumlah) {
                    DB::rollBack();
                    return redirect()->back()->with(
                        'error', 
                        "Stok alat '{$detail->alat->nama_alat}' tidak mencukupi. (Sisa stok: {$alat->stok})"
                    );
                }
            }

            foreach ($peminjaman->detailPinjams as $detail) {
                $alat = $detail->alat;
                $alat->stok -= $detail->jumlah;
                $alat->save();
            }

            $peminjaman->update(['status' => 'dipinjam']);

            DB::commit();
            return redirect()->back()->with('success', 'Peminjaman berhasil disetujui dan stok alat telah diperbarui.');

        } catch (Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Memproses pengembalian alat dan mengembalikan stok ke inventaris.
     */
    public function prosesPengembalian(Request $request, $peminjamanId)
    {
        $request->validate([
            'kondisi_kembali' => 'required|string|max:255',
            'denda'           => 'nullable|integer|min:0',
        ]);

        DB::beginTransaction();
        try {
            $peminjaman = Peminjaman::with('detailPinjams')->findOrFail($peminjamanId);

            if ($peminjaman->status === 'selesai') {
                return redirect()->back()->with('error', 'Transaksi peminjaman ini sudah diselesaikan sebelumnya.');
            }

            // 1. Simpan catatan pengembalian
            Pengembalian::create([
                'peminjaman_id'   => $peminjaman->id,
                'tgl_kembali'     => now(),
                'kondisi_kembali' => $request->kondisi_kembali,
                'denda'           => $request->denda ?? 0,
                'petugas_id'      => auth()->id(),
            ]);

            // 2. Ubah status peminjaman menjadi 'selesai'
            $peminjaman->update(['status' => 'selesai']);

            // 3. Kembalikan stok alat ke inventaris
            foreach ($peminjaman->detailPinjams as $detail) {
                $alat = Alat::findOrFail($detail->alat_id);
                $alat->stok += $detail->jumlah;
                $alat->save();
            }

            DB::commit();
            return redirect()->back()->with('success', 'Pengembalian berhasil dicatat dan stok alat telah dipulihkan.');

        } catch (Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}