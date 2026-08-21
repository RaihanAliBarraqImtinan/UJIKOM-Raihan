@extends('layouts.app')

@section('title', 'Katalog Alat')
@section('header-title', 'Katalog & Pengajuan Peminjaman')

@section('content')
@if(session('success'))
    <div class="mb-4 bg-emerald-50 border border-emerald-200 text-emerald-800 p-4 rounded-lg text-sm">
        {{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div class="mb-4 bg-red-50 border border-red-200 text-red-800 p-4 rounded-lg text-sm">
        {{ session('error') }}
    </div>
@endif

<form action="{{ route('peminjam.peminjaman.ajukan') }}" method="POST">
    @csrf

    <div class="bg-white p-5 rounded-lg shadow-sm border border-gray-200 mb-6 max-w-md">
        <label class="block text-gray-700 text-sm font-bold mb-2">Rencana Tanggal Kembali</label>
        <input type="date" name="tgl_kembali_plan" min="{{ date('Y-m-d', strtotime('+1 day')) }}" required
            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
    </div>

    <div class="bg-white rounded-lg shadow-sm overflow-hidden border border-gray-200">
        <div class="p-5 border-b border-gray-200 bg-gray-50 flex justify-between items-center">
            <h3 class="text-base font-bold text-gray-800">Daftar Alat Tersedia</h3>
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold px-4 py-2 rounded-lg transition">
                Ajukan Peminjaman
            </button>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-100 text-gray-600 text-xs uppercase tracking-wider border-b">
                        <th class="py-3 px-4 text-center w-12">Pilih</th>
                        <th class="py-3 px-4">Nama Alat</th>
                        <th class="py-3 px-4">Kategori</th>
                        <th class="py-3 px-4">Stok Tersedia</th>
                        <th class="py-3 px-4 w-32">Jumlah</th>
                    </tr>
                </thead>
                <tbody class="text-gray-700 text-sm divide-y divide-gray-200">
                    @forelse($alats as $index => $alat)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="py-3 px-4 text-center">
                                <input type="checkbox" name="alat_id[]" value="{{ $alat->id }}" class="rounded text-blue-600 focus:ring-blue-500">
                            </td>
                            <td class="py-3 px-4 font-medium text-gray-900">{{ $alat->nama_alat }}</td>
                            <td class="py-3 px-4">{{ $alat->kategori->nama_kategori ?? '-' }}</td>
                            <td class="py-3 px-4 font-semibold">{{ $alat->stok }}</td>
                            <td class="py-3 px-4">
                                <input type="number" name="jumlah[]" value="1" min="1" max="{{ $alat->stok }}" 
                                    class="w-full px-2 py-1 border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-blue-500 text-sm">
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-4 text-center text-gray-500">Tidak ada alat yang tersedia.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-gray-200 bg-gray-50">
            {{ $alats->links() }}
        </div>
    </div>
</form>
@endsection