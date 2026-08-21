@extends('layouts.app')

@section('title', 'Profil Saya')
@section('header-title', 'Profil Pengguna')

@section('content')
<div class="max-w-xl bg-white rounded-lg shadow-sm border border-gray-200 p-6 space-y-4">
    <div>
        <label class="block text-gray-500 text-xs font-bold uppercase tracking-wider">Nama Lengkap</label>
        <div class="text-lg font-bold text-gray-800 mt-1">{{ $user->name }}</div>
    </div>
    <div>
        <label class="block text-gray-500 text-xs font-bold uppercase tracking-wider">Email</label>
        <div class="text-base font-medium text-gray-700 mt-1">{{ $user->email }}</div>
    </div>
    <div>
        <label class="block text-gray-500 text-xs font-bold uppercase tracking-wider">Role</label>
        <span class="inline-block mt-1 px-3 py-1 bg-emerald-100 text-emerald-800 text-xs font-semibold rounded-full uppercase">
            {{ $user->role }}
        </span>
    </div>
</div>
@endsection