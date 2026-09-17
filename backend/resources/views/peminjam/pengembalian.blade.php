@extends('layouts.app')

@section('title', 'Mengembalikan Alat')
@section('header-title', 'Riwayat Pengembalian Alat')

@section('content')
@if(session('success'))
    <div class="mb-4 bg-emerald-50 border border-emerald-200 text-emerald-800 p-4 rounded-lg text-sm">
        {{ session('success') }}
    </div>
@endif

<div class="bg-white rounded-lg shadow-sm overflow-hidden border border-gray-200">
    <div class="p-5 border-b border-gray-200 bg-gray-50">
        <h3 class="text-base font-bold text-gray-800">Daftar Riwayat Pengembalian Alat</h3>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-100 text-gray-600 text-xs uppercase tracking-wider border-b">
                    <th class="py-3 px-4">Alat</th>
                    <th class="py-3 px-4">Tanggal Dikembalikan</th>
                    <th class="py-3 px-4">Kondisi</th>
                    <th class="py-3 px-4">Denda</th>
                </tr>
            </thead>
            <tbody class="text-gray-700 text-sm divide-y divide-gray-200">
                @forelse($pengembalians as $pengembalian)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="py-3 px-4">
                            <ul class="list-disc list-inside space-y-1">
                                @php
                                    $details = $pengembalian->peminjaman->detailPinjams ?? $pengembalian->peminjaman->detailPeminjaman ?? [];
                                @endphp
                                @forelse($details as $detail)
                                    <li>
                                        <span class="font-medium text-gray-900">{{ $detail->alat->nama_alat ?? 'Alat Dihapus' }}</span> 
                                        <span class="text-xs text-gray-500">({{ $detail->jumlah }} unit)</span>
                                    </li>
                                @empty
                                    <li class="text-gray-400 italic">Tidak ada detail alat</li>
                                @endforelse
                            </ul>
                        </td>
                        <td class="py-3 px-4 text-sm">
                            {{ $pengembalian->tgl_kembali ?? $pengembalian->created_at }}
                        </td>
                        <td class="py-3 px-4 font-medium">
                            {{ $pengembalian->kondisi_kembali ?? 'Baik' }}
                        </td>
                        <td class="py-3 px-4 font-semibold text-red-600">
                            Rp {{ number_format($pengembalian->denda ?? 0, 0, ',', '.') }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="py-4 text-center text-gray-500">Belum ada riwayat pengembalian.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="p-4 border-t border-gray-200 bg-gray-50">
        {{ $pengembalians->links() }}
    </div>
</div>
@endsection