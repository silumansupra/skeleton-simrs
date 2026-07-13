<?php

namespace App\Http\Controllers\Pengaturan;

use App\Http\Controllers\BaseController;
use App\Models\AuditLog;
use App\Models\Menu;
use Illuminate\Http\Request;

class MenuController extends BaseController
{
    public function index()
    {
        $menus = Menu::with('parent')
            ->orderByRaw('COALESCE(parent_id, id), parent_id IS NOT NULL, urutan')
            ->get();

        $parents = Menu::whereNull('parent_id')->orderBy('urutan')->get();

        return view('pengaturan.menu.index', $this->shareLayout([
            'title'      => 'Menu & Submenu',
            'breadcrumb' => [
                ['label' => 'Pengaturan',    'url' => '#'],
                ['label' => 'Menu & Submenu','url' => ''],
            ],
            'menus'   => $menus,
            'parents' => $parents,
        ]));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'parent_id' => 'nullable|integer|exists:menus,id',
            'kode'      => 'required|string|max:100|unique:menus,kode',
            'label'     => 'required|string|max:100',
            'icon'      => 'nullable|string|max:100',
            'url'       => 'nullable|string|max:255',
            'urutan'    => 'required|integer|min:0',
            'is_active' => 'boolean',
        ]);

        $menu = Menu::create($data);

        AuditLog::catat('create_menu', modul: 'pengaturan', tabel: 'menus',
            record_id: (string) $menu->id,
            keterangan: "Buat menu: {$menu->kode}");

        return $this->respondOk("Menu '{$menu->label}' berhasil dibuat.", ['id' => $menu->id]);
    }

    public function show(int $id)
    {
        return $this->respondOk(data: ['menu' => Menu::findOrFail($id)]);
    }

    public function update(Request $request, int $id)
    {
        $menu = Menu::findOrFail($id);

        $data = $request->validate([
            'parent_id' => "nullable|integer|exists:menus,id|not_in:{$id}",
            'kode'      => "required|string|max:100|unique:menus,kode,{$id}",
            'label'     => 'required|string|max:100',
            'icon'      => 'nullable|string|max:100',
            'url'       => 'nullable|string|max:255',
            'urutan'    => 'required|integer|min:0',
            'is_active' => 'boolean',
        ]);

        $before = $menu->toArray();
        $menu->update($data);

        AuditLog::catat('update_menu', modul: 'pengaturan', tabel: 'menus',
            record_id: (string) $id, before: $before, keterangan: "Update menu: {$menu->kode}");

        return $this->respondOk("Menu '{$menu->label}' berhasil diperbarui.");
    }

    public function toggle(int $id)
    {
        $menu   = Menu::findOrFail($id);
        $newVal = ! $menu->is_active;
        $label  = $newVal ? 'diaktifkan' : 'dinonaktifkan';

        // Jika parent dinonaktifkan, nonaktifkan semua anak
        if (! $newVal && is_null($menu->parent_id)) {
            Menu::where('parent_id', $id)->update(['is_active' => false]);
        }

        $menu->update(['is_active' => $newVal]);

        AuditLog::catat('toggle_menu', modul: 'pengaturan', tabel: 'menus',
            record_id: (string) $id, keterangan: "Menu '{$menu->kode}' {$label}");

        return $this->respondOk("Menu '{$menu->label}' berhasil {$label}.", ['is_active' => $newVal]);
    }

    public function destroy(int $id)
    {
        $menu = Menu::findOrFail($id);

        if (Menu::where('parent_id', $id)->exists()) {
            return $this->respondError("Menu '{$menu->label}' memiliki submenu. Hapus submenu terlebih dahulu.");
        }

        $menu->roleAccess()->delete();
        $menu->delete();

        AuditLog::catat('delete_menu', modul: 'pengaturan', tabel: 'menus',
            record_id: (string) $id, keterangan: "Hapus menu: {$menu->kode}");

        return $this->respondOk("Menu '{$menu->label}' berhasil dihapus.");
    }
}
