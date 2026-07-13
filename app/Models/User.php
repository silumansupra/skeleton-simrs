<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    use SoftDeletes;

    protected $fillable = [
        'nip', 'username', 'password_hash', 'nama_lengkap',
        'email', 'no_hp', 'avatar', 'is_active',
        'last_login_at', 'last_login_ip', 'failed_attempts',
        'locked_until', 'reset_token', 'reset_token_expires_at',
        'created_by', 'updated_by',
    ];

    protected $hidden = ['password_hash', 'remember_token', 'reset_token'];

    protected $casts = [
        'is_active'              => 'boolean',
        'last_login_at'          => 'datetime',
        'locked_until'           => 'datetime',
        'reset_token_expires_at' => 'datetime',
        'failed_attempts'        => 'integer',
    ];

    // Laravel auth pakai kolom 'password' — kita override
    public function getAuthPassword(): string
    {
        return $this->password_hash;
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'user_roles')
            ->withPivot('assigned_by', 'assigned_at')
            ->withTimestamps('assigned_at', 'assigned_at');
    }

    public function menuOverrides()
    {
        return $this->hasMany(UserMenuOverride::class);
    }

    public function isLocked(): bool
    {
        return $this->locked_until && $this->locked_until->isFuture();
    }

    public function isSuperAdmin(): bool
    {
        return $this->roles()->where('kode', 'superadmin')->exists();
    }

    /**
     * Ambil permission user untuk menu tertentu (berdasarkan role tertinggi + override).
     * Permission: 0=tidak ada, 1=lihat, 3=lihat+edit, 7=full
     */
    public function getMenuPermission(string $menuKode): int
    {
        if ($this->isSuperAdmin()) return 7;

        $menu = Menu::where('kode', $menuKode)->first();
        if (! $menu) return 0;

        // Cek user override dulu
        $override = $this->menuOverrides()->where('menu_id', $menu->id)->first();
        if ($override) return $override->permission;

        // Ambil permission tertinggi dari semua role user
        $roleIds = $this->roles()->pluck('roles.id');
        $perm = \DB::table('role_menu_access')
            ->whereIn('role_id', $roleIds)
            ->where('menu_id', $menu->id)
            ->max('permission');

        return (int) ($perm ?? 0);
    }
}
