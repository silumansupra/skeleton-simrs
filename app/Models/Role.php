<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $fillable = ['kode', 'nama', 'deskripsi', 'is_active'];
    protected $casts    = ['is_active' => 'boolean'];

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_roles');
    }

    public function menuAccess()
    {
        return $this->hasMany(RoleMenuAccess::class);
    }
}
