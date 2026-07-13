<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    protected $fillable = ['parent_id', 'kode', 'label', 'icon', 'url', 'urutan', 'is_active'];
    protected $casts    = ['is_active' => 'boolean', 'urutan' => 'integer'];

    public function parent()
    {
        return $this->belongsTo(Menu::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Menu::class, 'parent_id')->orderBy('urutan');
    }

    public function roleAccess()
    {
        return $this->hasMany(RoleMenuAccess::class);
    }
}
