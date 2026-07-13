<?php

namespace App\Http\Controllers\Pengaturan;

use App\Http\Controllers\BaseController;
use App\Models\AuditLog;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends BaseController
{
    private const MENU = 'pengaturan.user';

    public function index(Request $request)
    {
        $filter  = $request->only(['keyword', 'is_active']);
        $perPage = 15;

        $query = User::withTrashed(false)
            ->with('roles')
            ->when($filter['keyword'] ?? null, function ($q, $kw) {
                $q->where(function ($q) use ($kw) {
                    $q->where('username', 'like', "%{$kw}%")
                      ->orWhere('nama_lengkap', 'like', "%{$kw}%")
                      ->orWhere('nip', 'like', "%{$kw}%")
                      ->orWhere('email', 'like', "%{$kw}%");
                });
            })
            ->when(isset($filter['is_active']) && $filter['is_active'] !== '', function ($q) use ($filter) {
                $q->where('is_active', (bool) $filter['is_active']);
            })
            ->orderBy('nama_lengkap');

        $users = $query->paginate($perPage)->withQueryString();
        $roles = Role::where('is_active', true)->orderBy('nama')->get();

        return view('pengaturan.user.index', $this->shareLayout([
            'title'      => 'Manajemen Pengguna',
            'breadcrumb' => [
                ['label' => 'Pengaturan', 'url' => '#'],
                ['label' => 'Pengguna',   'url' => ''],
            ],
            'users'  => $users,
            'roles'  => $roles,
            'filter' => $filter,
            'perm'   => $request->integer('_user_permission'),
        ]));
    }

    public function show(int $id)
    {
        $user = User::with('roles')->findOrFail($id);
        return $this->respondOk(data: ['user' => $user]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nip'          => 'nullable|string|max:30|unique:users,nip',
            'username'     => 'required|string|max:50|unique:users,username',
            'nama_lengkap' => 'required|string|max:100',
            'email'        => 'nullable|email|max:100|unique:users,email',
            'no_hp'        => 'nullable|string|max:20',
            'password'     => 'required|string|min:8',
            'is_active'    => 'boolean',
            'role_ids'     => 'array',
            'role_ids.*'   => 'integer|exists:roles,id',
        ]);

        $user = User::create([
            'nip'          => $data['nip'] ?? null,
            'username'     => $data['username'],
            'password_hash'=> Hash::make($data['password']),
            'nama_lengkap' => $data['nama_lengkap'],
            'email'        => $data['email'] ?? null,
            'no_hp'        => $data['no_hp'] ?? null,
            'is_active'    => $data['is_active'] ?? true,
            'created_by'   => auth()->id(),
        ]);

        if (! empty($data['role_ids'])) {
            $user->roles()->sync($data['role_ids']);
        }

        AuditLog::catat('create_user', modul: 'pengaturan', tabel: 'users',
            record_id: (string) $user->id,
            keterangan: "Buat user: {$user->username}");

        return $this->respondOk("User {$user->nama_lengkap} berhasil dibuat.", ['user_id' => $user->id]);
    }

    public function update(Request $request, int $id)
    {
        $user = User::findOrFail($id);

        $data = $request->validate([
            'nip'          => "nullable|string|max:30|unique:users,nip,{$id}",
            'username'     => "required|string|max:50|unique:users,username,{$id}",
            'nama_lengkap' => 'required|string|max:100',
            'email'        => "nullable|email|max:100|unique:users,email,{$id}",
            'no_hp'        => 'nullable|string|max:20',
            'password'     => 'nullable|string|min:8',
            'is_active'    => 'boolean',
            'role_ids'     => 'array',
            'role_ids.*'   => 'integer|exists:roles,id',
        ]);

        $before = $user->only(['username', 'nama_lengkap', 'email', 'is_active']);

        $updateData = [
            'nip'          => $data['nip'] ?? null,
            'username'     => $data['username'],
            'nama_lengkap' => $data['nama_lengkap'],
            'email'        => $data['email'] ?? null,
            'no_hp'        => $data['no_hp'] ?? null,
            'is_active'    => $data['is_active'] ?? true,
            'updated_by'   => auth()->id(),
        ];

        if (! empty($data['password'])) {
            $updateData['password_hash'] = Hash::make($data['password']);
        }

        $user->update($updateData);
        $user->roles()->sync($data['role_ids'] ?? []);

        AuditLog::catat('update_user', modul: 'pengaturan', tabel: 'users',
            record_id: (string) $id,
            before: $before,
            after: $updateData,
            keterangan: "Update user: {$user->username}");

        return $this->respondOk("User {$user->nama_lengkap} berhasil diperbarui.");
    }

    public function toggleActive(int $id)
    {
        if ($id === auth()->id()) {
            return $this->respondError('Tidak dapat menonaktifkan akun sendiri.');
        }

        $user   = User::findOrFail($id);
        $newVal = ! $user->is_active;
        $user->update(['is_active' => $newVal]);
        $label = $newVal ? 'diaktifkan' : 'dinonaktifkan';

        AuditLog::catat('toggle_user', modul: 'pengaturan', tabel: 'users',
            record_id: (string) $id,
            keterangan: "User {$user->username} {$label}");

        return $this->respondOk("User berhasil {$label}.", ['is_active' => $newVal]);
    }

    public function resetLock(int $id)
    {
        $user = User::findOrFail($id);
        $user->update(['failed_attempts' => 0, 'locked_until' => null]);

        AuditLog::catat('reset_lock', modul: 'pengaturan', tabel: 'users',
            record_id: (string) $id, keterangan: "Reset lock: {$user->username}");

        return $this->respondOk('Lock berhasil direset.');
    }

    public function destroy(int $id)
    {
        if ($id === auth()->id()) {
            return $this->respondError('Tidak dapat menghapus akun sendiri.');
        }

        $user = User::findOrFail($id);
        $user->delete(); // soft delete

        AuditLog::catat('delete_user', modul: 'pengaturan', tabel: 'users',
            record_id: (string) $id, keterangan: "Hapus user: {$user->username}");

        return $this->respondOk("User {$user->nama_lengkap} berhasil dihapus.");
    }
}
