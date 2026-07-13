@extends('layouts.app')
@section('content')
<div x-data="auditPage()">

<div class="flex items-center justify-between mb-5">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Audit Log</h1>
        <p class="text-gray-500 text-sm mt-0.5">Total {{ $logs->total() }} entri log</p>
    </div>
</div>

{{-- Filter --}}
<div class="bg-white rounded-xl border border-gray-200 p-4 mb-4">
    <form method="GET" class="flex flex-wrap gap-3">
        <div class="relative flex-1 min-w-48">
            <i class="ri-search-line absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
            <input type="text" name="keyword" value="{{ $filter['keyword'] ?? '' }}" placeholder="Cari keterangan, aksi, username..."
                   class="w-full pl-8 pr-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
        <select name="modul" class="border border-gray-300 rounded-lg text-sm px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">Semua Modul</option>
            @foreach ($moduls as $m)
                <option value="{{ $m }}" {{ ($filter['modul'] ?? '') === $m ? 'selected' : '' }}>{{ $m }}</option>
            @endforeach
        </select>
        <input type="date" name="tgl_dari" value="{{ $filter['tgl_dari'] ?? '' }}"
               class="border border-gray-300 rounded-lg text-sm px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
        <input type="date" name="tgl_sampai" value="{{ $filter['tgl_sampai'] ?? '' }}"
               class="border border-gray-300 rounded-lg text-sm px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
        <button type="submit" class="bg-blue-700 text-white px-4 py-2 rounded-lg text-sm hover:bg-blue-800 transition-colors">
            <i class="ri-search-line mr-1"></i> Filter
        </button>
        @if (!empty(array_filter($filter)))
            <a href="{{ route('pengaturan.audit-log.index') }}" class="border border-gray-300 px-4 py-2 rounded-lg text-sm hover:bg-gray-50 text-gray-600 transition-colors">Reset</a>
        @endif
    </form>
</div>

{{-- Table --}}
<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">Waktu</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">Pengguna</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">Aksi</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">Keterangan</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-600 hidden md:table-cell">IP</th>
                    <th class="text-center px-4 py-3 font-medium text-gray-600">Detail</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($logs as $log)
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="px-4 py-3 text-gray-500 whitespace-nowrap text-xs">
                        <p>{{ $log->created_at->format('d/m/Y') }}</p>
                        <p class="text-gray-400">{{ $log->created_at->format('H:i:s') }}</p>
                    </td>
                    <td class="px-4 py-3">
                        <p class="font-medium text-gray-800">{{ $log->username ?? '—' }}</p>
                        <p class="text-gray-400 text-xs">{{ $log->modul ?? '—' }}</p>
                    </td>
                    <td class="px-4 py-3">
                        @php
                            $aksiColor = match(true) {
                                str_contains($log->aksi, 'delete') || str_contains($log->aksi, 'hapus') => 'bg-red-50 text-red-700 border-red-100',
                                str_contains($log->aksi, 'create') || str_contains($log->aksi, 'buat') => 'bg-green-50 text-green-700 border-green-100',
                                str_contains($log->aksi, 'update') || str_contains($log->aksi, 'edit') => 'bg-blue-50 text-blue-700 border-blue-100',
                                str_contains($log->aksi, 'login') => 'bg-purple-50 text-purple-700 border-purple-100',
                                default => 'bg-gray-100 text-gray-600',
                            };
                        @endphp
                        <span class="px-2 py-0.5 text-xs rounded-full border {{ $aksiColor }}">{{ $log->aksi }}</span>
                    </td>
                    <td class="px-4 py-3 text-gray-600 max-w-xs truncate">{{ $log->keterangan ?? '—' }}</td>
                    <td class="px-4 py-3 text-gray-400 text-xs hidden md:table-cell">{{ $log->ip_address ?? '—' }}</td>
                    <td class="px-4 py-3 text-center">
                        @if ($log->before || $log->after)
                        <button @click="showDetail({{ $log->id }})" class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors" title="Lihat detail">
                            <i class="ri-eye-line"></i>
                        </button>
                        @else
                            <span class="text-gray-300 text-xs">—</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-4 py-10 text-center text-gray-400">
                        <i class="ri-file-list-3-line text-4xl block mb-2 opacity-30"></i>
                        Tidak ada log ditemukan
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if ($logs->hasPages())
    <div class="px-4 py-3 border-t border-gray-100">
        {{ $logs->links('vendor.pagination.tailwind') }}
    </div>
    @endif
</div>

{{-- Detail Modal --}}
<div x-show="detailOpen" x-cloak class="fixed inset-0 z-[9990] flex items-center justify-center p-4" @keydown.escape.window="detailOpen = false">
    <div class="absolute inset-0 bg-black/50" @click="detailOpen = false"></div>
    <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-2xl max-h-[80vh] overflow-y-auto">
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
            <h3 class="font-semibold text-gray-800">Detail Perubahan</h3>
            <button @click="detailOpen = false" class="text-gray-400 hover:text-gray-600"><i class="ri-close-line text-xl"></i></button>
        </div>
        <div class="px-6 py-5 space-y-4" x-show="detail">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <p class="text-xs font-medium text-gray-500 uppercase mb-2">Sebelum</p>
                    <pre class="bg-gray-50 border border-gray-200 rounded-lg p-3 text-xs text-gray-700 overflow-auto" x-text="detail?.before ? JSON.stringify(detail.before, null, 2) : 'Tidak ada data'"></pre>
                </div>
                <div>
                    <p class="text-xs font-medium text-gray-500 uppercase mb-2">Sesudah</p>
                    <pre class="bg-gray-50 border border-gray-200 rounded-lg p-3 text-xs text-gray-700 overflow-auto" x-text="detail?.after ? JSON.stringify(detail.after, null, 2) : 'Tidak ada data'"></pre>
                </div>
            </div>
        </div>
    </div>
</div>

</div>
@push('scripts')
<script>
function auditPage() {
    return {
        detailOpen: false,
        detail: null,
        async showDetail(id) {
            const res = await fetch(`/pengaturan/audit-log/${id}`, { headers: { 'Accept': 'application/json' } });
            const data = await res.json();
            if (data.success) { this.detail = data.data.log; this.detailOpen = true; }
        }
    }
}
</script>
@endpush
@endsection
