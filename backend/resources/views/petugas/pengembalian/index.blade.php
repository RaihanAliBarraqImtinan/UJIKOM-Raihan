@extends('layouts.petugas')

@section('title', 'Kelola Pengembalian - Petugas')

@section('content')
<h5 class="mb-4 fw-normal text-dark">Daftar Alat yang Harus Dikembalikan</h5>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<div class="card border-0 shadow-sm rounded-3">
    <div class="card-body p-4">
        <form action="{{ route('petugas.pengembalian.index') }}" method="GET" class="mb-4">
            <div class="input-group ms-auto" style="max-width: 300px;">
                <input type="text" name="search" class="form-control bg-light" placeholder="Cari nama peminjam..." value="{{ $search ?? '' }}">
                <button class="btn btn-dark" type="submit">Cari</button>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr class="text-uppercase text-dark fw-bold fs-7 border-bottom">
                        <th scope="col" class="py-3">PEMINJAM</th>
                        <th scope="col" class="py-3">DETAIL ALAT</th>
                        <th scope="col" class="py-3">STATUS</th>
                        <th scope="col" class="py-3 text-center">AKSI PENGEMBALIAN</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($peminjamans as $item)
                        <tr class="border-bottom">
                            <td class="py-3 fw-semibold text-dark">{{ $item->user->name ?? '-' }}</td>
                            <td class="py-3 text-secondary">
                                @foreach($item->detailPinjams as $detail)
                                    <div>- {{ $detail->alat->nama_alat ?? 'Alat' }} ({{ $detail->jumlah }} unit)</div>
                                @endforeach
                            </td>
                            <td class="py-3 text-secondary">{{ $item->status }}</td>
                            <td class="py-3 text-center">
                                <a href="#" class="btn btn-sm btn-success">Proses Kembalikan</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center py-4 text-muted border-0">
                                Tidak ada transaksi peminjaman yang perlu dikembalikan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if(isset($peminjamans) && method_exists($peminjamans, 'links'))
            <div class="d-flex justify-content-end mt-4">
                {{ $peminjamans->links() }}
            </div>
        @endif
    </div>
</div>
@endsection