<?php

namespace App\Http\Controllers;

use App\Models\Peminjaman;
use App\Models\Pengembalian;
use App\Models\Alat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PetugasController extends Controller
{
    // ==================== KELOLA PEMINJAMAN ====================
    
    // Menampilkan daftar pengajuan peminjaman dari peminjam
    public function indexPeminjaman(Request $request)
    {
        $search = $request->input('search');

        $peminjamans = Peminjaman::with(['user', 'detailPinjams.alat'])
            ->where('status', 'diajukan')
            ->when($search, function ($query, $search) {
                return $query->whereHas('user', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(10);

        return view('petugas.peminjaman.index', compact('peminjamans', 'search'));
    }

    // Menyetujui Peminjaman (Mengubah status & mengurangi stok alat)
    public function setujuiPeminjaman($id)
    {
        DB::beginTransaction();
        try {
            $peminjaman = Peminjaman::with('detailPinjams')->findOrFail($id);
            $peminjaman->update(['status' => 'dipinjam']);

            foreach ($peminjaman->detailPinjams as $detail) {
                $alat = Alat::findOrFail($detail->alat_id);
                $alat->stok -= $detail->jumlah;
                $alat->save();
            }

            DB::commit();
            return redirect()->back()->with('success', 'Peminjaman disetujui dan stok alat dikurangi.');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    // Menolak Peminjaman
    public function tolakPeminjaman($id)
    {
        try {
            $peminjaman = Peminjaman::findOrFail($id);

            if ($peminjaman->status == 'diajukan') {
                $peminjaman->delete();
                return redirect()->back()->with('success', 'Pengajuan peminjaman berhasil ditolak.');
            }

            return redirect()->back()->with('error', 'Status peminjaman sudah berubah.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    // ==================== KELOLA PENGEMBALIAN ====================

    // Menampilkan daftar peminjaman yang sedang dipinjam
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
            ->paginate(10);

        return view('petugas.pengembalian.index', compact('peminjamans', 'search'));
    }

    // Memproses Pengembalian Alat
    public function prosesPengembalian(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $peminjaman = Peminjaman::with('detailPinjams')->findOrFail($id);

            if ($peminjaman->status === 'selesai') {
                return redirect()->back()->with('error', 'Peminjaman ini sudah dikembalikan.');
            }

            // 1. Kembalikan stok alat
            foreach ($peminjaman->detailPinjams as $detail) {
                $alat = Alat::findOrFail($detail->alat_id);
                $alat->stok += $detail->jumlah;
                $alat->save();
            }

            // 2. Hitung denda jika terlambat
            $tglRencana = Carbon::parse($peminjaman->tgl_kembali_rencana ?? $peminjaman->created_at->addDays(3));
            $tglKembali = Carbon::now();
            $dendaPerHari = 1000;
            $denda = 0;

            if ($tglKembali->greaterThan($tglRencana)) {
                $selisihHari = $tglKembali->diffInDays($tglRencana);
                $denda = $selisihHari * $dendaPerHari;
            }

            // 3. Simpan catatan pengembalian
            Pengembalian::create([
                'peminjaman_id'   => $peminjaman->id,
                'petugas_id'      => auth()->id(),
                'tgl_kembali'     => $tglKembali,
                'kondisi_kembali' => $request->input('kondisi_kembali', 'Baik'),
                'denda'           => $denda,
            ]);

            // 4. Update status peminjaman menjadi selesai
            $peminjaman->update(['status' => 'selesai']);

            DB::commit();
            return redirect()->back()->with('success', 'Pengembalian alat berhasil diproses.');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'Gagal memproses pengembalian: ' . $e->getMessage());
        }
    }

    // ==================== KELOLA LAPORAN ====================

    // Menampilkan Halaman Rekap Laporan & Filter
    public function indexLaporan(Request $request)
    {
        $status = $request->input('status');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $peminjamans = Peminjaman::with(['user', 'detailPinjams.alat', 'pengembalian'])
            ->when($status, function ($query, $status) {
                return $query->where('status', $status);
            })
            ->when($startDate && $endDate, function ($query) use ($startDate, $endDate) {
                return $query->whereBetween(DB::raw('DATE(created_at)'), [$startDate, $endDate]);
            })
            ->latest()
            ->paginate(10);

        return view('petugas.laporan.index', compact('peminjamans', 'status', 'startDate', 'endDate'));
    }

    // Menampilkan Cetak Nota Struk Peminjaman
    public function cetakNota($id)
    {
        $peminjaman = Peminjaman::with(['user', 'detailPinjams.alat', 'pengembalian'])->findOrFail($id);

        return view('petugas.laporan.cetak', compact('peminjaman'));
    }
}