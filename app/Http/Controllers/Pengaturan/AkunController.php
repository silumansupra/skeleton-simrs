<?php

namespace App\Http\Controllers\Pengaturan;

use App\Http\Controllers\BaseController;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AkunController extends BaseController
{
    public function index()
    {
        return view('pengaturan.akun.index', $this->shareLayout([
            'title'      => 'Akun Saya',
            'breadcrumb' => [
                ['label' => 'Pengaturan', 'url' => '#'],
                ['label' => 'Akun Saya',  'url' => ''],
            ],
        ]));
    }

    public function updateProfil(Request $request)
    {
        $user = auth()->user();

        $data = $request->validate([
            'nama_lengkap' => 'required|string|max:100',
            'email'        => "nullable|email|max:100|unique:users,email,{$user->id}",
            'no_hp'        => 'nullable|string|max:20',
        ]);

        $before = $user->only(['nama_lengkap', 'email', 'no_hp']);
        $user->update($data);

        AuditLog::catat('update_profil', modul: 'pengaturan', tabel: 'users',
            record_id: (string) $user->id, before: $before, after: $data,
            keterangan: 'Update profil sendiri');

        return $this->respondOk('Profil berhasil diperbarui.');
    }

    public function gantiPassword(Request $request)
    {
        $request->validate([
            'password_lama' => 'required',
            'password_baru' => 'required|min:8|confirmed',
        ], [
            'password_baru.confirmed' => 'Konfirmasi password tidak cocok.',
            'password_baru.min'       => 'Password minimal 8 karakter.',
        ]);

        $user = auth()->user();

        if (! Hash::check($request->password_lama, $user->password_hash)) {
            return $this->respondError('Password lama tidak sesuai.');
        }

        $user->update(['password_hash' => Hash::make($request->password_baru)]);

        AuditLog::catat('ganti_password', modul: 'pengaturan', tabel: 'users',
            record_id: (string) $user->id, keterangan: 'Ganti password sendiri');

        return $this->respondOk('Password berhasil diubah.');
    }
}
