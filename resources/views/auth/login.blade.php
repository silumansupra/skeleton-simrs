@extends('layouts.auth')
@section('content')
<div class="w-full max-w-sm">
    {{-- Card --}}
    <div class="bg-white rounded-2xl shadow-2xl overflow-hidden">
        {{-- Header --}}
        <div class="bg-gradient-to-br from-blue-800 to-blue-900 px-8 py-8 text-center">
            <div class="w-16 h-16 rounded-2xl bg-white/20 flex items-center justify-center mx-auto mb-4">
                <i class="ri-hospital-line text-white text-3xl"></i>
            </div>
            <h1 class="text-white text-xl font-bold">SIMRS</h1>
            <p class="text-blue-200 text-sm mt-1">RSU Fastabiq Sehat PKU Muhammadiyah</p>
        </div>

        {{-- Form --}}
        <div class="px-8 py-7" x-data="{ showPass: false }">
            <h2 class="text-gray-800 font-semibold text-lg mb-5">Masuk ke sistem</h2>

            @if (session('error'))
                <div class="bg-red-50 border border-red-200 text-red-700 rounded-lg px-4 py-3 text-sm mb-4 flex items-start gap-2">
                    <i class="ri-error-warning-line text-red-500 mt-0.5 flex-shrink-0"></i>
                    <span>{{ session('error') }}</span>
                </div>
            @endif
            @if (session('success'))
                <div class="bg-green-50 border border-green-200 text-green-700 rounded-lg px-4 py-3 text-sm mb-4 flex items-start gap-2">
                    <i class="ri-checkbox-circle-line text-green-500 mt-0.5 flex-shrink-0"></i>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            <form method="POST" action="{{ route('login.post') }}">
                @csrf
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Username</label>
                    <div class="relative">
                        <i class="ri-user-line absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                        <input type="text" name="username" value="{{ old('username') }}" autofocus
                               class="w-full pl-9 pr-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('username') border-red-400 @enderror"
                               placeholder="Masukkan username">
                    </div>
                    @error('username') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="mb-5">
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Password</label>
                    <div class="relative">
                        <i class="ri-lock-line absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                        <input :type="showPass ? 'text' : 'password'" name="password"
                               class="w-full pl-9 pr-10 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('password') border-red-400 @enderror"
                               placeholder="Masukkan password">
                        <button type="button" @click="showPass = !showPass"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                            <i :class="showPass ? 'ri-eye-off-line' : 'ri-eye-line'"></i>
                        </button>
                    </div>
                    @error('password') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="flex items-center justify-between mb-5">
                    <label class="flex items-center gap-2 text-sm text-gray-600 cursor-pointer">
                        <input type="checkbox" name="remember" class="rounded border-gray-300 text-blue-600">
                        Ingat saya
                    </label>
                    <a href="{{ route('forgot-password') }}" class="text-sm text-blue-600 hover:text-blue-800 hover:underline">
                        Lupa password?
                    </a>
                </div>

                <button type="submit"
                        class="w-full bg-blue-800 hover:bg-blue-900 text-white font-medium py-2.5 rounded-lg text-sm transition-colors flex items-center justify-center gap-2">
                    <i class="ri-login-box-line"></i> Masuk
                </button>
            </form>
        </div>
    </div>

    <p class="text-center text-blue-300 text-xs mt-6">
        &copy; {{ date('Y') }} RSU Fastabiq Sehat &mdash; Sistem Informasi Manajemen Akuntansi &amp; Keuangan
    </p>
</div>
@endsection
