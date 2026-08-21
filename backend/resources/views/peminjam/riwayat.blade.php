@extends('layouts.app')

@section('title', 'Peminjaman Saya')
@section('header-title', 'Riwayat Peminjaman Alat')

@section('content')
<div class="bg-white rounded-lg shadow-sm overflow-hidden border border-gray-200">
    <div class="p-5 border-b border-gray-200 bg-gray-50">
        <h3 class="text-base font-bold text-gray-800">Daftar Peminjaman Saya</h3>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-100 text-gray-600 text-xs uppercase tracking-wider border-b">
                    <th class="py-3 px-4">Alat Dipinjam</th>
                    <th class="py-3 px-4">Tanggal Pinjam / Rencana Kembali</th>
                    <th class="py-3 px-4">Status</th>
                </tr>
            </thead>
            <tbody class="text-gray-700 text-sm divide-y divide-gray-200">
                @forelse($peminjamans as $peminjaman)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="py-3 px-4">
                            <ul class="list-disc list-inside space-y-1">
                                @foreach($peminjaman->detailPinjams as $detail)
                                    <li>
                                        <span class="font-medium text-gray-900">{{ $detail->alat->nama_alat ?? 'Alat Dihapus' }}</span> 
                                        <span class="text-xs text-gray-500">({{ $detail->jumlah }} unit)</span>
                                    </li>
                                @endforeach
                            </ul>
                        </td>
                        <td class="py-3 px-4 text-xs text-gray-600">
                            <div>Pinjam: <strong class="text-gray-800">{{ $peminjaman->tgl_pinjam }}</strong></div>
                            <div>Rencana: <strong class="text-gray-800">{{ $peminjaman->tgl_kembali_plan }}</strong></div>
                        </td>
                        <td class="py-3 px-4">
                            <span class="px-2.5 py-1 text-xs font-semibold rounded-full
                                @if($peminjaman->status == 'diajukan') bg-yellow-100 text-yellow-808
                                @elseif($peminjaman->status == 'dipinjam') bg-blue-100 text-blue-800
                                @elseif($peminjaman->status == 'selesai') bg-emerald-100 text-emerald-800
                                @else bg-red-100 text-red-800 @endif">
                                {{ ucfirst($peminjaman->status) }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="py-4 text-center text-gray-500">Belum ada riwayat peminjaman.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="p-4 border-t border-gray-200 bg-gray-50">
        {{ $peminjamans->links() }}
    </div>
</div>
@endsection