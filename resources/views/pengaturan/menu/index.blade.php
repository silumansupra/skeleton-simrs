@extends('layouts.app')
@section('content')
<div x-data="menuPage()">

    <div class="flex items-center justify-between mb-5">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Menu & Submenu</h1>
            <p class="text-gray-500 text-sm mt-0.5">Total {{ $menus->count() }} menu terdaftar</p>
        </div>
        <button @click="openModal()" class="flex items-center gap-2 bg-blue-700 hover:bg-blue-800 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
            <i class="ri-add-line"></i> Tambah Menu
        </button>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="text-left px-4 py-3 font-medium text-gray-600">Label</th>
                        <th class="text-left px-4 py-3 font-medium text-gray-600">Kode</th>
                        <th class="text-left px-4 py-3 font-medium text-gray-600 hidden md:table-cell">URL</th>
                        <th class="text-left px-4 py-3 font-medium text-gray-600 hidden lg:table-cell">Parent</th>
                        <th class="text-center px-4 py-3 font-medium text-gray-600">Urutan</th>
                        <th class="text-center px-4 py-3 font-medium text-gray-600">Status</th>
                        <th class="text-center px-4 py-3 font-medium text-gray-600">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($menus as $menu)
                    <tr class="hover:bg-gray-50 transition-colors {{ is_null($menu->parent_id) ? 'bg-gray-50/50' : '' }}">
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-2">
                                @if (!is_null($menu->parent_id))
                                <span class="text-gray-300 ml-3">└</span>
                                @endif
                                <i class="{{ $menu->icon ?? 'ri-circle-line' }} text-blue-500"></i>
                                <span class="{{ is_null($menu->parent_id) ? 'font-medium text-gray-800' : 'text-gray-600' }}">
                                    {{ $menu->label }}
                                </span>
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <code class="text-xs bg-gray-100 text-gray-600 px-2 py-0.5 rounded">{{ $menu->kode }}</code>
                        </td>
                        <td class="px-4 py-3 text-gray-500 text-xs hidden md:table-cell">{{ $menu->url ?? '—' }}</td>
                        <td class="px-4 py-3 text-gray-500 hidden lg:table-cell">{{ $menu->parent_label ?? '—' }}</td>
                        <td class="px-4 py-3 text-center text-gray-500">{{ $menu->urutan }}</td>
                        <td class="px-4 py-3 text-center">
                            @if ($menu->is_active)
                            <span class="px-2 py-0.5 bg-green-50 text-green-700 text-xs rounded-full border border-green-100">Aktif</span>
                            @else
                            <span class="px-2 py-0.5 bg-gray-100 text-gray-500 text-xs rounded-full">Nonaktif</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-center gap-1">
                                <button @click="openModal({{ $menu->id }})" class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors" title="Edit">
                                    <i class="ri-pencil-line"></i>
                                </button>
                                <button @click="toggleMenu({{ $menu->id }}, '{{ $menu->label }}', {{ $menu->is_active ? 'true' : 'false' }})"
                                    class="p-1.5 {{ $menu->is_active ? 'text-orange-500 hover:bg-orange-50' : 'text-green-600 hover:bg-green-50' }} rounded-lg transition-colors"
                                    title="{{ $menu->is_active ? 'Nonaktifkan' : 'Aktifkan' }}">
                                    <i class="{{ $menu->is_active ? 'ri-toggle-fill' : 'ri-toggle-line' }}"></i>
                                </button>
                                <button @click="deleteMenu({{ $menu->id }}, '{{ $menu->label }}')"
                                    class="p-1.5 text-red-500 hover:bg-red-50 rounded-lg transition-colors" title="Hapus">
                                    <i class="ri-delete-bin-line"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-4 py-10 text-center text-gray-400">
                            <i class="ri-menu-line text-4xl block mb-2 opacity-30"></i>
                            Belum ada menu
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Modal Form --}}
    <div x-show="modalOpen" x-cloak class="fixed inset-0 z-[9990] flex items-center justify-center p-4" @keydown.escape.window="closeModal()">
        <div class="absolute inset-0 bg-black/50" @click="closeModal()"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-lg">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                <h3 class="font-semibold text-gray-800" x-text="editId ? 'Edit Menu' : 'Tambah Menu'"></h3>
                <button @click="closeModal()" class="text-gray-400 hover:text-gray-600"><i class="ri-close-line text-xl"></i></button>
            </div>
            <form @submit.prevent="submitForm()" class="px-6 py-5 space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Label <span class="text-red-500">*</span></label>
                        <input type="text" x-model="form.label" required
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="contoh: Dashboard">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Kode <span class="text-red-500">*</span></label>
                        <input type="text" x-model="form.kode" required
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="contoh: dashboard">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Parent Menu</label>
                    <select x-model="form.parent_id" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">— Tidak ada (menu utama) —</option>
                        @foreach ($parents as $parent)
                        <option value="{{ $parent->id }}">{{ $parent->label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">URL</label>
                        <input type="text" x-model="form.url"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="/dashboard">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Icon</label>
                        <input type="text" x-model="form.icon"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="ri-dashboard-line">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Urutan</label>
                    <input type="number" x-model="form.urutan" min="0"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="flex items-center gap-2">
                    <input type="checkbox" x-model="form.is_active" id="is_active_menu" class="rounded border-gray-300 text-blue-600">
                    <label for="is_active_menu" class="text-sm text-gray-700 cursor-pointer">Menu aktif</label>
                </div>
                <div class="flex justify-end gap-3 pt-2 border-t border-gray-100">
                    <button type="button" @click="closeModal()" class="px-4 py-2 text-sm border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">Batal</button>
                    <button type="submit" :disabled="loading"
                        class="px-5 py-2 text-sm bg-blue-700 hover:bg-blue-800 text-white rounded-lg transition-colors disabled:opacity-50 flex items-center gap-2">
                        <i class="ri-loader-4-line animate-spin" x-show="loading"></i>
                        <i class="ri-save-line" x-show="!loading"></i>
                        <span x-text="loading ? 'Menyimpan...' : 'Simpan'"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@push('scripts')
<script>
    function menuPage() {
        return {
            modalOpen: false,
            editId: null,
            loading: false,
            form: {
                label: '',
                kode: '',
                parent_id: '',
                url: '',
                icon: '',
                urutan: 0,
                is_active: true
            },

            openModal(id = null) {
                this.editId = id;
                this.form = {
                    label: '',
                    kode: '',
                    parent_id: '',
                    url: '',
                    icon: '',
                    urutan: 0,
                    is_active: true
                };
                if (id) {
                    fetch(`/pengaturan/menu/${id}`, {
                            headers: {
                                'Accept': 'application/json'
                            }
                        })
                        .then(r => r.json()).then(res => {
                            if (res.success) {
                                const m = res.data.menu;
                                this.form = {
                                    label: m.label,
                                    kode: m.kode,
                                    parent_id: m.parent_id ?? '',
                                    url: m.url ?? '',
                                    icon: m.icon ?? '',
                                    urutan: m.urutan,
                                    is_active: !!m.is_active,
                                };
                            }
                        });
                }
                this.modalOpen = true;
            },
            closeModal() {
                this.modalOpen = false;
            },

            async submitForm() {
                this.loading = true;
                const url = this.editId ? `/pengaturan/menu/${this.editId}` : '/pengaturan/menu';
                const method = this.editId ? 'PUT' : 'POST';
                const res = await apiRequest(url, method, this.form);
                this.loading = false;
                if (res.success) {
                    showToast(res.message, 'success');
                    this.closeModal();
                    setTimeout(() => location.reload(), 800);
                } else showToast(res.message || 'Terjadi kesalahan.', 'error');
            },

            async toggleMenu(id, label, isActive) {
                const confirmed = await showConfirm({
                    title: isActive ? 'Nonaktifkan Menu' : 'Aktifkan Menu',
                    message: `${isActive ? 'Nonaktifkan' : 'Aktifkan'} menu "${label}"?`,
                    type: 'warning',
                    confirmText: 'Ya, Lanjutkan',
                });
                if (!confirmed) return;
                const res = await apiRequest(`/pengaturan/menu/${id}/toggle`, 'PATCH');
                if (res.success) {
                    showToast(res.message, 'success');
                    setTimeout(() => location.reload(), 800);
                } else showToast(res.message, 'error');
            },

            async deleteMenu(id, label) {
                const confirmed = await showConfirm({
                    title: 'Hapus Menu',
                    message: `Hapus menu "${label}"?`,
                    confirmText: 'Ya, Hapus',
                });
                if (!confirmed) return;
                const res = await apiRequest(`/pengaturan/menu/${id}`, 'DELETE');
                if (res.success) {
                    showToast(res.message, 'success');
                    setTimeout(() => location.reload(), 800);
                } else showToast(res.message, 'error');
            },
        }
    }
</script>
@endpush
@endsection