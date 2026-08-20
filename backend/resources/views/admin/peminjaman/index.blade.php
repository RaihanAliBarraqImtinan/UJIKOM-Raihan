@extends('layouts.app')

@section('title', 'Kelola Peminjaman')
@section('header-title', 'Manajemen Transaksi Peminjaman')

@section('content')
<div class="bg-white rounded-xl shadow-md p-6 border border-gray-100">
    
    {{-- Notifikasi --}}
    @if(session('success'))
        <div class="mb-4 p-4 bg-green-100 border-l-4 border-green-500 text-green-700 rounded">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="mb-4 p-4 bg-red-100 border-l-4 border-red-500 text-red-700 rounded">
            {{ session('error') }}
        </div>
    @endif

    {{-- Header & Pencarian --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
        <h2 class="text-xl font-bold text-gray-800">Daftar Transaksi Peminjaman</h2>
        
        <div class="flex flex-col sm:flex-row gap-3">
            <form action="{{ route('admin.peminjaman.index') }}" method="GET" class="flex gap-2">
                <input type="text" name="search" placeholder="Cari nama peminjam / status..." value="{{ $search }}"
                    class="px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none w-full sm:w-64">
                <button type="submit" class="bg-gray-800 hover:bg-gray-900 text-white px-4 py-2 rounded-lg text-sm font-medium transition">
                    Cari
                </button>
            </form>

            <a href="{{ route('admin.peminjaman.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition text-center">
                + Tambah Peminjaman
            </a>
        </div>
    </div>

    {{-- Tabel Peminjaman --}}
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-200 text-xs font-semibold text-gray-600 uppercase tracking-wider">
                    <th class="p-4">Peminjam</th>
                    <th class="p-4">Alat Yang Dipinjam</th>
                    <th class="p-4">Tgl Pinjam / Rencana Kembali</th>
                    <th class="p-4">Status</th>
                    <th class="p-4 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 text-sm">
                @forelse($peminjamans as $item)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="p-4 font-semibold text-gray-800">{{ $item->user->name }}</td>
                        <td class="p-4">
                            <ul class="list-disc list-inside space-y-1">
                                @foreach($item->detailPinjams as $detail)
                                    <li>{{ $detail->alat->nama_alat }} <span class="bg-gray-200 text-gray-700 text-xs px-2 py-0.5 rounded-full font-medium">{{ $detail->jumlah }} pcs</span></li>
                                @endforeach
                            </ul>
                        </td>
                        <td class="p-4 text-gray-600">
                            <div><span class="font-medium text-gray-700">Pinjam:</span> {{ $item->tgl_pinjam }}</div>
                            <div><span class="font-medium text-gray-700">Rencana:</span> {{ $item->tgl_kembali_plan }}</div>
                        </td>
                        <td class="p-4">
                            @if($item->status == 'diajukan')
                                <span class="px-3 py-1 bg-yellow-100 text-yellow-800 text-xs font-semibold rounded-full">Diajukan</span>
                            @elseif($item->status == 'dipinjam')
                                <span class="px-3 py-1 bg-blue-100 text-blue-800 text-xs font-semibold rounded-full">Dipinjam</span>
                            @elseif($item->status == 'telat')
                                <span class="px-3 py-1 bg-red-100 text-red-800 text-xs font-semibold rounded-full">Telat</span>
                            @elseif($item->status == 'selesai')
                                <span class="px-3 py-1 bg-green-100 text-green-800 text-xs font-semibold rounded-full">Selesai</span>
                            @endif
                        </td>
                        <td class="p-4 text-center">
                            {{-- Menggunakan flex-col dan w-32 agar posisi menurun & rapi --}}
                            <div class="flex flex-col items-center justify-center gap-2 mx-auto w-32">
                                {{-- 1. Dropdown Pilihan Status (Atas) --}}
                                <form action="{{ route('admin.peminjaman.update', $item->id) }}" method="POST" class="w-full">
                                    @csrf
                                    @method('PUT')
                                    <select name="status" onchange="this.form.submit()" class="w-full bg-white border border-gray-300 text-gray-700 text-xs font-semibold rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none p-1.5 shadow-sm cursor-pointer text-center">
                                        <option value="diajukan" {{ $item->status == 'diajukan' ? 'selected' : '' }}>Diajukan</option>
                                        <option value="dipinjam" {{ $item->status == 'dipinjam' ? 'selected' : '' }}>Dipinjam</option>
                                        <option value="selesai" {{ $item->status == 'selesai' ? 'selected' : '' }}>Selesai</option>
                                        <option value="telat" {{ $item->status == 'telat' ? 'selected' : '' }}>Telat</option>
                                    </select>
                                </form>

                                {{-- 2. Tombol Hapus (Bawah) --}}
                                <form action="{{ route('admin.peminjaman.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus peminjaman ini?');" class="w-full">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="w-full bg-red-500 hover:bg-red-600 text-white px-3 py-1.5 rounded-lg text-xs font-semibold transition">
                                        Hapus
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="p-6 text-center text-gray-500">Tidak ada data peminjaman.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    <div class="mt-4">
        {{ $peminjamans->links() }}
    </div>
</div>
@endsection