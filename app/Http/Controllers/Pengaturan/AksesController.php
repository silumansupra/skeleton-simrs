<?php

namespace App\Http\Controllers\Pengaturan;

use App\Http\Controllers\BaseController;
use App\Models\AuditLog;
use App\Models\Menu;
use App\Models\Role;
use App\Models\RoleMenuAccess;
use Illuminate\Http\Request;

class AksesController extends BaseController
{
    public function index()
    {
        $roles = Role::where('is_active', true)->orderBy('nama')->get();

        return view('pengaturan.akses.index', $this->shareLayout([
            'title'      => 'Hak Akses',
            'breadcrumb' => [
                ['label' => 'Pengaturan', 'url' => '#'],
                ['label' => 'Hak Akses',  'url' => ''],
            ],
            'roles' => $roles,
        ]));
    }

    public function getPermissions(int $roleId)
    {
        $role = Role::findOrFail($roleId);

        $menus = Menu::whereNull('parent_id')
            ->where('is_active', true)
            ->orderBy('urutan')
            ->with(['children' => fn($q) => $q->where('is_active', true)->orderBy('urutan')])
            ->get();

        // Ambil semua permission role ini
        $existing = RoleMenuAccess::where('role_id', $roleId)
            ->pluck('permission', 'menu_id');

        $result = [];
        foreach ($menus as $parent) {
            $parentData = [
                'id'         => $parent->id,
                'kode'       => $parent->kode,
                'label'      => $parent->label,
                'permission' => (int) ($existing[$parent->id] ?? 0),
                'children'   => [],
            ];
            foreach ($parent->children as $child) {
                $parentData['children'][] = [
                    'id'         => $child->id,
                    'kode'       => $child->kode,
                    'label'      => $child->label,
                    'permission' => (int) ($existing[$child->id] ?? 0),
                ];
            }
            $result[] = $parentData;
        }

        return $this->respondOk(data: ['role' => $role, 'menus' => $result]);
    }

    public function savePermissions(Request $request, int $roleId)
    {
        $role = Role::findOrFail($roleId);

        if ($role->kode === 'superadmin') {
            return $this->respondError('Permission role superadmin tidak dapat diubah.');
        }

        $permissions = $request->input('permissions', []); // ['menu_id' => permission_int]

        \DB::transaction(function () use ($roleId, $permissions) {
            RoleMenuAccess::where('role_id', $roleId)->delete();
            foreach ($permissions as $menuId => $perm) {
                if ((int) $perm === 0) continue;
                RoleMenuAccess::create([
                    'role_id'    => $roleId,
                    'menu_id'    => (int) $menuId,
                    'permission' => (int) $perm,
                ]);
            }
        });

        AuditLog::catat('save_permissions', modul: 'pengaturan', tabel: 'role_menu_access',
            record_id: (string) $roleId,
            keterangan: "Update permission role: {$role->kode}");

        return $this->respondOk('Hak akses berhasil disimpan.');
    }
}
