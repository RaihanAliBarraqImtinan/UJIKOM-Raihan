@extends('layouts.app')

@section('title', 'Cetak Laporan')
@section('header-title', 'Laporan Peminjaman & Pengembalian Alat')

@section('content')
<!-- Section Filter -->
<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6">
    <h3 class="text-lg font-bold text-gray-800 mb-4">Filter Laporan</h3>
    <form action="{{ route('petugas.laporan.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-5 gap-4 items-end">
        <div>
            <label class="block text-xs font-semibold text-gray-600 mb-1">Status Peminjaman:</label>
            <select name="status" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none">
                <option value="">Semua Status</option>
                <option value="dipinjam" {{ request('status') == 'dipinjam' ? 'selected' : '' }}>Dipinjam</option>
                <option value="selesai" {{ request('status') == 'selesai' ? 'selected' : '' }}>Selesai</option>
            </select>
        </div>
        <div>
            <label class="block text-xs font-semibold text-gray-600 mb-1">Dari Tanggal (Pinjam):</label>
            <input type="date" name="start_date" value="{{ $startDate ?? '' }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none">
        </div>
        <div>
            <label class="block text-xs font-semibold text-gray-600 mb-1">Sampai Tanggal (Pinjam):</label>
            <input type="date" name="end_date" value="{{ $endDate ?? '' }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none">
        </div>
        <div class="flex gap-2">
            <button type="submit" class="bg-slate-800 text-white px-5 py-2 rounded-lg text-sm font-semibold hover:bg-slate-700 transition w-full">Filter</button>
            <a href="{{ route('petugas.laporan.index') }}" class="bg-gray-200 text-gray-700 px-4 py-2 rounded-lg text-sm font-semibold hover:bg-gray-300 transition text-center">Reset</a>
        </div>
    </form>
</div>

<!-- Section Table Result -->
<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
    <div class="flex justify-between items-center mb-6">
        <h3 class="text-lg font-bold text-gray-800">Hasil Rekap Laporan</h3>
        <button onclick="window.print()" class="bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-sm px-4 py-2 rounded-lg transition">
            Cetak / Print Rekap Halaman
        </button>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-200 text-xs font-bold text-gray-500 uppercase tracking-wider">
                    <th class="py-3 px-4">NO</th>
                    <th class="py-3 px-4">PEMINJAM</th>
                    <th class="py-3 px-4">TGL PINJAM</th>
                    <th class="py-3 px-4">RENCANA KEMBALI</th>
                    <th class="py-3 px-4">STATUS</th>
                    <th class="py-3 px-4">DETAIL ALAT</th>
                    <th class="py-3 px-4">DENDA</th>
                    <th class="py-3 px-4">AKSI</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 text-sm text-gray-700">
                @forelse ($peminjamans as $index => $item)
                    <tr class="hover:bg-gray-50">
                        <td class="py-4 px-4 text-gray-500">{{ $loop->iteration }}</td>
                        <td class="py-4 px-4 font-bold text-gray-800">{{ $item->user->name ?? '-' }}</td>
                        <td class="py-4 px-4">{{ \Carbon\Carbon::parse($item->created_at)->format('Y-m-d') }}</td>
                        <td class="py-4 px-4">{{ $item->tgl_kembali_rencana ?? \Carbon\Carbon::parse($item->created_at)->addDays(3)->format('Y-m-d') }}</td>
                        <td class="py-4 px-4">
                            <span class="px-2.5 py-1 text-xs font-semibold rounded-md 
                                {{ $item->status == 'dipinjam' ? 'bg-blue-100 text-blue-600' : 'bg-green-100 text-green-600' }}">
                                {{ ucfirst($item->status) }}
                            </span>
                        </td>
                        <td class="py-4 px-4">
                            <ul class="list-disc list-inside">
                                @foreach($item->detailPinjams as $detail)
                                    <li>{{ $detail->alat->nama_alat ?? 'Alat' }} ({{ $detail->jumlah }})</li>
                                @endforeach
                            </ul>
                        </td>
                        <td class="py-4 px-4 font-semibold text-gray-800">
                            Rp {{ number_format($item->pengembalian->denda ?? 0, 0, ',', '.') }}
                        </td>
                        <td class="py-4 px-4">
                            <a href="{{ route('petugas.laporan.nota', $item->id) }}" target="_blank" class="bg-blue-600 hover:bg-blue-700 text-white text-xs px-3 py-1.5 rounded-md font-semibold transition inline-block">
                                Cetak Nota
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-6 text-gray-400">Data laporan tidak ditemukan.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection