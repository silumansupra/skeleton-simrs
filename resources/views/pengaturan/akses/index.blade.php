@extends('layouts.app')
@section('content')
<div x-data="aksesPage()" x-init="init()">

    <div class="mb-5">
        <h1 class="text-2xl font-bold text-gray-800">Hak Akses</h1>
        <p class="text-gray-500 text-sm mt-0.5">Atur permission tiap role per menu</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-5">

        {{-- Panel kiri: daftar role --}}
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden h-fit">
            <div class="px-4 py-3 border-b border-gray-100 font-medium text-gray-700 text-sm">Pilih Role</div>
            <div class="divide-y divide-gray-50">
                @foreach ($roles as $role)
                <button @click="loadRole({{ $role->id }}, '{{ $role->nama }}')"
                    class="w-full text-left px-4 py-3 text-sm transition-colors hover:bg-blue-50"
                    :class="selectedRoleId === {{ $role->id }} ? 'bg-blue-50 text-blue-700 font-medium border-r-2 border-blue-600' : 'text-gray-700'">
                    <p class="font-medium">{{ $role->nama }}</p>
                    <p class="text-xs text-gray-400">{{ $role->kode }}</p>
                </button>
                @endforeach
            </div>
        </div>

        {{-- Panel kanan: matrix permission --}}
        <div class="lg:col-span-3">
            <div x-show="!selectedRoleId" class="bg-white rounded-xl border border-gray-200 p-10 text-center text-gray-400">
                <i class="ri-shield-keyhole-line text-5xl block mb-3 opacity-30"></i>
                <p>Pilih role di sebelah kiri untuk mengatur permission</p>
            </div>

            <div x-show="selectedRoleId" x-cloak class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                    <div>
                        <h2 class="font-semibold text-gray-800" x-text="'Permission: ' + selectedRoleName"></h2>
                        <p class="text-xs text-gray-400 mt-0.5">0=tidak ada · 1=lihat · 3=lihat+edit · 7=full</p>
                    </div>
                    <button @click="savePermissions()" :disabled="saving"
                        class="flex items-center gap-2 bg-blue-700 hover:bg-blue-800 text-white px-4 py-2 rounded-lg text-sm transition-colors disabled:opacity-50">
                        <i class="ri-loader-4-line animate-spin" x-show="saving"></i>
                        <i class="ri-save-line" x-show="!saving"></i>
                        <span x-text="saving ? 'Menyimpan...' : 'Simpan'"></span>
                    </button>
                </div>

                <div x-show="loading" class="p-10 text-center text-gray-400">
                    <i class="ri-loader-4-line animate-spin text-3xl block mb-2"></i> Memuat...
                </div>

                <div x-show="!loading" class="divide-y divide-gray-100">
                    <template x-for="parent in menus" :key="parent.id">
                        <div class="p-4">
                            {{-- Parent menu --}}
                            <div class="flex items-center justify-between py-1.5">
                                <div class="flex items-center gap-2">
                                    <i class="ri-folder-line text-blue-500"></i>
                                    <span class="font-medium text-gray-800 text-sm" x-text="parent.label"></span>
                                    <code class="text-xs bg-gray-100 text-gray-500 px-1.5 py-0.5 rounded" x-text="parent.kode"></code>
                                </div>
                                <div class="flex items-center gap-1">
                                    <template x-for="p in [0,1,3,7]" :key="p">
                                        <button @click="setPermission(parent.id, p)"
                                            class="w-9 h-8 text-xs rounded-lg border transition-colors font-medium"
                                            :class="permissions[parent.id] === p
                                                ? 'bg-blue-600 border-blue-600 text-white'
                                                : 'border-gray-200 text-gray-500 hover:border-blue-300 hover:text-blue-600'"
                                            x-text="p"></button>
                                    </template>
                                </div>
                            </div>
                            {{-- Children --}}
                            <template x-for="child in parent.children" :key="child.id">
                                <div class="flex items-center justify-between py-1.5 pl-6 border-t border-gray-50">
                                    <div class="flex items-center gap-2">
                                        <span class="text-gray-300 text-xs">└</span>
                                        <span class="text-gray-600 text-sm" x-text="child.label"></span>
                                        <code class="text-xs bg-gray-100 text-gray-500 px-1.5 py-0.5 rounded" x-text="child.kode"></code>
                                    </div>
                                    <div class="flex items-center gap-1">
                                        <template x-for="p in [0,1,3,7]" :key="p">
                                            <button @click="setPermission(child.id, p)"
                                                class="w-9 h-8 text-xs rounded-lg border transition-colors font-medium"
                                                :class="permissions[child.id] === p
                                                    ? 'bg-blue-600 border-blue-600 text-white'
                                                    : 'border-gray-200 text-gray-500 hover:border-blue-300 hover:text-blue-600'"
                                                x-text="p"></button>
                                        </template>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>

</div>
@push('scripts')
<script>
    function aksesPage() {
        return {
            selectedRoleId: null,
            selectedRoleName: '',
            menus: [],
            permissions: {},
            loading: false,
            saving: false,

            init() {},

            async loadRole(id, nama) {
                this.selectedRoleId = id;
                this.selectedRoleName = nama;
                this.loading = true;
                this.menus = [];
                this.permissions = {};
                const res = await fetch(`/pengaturan/akses/role/${id}`, {
                    headers: {
                        'Accept': 'application/json'
                    }
                });
                const data = await res.json();
                if (data.success) {
                    this.menus = data.data.menus;
                    data.data.menus.forEach(m => {
                        this.permissions[m.id] = m.permission;
                        m.children.forEach(c => {
                            this.permissions[c.id] = c.permission;
                        });
                    });
                }
                this.loading = false;
            },

            setPermission(menuId, val) {
                this.permissions[menuId] = val;
            },

            async savePermissions() {
                this.saving = true;
                const res = await apiRequest(`/pengaturan/akses/role/${this.selectedRoleId}`, 'POST', {
                    permissions: this.permissions
                });
                this.saving = false;
                if (res.success) showToast(res.message, 'success');
                else showToast(res.message, 'error');
            },
        }
    }
</script>
@endpush
@endsection