@extends('layouts.petugas')

@section('title', 'Cetak Laporan Peminjaman')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h5 class="fw-normal text-dark m-0">Laporan Transaksi Peminjaman</h5>
</div>

<!-- Filter Tanggal -->
<div class="card border-0 shadow-sm rounded-3 mb-4">
    <div class="card-body p-3">
        <form action="{{ route('petugas.laporan.index') }}" method="GET" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label small fw-bold text-secondary">Dari Tanggal</label>
                <input type="date" name="start_date" class="form-control bg-light" value="{{ $startDate }}">
            </div>
            <div class="col-md-4">
                <label class="form-label small fw-bold text-secondary">Sampai Tanggal</label>
                <input type="date" name="end_date" class="form-control bg-light" value="{{ $endDate }}">
            </div>
            <div class="col-md-4 d-flex gap-2">
                <button type="submit" class="btn btn-dark w-100">Filter</button>
                <a href="{{ route('petugas.laporan.index') }}" class="btn btn-outline-secondary w-100">Reset</a>
            </div>
        </form>
    </div>
</div>

<!-- Table Data Laporan -->
<div class="card border-0 shadow-sm rounded-3">
    <div class="card-body p-4">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr class="text-uppercase text-dark fw-bold fs-7 border-bottom">
                        <th scope="col" class="py-3">NO</th>
                        <th scope="col" class="py-3">PEMINJAM</th>
                        <th scope="col" class="py-3">DETAIL ALAT</th>
                        <th scope="col" class="py-3">TANGGAL PINJAM</th>
                        <th scope="col" class="py-3 text-center">STATUS</th>
                        <th scope="col" class="py-3 text-center">AKSI</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($peminjamans as $index => $item)
                        <tr class="border-bottom">
                            <td class="py-3 fw-semibold text-dark">{{ $index + 1 }}</td>
                            <td class="py-3 fw-semibold text-dark">{{ $item->user->name ?? '-' }}</td>
                            <td class="py-3 text-secondary">
                                @foreach($item->detailPinjams as $detail)
                                    <div>- {{ $detail->alat->nama_alat ?? 'Alat' }} ({{ $detail->jumlah }} unit)</div>
                                @endforeach
                            </td>
                            <td class="py-3 text-secondary">{{ \Carbon\Carbon::parse($item->created_at)->format('d-m-Y H:i') }}</td>
                            <td class="py-3 text-center">
                                <span class="badge bg-secondary">{{ ucfirst($item->status) }}</span>
                            </td>
                            <td class="py-3 text-center">
                                <a href="{{ route('petugas.laporan.nota', $item->id) }}" target="_blank" class="btn btn-sm btn-dark">
                                    Cetak Bon PDF
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted border-0">
                                Tidak ada data transaksi peminjaman.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection