@extends('layouts.app')

@section('title', 'Dashboard Peminjam')
@section('header-title', 'Dashboard Peminjam')

@section('content')
<div class="mb-6 bg-blue-50 border border-blue-200 text-blue-800 p-4 rounded-lg shadow-sm">
    Selamat datang kembali, <strong class="font-semibold">{{ auth()->user()->name }}</strong>!
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
        <div class="text-xs font-bold text-gray-500 uppercase tracking-wider">Total Peminjaman</div>
        <div class="text-3xl font-extrabold text-gray-800 mt-2">{{ $totalPinjam }}</div>
    </div>
    <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
        <div class="text-xs font-bold text-gray-500 uppercase tracking-wider">Sedang Dipinjam</div>
        <div class="text-3xl font-extrabold text-blue-600 mt-2">{{ $sedangDipinjam }}</div>
    </div>
    <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
        <div class="text-xs font-bold text-gray-500 uppercase tracking-wider">Selesai</div>
        <div class="text-3xl font-extrabold text-emerald-600 mt-2">{{ $selesai }}</div>
    </div>
</div>
@endsection