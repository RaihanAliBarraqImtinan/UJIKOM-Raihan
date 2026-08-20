@extends('layouts.app')

@section('title', 'Kelola Pengembalian')
@section('header-title', 'Manajemen Transaksi Pengembalian')

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
        <h2 class="text-xl font-bold text-gray-800">Daftar Peminjaman Aktif (Pengembalian)</h2>
        
        <form action="{{ route('admin.pengembalian.index') }}" method="GET" class="flex gap-2">
            <input type="text" name="search" placeholder="Cari nama peminjam..." value="{{ $search }}"
                class="px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none w-full sm:w-64">
            <button type="submit" class="bg-gray-800 hover:bg-gray-900 text-white px-4 py-2 rounded-lg text-sm font-medium transition">
                Cari
            </button>
        </form>
    </div>

    {{-- Tabel Pengembalian --}}
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-200 text-xs font-semibold text-gray-600 uppercase tracking-wider">
                    <th class="p-4">Peminjam</th>
                    <th class="p-4">Alat Yang Dipinjam</th>
                    <th class="p-4">Tgl Pinjam / Rencana Kembali</th>
                    <th class="p-4">Keterlambatan (Otomatis)</th>
                    <th class="p-4 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 text-sm">
                @forelse($peminjamans as $item)
                    @php
                        $tglKembali = \Carbon\Carbon::parse($item->tgl_kembali_plan)->startOfDay();
                        $hariIni = \Carbon\Carbon::now()->startOfDay();
                        $hariTelat = $hariIni->greaterThan($tglKembali) ? $tglKembali->diffInDays($hariIni) : 0;
                        $dendaTelat = $hariTelat * 1000; // Contoh denda telat Rp 1.000 / hari
                    @endphp
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
                            @if($hariTelat > 0)
                                <span class="px-3 py-1 bg-red-100 text-red-800 text-xs font-semibold rounded-full">{{ $hariTelat }} Hari Telat</span>
                                <div class="text-xs text-red-600 font-medium mt-1">Denda Telat: Rp {{ number_format($dendaTelat, 0, ',', '.') }}</div>
                            @else
                                <span class="px-3 py-1 bg-green-100 text-green-800 text-xs font-semibold rounded-full">Tepat Waktu</span>
                            @endif
                        </td>
                        <td class="p-4 text-center">
                            <button onclick="openModal('modalKembali-{{ $item->id }}')" class="bg-emerald-600 hover:bg-emerald-700 text-white px-3 py-1.5 rounded-lg text-xs font-semibold transition">
                                ✓ Process Kembali
                            </button>

                            {{-- Modal Process Kembali --}}
                            <div id="modalKembali-{{ $item->id }}" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50 p-4">
                                <div class="bg-white rounded-xl shadow-lg max-w-md w-full p-6 text-left">
                                    <div class="flex justify-between items-center border-b pb-3 mb-4">
                                        <h3 class="text-lg font-bold text-gray-800">Proses Pengembalian #{{ $item->id }}</h3>
                                        <button type="button" onclick="closeModal('modalKembali-{{ $item->id }}')" class="text-gray-400 hover:text-gray-600 font-bold">&times;</button>
                                    </div>

                                    <form action="{{ route('admin.pengembalian.proses', $item->id) }}" method="POST">
                                        @csrf
                                        <div class="mb-4">
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Dikembalikan</label>
                                            <input type="text" class="w-full px-3 py-2 border rounded-lg bg-gray-100 text-sm" value="{{ \Carbon\Carbon::now()->format('Y-m-d H:i') }}" readonly>
                                        </div>

                                        <div class="mb-4">
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Kondisi Alat Saat Dikembalikan</label>
                                            <select name="kondisi_kembali" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none" required>
                                                <option value="Baik">Baik</option>
                                                <option value="Rusak">Rusak</option>
                                                <option value="Hilang">Hilang</option>
                                            </select>
                                        </div>

                                        {{-- Input Denda Kerusakan (Manual Ketik) --}}
                                        <div class="mb-4">
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Denda Kerusakan / Hilang (Rp)</label>
                                            <input type="number" id="denda_kondisi_{{ $item->id }}" min="0" value="0" placeholder="Ketik nominal denda..."
                                                oninput="hitungTotalDenda({{ $item->id }}, {{ $dendaTelat }})"
                                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                                            <p class="text-xs text-gray-500 mt-1">*Ketik nominal denda jika alat rusak/hilang (isi 0 jika kondisi baik).</p>
                                        </div>

                                        {{-- Rincian & Total Denda --}}
                                        <div class="p-4 bg-gray-50 rounded-lg border border-gray-200 mb-4 text-sm space-y-2">
                                            <div class="flex justify-between">
                                                <span class="text-gray-600">Denda Telat ({{ $hariTelat }} hari):</span>
                                                <span class="font-semibold text-gray-800">Rp {{ number_format($dendaTelat, 0, ',', '.') }}</span>
                                            </div>
                                            <div class="flex justify-between">
                                                <span class="text-gray-600">Denda Kerusakan:</span>
                                                <span id="text_denda_kondisi_{{ $item->id }}" class="font-semibold text-gray-800">Rp 0</span>
                                            </div>
                                            <hr>
                                            <div class="flex justify-between text-base font-bold text-blue-600">
                                                <span>Total Denda Keseluruhan:</span>
                                                <span id="text_total_denda_{{ $item->id }}">Rp {{ number_format($dendaTelat, 0, ',', '.') }}</span>
                                            </div>
                                        </div>

                                        {{-- Input Hidden Nilai Total Denda yang dikirim ke Backend --}}
                                        <input type="hidden" name="denda" id="input_total_denda_{{ $item->id }}" value="{{ $dendaTelat }}">

                                        <div class="flex justify-end gap-2 pt-2">
                                            <button type="button" onclick="closeModal('modalKembali-{{ $item->id }}')" class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 text-xs font-semibold rounded-lg transition">
                                                Batal
                                            </button>
                                            <button type="submit" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-lg transition">
                                                Simpan Pengembalian
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="p-6 text-center text-gray-500">Tidak ada peminjaman aktif yang perlu dikembalikan.</td>
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

<script>
function openModal(id) {
    document.getElementById(id).classList.remove('hidden');
    document.getElementById(id).classList.add('flex');
}

function closeModal(id) {
    document.getElementById(id).classList.add('hidden');
    document.getElementById(id).classList.remove('flex');
}

function hitungTotalDenda(id, dendaTelat) {
    const inputDendaKondisi = parseFloat(document.getElementById(`denda_kondisi_${id}`).value) || 0;
    const totalDenda = dendaTelat + inputDendaKondisi;

    // Update tampilan ringkasan
    document.getElementById(`text_denda_kondisi_${id}`).innerText = 'Rp ' + inputDendaKondisi.toLocaleString('id-ID');
    document.getElementById(`text_total_denda_${id}`).innerText = 'Rp ' + totalDenda.toLocaleString('id-ID');
    
    // Update value hidden input yang dikirim ke database
    document.getElementById(`input_total_denda_${id}`).value = totalDenda;
}
</script>
@endsection