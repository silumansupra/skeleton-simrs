@extends('layouts.app')
@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-800">Dashboard</h1>
    <p class="text-gray-500 text-sm mt-1">Selamat datang, {{ $authUser->nama_lengkap }}</p>
</div>

{{-- Stat Cards --}}
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    @php
        $stats = [
            ['label' => 'Total Pengguna',   'value' => \App\Models\User::count(),   'icon' => 'ri-group-line',        'color' => 'blue'],
            ['label' => 'Role Aktif',        'value' => \App\Models\Role::where('is_active', true)->count(), 'icon' => 'ri-shield-keyhole-line', 'color' => 'purple'],
            ['label' => 'Total Menu',        'value' => \App\Models\Menu::count(),   'icon' => 'ri-menu-line',         'color' => 'green'],
            ['label' => 'Log Hari Ini',      'value' => \App\Models\AuditLog::whereDate('created_at', today())->count(), 'icon' => 'ri-file-list-3-line', 'color' => 'orange'],
        ];
        $colorMap = [
            'blue'   => ['bg' => 'bg-blue-50',   'icon' => 'text-blue-600',   'val' => 'text-blue-700'],
            'purple' => ['bg' => 'bg-purple-50', 'icon' => 'text-purple-600', 'val' => 'text-purple-700'],
            'green'  => ['bg' => 'bg-green-50',  'icon' => 'text-green-600',  'val' => 'text-green-700'],
            'orange' => ['bg' => 'bg-orange-50', 'icon' => 'text-orange-600', 'val' => 'text-orange-700'],
        ];
    @endphp
    @foreach ($stats as $stat)
        @php $c = $colorMap[$stat['color']]; @endphp
        <div class="bg-white rounded-xl border border-gray-200 p-5 flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl {{ $c['bg'] }} flex items-center justify-center flex-shrink-0">
                <i class="{{ $stat['icon'] }} {{ $c['icon'] }} text-2xl"></i>
            </div>
            <div>
                <p class="text-gray-500 text-xs font-medium uppercase tracking-wide">{{ $stat['label'] }}</p>
                <p class="text-2xl font-bold {{ $c['val'] }} mt-0.5">{{ $stat['value'] }}</p>
            </div>
        </div>
    @endforeach
</div>

{{-- Recent Audit Log --}}
<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
        <h2 class="font-semibold text-gray-800 flex items-center gap-2">
            <i class="ri-file-list-3-line text-blue-600"></i>
            Aktivitas Terakhir
        </h2>
        <a href="{{ route('pengaturan.audit-log.index') }}" class="text-xs text-blue-600 hover:underline">Lihat semua →</a>
    </div>
    <div class="divide-y divide-gray-50">
        @forelse (\App\Models\AuditLog::with('user')->latest()->limit(8)->get() as $log)
            <div class="px-5 py-3 flex items-start gap-3">
                <div class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center flex-shrink-0 mt-0.5">
                    <span class="text-xs font-semibold text-gray-600">
                        {{ strtoupper(substr($log->username ?? '?', 0, 2)) }}
                    </span>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm text-gray-800">
                        <span class="font-medium">{{ $log->username ?? 'Sistem' }}</span>
                        <span class="text-gray-500"> — </span>
                        {{ $log->keterangan ?? $log->aksi }}
                    </p>
                    <p class="text-xs text-gray-400 mt-0.5">{{ $log->created_at->diffForHumans() }} · {{ $log->ip_address }}</p>
                </div>
                <span class="text-xs bg-gray-100 text-gray-600 px-2 py-0.5 rounded-full flex-shrink-0">{{ $log->modul ?? '—' }}</span>
            </div>
        @empty
            <div class="px-5 py-8 text-center text-gray-400 text-sm">
                <i class="ri-file-list-3-line text-3xl block mb-2 opacity-30"></i>
                Belum ada aktivitas
            </div>
        @endforelse
    </div>
</div>
@endsection
