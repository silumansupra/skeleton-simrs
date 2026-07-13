@extends('layouts.app')
@section('content')
<div class="flex flex-col items-center justify-center min-h-[60vh] text-center">
    <div class="w-24 h-24 rounded-full bg-red-50 flex items-center justify-center mb-6">
        <i class="ri-forbid-2-line text-red-400 text-5xl"></i>
    </div>
    <h1 class="text-6xl font-bold text-gray-200 mb-2">403</h1>
    <h2 class="text-xl font-semibold text-gray-700 mb-2">Akses Ditolak</h2>
    <p class="text-gray-500 text-sm mb-6 max-w-sm">
        Anda tidak memiliki izin untuk mengakses halaman ini. Hubungi administrator jika ini adalah kesalahan.
    </p>
    <a href="{{ route('dashboard') }}" class="flex items-center gap-2 bg-blue-700 hover:bg-blue-800 text-white px-5 py-2.5 rounded-lg text-sm transition-colors">
        <i class="ri-home-3-line"></i> Kembali ke Dashboard
    </a>
</div>
@endsection