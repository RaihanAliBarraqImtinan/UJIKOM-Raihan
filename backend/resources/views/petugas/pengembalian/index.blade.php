@extends('layouts.app')

@section('title', 'Kelola Pengembalian Alat')
@section('header-title', 'Pengembalian Alat')

@section('content')
<div class="bg-white rounded-lg shadow-sm border border-gray-200">
    <div class="p-5 border-b bg-gray-50 flex justify-between items-center">
        <h3 class="text-lg font-bold text-gray-800">Daftar Alat yang Harus Dikembalikan</h3>
        
        <form action="{{ route('petugas.pengembalian.index') }}" method="GET" class="flex w-80">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama peminjam..." class="w-full px-3 py-2 text-sm border rounded-l-lg focus:outline-none focus:ring-1 focus:ring-gray-400">
            <button type="submit" class="bg-gray-800 text-white px-4 py-2 text-sm rounded-r-lg hover:bg-gray-700">Cari</button>
        </form>
    </div>

    @if(session('success'))
        <div class="m-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg text-sm">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="m-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg text-sm">{{ session('error') }}</div>
    @endif

    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm">
            <thead class="bg-gray-100 text-gray-600 uppercase border-b">
                <tr>
                    <th class="py-3 px-4">Peminjam</th>
                    <th class="py-3 px-4">Detail Alat</th>
                    <th class="py-3 px-4">Status</th>
                    <th class="py-3 px-4 text-center">Aksi Pengembalian</th>
                </tr>
            </thead>
            <tbody>
                @forelse($peminjamans as $peminjaman)
                <tr class="border-b hover:bg-gray-50">
                    <td class="py-3 px-4 font-medium text-gray-800">{{ $peminjaman->user->name ?? '-' }}</td>
                    <td class="py-3 px-4">
                        @foreach($peminjaman->detailPinjams as $detail)
                            <div>• {{ $detail->alat->nama_alat ?? 'Alat N/A' }} ({{ $detail->jumlah }} unit)</div>
                        @endforeach
                    </td>
                    <td class="py-3 px-4">
                        <span class="px-2 py-1 text-xs font-semibold rounded bg-yellow-100 text-yellow-800">
                            {{ ucfirst($peminjaman->status) }}
                        </span>
                    </td>
                    <td class="py-3 px-4 text-center">
                        <form action="{{ route('petugas.pengembalian.proses', $peminjaman->id) }}" method="POST" class="flex gap-2 justify-center">
                            @csrf
                            <select name="kondisi_kembali" required class="border text-xs rounded px-2 py-1 bg-white">
                                <option value="Baik">Baik</option>
                                <option value="Rusak">Rusak</option>
                                <option value="Hilang">Hilang</option>
                            </select>
                            <input type="number" name="denda" placeholder="Denda (Rp)" value="0" class="w-28 border text-xs rounded px-2 py-1">
                            <button type="submit" onclick="return confirm('Proses pengembalian alat ini?')" class="bg-emerald-600 hover:bg-emerald-700 text-white px-3 py-1 rounded text-xs font-semibold transition">
                                Terima Pengembalian
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="py-6 text-center text-gray-500">Tidak ada transaksi peminjaman yang perlu dikembalikan.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="p-4 border-t bg-gray-50">{{ $peminjamans->links() }}</div>
</div>
@endsection