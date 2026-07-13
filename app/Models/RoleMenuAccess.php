<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RoleMenuAccess extends Model
{
    public $timestamps  = false;
    protected $table    = 'role_menu_access';
    protected $fillable = ['role_id', 'menu_id', 'permission'];
    protected $casts    = ['permission' => 'integer'];
}
