@extends('layouts.auth')
@section('content')
<div class="w-full max-w-sm">
    <div class="bg-white rounded-2xl shadow-2xl overflow-hidden">
        <div class="bg-gradient-to-br from-blue-800 to-blue-900 px-8 py-6 text-center">
            <i class="ri-lock-password-line text-white text-4xl mb-2"></i>
            <h1 class="text-white font-bold text-lg">Lupa Password</h1>
            <p class="text-blue-200 text-sm mt-1">Masukkan email terdaftar Anda</p>
        </div>
        <div class="px-8 py-7">
            @if (session('success'))
                <div class="bg-green-50 border border-green-200 text-green-700 rounded-lg px-4 py-3 text-sm mb-4 flex gap-2">
                    <i class="ri-checkbox-circle-line flex-shrink-0 mt-0.5"></i>
                    <span>{{ session('success') }}</span>
                </div>
            @endif
            @if (session('error'))
                <div class="bg-red-50 border border-red-200 text-red-700 rounded-lg px-4 py-3 text-sm mb-4">
                    {{ session('error') }}
                </div>
            @endif

            <p class="text-sm text-gray-600 mb-5">
                Link reset password akan dikirim ke email Anda jika terdaftar di sistem.
            </p>

            <form method="POST" action="{{ route('forgot-password.post') }}">
                @csrf
                <div class="mb-5">
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Email</label>
                    <div class="relative">
                        <i class="ri-mail-line absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                        <input type="email" name="email" value="{{ old('email') }}" autofocus
                               class="w-full pl-9 pr-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                               placeholder="email@contoh.com">
                    </div>
                    @error('email') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <button type="submit"
                        class="w-full bg-blue-800 hover:bg-blue-900 text-white font-medium py-2.5 rounded-lg text-sm transition-colors flex items-center justify-center gap-2">
                    <i class="ri-send-plane-line"></i> Kirim Link Reset
                </button>
            </form>
            <div class="text-center mt-4">
                <a href="{{ route('login') }}" class="text-sm text-blue-600 hover:underline">
                    <i class="ri-arrow-left-line"></i> Kembali ke Login
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
