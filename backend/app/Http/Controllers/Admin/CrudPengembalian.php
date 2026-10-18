<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Peminjaman;
use App\Models\Pengembalian;
use App\Models\LogAktivitas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CrudPengembalian extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');

        $peminjamans = Peminjaman::with(['user', 'detailPinjam.alat'])
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

    public function store(Request $request, $id)
    {
        $request->validate([
            'kondisi_kembali' => 'required|in:Baik,Rusak,Hilang',
        ]);

        DB::beginTransaction();
        try {
            $peminjaman = Peminjaman::with('detailPinjam.alat')->findOrFail($id);

            // Hitung denda keterlambatan
            $tglRencanaKembali = Carbon::parse($peminjaman->tgl_kembali_plan)->startOfDay();
            $tglDikembalikan = Carbon::now()->startOfDay();

            $hariTelat = 0;
            $dendaTelat = 0;

            if ($tglDikembalikan->greaterThan($tglRencanaKembali)) {
                $hariTelat = $tglRencanaKembali->diffInDays($tglDikembalikan);
                $dendaTelat = $hariTelat * 1000; // Rp 1.000 / hari
            }

            // Hitung denda kondisi barang
            $dendaKondisi = 0;
            if ($request->kondisi_kembali == 'Rusak') {
                $dendaKondisi = 30000;
            } elseif ($request->kondisi_kembali == 'Hilang') {
                $dendaKondisi = 50000;
            }

            $totalDenda = $dendaTelat + $dendaKondisi;

            // Kembalikan stok alat
            foreach ($peminjaman->detailPinjam as $detail) {
                $detail->alat->increment('stok', $detail->jumlah);
            }

            // Update status peminjaman
            $peminjaman->update([
                'status' => 'selesai'
            ]);

            // Simpan data pengembalian
            if (class_exists(Pengembalian::class)) {
                Pengembalian::create([
                    'peminjaman_id'   => $peminjaman->id,
                    'petugas_id'      => auth()->id(),
                    'tgl_kembali'     => now(),
                    'kondisi_kembali' => $request->kondisi_kembali,
                    'denda'           => $totalDenda,
                ]);
            }

            LogAktivitas::create([
                'user_id'   => auth()->id(),
                'aktivitas' => "Memproses pengembalian alat untuk peminjaman ID #{$peminjaman->id} (Kondisi: {$request->kondisi_kembali}, Denda: Rp " . number_format($totalDenda, 0, ',', '.') . ").",
            ]);

            DB::commit();

            $pesan = "Pengembalian berhasil diproses!";
            if ($totalDenda > 0) {
                $pesan .= " Total Denda: Rp " . number_format($totalDenda, 0, ',', '.') . " (Telat {$hariTelat} hari & Kondisi {$request->kondisi_kembali})";
            }

            return redirect()->route('admin.pengembalian.index')->with('success', $pesan);
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal memproses pengembalian: ' . $e->getMessage());
        }
    }
}