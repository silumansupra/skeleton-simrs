@extends('layouts.app')
@section('content')
<div x-data="akunPage()">

    <div class="mb-5">
        <h1 class="text-2xl font-bold text-gray-800">Akun Saya</h1>
        <p class="text-gray-500 text-sm mt-0.5">Kelola informasi profil dan keamanan akun</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

        {{-- Kartu profil --}}
        <div class="bg-white rounded-xl border border-gray-200 p-6 text-center h-fit">
            <div class="w-20 h-20 rounded-full bg-blue-100 flex items-center justify-center text-blue-700 text-2xl font-bold mx-auto mb-3">
                {{ strtoupper(substr($authUser->nama_lengkap, 0, 2)) }}
            </div>
            <h2 class="font-semibold text-gray-800">{{ $authUser->nama_lengkap }}</h2>
            <p class="text-gray-500 text-sm">{{ $authUser->username }}</p>
            <div class="flex flex-wrap justify-center gap-1 mt-2">
                @foreach ($authUser->roles as $role)
                <span class="px-2 py-0.5 bg-blue-50 text-blue-700 text-xs rounded-full border border-blue-100">{{ $role->nama }}</span>
                @endforeach
            </div>
            <div class="mt-4 pt-4 border-t border-gray-100 text-left space-y-2 text-sm text-gray-600">
                <div class="flex items-center gap-2">
                    <i class="ri-id-card-line text-gray-400 w-4"></i>
                    <span>{{ $authUser->nip ?? 'NIP belum diisi' }}</span>
                </div>
                <div class="flex items-center gap-2">
                    <i class="ri-mail-line text-gray-400 w-4"></i>
                    <span>{{ $authUser->email ?? 'Email belum diisi' }}</span>
                </div>
                <div class="flex items-center gap-2">
                    <i class="ri-phone-line text-gray-400 w-4"></i>
                    <span>{{ $authUser->no_hp ?? 'No. HP belum diisi' }}</span>
                </div>
                <div class="flex items-center gap-2">
                    <i class="ri-time-line text-gray-400 w-4"></i>
                    <span>Login: {{ $authUser->last_login_at?->diffForHumans() ?? 'Belum pernah' }}</span>
                </div>
            </div>
        </div>

        {{-- Form --}}
        <div class="lg:col-span-2 space-y-5">

            {{-- Edit Profil --}}
            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100">
                    <h3 class="font-semibold text-gray-800 flex items-center gap-2">
                        <i class="ri-user-settings-line text-blue-500"></i> Edit Profil
                    </h3>
                </div>
                <form @submit.prevent="saveProfil()" class="px-5 py-5 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Nama Lengkap <span class="text-red-500">*</span></label>
                        <input type="text" x-model="profil.nama_lengkap" required
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Email</label>
                            <input type="email" x-model="profil.email"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">No. HP</label>
                            <input type="text" x-model="profil.no_hp"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>
                    <div class="flex justify-end">
                        <button type="submit" :disabled="loadingProfil"
                            class="flex items-center gap-2 bg-blue-700 hover:bg-blue-800 text-white px-5 py-2 rounded-lg text-sm transition-colors disabled:opacity-50">
                            <i class="ri-loader-4-line animate-spin" x-show="loadingProfil"></i>
                            <i class="ri-save-line" x-show="!loadingProfil"></i>
                            <span x-text="loadingProfil ? 'Menyimpan...' : 'Simpan Profil'"></span>
                        </button>
                    </div>
                </form>
            </div>

            {{-- Ganti Password --}}
            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100">
                    <h3 class="font-semibold text-gray-800 flex items-center gap-2">
                        <i class="ri-lock-password-line text-orange-500"></i> Ganti Password
                    </h3>
                </div>
                <form @submit.prevent="gantiPassword()" class="px-5 py-5 space-y-4" x-data="{ show: { lama: false, baru: false, konfirmasi: false } }">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Password Lama <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <input :type="show.lama ? 'text' : 'password'" x-model="pass.password_lama" required
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 pr-10 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <button type="button" @click="show.lama = !show.lama" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                                <i :class="show.lama ? 'ri-eye-off-line' : 'ri-eye-line'"></i>
                            </button>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Password Baru <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <input :type="show.baru ? 'text' : 'password'" x-model="pass.password_baru" required minlength="8"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 pr-10 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                                placeholder="Min. 8 karakter">
                            <button type="button" @click="show.baru = !show.baru" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                                <i :class="show.baru ? 'ri-eye-off-line' : 'ri-eye-line'"></i>
                            </button>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Konfirmasi Password Baru <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <input :type="show.konfirmasi ? 'text' : 'password'" x-model="pass.password_baru_confirmation" required
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 pr-10 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <button type="button" @click="show.konfirmasi = !show.konfirmasi" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                                <i :class="show.konfirmasi ? 'ri-eye-off-line' : 'ri-eye-line'"></i>
                            </button>
                        </div>
                    </div>
                    <div class="flex justify-end">
                        <button type="submit" :disabled="loadingPass"
                            class="flex items-center gap-2 bg-orange-500 hover:bg-orange-600 text-white px-5 py-2 rounded-lg text-sm transition-colors disabled:opacity-50">
                            <i class="ri-loader-4-line animate-spin" x-show="loadingPass"></i>
                            <i class="ri-lock-password-line" x-show="!loadingPass"></i>
                            <span x-text="loadingPass ? 'Menyimpan...' : 'Ganti Password'"></span>
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </div>

</div>
@push('scripts')
<script>
    function akunPage() {
        return {
            loadingProfil: false,
            loadingPass: false,
            profil: {
                nama_lengkap: @json($authUser->nama_lengkap),
                email: @json($authUser->email),
                no_hp: @json($authUser->no_hp),
            },
            pass: {
                password_lama: '',
                password_baru: '',
                password_baru_confirmation: ''
            },

            async saveProfil() {
                this.loadingProfil = true;
                const res = await apiRequest('/pengaturan/akun/profil', 'POST', this.profil);
                this.loadingProfil = false;
                showToast(res.message, res.success ? 'success' : 'error');
            },

            async gantiPassword() {
                if (this.pass.password_baru !== this.pass.password_baru_confirmation) {
                    showToast('Konfirmasi password tidak cocok.', 'error');
                    return;
                }
                this.loadingPass = true;
                const res = await apiRequest('/pengaturan/akun/password', 'POST', this.pass);
                this.loadingPass = false;
                if (res.success) {
                    showToast(res.message, 'success');
                    this.pass = {
                        password_lama: '',
                        password_baru: '',
                        password_baru_confirmation: ''
                    };
                } else {
                    showToast(res.message, 'error');
                }
            },
        }
    }
</script>
@endpush
@endsection