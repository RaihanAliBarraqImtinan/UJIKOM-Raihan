@extends('layouts.app')

@section('title', 'Pemantauan Pengembalian')
@section('header-title', 'Pemantauan & Proses Pengembalian Alat')

@section('content')
<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
    <h3 class="text-lg font-bold text-gray-800 mb-4">Daftar Peminjaman Aktif (Belum Kembali)</h3>

    @if(session('success'))
        <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
            {{ session('success') }}
        </div>
    @endif

    <form action="{{ route('petugas.pengembalian.index') }}" method="GET" class="mb-6">
        <div class="flex max-w-xs">
            <input type="text" name="search" class="w-full px-3 py-2 border border-gray-300 rounded-l-lg text-sm focus:outline-none" placeholder="Cari nama peminjam..." value="{{ $search }}">
            <button class="px-4 py-2 bg-slate-800 text-white rounded-r-lg text-sm hover:bg-slate-700 transition" type="submit">Cari</button>
        </div>
    </form>

    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-200 text-xs font-bold text-gray-500 uppercase tracking-wider">
                    <th class="py-3 px-4">PEMINJAM</th>
                    <th class="py-3 px-4">TGL PINJAM</th>
                    <th class="py-3 px-4">RENCANA KEMBALI</th>
                    <th class="py-3 px-4">STATUS</th>
                    <th class="py-3 px-4">DETAIL ALAT</th>
                    <th class="py-3 px-4 text-center">AKSI PENGEMBALIAN</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 text-sm text-gray-700">
                @forelse ($peminjamans as $item)
                    <tr class="hover:bg-gray-50">
                        <td class="py-4 px-4 font-bold text-gray-800">{{ $item->user->name ?? '-' }}</td>
                        <td class="py-4 px-4">{{ \Carbon\Carbon::parse($item->created_at)->format('Y-m-d') }}</td>
                        <td class="py-4 px-4">{{ $item->tgl_kembali_rencana ?? \Carbon\Carbon::parse($item->created_at)->addDays(3)->format('Y-m-d') }}</td>
                        <td class="py-4 px-4">
                            <span class="px-2.5 py-1 text-xs font-semibold rounded-md bg-blue-100 text-blue-600">
                                {{ ucfirst($item->status) }}
                            </span>
                        </td>
                        <td class="py-4 px-4">
                            <ul class="list-disc list-inside">
                                @foreach($item->detailPinjams as $detail)
                                    <li><span class="font-bold">{{ $detail->alat->nama_alat ?? 'Alat' }}</span> (Jumlah: {{ $detail->jumlah }})</li>
                                @endforeach
                            </ul>
                        </td>
                        <td class="py-4 px-4">
                            <form action="{{ route('petugas.pengembalian.proses', $item->id) }}" method="POST" class="bg-gray-50 p-3 rounded-lg border border-gray-200 text-xs space-y-2">
                                @csrf
                                <div>
                                    <label class="block font-medium text-gray-600 mb-1">Kondisi Kembali:</label>
                                    <select name="kondisi_kembali" class="w-full border border-gray-300 rounded px-2 py-1 text-xs focus:outline-none">
                                        <option value="Baik">Baik</option>
                                        <option value="Rusak">Rusak</option>
                                        <option value="Hilang">Hilang</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block font-medium text-gray-600 mb-1">Denda (Rp):</label>
                                    <input type="number" name="denda" value="0" class="w-full border border-gray-300 rounded px-2 py-1 text-xs focus:outline-none">
                                </div>
                                <button type="submit" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-semibold py-1.5 rounded transition">
                                    Terima Pengembalian
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-6 text-gray-400">Tidak ada peminjaman aktif.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection