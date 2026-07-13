<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        // ── ROLES ────────────────────────────────────────────
        $roles = [
            ['kode' => 'superadmin', 'nama' => 'Super Administrator', 'deskripsi' => 'Akses penuh ke semua fitur'],
            ['kode' => 'admin',      'nama' => 'Administrator',        'deskripsi' => 'Manajemen user dan pengaturan'],
            ['kode' => 'viewer',     'nama' => 'Viewer',               'deskripsi' => 'Hanya dapat melihat data'],
        ];
        foreach ($roles as &$r) {
            $r['is_active']   = 1;
            $r['created_at']  = $now;
            $r['updated_at']  = $now;
        }
        DB::table('roles')->insert($roles);

        $superadminRoleId = DB::table('roles')->where('kode', 'superadmin')->value('id');

        // ── SUPERADMIN USER ──────────────────────────────────
        $userId = DB::table('users')->insertGetId([
            'username'      => 'superadmin',
            'password_hash' => Hash::make('Admin@1234'),
            'nama_lengkap'  => 'Super Administrator',
            'email'         => 'superadmin@fastabiqsehat.web.id',
            'is_active'     => 1,
            'created_at'    => $now,
            'updated_at'    => $now,
        ]);

        DB::table('user_roles')->insert([
            'user_id'     => $userId,
            'role_id'     => $superadminRoleId,
            'assigned_at' => $now,
        ]);

        // ── MENUS ────────────────────────────────────────────
        // Parent menus
        $menuDashboard = DB::table('menus')->insertGetId([
            'parent_id'  => null,
            'kode'       => 'dashboard',
            'label'      => 'Dashboard',
            'icon'       => 'ri-dashboard-line',
            'url'        => '/dashboard',
            'urutan'     => 1,
            'is_active'  => 1,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $menuPengaturan = DB::table('menus')->insertGetId([
            'parent_id'  => null,
            'kode'       => 'pengaturan',
            'label'      => 'Pengaturan',
            'icon'       => 'ri-settings-3-line',
            'url'        => null,
            'urutan'     => 2,
            'is_active'  => 1,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        // Submenus Pengaturan
        $submenus = [
            ['kode' => 'pengaturan.akun',      'label' => 'Akun Saya',      'icon' => 'ri-user-settings-line', 'url' => '/pengaturan/akun',      'urutan' => 1],
            ['kode' => 'pengaturan.user',       'label' => 'Pengguna',       'icon' => 'ri-group-line',          'url' => '/pengaturan/user',       'urutan' => 2],
            ['kode' => 'pengaturan.menu',       'label' => 'Menu & Submenu', 'icon' => 'ri-menu-line',           'url' => '/pengaturan/menu',       'urutan' => 3],
            ['kode' => 'pengaturan.akses',      'label' => 'Hak Akses',      'icon' => 'ri-shield-keyhole-line', 'url' => '/pengaturan/akses',      'urutan' => 4],
            ['kode' => 'pengaturan.audit_log',  'label' => 'Audit Log',      'icon' => 'ri-file-list-3-line',    'url' => '/pengaturan/audit-log',  'urutan' => 5],
        ];

        foreach ($submenus as $sm) {
            DB::table('menus')->insert(array_merge($sm, [
                'parent_id'  => $menuPengaturan,
                'is_active'  => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ]));
        }

        // ── ROLE MENU ACCESS (superadmin = permission 7 semua) ─
        $allMenuIds = DB::table('menus')->pluck('id');
        foreach ($allMenuIds as $menuId) {
            DB::table('role_menu_access')->insert([
                'role_id'    => $superadminRoleId,
                'menu_id'    => $menuId,
                'permission' => 7,
            ]);
        }
    }
}
