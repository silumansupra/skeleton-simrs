@extends('layouts.app')
@section('content')
<div x-data="userPage()" x-init="init()">

{{-- Header --}}
<div class="flex items-center justify-between mb-5">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Manajemen Pengguna</h1>
        <p class="text-gray-500 text-sm mt-0.5">Total {{ $users->total() }} pengguna terdaftar</p>
    </div>
    @if ($perm >= 3)
    <button @click="openModal()" class="flex items-center gap-2 bg-blue-700 hover:bg-blue-800 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
        <i class="ri-user-add-line"></i> Tambah Pengguna
    </button>
    @endif
</div>

{{-- Filter --}}
<div class="bg-white rounded-xl border border-gray-200 p-4 mb-4 flex flex-wrap gap-3">
    <form method="GET" class="flex flex-wrap gap-3 flex-1">
        <div class="relative flex-1 min-w-48">
            <i class="ri-search-line absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
            <input type="text" name="keyword" value="{{ $filter['keyword'] ?? '' }}"
                   placeholder="Cari nama, username, NIP..."
                   class="w-full pl-8 pr-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
        <select name="is_active" class="border border-gray-300 rounded-lg text-sm px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">Semua Status</option>
            <option value="1" {{ ($filter['is_active'] ?? '') === '1' ? 'selected' : '' }}>Aktif</option>
            <option value="0" {{ ($filter['is_active'] ?? '') === '0' ? 'selected' : '' }}>Nonaktif</option>
        </select>
        <button type="submit" class="bg-blue-700 text-white px-4 py-2 rounded-lg text-sm hover:bg-blue-800 transition-colors">
            <i class="ri-search-line mr-1"></i> Cari
        </button>
        @if (!empty(array_filter($filter)))
            <a href="{{ route('pengaturan.user.index') }}" class="border border-gray-300 px-4 py-2 rounded-lg text-sm hover:bg-gray-50 text-gray-600 transition-colors">
                <i class="ri-refresh-line mr-1"></i> Reset
            </a>
        @endif
    </form>
</div>

{{-- Table --}}
<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">Pengguna</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-600 hidden md:table-cell">NIP</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-600 hidden lg:table-cell">Role</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-600 hidden lg:table-cell">Login Terakhir</th>
                    <th class="text-center px-4 py-3 font-medium text-gray-600">Status</th>
                    <th class="text-center px-4 py-3 font-medium text-gray-600">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($users as $user)
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-full bg-blue-100 flex items-center justify-center text-blue-700 text-xs font-semibold flex-shrink-0">
                                {{ strtoupper(substr($user->nama_lengkap, 0, 2)) }}
                            </div>
                            <div>
                                <p class="font-medium text-gray-800">{{ $user->nama_lengkap }}</p>
                                <p class="text-gray-400 text-xs">{{ $user->username }} @if($user->email) · {{ $user->email }} @endif</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-4 py-3 text-gray-500 hidden md:table-cell">{{ $user->nip ?? '—' }}</td>
                    <td class="px-4 py-3 hidden lg:table-cell">
                        <div class="flex flex-wrap gap-1">
                            @forelse ($user->roles as $role)
                                <span class="px-2 py-0.5 bg-blue-50 text-blue-700 text-xs rounded-full border border-blue-100">{{ $role->nama }}</span>
                            @empty
                                <span class="text-gray-400 text-xs">—</span>
                            @endforelse
                        </div>
                    </td>
                    <td class="px-4 py-3 text-gray-500 text-xs hidden lg:table-cell">
                        {{ $user->last_login_at ? $user->last_login_at->diffForHumans() : 'Belum pernah' }}
                    </td>
                    <td class="px-4 py-3 text-center">
                        @if ($user->isLocked())
                            <span class="px-2 py-1 bg-red-50 text-red-600 text-xs rounded-full border border-red-100">
                                <i class="ri-lock-line"></i> Terkunci
                            </span>
                        @elseif ($user->is_active)
                            <span class="px-2 py-1 bg-green-50 text-green-700 text-xs rounded-full border border-green-100">
                                <i class="ri-checkbox-circle-line"></i> Aktif
                            </span>
                        @else
                            <span class="px-2 py-1 bg-gray-100 text-gray-500 text-xs rounded-full">
                                <i class="ri-forbid-line"></i> Nonaktif
                            </span>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex items-center justify-center gap-1">
                            @if ($perm >= 3)
                            <button @click="openModal({{ $user->id }})"
                                    class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors" title="Edit">
                                <i class="ri-pencil-line"></i>
                            </button>
                            <button @click="toggleActive({{ $user->id }}, '{{ $user->nama_lengkap }}', {{ $user->is_active ? 'true' : 'false' }})"
                                    class="p-1.5 {{ $user->is_active ? 'text-orange-500 hover:bg-orange-50' : 'text-green-600 hover:bg-green-50' }} rounded-lg transition-colors"
                                    title="{{ $user->is_active ? 'Nonaktifkan' : 'Aktifkan' }}">
                                <i class="{{ $user->is_active ? 'ri-toggle-fill' : 'ri-toggle-line' }}"></i>
                            </button>
                            @if ($user->isLocked())
                            <button @click="resetLock({{ $user->id }}, '{{ $user->nama_lengkap }}')"
                                    class="p-1.5 text-yellow-600 hover:bg-yellow-50 rounded-lg transition-colors" title="Reset Lock">
                                <i class="ri-lock-unlock-line"></i>
                            </button>
                            @endif
                            @endif
                            @if ($perm >= 7)
                            <button @click="deleteUser({{ $user->id }}, '{{ $user->nama_lengkap }}')"
                                    class="p-1.5 text-red-500 hover:bg-red-50 rounded-lg transition-colors" title="Hapus">
                                <i class="ri-delete-bin-line"></i>
                            </button>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-4 py-10 text-center text-gray-400">
                        <i class="ri-group-line text-4xl block mb-2 opacity-30"></i>
                        Tidak ada pengguna ditemukan
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{-- Pagination --}}
    @if ($users->hasPages())
    <div class="px-4 py-3 border-t border-gray-100">
        {{ $users->links('vendor.pagination.tailwind') }}
    </div>
    @endif
</div>

{{-- ── MODAL FORM USER ─────────────────────────────────── --}}
<div x-show="modalOpen" x-cloak class="fixed inset-0 z-[9990] flex items-center justify-center p-4" @keydown.escape.window="closeModal()">
    <div class="absolute inset-0 bg-black/50" @click="closeModal()"></div>
    <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-xl max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
            <h3 class="font-semibold text-gray-800" x-text="editId ? 'Edit Pengguna' : 'Tambah Pengguna'"></h3>
            <button @click="closeModal()" class="text-gray-400 hover:text-gray-600"><i class="ri-close-line text-xl"></i></button>
        </div>
        <form @submit.prevent="submitForm()" class="px-6 py-5 space-y-4">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">NIP</label>
                    <input type="text" x-model="form.nip" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Opsional">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Username <span class="text-red-500">*</span></label>
                    <input type="text" x-model="form.username" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Nama Lengkap <span class="text-red-500">*</span></label>
                <input type="text" x-model="form.nama_lengkap" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Email</label>
                    <input type="email" x-model="form.email" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">No. HP</label>
                    <input type="text" x-model="form.no_hp" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">
                    Password <span x-show="!editId" class="text-red-500">*</span>
                    <span x-show="editId" class="text-gray-400 font-normal">(kosongkan jika tidak diubah)</span>
                </label>
                <input type="password" x-model="form.password" :required="!editId" minlength="8"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                       placeholder="Min. 8 karakter">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Role</label>
                <div class="grid grid-cols-2 gap-2">
                    @foreach ($roles as $role)
                    <label class="flex items-center gap-2 p-2 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50">
                        <input type="checkbox" :value="{{ $role->id }}" x-model="form.role_ids" class="rounded border-gray-300 text-blue-600">
                        <span class="text-sm text-gray-700">{{ $role->nama }}</span>
                    </label>
                    @endforeach
                </div>
            </div>
            <div class="flex items-center gap-2">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" x-model="form.is_active" class="rounded border-gray-300 text-blue-600">
                    <span class="text-sm text-gray-700">Pengguna aktif</span>
                </label>
            </div>
            <div class="flex justify-end gap-3 pt-2 border-t border-gray-100">
                <button type="button" @click="closeModal()" class="px-4 py-2 text-sm border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">Batal</button>
                <button type="submit" :disabled="loading"
                        class="px-5 py-2 text-sm bg-blue-700 hover:bg-blue-800 text-white rounded-lg transition-colors disabled:opacity-50 flex items-center gap-2">
                    <i class="ri-save-line" x-show="!loading"></i>
                    <i class="ri-loader-4-line animate-spin" x-show="loading"></i>
                    <span x-text="loading ? 'Menyimpan...' : 'Simpan'"></span>
                </button>
            </div>
        </form>
    </div>
</div>

</div><!-- x-data -->

@push('scripts')
<script>
function userPage() {
    return {
        modalOpen: false,
        editId: null,
        loading: false,
        form: { nip:'', username:'', nama_lengkap:'', email:'', no_hp:'', password:'', role_ids:[], is_active: true },

        init() {},

        openModal(id = null) {
            this.editId = id;
            this.form   = { nip:'', username:'', nama_lengkap:'', email:'', no_hp:'', password:'', role_ids:[], is_active: true };
            if (id) {
                fetch(`/pengaturan/user/${id}`, { headers: { 'Accept': 'application/json' } })
                    .then(r => r.json()).then(res => {
                        if (res.success) {
                            const u = res.data.user;
                            this.form = { nip: u.nip||'', username: u.username, nama_lengkap: u.nama_lengkap,
                                email: u.email||'', no_hp: u.no_hp||'', password: '',
                                role_ids: u.roles.map(r => r.id), is_active: u.is_active };
                        }
                    });
            }
            this.modalOpen = true;
        },

        closeModal() { this.modalOpen = false; },

        async submitForm() {
            this.loading = true;
            const url    = this.editId ? `/pengaturan/user/${this.editId}` : '/pengaturan/user';
            const method = this.editId ? 'PUT' : 'POST';
            const res    = await apiRequest(url, method, this.form);
            this.loading = false;
            if (res.success) {
                showToast(res.message, 'success');
                this.closeModal();
                setTimeout(() => location.reload(), 800);
            } else {
                showToast(res.message || 'Terjadi kesalahan.', 'error');
            }
        },

        async toggleActive(id, nama, isActive) {
            const confirmed = await showConfirm({
                title: isActive ? 'Nonaktifkan Pengguna' : 'Aktifkan Pengguna',
                message: `${isActive ? 'Nonaktifkan' : 'Aktifkan'} pengguna "${nama}"?`,
                type: isActive ? 'warning' : 'info',
                confirmText: isActive ? 'Ya, Nonaktifkan' : 'Ya, Aktifkan',
            });
            if (!confirmed) return;
            const res = await apiRequest(`/pengaturan/user/${id}/toggle-active`, 'PATCH');
            if (res.success) { showToast(res.message, 'success'); setTimeout(() => location.reload(), 800); }
            else showToast(res.message, 'error');
        },

        async resetLock(id, nama) {
            const confirmed = await showConfirm({ title: 'Reset Lock', message: `Reset lock akun "${nama}"?`, type: 'warning', confirmText: 'Ya, Reset' });
            if (!confirmed) return;
            const res = await apiRequest(`/pengaturan/user/${id}/reset-lock`, 'PATCH');
            if (res.success) { showToast(res.message, 'success'); setTimeout(() => location.reload(), 800); }
            else showToast(res.message, 'error');
        },

        async deleteUser(id, nama) {
            const confirmed = await showConfirm({ title: 'Hapus Pengguna', message: `Hapus pengguna "${nama}"? Tindakan ini tidak dapat dibatalkan.`, confirmText: 'Ya, Hapus' });
            if (!confirmed) return;
            const res = await apiRequest(`/pengaturan/user/${id}`, 'DELETE');
            if (res.success) { showToast(res.message, 'success'); setTimeout(() => location.reload(), 800); }
            else showToast(res.message, 'error');
        },
    }
}
</script>
@endpush
@endsection
