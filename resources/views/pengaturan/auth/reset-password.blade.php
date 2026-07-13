@extends('layouts.auth')
@section('content')
<div class="w-full max-w-sm">
    <div class="bg-white rounded-2xl shadow-2xl overflow-hidden">
        <div class="bg-gradient-to-br from-blue-800 to-blue-900 px-8 py-6 text-center">
            <i class="ri-lock-password-line text-white text-4xl mb-2"></i>
            <h1 class="text-white font-bold text-lg">Reset Password</h1>
            <p class="text-blue-200 text-sm mt-1">Buat password baru untuk akun Anda</p>
        </div>
        <div class="px-8 py-7" x-data="{ show: { baru: false, konfirmasi: false } }">
            @if (session('error'))
            <div class="bg-red-50 border border-red-200 text-red-700 rounded-lg px-4 py-3 text-sm mb-4">
                {{ session('error') }}
            </div>
            @endif
            <form method="POST" action="{{ route('reset-password.post') }}">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Password Baru <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <input :type="show.baru ? 'text' : 'password'" name="password" required minlength="8"
                            class="w-full pl-3 pr-10 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="Min. 8 karakter">
                        <button type="button" @click="show.baru = !show.baru" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400">
                            <i :class="show.baru ? 'ri-eye-off-line' : 'ri-eye-line'"></i>
                        </button>
                    </div>
                    @error('password') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div class="mb-5">
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Konfirmasi Password <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <input :type="show.konfirmasi ? 'text' : 'password'" name="password_confirmation" required
                            class="w-full pl-3 pr-10 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <button type="button" @click="show.konfirmasi = !show.konfirmasi" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400">
                            <i :class="show.konfirmasi ? 'ri-eye-off-line' : 'ri-eye-line'"></i>
                        </button>
                    </div>
                </div>
                <button type="submit" class="w-full bg-blue-800 hover:bg-blue-900 text-white font-medium py-2.5 rounded-lg text-sm transition-colors">
                    <i class="ri-lock-unlock-line mr-1"></i> Reset Password
                </button>
            </form>
        </div>
    </div>
</div>
@endsection