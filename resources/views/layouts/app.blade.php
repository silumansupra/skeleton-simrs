<!DOCTYPE html>
<html lang="id" x-data="appLayout()" :class="{ 'sidebar-collapsed': sidebarCollapsed }">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'SIMRS' }} — RSU Fastabiq Sehat</title>

    {{-- Tailwind CSS CDN (Play CDN — untuk development) --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: { DEFAULT: '#1e40af', 50: '#eff6ff', 100: '#dbeafe', 500: '#3b82f6', 600: '#2563eb', 700: '#1d4ed8', 800: '#1e40af', 900: '#1e3a8a' },
                        sidebar: { DEFAULT: '#0f172a', text: '#94a3b8', hover: '#1e293b', active: '#1e40af' },
                    }
                }
            }
        }
    </script>

    {{-- Remix Icons (untuk ikon sidebar — sama seperti seeder) --}}
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.3.0/fonts/remixicon.css" rel="stylesheet">

    {{-- Alpine.js --}}
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        [x-cloak] { display: none !important; }
        .sidebar-width { width: 240px; }
        .sidebar-collapsed .sidebar-width { width: 64px; }
        .sidebar-collapsed .sidebar-label { display: none; }
        .sidebar-collapsed .sidebar-submenu { display: none; }
        .main-content { margin-left: 240px; transition: margin-left .2s; }
        .sidebar-collapsed .main-content { margin-left: 64px; }
        @media (max-width: 768px) {
            .sidebar-width { width: 240px; position: fixed; z-index: 50; transform: translateX(-100%); }
            .sidebar-open .sidebar-width { transform: translateX(0); }
            .main-content { margin-left: 0 !important; }
        }
        /* Scrollbar sidebar */
        .sidebar-scroll { scrollbar-width: thin; scrollbar-color: #334155 transparent; }
        .sidebar-scroll::-webkit-scrollbar { width: 4px; }
        .sidebar-scroll::-webkit-scrollbar-thumb { background: #334155; border-radius: 2px; }
    </style>

    @stack('head')
</head>
<body class="bg-gray-100 text-gray-800 font-sans" @click.away="mobileOpen = false">

{{-- ── SIDEBAR ──────────────────────────────────────── --}}
<aside class="sidebar-width bg-[#0f172a] text-gray-300 fixed top-0 left-0 h-full flex flex-col transition-all duration-200 z-40 sidebar-scroll overflow-y-auto">

    {{-- Logo --}}
    <div class="flex items-center gap-3 px-4 py-4 border-b border-slate-700 min-h-[60px]">
        <div class="w-8 h-8 rounded-lg bg-primary-600 flex items-center justify-center flex-shrink-0">
            <i class="ri-hospital-line text-white text-sm"></i>
        </div>
        <div class="sidebar-label overflow-hidden">
            <p class="text-white font-semibold text-sm leading-tight truncate">SIMRS</p>
            <p class="text-slate-400 text-xs truncate">RSU Fastabiq Sehat</p>
        </div>
    </div>

    {{-- Nav --}}
    <nav class="flex-1 py-3 px-2">
        @foreach ($sidebarMenus as $item)
            @php $menu = $item['menu']; $children = $item['children']; $currentUrl = request()->path(); @endphp

            @if (count($children) === 0)
                {{-- Menu tanpa anak --}}
                <a href="{{ $menu->url ?? '#' }}"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg mb-0.5 text-sm transition-colors
                          {{ str_starts_with($currentUrl, ltrim($menu->url ?? '__', '/')) ? 'bg-primary-800 text-white' : 'text-slate-300 hover:bg-slate-700 hover:text-white' }}">
                    <i class="{{ $menu->icon ?? 'ri-circle-line' }} text-lg flex-shrink-0"></i>
                    <span class="sidebar-label truncate">{{ $menu->label }}</span>
                </a>
            @else
                {{-- Menu dengan anak — accordion --}}
                <div x-data="{ open: {{ collect($children)->contains(fn($c) => str_starts_with($currentUrl, ltrim($c->url ?? '__', '/'))) ? 'true' : 'false' }} }">
                    <button @click="open = !open"
                            class="flex items-center gap-3 px-3 py-2.5 rounded-lg mb-0.5 text-sm w-full transition-colors hover:bg-slate-700 hover:text-white
                                   {{ collect($children)->contains(fn($c) => str_starts_with($currentUrl, ltrim($c->url ?? '__', '/'))) ? 'text-white' : 'text-slate-300' }}">
                        <i class="{{ $menu->icon ?? 'ri-circle-line' }} text-lg flex-shrink-0"></i>
                        <span class="sidebar-label flex-1 text-left truncate">{{ $menu->label }}</span>
                        <i class="sidebar-label ri-arrow-right-s-line text-slate-500 transition-transform" :class="{ 'rotate-90': open }"></i>
                    </button>
                    <div x-show="open" x-cloak class="sidebar-submenu pl-4 mb-1">
                        @foreach ($children as $child)
                            <a href="{{ $child->url ?? '#' }}"
                               class="flex items-center gap-3 px-3 py-2 rounded-lg mb-0.5 text-sm transition-colors
                                      {{ str_starts_with($currentUrl, ltrim($child->url ?? '__', '/')) ? 'bg-primary-800 text-white' : 'text-slate-400 hover:bg-slate-700 hover:text-white' }}">
                                <i class="{{ $child->icon ?? 'ri-arrow-right-s-line' }} text-base flex-shrink-0"></i>
                                <span class="sidebar-label truncate">{{ $child->label }}</span>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif
        @endforeach
    </nav>

    {{-- User info di bawah --}}
    <div class="border-t border-slate-700 p-3">
        <div x-data="{ open: false }" class="relative">
            <button @click="open = !open" class="flex items-center gap-2 w-full rounded-lg px-2 py-2 hover:bg-slate-700 transition-colors">
                <div class="w-8 h-8 rounded-full bg-primary-700 flex items-center justify-center text-white text-xs font-semibold flex-shrink-0">
                    {{ strtoupper(substr($authUser->nama_lengkap, 0, 2)) }}
                </div>
                <div class="sidebar-label flex-1 text-left overflow-hidden">
                    <p class="text-white text-xs font-medium truncate">{{ $authUser->nama_lengkap }}</p>
                    <p class="text-slate-400 text-xs truncate">{{ $authUser->roles->first()?->nama ?? '—' }}</p>
                </div>
                <i class="sidebar-label ri-more-2-fill text-slate-500 flex-shrink-0"></i>
            </button>
            <div x-show="open" x-cloak @click.away="open = false"
                 class="absolute bottom-full left-0 w-full mb-1 bg-slate-800 border border-slate-700 rounded-lg shadow-lg py-1 z-50">
                <a href="{{ route('pengaturan.akun') }}" class="flex items-center gap-2 px-3 py-2 text-sm text-slate-300 hover:bg-slate-700 hover:text-white">
                    <i class="ri-user-settings-line"></i> Akun Saya
                </a>
                <hr class="border-slate-700 my-1">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="flex items-center gap-2 px-3 py-2 text-sm text-red-400 hover:bg-slate-700 hover:text-red-300 w-full text-left">
                        <i class="ri-logout-box-r-line"></i> Logout
                    </button>
                </form>
            </div>
        </div>
    </div>
</aside>

{{-- ── MAIN CONTENT ──────────────────────────────────── --}}
<div class="main-content min-h-screen flex flex-col">

    {{-- Navbar --}}
    <header class="bg-white border-b border-gray-200 px-4 py-3 flex items-center gap-3 sticky top-0 z-30 shadow-sm">
        <button @click="sidebarCollapsed = !sidebarCollapsed" class="text-gray-500 hover:text-gray-800 transition-colors">
            <i class="ri-menu-2-line text-xl"></i>
        </button>
        {{-- Breadcrumb --}}
        <nav class="flex items-center gap-1.5 text-sm text-gray-500 flex-1">
            <a href="{{ route('dashboard') }}" class="hover:text-primary-600 transition-colors">
                <i class="ri-home-3-line"></i>
            </a>
            @isset($breadcrumb)
                @foreach ($breadcrumb as $crumb)
                    <i class="ri-arrow-right-s-line text-gray-300"></i>
                    @if ($crumb['url'])
                        <a href="{{ $crumb['url'] }}" class="hover:text-primary-600 transition-colors">{{ $crumb['label'] }}</a>
                    @else
                        <span class="text-gray-800 font-medium">{{ $crumb['label'] }}</span>
                    @endif
                @endforeach
            @endisset
        </nav>
        {{-- Notifikasi placeholder --}}
        <button class="text-gray-500 hover:text-gray-800 relative">
            <i class="ri-notification-3-line text-xl"></i>
        </button>
    </header>

    {{-- Konten --}}
    <main class="flex-1 p-5">
        @yield('content')
    </main>

    {{-- Footer --}}
    <footer class="border-t border-gray-200 bg-white px-5 py-3 text-center text-xs text-gray-400">
        &copy; {{ date('Y') }} RSU Fastabiq Sehat PKU Muhammadiyah &mdash; SIMRS v1.0
    </footer>
</div>

{{-- ── TOAST CONTAINER ──────────────────────────────── --}}
<div id="toast-container" class="fixed bottom-4 right-4 z-[9999] flex flex-col gap-2 w-80"></div>

{{-- ── CONFIRM MODAL ─────────────────────────────────── --}}
<div id="confirm-modal" x-data="confirmModal()" x-show="show" x-cloak
     class="fixed inset-0 z-[9998] flex items-center justify-center p-4"
     @keydown.escape.window="cancel()">
    <div class="absolute inset-0 bg-black/50" @click="cancel()"></div>
    <div class="relative bg-white rounded-xl shadow-2xl w-full max-w-md p-6">
        <div class="flex items-start gap-4">
            <div class="w-10 h-10 rounded-full flex items-center justify-center flex-shrink-0"
                 :class="typeClass">
                <i :class="typeIcon + ' text-xl'"></i>
            </div>
            <div class="flex-1">
                <h3 class="font-semibold text-gray-900 mb-1" x-text="title"></h3>
                <p class="text-sm text-gray-600" x-text="message"></p>
            </div>
        </div>
        <div class="flex justify-end gap-3 mt-5">
            <button @click="cancel()" class="px-4 py-2 text-sm border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                Batal
            </button>
            <button @click="confirm()" class="px-4 py-2 text-sm text-white rounded-lg transition-colors" :class="btnClass" x-text="confirmText"></button>
        </div>
    </div>
</div>

<script>
// ── LAYOUT STATE ─────────────────────────────────────────
function appLayout() {
    return {
        sidebarCollapsed: localStorage.getItem('sidebar_collapsed') === 'true',
        mobileOpen: false,
        watch: {
            sidebarCollapsed(v) { localStorage.setItem('sidebar_collapsed', v); }
        }
    }
}

// ── TOAST ────────────────────────────────────────────────
function showToast(message, type = 'success', duration = 4000) {
    const container = document.getElementById('toast-container');
    const icons = { success: 'ri-checkbox-circle-line', error: 'ri-error-warning-line', warning: 'ri-alert-line', info: 'ri-information-line' };
    const colors = {
        success: 'bg-green-50 border-green-200 text-green-800',
        error:   'bg-red-50 border-red-200 text-red-800',
        warning: 'bg-yellow-50 border-yellow-200 text-yellow-800',
        info:    'bg-blue-50 border-blue-200 text-blue-800',
    };
    const iconColors = { success: 'text-green-500', error: 'text-red-500', warning: 'text-yellow-500', info: 'text-blue-500' };

    const el = document.createElement('div');
    el.className = `flex items-start gap-3 p-4 rounded-xl border shadow-lg text-sm transition-all duration-300 ${colors[type]}`;
    el.style.opacity = '0'; el.style.transform = 'translateX(20px)';
    el.innerHTML = `
        <i class="${icons[type]} ${iconColors[type]} text-lg flex-shrink-0 mt-0.5"></i>
        <span class="flex-1">${message}</span>
        <button onclick="this.closest('div').remove()" class="text-current opacity-50 hover:opacity-100 ml-2">
            <i class="ri-close-line"></i>
        </button>`;

    container.appendChild(el);
    requestAnimationFrame(() => { el.style.opacity = '1'; el.style.transform = 'translateX(0)'; });
    setTimeout(() => { el.style.opacity = '0'; el.style.transform = 'translateX(20px)'; setTimeout(() => el.remove(), 300); }, duration);
}

// ── CONFIRM MODAL ────────────────────────────────────────
let _confirmResolve = null;
function confirmModal() {
    return {
        show: false, title: '', message: '', type: 'danger', confirmText: 'Ya, Lanjutkan',
        get typeClass() {
            return { danger: 'bg-red-100', warning: 'bg-yellow-100', info: 'bg-blue-100' }[this.type] ?? 'bg-red-100';
        },
        get typeIcon() {
            return ({ danger: 'ri-delete-bin-line text-red-500', warning: 'ri-alert-line text-yellow-500', info: 'ri-information-line text-blue-500' })[this.type] ?? 'ri-delete-bin-line text-red-500';
        },
        get btnClass() {
            return { danger: 'bg-red-600 hover:bg-red-700', warning: 'bg-yellow-500 hover:bg-yellow-600', info: 'bg-blue-600 hover:bg-blue-700' }[this.type] ?? 'bg-red-600 hover:bg-red-700';
        },
        confirm() { this.show = false; if (_confirmResolve) _confirmResolve(true); },
        cancel()  { this.show = false; if (_confirmResolve) _confirmResolve(false); },
    }
}

function showConfirm({ title = 'Konfirmasi', message = 'Apakah Anda yakin?', type = 'danger', confirmText = 'Ya, Lanjutkan' } = {}) {
    return new Promise(resolve => {
        _confirmResolve = resolve;
        const modal = Alpine.store ? document.querySelector('#confirm-modal').__x : null;
        const comp  = document.querySelector('#confirm-modal')._x_dataStack?.[0];
        if (comp) { comp.title = title; comp.message = message; comp.type = type; comp.confirmText = confirmText; comp.show = true; }
    });
}

// ── AJAX HELPER ──────────────────────────────────────────
async function apiRequest(url, method = 'POST', body = null) {
    const headers = {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        'Accept': 'application/json',
    };
    const res = await fetch(url, {
        method, headers,
        body: body ? JSON.stringify(body) : null,
    });
    return res.json();
}

// ── FLASH MESSAGES ────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
    @if (session('success')) showToast(@json(session('success')), 'success'); @endif
    @if (session('error'))   showToast(@json(session('error')),   'error');   @endif
    @if (session('warning')) showToast(@json(session('warning')), 'warning'); @endif
});
</script>

@stack('scripts')
</body>
</html>
