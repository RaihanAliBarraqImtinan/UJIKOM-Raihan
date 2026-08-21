@extends('layouts.petugas')

@section('title', 'Kelola Peminjaman - Petugas')

@section('content')
<h5 class="mb-4 fw-normal text-dark">Daftar Pengajuan Peminjaman Alat</h5>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<div class="card border-0 shadow-sm rounded-3">
    <div class="card-body p-4">
        <form action="{{ route('petugas.peminjaman.index') }}" method="GET" class="mb-4">
            <div class="input-group" style="max-width: 300px;">
                <input type="text" name="search" class="form-control bg-light" placeholder="Cari peminjam..." value="{{ $search }}">
                <button class="btn btn-outline-secondary" type="submit">Cari</button>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr class="text-uppercase text-dark fw-bold fs-7 border-bottom">
                        <th scope="col" class="py-3">NO</th>
                        <th scope="col" class="py-3">NAMA PEMINJAM</th>
                        <th scope="col" class="py-3">TANGGAL PINJAM</th>
                        <th scope="col" class="py-3">STATUS</th>
                        <th scope="col" class="py-3 text-center">AKSI</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($peminjamans as $index => $item)
                        <tr class="border-bottom">
                            <td class="py-3 text-secondary">{{ $peminjamans->firstItem() + $index }}</td>
                            <td class="py-3 fw-semibold text-dark">{{ $item->user->name ?? '-' }}</td>
                            <td class="py-3 text-secondary">{{ \Carbon\Carbon::parse($item->created_at)->format('Y-m-d H:i:s') }}</td>
                            <td class="py-3 text-secondary">{{ $item->status }}</td>
                            <td class="py-3 text-center">
                                <a href="#" class="text-decoration-none fw-semibold">Detail</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted border-0">
                                Belum ada data peminjaman alat.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="d-flex justify-content-end mt-4">
            {{ $peminjamans->links() }}
        </div>
    </div>
</div>
@endsection