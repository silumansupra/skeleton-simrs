<?php

namespace App\Http\Controllers;

use App\Models\Menu;

abstract class BaseController extends Controller
{
    protected function respondOk(string $message = '', mixed $data = null, int $status = 200)
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data'    => $data,
        ], $status);
    }

    protected function respondError(string $message, int $status = 422, mixed $data = null)
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'data'    => $data,
        ], $status);
    }

    /**
     * Ambil menu sidebar untuk user yang sedang login.
     * Return: nested array parent → children, hanya yang punya permission ≥ 1.
     */
    protected function getSidebarMenus(): array
    {
        $user    = auth()->user();
        $parents = Menu::whereNull('parent_id')
            ->where('is_active', true)
            ->orderBy('urutan')
            ->with(['children' => fn($q) => $q->where('is_active', true)->orderBy('urutan')])
            ->get();

        $result = [];
        foreach ($parents as $parent) {
            $parentPerm = $user->getMenuPermission($parent->kode);

            $children = [];
            foreach ($parent->children as $child) {
                if ($user->getMenuPermission($child->kode) >= 1) {
                    $children[] = $child;
                }
            }

            // Tampilkan parent jika punya permission langsung ATAU punya anak yang accessible
            if ($parentPerm >= 1 || count($children) > 0) {
                $result[] = [
                    'menu'     => $parent,
                    'children' => $children,
                    'perm'     => $parentPerm,
                ];
            }
        }

        return $result;
    }

    protected function shareLayout(array $extra = []): array
    {
        return array_merge([
            'sidebarMenus' => $this->getSidebarMenus(),
            'authUser'     => auth()->user(),
        ], $extra);
    }
}
